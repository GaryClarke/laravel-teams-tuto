<?php

use App\Http\Middleware\TeamsPermission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;
use function Pest\Laravel\actingAs;

it('can remove a member from the team', function () {
    $user = User::factory()->create();

    $userTeam = $user->currentTeam;

    $newMember = User::factory()->create();

    $userTeam->members()->attach($newMember);

    $newMember->currentTeam()->associate($userTeam)->save();

    actingAs($user)
        ->delete(route('team.members.destroy', [$userTeam, $newMember]))
        ->assertRedirect();

    expect($userTeam->fresh()->members->contains($newMember))
        ->toBeFalse()
        ->and($newMember->fresh()->current_team_id)
        ->not->toEqual($user->current_team_id);
});

it('can not remove a member from the team without permission', function () {
    $user = User::factory()->create();
    $userTeam = $user->currentTeam;

    $newMember = User::factory()->create();
    $userTeam->members()->attach($newMember);

    $anotherMember = User::factory()->create();

    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($userTeam->id);

    actingAs($anotherMember)
        ->withoutMiddleware(TeamsPermission::class)
        ->delete(route('team.members.destroy', [$userTeam, $newMember]))
        ->assertForbidden();
});

it('can not remove self from the team', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->delete(route('team.members.destroy', [$user->currentTeam, $user]))
        ->assertForbidden();
});
