<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class MemberRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::findOrCreate('team member');
        $viewMembers = Permission::findOrCreate(Permission::VIEW_TEAM_MEMBERS);

        $role->givePermissionTo($viewMembers);
    }
}
