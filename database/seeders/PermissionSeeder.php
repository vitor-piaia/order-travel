<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'show_orders']);
        Permission::create(['name' => 'store_orders']);
        Permission::create(['name' => 'update_orders']);
        Permission::create(['name' => 'update_status_order']);
        Permission::create(['name' => 'delete_orders']);
        Permission::create(['name' => 'show_cancel_orders']);
        Permission::create(['name' => 'store_cancel_orders']);
        Permission::create(['name' => 'update_status_cancel_order']);

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'writer']);
        $role->givePermissionTo('show_orders');
        $role->givePermissionTo('store_orders');
        $role->givePermissionTo('update_orders');
        $role->givePermissionTo('delete_orders');
        $role->givePermissionTo('show_cancel_orders');
        $role->givePermissionTo('store_cancel_orders');
        $role->givePermissionTo('update_status_cancel_order');
    }
}
