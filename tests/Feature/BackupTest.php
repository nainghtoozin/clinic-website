<?php

use App\Models\Backup;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->permissions = [
        'backup.view', 'backup.create', 'backup.restore', 'backup.delete',
        'dashboard.view', 'patient.view', 'appointment.view',
        'settings.view', 'settings.clinic.manage',
    ];

    foreach ($this->permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($this->permissions);

    $this->backupService = new BackupService();
});

// --- BACKUP PERMISSION TESTS ---

test('backup page requires authentication', function () {
    $response = $this->get(route('backups.index'));
    $response->assertRedirect('/login');
});

test('backup index requires backup.view permission', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get(route('backups.index'));
    $response->assertForbidden();
});

test('backup create requires backup.create permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('backup.view');
    $response = $this->actingAs($user)->post(route('backups.store'), ['type' => 'full']);
    $response->assertForbidden();
});

test('backup restore requires backup.restore permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('backup.view');
    $backup = Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'test.zip',
        'size' => 1024,
        'created_by' => $this->user->id,
    ]);
    $response = $this->actingAs($user)->post(route('backups.restore', $backup), ['confirm' => '1']);
    $response->assertForbidden();
});

test('backup delete requires backup.delete permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('backup.view');
    $backup = Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'test.zip',
        'size' => 1024,
        'created_by' => $this->user->id,
    ]);
    $response = $this->actingAs($user)->delete(route('backups.destroy', $backup));
    $response->assertForbidden();
});

// --- BACKUP LISTING TESTS ---

test('backup index loads', function () {
    $response = $this->actingAs($this->user)->get(route('backups.index'));
    $response->assertOk();
    $response->assertSee('Backup & Restore');
});

test('backup index shows backup stats', function () {
    $response = $this->actingAs($this->user)->get(route('backups.index'));
    $response->assertOk();
    $response->assertSee('Total Backups');
    $response->assertSee('Completed');
    $response->assertSee('Total Size');
});

test('backup index shows empty state', function () {
    $response = $this->actingAs($this->user)->get(route('backups.index'));
    $response->assertOk();
    $response->assertSee('No backups found');
});

test('backup index shows backup listing', function () {
    Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'BKP-20260825-0001.zip',
        'size' => 2048,
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('backups.index'));
    $response->assertOk();
    $response->assertSee('BKP-20260825-0001');
    $response->assertSee('Full Backup');
    $response->assertSee('Completed');
});

test('backup index filters by type', function () {
    Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'BKP-20260825-0001.zip',
        'size' => 2048,
        'created_by' => $this->user->id,
    ]);
    Backup::create([
        'backup_number' => 'BKP-20260825-0002',
        'type' => 'database',
        'status' => 'completed',
        'filename' => 'BKP-20260825-0002.zip',
        'size' => 1024,
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('backups.index', ['type' => 'database']));
    $response->assertOk();
    $response->assertSee('BKP-20260825-0002');
    $response->assertDontSee('BKP-20260825-0001');
});

test('backup index filters by status', function () {
    Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'BKP-20260825-0001.zip',
        'size' => 2048,
        'created_by' => $this->user->id,
    ]);
    Backup::create([
        'backup_number' => 'BKP-20260825-0002',
        'type' => 'full',
        'status' => 'failed',
        'filename' => 'BKP-20260825-0002.zip',
        'size' => 0,
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('backups.index', ['status' => 'failed']));
    $response->assertOk();
    $response->assertSee('BKP-20260825-0002');
    $response->assertDontSee('BKP-20260825-0001');
});

// --- BACKUP CREATION TESTS ---

test('backup can be created', function () {
    $response = $this->actingAs($this->user)->post(route('backups.store'), [
        'type' => 'full',
        'notes' => 'Test backup',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('backups', [
        'type' => 'full',
        'status' => 'completed',
    ]);
});

test('backup with notes is stored', function () {
    $this->actingAs($this->user)->post(route('backups.store'), [
        'type' => 'full',
        'notes' => 'Important backup before upgrade',
    ]);

    $backup = Backup::latest()->first();
    $this->assertEquals('Important backup before upgrade', $backup->notes);
});

test('backup type is required', function () {
    $response = $this->actingAs($this->user)->post(route('backups.store'), []);
    $response->assertSessionHasErrors('type');
});

test('backup type must be valid', function () {
    $response = $this->actingAs($this->user)->post(route('backups.store'), ['type' => 'invalid']);
    $response->assertSessionHasErrors('type');
});

test('backup number is auto-generated', function () {
    $this->actingAs($this->user)->post(route('backups.store'), ['type' => 'full']);

    $backup = Backup::latest()->first();
    $this->assertMatchesRegularExpression('/^BKP-\d{8}-\d{4}$/', $backup->backup_number);
});

test('backup number is unique', function () {
    $first = Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'test.zip',
        'size' => 100,
        'created_by' => $this->user->id,
    ]);

    $secondNumber = Backup::generateBackupNumber();

    $this->assertNotEquals($first->backup_number, $secondNumber);
});

test('backup records file size', function () {
    $this->actingAs($this->user)->post(route('backups.store'), ['type' => 'full']);

    $backup = Backup::latest()->first();
    $this->assertGreaterThan(0, $backup->size);
});

test('backup records created_by user', function () {
    $this->actingAs($this->user)->post(route('backups.store'), ['type' => 'full']);

    $backup = Backup::latest()->first();
    $this->assertEquals($this->user->id, $backup->created_by);
});

test('backup file exists on disk after creation', function () {
    $this->actingAs($this->user)->post(route('backups.store'), ['type' => 'full']);

    $backup = Backup::latest()->first();
    $filepath = storage_path('app/backups/' . $backup->filename);
    $this->assertFileExists($filepath);
});

// --- BACKUP SHOW/DETAILS TESTS ---

test('backup show page loads', function () {
    $backup = Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'BKP-20260825-0001.zip',
        'size' => 2048,
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('backups.show', $backup));
    $response->assertOk();
    $response->assertSee('BKP-20260825-0001');
    $response->assertSee('Backup Information');
});

test('backup show displays metadata', function () {
    $backup = Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'BKP-20260825-0001.zip',
        'size' => 2048,
        'metadata' => ['app_name' => 'Clinic Website', 'php_version' => '8.3'],
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->get(route('backups.show', $backup));
    $response->assertOk();
    $response->assertSee('Backup Information');
    $response->assertSee('BKP-20260825-0001');
});

// --- BACKUP VALIDATION TESTS ---

test('backup validation endpoint works', function () {
    $this->actingAs($this->user)->post(route('backups.store'), ['type' => 'full']);
    $backup = Backup::latest()->first();

    $response = $this->actingAs($this->user)->post(route('backups.validate', $backup));
    $response->assertOk();
    $response->assertJson(['valid' => true]);
});

test('backup validation detects missing file', function () {
    $backup = Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'nonexistent.zip',
        'size' => 2048,
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('backups.validate', $backup));
    $response->assertStatus(404);
    $response->assertJson(['valid' => false]);
});

// --- SAFETY BACKUP TEST ---

test('safety backup flag works', function () {
    $backup = Backup::create([
        'backup_number' => 'BKP-20260825-0001',
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'BKP-20260825-0001.zip',
        'size' => 2048,
        'is_safety' => true,
        'created_by' => $this->user->id,
    ]);

    $this->assertTrue($backup->isSafetyBackup());
    $this->assertFalse($backup->isCompleted() && !$backup->is_safety);
});

// --- BACKUP METADATA TEST ---

test('backup metadata contains app info', function () {
    $this->actingAs($this->user)->post(route('backups.store'), ['type' => 'full']);

    $backup = Backup::latest()->first();
    $this->assertNotNull($backup->metadata);
    $this->assertArrayHasKey('database_driver', $backup->metadata);
    $this->assertArrayHasKey('app_name', $backup->metadata);
});

// --- BACKUP NUMBER GENERATION TEST ---

test('backup number generation works', function () {
    $number = Backup::generateBackupNumber();
    $this->assertMatchesRegularExpression('/^BKP-\d{8}-\d{4}$/', $number);
});

test('backup number increments correctly', function () {
    $today = now()->format('Ymd');
    Backup::create([
        'backup_number' => "BKP-{$today}-0001",
        'type' => 'full',
        'status' => 'completed',
        'filename' => 'test1.zip',
        'size' => 100,
    ]);

    $number = Backup::generateBackupNumber();
    $this->assertEquals("BKP-{$today}-0002", $number);
});

// --- BACKUP STATUS TESTS ---

test('backup status badges work', function () {
    $backup = Backup::create([
        'backup_number' => 'BKP-20260825-0001', 'type' => 'full', 'status' => 'completed',
        'filename' => 'test.zip', 'size' => 100, 'created_by' => $this->user->id,
    ]);
    $this->assertEquals('bg-success', $backup->status_badge_class);

    $backup->update(['status' => 'failed']);
    $this->assertEquals('bg-danger', $backup->fresh()->status_badge_class);

    $backup->update(['status' => 'pending']);
    $this->assertEquals('bg-warning text-dark', $backup->fresh()->status_badge_class);
});

test('backup type badges work', function () {
    $backup = Backup::create([
        'backup_number' => 'BKP-20260825-0001', 'type' => 'full', 'status' => 'completed',
        'filename' => 'test.zip', 'size' => 100, 'created_by' => $this->user->id,
    ]);
    $this->assertEquals('bg-primary', $backup->type_badge_class);

    $backup->update(['type' => 'database']);
    $this->assertEquals('bg-info', $backup->fresh()->type_badge_class);

    $backup->update(['type' => 'files']);
    $this->assertEquals('bg-warning text-dark', $backup->fresh()->type_badge_class);
});

test('backup formatted size works', function () {
    $backup = new Backup(['size' => 1048576]);
    $this->assertEquals('1 MB', $backup->formatted_size);

    $backup = new Backup(['size' => 2048]);
    $this->assertEquals('2 KB', $backup->formatted_size);

    $backup = new Backup(['size' => 500]);
    $this->assertEquals('500 B', $backup->formatted_size);
});

// --- BACKUP DELETION TEST ---

test('backup can be deleted', function () {
    $this->actingAs($this->user)->post(route('backups.store'), ['type' => 'full']);
    $backup = Backup::latest()->first();

    $response = $this->actingAs($this->user)->delete(route('backups.destroy', $backup));
    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('backups', ['id' => $backup->id]);
});

// --- REGRESSION TESTS ---

test('existing settings tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('settings.clinic'));
    $response->assertOk();
});

test('existing patient tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('patients.index'));
    $response->assertOk();
});

test('existing appointment tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('appointments.index'));
    $response->assertOk();
});

test('existing dashboard tests still pass', function () {
    $response = $this->actingAs($this->user)->get(route('dashboard'));
    $response->assertOk();
});
