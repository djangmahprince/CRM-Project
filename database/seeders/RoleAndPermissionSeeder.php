<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $objects = config('crm.objects');
        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($objects as $object) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$object}.{$action}");
            }
        }

        Permission::findOrCreate('records.view-all');
        Permission::findOrCreate('records.manage-all');

        $allPermissions = Permission::query()->pluck('name')->all();

        $matrix = [
            'System Administrator' => $allPermissions,
            'Sales Manager' => [
                ...$this->crud('leads'),
                ...$this->crud('accounts'),
                ...$this->crud('contacts'),
                ...$this->crud('opportunities'),
                ...$this->crud('tasks'),
                ...$this->crud('events'),
                'cases.view',
                'records.view-all',
            ],
            'Sales Representative' => [
                ...$this->crud('leads'),
                ...$this->crud('accounts'),
                ...$this->crud('contacts'),
                ...$this->crud('opportunities'),
                ...$this->crud('tasks'),
                ...$this->crud('events'),
                'cases.view',
            ],
            'Service Representative' => [
                ...$this->crud('cases'),
                ...$this->crud('accounts'),
                ...$this->crud('contacts'),
                ...$this->crud('tasks'),
                ...$this->crud('events'),
                'leads.view',
                'opportunities.view',
            ],
            'Read-Only User' => collect($objects)
                ->map(fn (string $object) => "{$object}.view")
                ->all(),
        ];

        foreach (config('crm.roles') as $roleName) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions($matrix[$roleName] ?? []);
        }
    }

    /**
     * @return list<string>
     */
    private function crud(string $object): array
    {
        return [
            "{$object}.view",
            "{$object}.create",
            "{$object}.update",
            "{$object}.delete",
        ];
    }
}
