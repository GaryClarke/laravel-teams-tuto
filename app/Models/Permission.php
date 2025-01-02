<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public const string UPDATE_TEAM = 'update team';
    public const string VIEW_TEAM_MEMBERS = 'view team members';
    public const string REMOVE_TEAM_MEMBERS = 'remove team members';
    public const string INVITE_TO_TEAM = 'invite to team';
    public const string REVOKE_INVITATION = 'revoke invitation';
    public const string CHANGE_MEMBER_ROLE = 'change member role';
}
