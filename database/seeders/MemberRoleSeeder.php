<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class MemberRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::findOrCreate(Role::TEAM_MEMBER);
        $viewMembers = Permission::findOrCreate(Permission::VIEW_TEAM_MEMBERS);

        $role->givePermissionTo($viewMembers);
    }
}
