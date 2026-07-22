<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'sales.view', 'sales.create',
            'purchases.view', 'purchases.create',
            'products.view', 'products.manage',
            'customers.view', 'customers.manage',
            'suppliers.view', 'suppliers.manage',
            'reports.view', 'reports.export',
            'configuration.manage',
            'users.manage', 'users.edit',
            'audit.view',
            'employee-payments.manage',
            'accounts-receivable.manage',
            'accounts-payable.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('administrador');
        $gerente = Role::findOrCreate('gerente');
        $vendedor = Role::findOrCreate('vendedor');
        $contador = Role::findOrCreate('contador');

        $admin->syncPermissions(Permission::all());
        $gerente->syncPermissions([
            'dashboard.view', 'sales.view', 'sales.create',
            'purchases.view', 'purchases.create',
            'products.view', 'products.manage',
            'customers.view', 'customers.manage',
            'suppliers.view', 'suppliers.manage',
            'reports.view', 'reports.export',
        ]);
        $vendedor->syncPermissions([
            'dashboard.view', 'sales.view', 'sales.create',
            'products.view', 'customers.view', 'customers.manage',
        ]);
        $contador->syncPermissions([
            'dashboard.view', 'sales.view', 'purchases.view',
            'reports.view', 'reports.export',
            'accounts-receivable.manage',
            'accounts-payable.manage',
        ]);

        Setting::set('company_name', 'Mi Negocio C.A.');
        Setting::set('company_rif', 'J-12345678-9');
        Setting::set('company_address', 'Caracas, Venezuela');
        Setting::set('company_phone', '+58 412-0000000');
        Setting::set('tax_rate', '16');
        Setting::set('invoice_prefix', 'F');
        Setting::set('exchange_rate_usd_ves', '45.50');
        Setting::set('exchange_rate_eur_usd', '1.08');
        Setting::set('support_whatsapp', '+584242768464');
        Setting::set('support_email', 'antoniolenovo115@gmail.com');
        Setting::set('scanner_enabled', '1');
        Setting::set('scanner_scope', 'both');
        Setting::set('scanner_min_length', '4');
        Setting::set('credit_system_enabled', '1');
        Setting::set('credit_late_fee_usd', '1');
        Setting::set('credit_initial_by_percentage', '0');
        Setting::set('credit_initial_percentage', '10');

        $user = User::query()->firstOrCreate(
            ['email' => 'admin@negocio.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        $user->assignRole('administrador');
    }
}
