<?php

declare(strict_types=1);

use App\Http\Middleware\TeamsPermission;
use App\Mail\TeamInvitation;
use App\Models\Team;
use App\Models\TeamInvite;
use App\Models\User;
use Illuminate\Routing\Middleware\ValidateSignature;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use Illuminate\Support\Str;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;

beforeEach(function () {
    Str::createRandomStringsNormally();
});

it('creates a team invite', function () {
    Mail::fake();
    $user = User::factory()->create();
    $invitedEmail = 'invitee@example.com';

    Str::createRandomStringsUsing(fn() => 'abc');

    actingAs($user)
        ->post(route('team.invites.store', $user->currentTeam), [
            'email' => $invitedEmail,
        ])
        ->assertRedirect();

    Mail::assertSent(TeamInvitation::class, function (TeamInvitation $mail) use ($invitedEmail) {
        return $mail->hasTo($invitedEmail)
            && $mail->teamInvite->token === 'abc';
    });

    assertDatabaseHas('team_invites', [
        'team_id' => $user->currentTeam->id,
        'email' => $invitedEmail,
        'token' => 'abc',
    ]);
});

it('requires a valid email address', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('team.invites.store', $user->currentTeam))
        ->assertSessionHasErrors('email');
});

it('fails to create invite if email already used', function () {
    $inviter = User::factory()->create();

    $inviteeEmail = 'invitee@example.com';

    TeamInvite::factory()->create([
        'team_id' => $inviter->currentTeam->id,
        'email' => $inviteeEmail,
    ]);

    actingAs($inviter)
        ->post(route('team.invites.store', $inviter->currentTeam), [
            'email' => $inviteeEmail,
        ])
        ->assertInvalid();
});

it('creates invite if email already invited to another team', function () {
    $inviter = User::factory()->create();

    $inviteeEmail = 'invitee@example.com';

    $otherTeam = Team::factory()->create();

    TeamInvite::factory()
        ->create([
            'team_id' => $otherTeam->id,
            'email' => $inviteeEmail,
        ]);

    actingAs($inviter)
        ->post(route('team.invites.store', $inviter->currentTeam), [
            'email' => $inviteeEmail,
        ])
        ->assertValid();
});

it('fails to send invite without permission', function () {
    $user = User::factory()->create();

    $anotherTeam = Team::factory()->create();

    $user->teams()->attach($anotherTeam);

    $permissionRegistrar = app(\Spatie\Permission\PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($anotherTeam->id);

    actingAs($user)
        ->withoutMiddleware(TeamsPermission::class)
        ->post(route('team.invites.store', $anotherTeam), [
            'email' => 'another@example.com',
        ])
        ->assertForbidden();
});

it('can revoke an invite', function () {
    // Create a user
    $user = User::factory()->create();

    // Create an invitation for the user's team
    $teamInvite = TeamInvite::factory()->create([
        'team_id' => $user->currentTeam->id,
    ]);

    // Send a delete request to destroy the invite
    // Assert redirected to team.edit page
    actingAs($user)
        ->delete(route('team.invites.destroy', [$user->currentTeam, $teamInvite]))
        ->assertRedirect('/team');

    assertDatabaseMissing('team_invites', [
        'team_id' => $user->currentTeam->id,
        'email' => $teamInvite->email,
        'token' => $teamInvite->token,
    ]);
});

it('can not revoke an invite without permission', function () {
    // User
    $user = User::factory()->create();

    // Other team
    $anotherTeam = Team::factory()->create();

    // Attach user to another team
    $user->teams()->attach($anotherTeam);

    // Invite for other team
    $teamInvite = TeamInvite::factory()->create([
        'team_id' => $anotherTeam->id,
    ]);

    $permissionRegistrar = app(\Spatie\Permission\PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($anotherTeam->id);

    // Acting as user, try to delete the invite
    // Assert forbidden
    actingAs($user)
        ->withoutMiddleware(TeamsPermission::class)
        ->delete(route('team.invites.destroy', [$anotherTeam, $teamInvite]))
        ->assertForbidden();
});

it('fails to accept invite if route is not signed', function () {
    $team = Team::factory()->create();

    $invite = TeamInvite::factory()
        ->for($team)
        ->create();

    $acceptingUser = User::factory()->create();

    actingAs($acceptingUser)
        ->get('/team/invites/accept?token=' . $invite->token)
        ->assertForbidden();
});

it('can accept an invite', function() {
    $team = Team::factory()->create();

    $invite = TeamInvite::factory()
        ->for($team)
        ->create();

    $acceptingUser = User::factory()->create();

    actingAs($acceptingUser)
        ->withoutMiddleware(ValidateSignature::class)
        ->get('/team/invites/accept?token=' . $invite->token)
        ->assertRedirect('/dashboard');

    assertDatabaseMissing('team_invites', [
        'team_id' => $invite->team_id,
        'token' => $invite->token,
        'email' => $invite->email
    ]);

    expect($acceptingUser->teams->contains($invite->team))->toBeTrue()
        ->and($acceptingUser->hasRole(\App\Models\Role::TEAM_MEMBER))->toBeTrue()
        ->and($acceptingUser->current_team_id)->toBe($invite->team->id);
});
