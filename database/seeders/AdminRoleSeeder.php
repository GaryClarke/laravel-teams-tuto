<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::findOrCreate(Role::TEAM_ADMIN);

        $updateTeam = Permission::findOrCreate(Permission::UPDATE_TEAM);
        $viewMembers = Permission::findOrCreate(Permission::VIEW_TEAM_MEMBERS);
        $removeMembers = Permission::findOrCreate(Permission::REMOVE_TEAM_MEMBERS);
        $inviteMembers = Permission::findOrCreate(Permission::INVITE_TO_TEAM);
        $revokeInvitation = Permission::findOrCreate(Permission::REVOKE_INVITATION);

        $role->givePermissionTo($updateTeam);
        $role->givePermissionTo($viewMembers);
        $role->givePermissionTo($removeMembers);
        $role->givePermissionTo($inviteMembers);
        $role->givePermissionTo($revokeInvitation);
    }
}
