<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    protected string $backupPath;

    protected array $excludePaths = [
        'logs',
        'framework/views',
        'framework/cache',
        'framework/sessions',
    ];

    protected array $excludeExtensions = [
        'log',
        'cache',
    ];

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');

        if (!File::isDirectory($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true, true);
        }
    }

    public function createBackup(string $type = 'full', ?string $notes = null, bool $isSafety = false): Backup
    {
        $backup = Backup::create([
            'backup_number' => Backup::generateBackupNumber(),
            'type' => $type,
            'status' => 'creating',
            'filename' => '',
            'size' => 0,
            'disk' => 'local',
            'notes' => $notes,
            'metadata' => $this->getMetadata($type),
            'is_safety' => $isSafety,
            'created_by' => auth()->id(),
        ]);

        try {
            $filename = $backup->backup_number . '.zip';
            $filepath = $this->backupPath . DIRECTORY_SEPARATOR . $filename;

            if (!File::isDirectory($this->backupPath)) {
                File::makeDirectory($this->backupPath, 0755, true, true);
            }

            $zip = new ZipArchive();
            $result = $zip->open($filepath, ZipArchive::CREATE);
            if ($result !== true) {
                throw new \RuntimeException('Failed to create backup archive. Error code: ' . $result);
            }

            if ($type === 'full' || $type === 'database') {
                $this->backupDatabase($zip);
            }

            if ($type === 'full' || $type === 'files') {
                $this->backupFiles($zip);
            }

            $this->backupMetadata($zip, $backup);

            $zip->close();

            if (!File::exists($filepath)) {
                throw new \RuntimeException('Backup file was not created.');
            }

            $backup->update([
                'filename' => $filename,
                'size' => File::size($filepath),
                'status' => 'completed',
            ]);

            return $backup->fresh();
        } catch (\Throwable $e) {
            if (isset($zip) && $zip instanceof ZipArchive) {
                try { $zip->close(); } catch (\Throwable) {}
            }

            if (File::exists($filepath)) {
                File::delete($filepath);
            }

            $backup->update([
                'status' => 'failed',
                'metadata' => array_merge($backup->metadata ?? [], [
                    'error' => $e->getMessage(),
                ]),
            ]);

            throw $e;
        }
    }

    protected function backupDatabase(ZipArchive $zip): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] === 'sqlite') {
            $this->backupSqlite($zip, $config);
        } else {
            $this->backupMysql($zip, $config);
        }
    }

    protected function backupSqlite(ZipArchive $zip, array $config): void
    {
        $dbPath = $config['database'] ?? ':memory:';

        if ($dbPath === ':memory:' || !File::exists($dbPath)) {
            $this->backupViaPdo($zip);
            return;
        }

        $zip->addFile($dbPath, 'database/database.sqlite');
    }

    protected function backupMysql(ZipArchive $zip, array $config): void
    {
        $tables = $this->getTables();

        $sql = "-- Clinic Website Database Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Database: " . ($config['database'] ?? 'unknown') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $sql .= $this->getCreateTableSql($table);
            $sql .= $this->getDataSql($table);
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $zip->addFromString('database/clinic_website.sql', $sql);
    }

    protected function backupViaPdo(ZipArchive $zip): void
    {
        $tables = $this->getTables();
        $allData = [];

        foreach ($tables as $table) {
            $allData[$table] = DB::table($table)->get()->toArray();
        }

        $zip->addFromString('database/clinic_website.json', json_encode($allData, JSON_PRETTY_PRINT));
    }

    protected function backupFiles(ZipArchive $zip): void
    {
        $publicPath = storage_path('app/public');

        if (!File::isDirectory($publicPath)) {
            return;
        }

        $files = $this->getFilesRecursive($publicPath);

        foreach ($files as $file) {
            $relativePath = ltrim(str_replace($publicPath, '', $file), DIRECTORY_SEPARATOR);
            $zip->addFile($file, 'files/' . $relativePath);
        }
    }

    protected function backupMetadata(ZipArchive $zip, Backup $backup): void
    {
        $meta = [
            'backup_number' => $backup->backup_number,
            'type' => $backup->type,
            'created_at' => $backup->created_at->toDateTimeString(),
            'app_name' => config('app.name', 'Clinic Website'),
            'app_version' => config('app.version', '1.0.0'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_driver' => config('database.default'),
            'tables' => $this->getTables(),
            'table_counts' => $this->getTableCounts(),
        ];

        $zip->addFromString('metadata.json', json_encode($meta, JSON_PRETTY_PRINT));
    }

    public function validateBackup(string $filepath): array
    {
        $errors = [];

        if (!File::exists($filepath)) {
            return ['Backup file does not exist.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            return ['Invalid ZIP archive.'];
        }

        $hasMetadata = false;
        $hasDatabase = false;
        $hasFiles = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === 'metadata.json') $hasMetadata = true;
            if (str_starts_with($name, 'database/')) $hasDatabase = true;
            if (str_starts_with($name, 'files/')) $hasFiles = true;
        }

        $zip->close();

        if (!$hasMetadata) {
            $errors[] = 'Backup metadata is missing.';
        }

        if (!$hasDatabase && !$hasFiles) {
            $errors[] = 'Backup contains no database or file data.';
        }

        return $errors;
    }

    public function getBackupInfo(string $filepath): ?array
    {
        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            return null;
        }

        $metaContent = $zip->getFromName('metadata.json');
        $zip->close();

        if ($metaContent) {
            return json_decode($metaContent, true);
        }

        return null;
    }

    public function restoreBackup(Backup $backup): bool
    {
        $filepath = $this->backupPath . '/' . $backup->filename;

        if (!File::exists($filepath)) {
            throw new \RuntimeException('Backup file not found.');
        }

        $errors = $this->validateBackup($filepath);
        if (!empty($errors)) {
            throw new \RuntimeException('Backup validation failed: ' . implode(' ', $errors));
        }

        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            throw new \RuntimeException('Failed to open backup archive.');
        }

        $this->restoreDatabaseFromZip($zip);
        $this->restoreFilesFromZip($zip);

        $zip->close();

        return true;
    }

    protected function restoreDatabaseFromZip(ZipArchive $zip): void
    {
        $sqlFile = $zip->getFromName('database/clinic_website.sql');
        if ($sqlFile) {
            $statements = array_filter(array_map('trim', explode(';', $sqlFile)));
            foreach ($statements as $statement) {
                if (!empty($statement) && $statement !== 'SET FOREIGN_KEY_CHECKS = 0' && $statement !== 'SET FOREIGN_KEY_CHECKS = 1') {
                    try {
                        DB::statement($statement);
                    } catch (\Throwable $e) {
                        \Log::warning('Restore SQL statement failed: ' . $e->getMessage());
                    }
                }
            }
            return;
        }

        $jsonFile = $zip->getFromName('database/clinic_website.json');
        if ($jsonFile) {
            $data = json_decode($jsonFile, true);
            if (is_array($data)) {
                DB::beginTransaction();
                try {
                    foreach ($data as $table => $rows) {
                        DB::table($table)->truncate();
                        if (!empty($rows)) {
                            DB::table($table)->insert($rows);
                        }
                    }
                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }
        }
    }

    protected function restoreFilesFromZip(ZipArchive $zip): void
    {
        $publicPath = storage_path('app/public');

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (!str_starts_with($name, 'files/')) {
                continue;
            }

            $relativePath = substr($name, 6);
            if (empty($relativePath)) {
                continue;
            }

            $realRelativePath = realpath($publicPath . '/' . $relativePath);
            $realPublicPath = realpath($publicPath);

            if ($realRelativePath && $realPublicPath && !str_starts_with($realRelativePath, $realPublicPath)) {
                \Log::warning("Path traversal attempt blocked in restore: {$name}");
                continue;
            }

            $destPath = $publicPath . '/' . $relativePath;
            $dir = dirname($destPath);

            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }

            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                File::put($destPath, $content);
            }
        }
    }

    public function deleteBackup(Backup $backup): bool
    {
        $filepath = $this->backupPath . '/' . $backup->filename;

        if (File::exists($filepath)) {
            File::delete($filepath);
        }

        $backup->delete();
        return true;
    }

    protected function getTables(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            return array_column($result, 'name');
        }

        $dbName = $config['database'] ?? 'clinic_website';
        $result = DB::select("SHOW TABLES LIKE '{$dbName}'");

        return [];
    }

    protected function getTableCounts(): array
    {
        $tables = $this->getTables();
        $counts = [];

        foreach ($tables as $table) {
            try {
                $counts[$table] = DB::table($table)->count();
            } catch (\Throwable $e) {
                $counts[$table] = -1;
            }
        }

        return $counts;
    }

    protected function getCreateTableSql(string $table): string
    {
        $result = DB::select("SHOW CREATE TABLE `{$table}`");
        if (!empty($result)) {
            return "\n-- Table: {$table}\n" . $result[0]->{'Create Table'} . ";\n\n";
        }
        return '';
    }

    protected function getDataSql(string $table): string
    {
        $rows = DB::table($table)->get();

        if ($rows->isEmpty()) {
            return '';
        }

        $sql = "LOCK TABLES `{$table}` WRITE;\n";

        $chunks = $rows->chunk(100);
        foreach ($chunks as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                $rowArr = (array) $row;
                $escaped = array_map(function ($value) {
                    if ($value === null) return 'NULL';
                    return "'" . addslashes($value) . "'";
                }, $rowArr);
                $values[] = '(' . implode(', ', $escaped) . ')';
            }
            $sql .= "INSERT INTO `{$table}` VALUES\n" . implode(",\n", $values) . ";\n";
        }

        $sql .= "UNLOCK TABLES;\n";
        return $sql;
    }

    protected function getFilesRecursive(string $directory): array
    {
        $files = [];
        $items = File::allFiles($directory);

        foreach ($items as $item) {
            $relativePath = ltrim(str_replace($directory, '', $item->getPathname()), DIRECTORY_SEPARATOR);

            $excluded = false;
            foreach ($this->excludePaths as $exclude) {
                if (str_starts_with($relativePath, $exclude)) {
                    $excluded = true;
                    break;
                }
            }

            if ($excluded) continue;

            if (in_array($item->getExtension(), $this->excludeExtensions)) {
                continue;
            }

            $files[] = $item->getPathname();
        }

        return $files;
    }

    protected function getMetadata(string $type): array
    {
        return [
            'type' => $type,
            'created_at' => now()->toDateTimeString(),
            'database_driver' => config('database.default'),
            'app_name' => config('app.name', 'Clinic Website'),
        ];
    }
}
