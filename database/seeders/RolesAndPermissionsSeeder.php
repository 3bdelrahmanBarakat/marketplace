<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $arrayOfPermissionNames = [
            'ads index', 'ad update', 'ad delete',
            'banners show', 'banner store', 'banner update', 'banner delete',
            'clients show', 'client show', 'client update', 'client delete',
            'admins show', 'admin show', 'admin create', 'Admin update', 'admin delete',
            'companies show', 'company show', 'company update', 'company delete',
            'subcategories show', 'subcategory show', 'subcategory update', 'subcategory delete',
            'gifts show', 'gift add',
            'points show', 'points share',

            

        ];


        $permissions = collect($arrayOfPermissionNames)->map(function ($permission) {
            return ['name' => $permission, 'guard_name' => 'web'];
        });

        Permission::insert($permissions->toArray());
        $role = Role::create(['name' => 'admin'])->givePermissionTo($arrayOfPermissionNames);
    }
}
