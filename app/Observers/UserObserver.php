<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class UserObserver
{
    public function __construct(
        private PermissionRegistrar $permissionRegistrar
    ) {
    }

    public function created(User $user): void
    {
        $team = Team::create(['name' => $user->name]);

        $user->teams()->attach($team);

        $user->current_team_id = $team->id;

        $user->save();

        $this->permissionRegistrar->setPermissionsTeamId($team->id);

        $user->assignRole(Role::TEAM_ADMIN);
    }

    public function deleting(User $user): void
    {
        $user->teams()->detach();
    }
}
