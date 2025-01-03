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

it('only updates role if provided', function () {
    $user = User::factory()->create();

    // Won't dispatch model events
    $member = User::factory()->createQuietly();

    $user->currentTeam->members()->attach($member);

    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($user->current_team_id);

    $member->assignRole(\App\Models\Role::TEAM_MEMBER);

    actingAs($user)
        ->patch(route('team.members.update', [$user->currentTeam, $member]), [

        ])
        ->assertRedirect();

    expect($member->hasRole(\App\Models\Role::TEAM_MEMBER))->toBeTrue()
        ->and($member->roles()->count())->toBe(1);
});

it('updates a role', function () {
    $user = User::factory()->create();

    // Won't dispatch model events
    $member = User::factory()->createQuietly();

    $user->currentTeam->members()->attach($member);

    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($user->current_team_id);

    $member->assignRole(\App\Models\Role::TEAM_MEMBER);

    actingAs($user)
        ->patch(route('team.members.update', [$user->currentTeam, $member]), [
            'role' => \App\Models\Role::TEAM_ADMIN
        ])
        ->assertRedirect();

    expect($member->hasRole(\App\Models\Role::TEAM_ADMIN))->toBeTrue()
        ->and($member->roles()->count())->toBe(1);
});

it('does not update the role if no permission', function () {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    $user->currentTeam->members()->attach($anotherUser);

    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($user->current_team_id);

    $anotherUser->assignRole(\App\Models\Role::TEAM_MEMBER);

    actingAs($anotherUser)
        ->withoutMiddleware(TeamsPermission::class)
        ->patch(route('team.members.update', [$user->currentTeam, $user]), [
            'role' => \App\Models\Role::TEAM_ADMIN
        ])
        ->assertForbidden();
});

it('does not update the user if they are not in the team', function () {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    $anotherUser->assignRole(\App\Models\Role::TEAM_MEMBER);

    actingAs($user)
        ->patch(route('team.members.update', [$user->currentTeam, $anotherUser]), [
            'role' => \App\Models\Role::TEAM_ADMIN
        ])
        ->assertForbidden();
});

it('validates the role to make sure it exists', function () {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    $user->currentTeam->members()->attach($anotherUser);

    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($user->current_team_id);

    $anotherUser->assignRole(\App\Models\Role::TEAM_MEMBER);

    actingAs($user)
        ->patch(route('team.members.update', [$user->currentTeam, $anotherUser]), [
            'role' => 'non existent role'
        ])
        ->assertSessionHasErrors(['role']);
});

