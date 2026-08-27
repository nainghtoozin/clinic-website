<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Permission::firstOrCreate(['name' => 'invoice.delete', 'guard_name' => 'web']);

        // Assign to admin and super-admin roles
        $superAdmin = Role::where('name', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo('invoice.delete');
        }
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo('invoice.delete');
        }
    }

    public function down(): void
    {
        Permission::where('name', 'invoice.delete')->delete();
    }
};
