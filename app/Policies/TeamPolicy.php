<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public const string SET_CURRENT_TEAM = 'setCurrentTeam';

    /**
     * @param User $user gets automatically passed in
     * @param Team $team
     * @return bool
     */
    public function setCurrentTeam(User $user, Team $team): bool
    {
        return $user->teams->contains($team);
    }

    public function updateTeam(User $user, Team $team): bool
    {
        if (!$user->teams->contains($team)) {
            return false;
        }

        return $user->can(Permission::UPDATE_TEAM);
    }

    public function leaveTeam(User $user, Team $team): bool
    {
        if (!$user->teams->contains($team)) {
            return false;
        }

        return $user->teams->count() >= 2;
    }

    public function removeTeamMember(User $user, Team $team, User $member): bool
    {
        if ($user->id === $member->id) {
            return false;
        }

        if (!$team->members->contains($member)) {
            return false;
        }

        return $user->can(Permission::REMOVE_TEAM_MEMBERS);
    }

    public function inviteToTeam(User $user, Team $team): bool
    {
        if (!$user->teams->contains($team)) {
            return false;
        }

        return $user->can(Permission::INVITE_TO_TEAM);
    }

    public function viewTeamMembers(User $user, Team $team): bool
    {
        if (!$user->teams->contains($team)) {
            return false;
        }

        return $user->can(Permission::VIEW_TEAM_MEMBERS);
    }

    public function revokeInvite(User $user, Team $team): bool
    {
        return $user->can(Permission::REVOKE_INVITATION);
    }

    public function changeMemberRole(User $user, Team $team, User $member): bool
    {
        if (!$team->members->contains($member)) {
            return false;
        }

        return $user->can(Permission::CHANGE_MEMBER_ROLE);
    }
}
