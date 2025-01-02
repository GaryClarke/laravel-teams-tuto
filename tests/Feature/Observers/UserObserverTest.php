<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use function Pest\Laravel\assertDatabaseEmpty;

it('creates a personal team when a user is created', function () {
    $user = User::factory()->create([
        'name' => 'Gary'
    ]);

    expect($user->teams)
        ->toHaveCount(1)
        ->first()->name->toBe($user->name);

});

it('removes all team attachments when deleted', function () {
    $user = User::factory()
        ->has(Team::factory()->times(2))
        ->createQuietly(); // Ignores observers

    expect($user->teams)
        ->toHaveCount(2);

    $user->delete();

    assertDatabaseEmpty('team_user');
});

it('sets the current team to the personal team on create', function () {
    $user = User::factory()->create([
        'name' => 'Gary'
    ]);

    expect($user->fresh())
        ->current_team_id->toBe($user->teams->first()->id)
        ->and($user->teams->count())->toBe(1);
});

it('gives the admin role to personal team', function () {
    $user = User::factory()->create();

    expect($user->hasRole('team admin'))->toBeTrue();
});
