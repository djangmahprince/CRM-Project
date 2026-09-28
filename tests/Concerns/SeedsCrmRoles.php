<?php

namespace Tests\Concerns;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

trait SeedsCrmRoles
{
    protected function seedRoles(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
    }

    protected function userWithRole(string $role, array $attributes = []): User
    {
        $this->seedRoles();

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
