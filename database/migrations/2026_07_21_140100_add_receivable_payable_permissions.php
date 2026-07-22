<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $newPermissions = [
            'accounts-receivable.manage',
            'accounts-payable.manage',
        ];

        foreach ($newPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('administrador', 'web');
        $contador = Role::findOrCreate('contador', 'web');

        $admin?->givePermissionTo($newPermissions);
        $contador?->givePermissionTo($newPermissions);
    }

    public function down(): void
    {
        //
    }
};
