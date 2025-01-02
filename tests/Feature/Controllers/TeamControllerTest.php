<?php

declare(strict_types=1);

use App\Http\Middleware\TeamsPermission;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;
use function Pest\Laravel\actingAs;

it('switches the current team for the user', function () {
    $user = User::factory()->create();

    $team = Team::factory()->create();

    $user->teams()->attach($team);

    actingAs($user)
        ->patch(route('team.set-current', $team))
        ->assertRedirect();

    expect($user->current_team_id)->toBe($team->id);
});

it('can not switch to a team that the user does not belong to', function () {
    $user = User::factory()->create();

    $nonUserTeam = Team::factory()->create();

    actingAs($user)
        ->patch(route('team.set-current', $nonUserTeam))
        ->assertStatus(403);

    expect($user->current_team_id)->not->toBe($nonUserTeam->id);
});

it('can update team', function () {
    $user = User::factory()->create();

    $newTeamName = 'New Team Name';

    actingAs($user)
        ->patch(route('team.update', $user->currentTeam), [
            'name' => $newTeamName,
        ])
        ->assertRedirect();

    expect($user->fresh()->currentTeam->name)
        ->toBe($newTeamName);
});

it('can not update a team if they are not a member', function () {
    $user = User::factory()->create();

    $notTheUsersTeam = Team::factory()->create();

    $newTeamName = 'New Team Name';

    actingAs($user)
        ->patch(route('team.update', $notTheUsersTeam), [
            'name' => $newTeamName,
        ])
        ->assertForbidden();

    expect($user->fresh()->currentTeam->name)
        ->not->toBe($newTeamName);
});

it('can not update a team without permission', function () {
    $user = User::factory()->create();

    $anotherTeam = Team::factory()->create();

    $user->teams()->attach($anotherTeam);

    $registrar = app(PermissionRegistrar::class);
    $registrar->setPermissionsTeamId($anotherTeam->id);

    actingAs($user)
        ->withoutMiddleware(TeamsPermission::class)
        ->patch(route('team.update', $anotherTeam), [
            'name' => 'New Team'
        ])
        ->assertForbidden();
});

it('can leave team', function () {
    $user = User::factory()
        ->has(Team::factory())
        ->create();

    $teamToLeave = $user->currentTeam;

    actingAs($user)
        ->post(route('team.leave', $teamToLeave))
        ->assertRedirect('/dashboard');

    $freshUser = $user->fresh(); // Refresh once after the operations affecting the user

    expect($freshUser->teams->contains('id', $teamToLeave->id))->toBeFalse()
        ->and($freshUser->currentTeam->id)->not->toEqual($teamToLeave->id);
});

it('can not leave a team if there is one team remaining', function () {
    $user = User::factory()->create();

    $teamToLeave = $user->currentTeam;

    actingAs($user)
        ->post(route('team.leave', $teamToLeave))
        ->assertForbidden();
});

it('can not leave a team which do not belong to', function () {
    $user = User::factory()->create();

    $anotherTeam = Team::factory()->create();

    actingAs($user)
        ->post(route('team.leave', $anotherTeam))
        ->assertForbidden();
});

it('should show a list of members', function () {
    $user = User::factory()->create();

    $members = User::factory()->times(2)->create();

    $user->currentTeam->members()->attach($members);

    $emails = $members->pluck('email')->toArray();
    $names = $members->pluck('name')->toArray();

    actingAs($user)
        ->get(route('team.edit'))
        ->assertSeeText($emails)
        ->assertSeeText($names);
});


