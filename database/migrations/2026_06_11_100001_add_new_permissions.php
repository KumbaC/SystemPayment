<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $newPermissions = ['users.edit', 'audit.view', 'employee-payments.manage'];

        foreach ($newPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('administrador', 'web');
        $admin?->givePermissionTo($newPermissions);
    }

    public function down(): void
    {
        //
    }
};
