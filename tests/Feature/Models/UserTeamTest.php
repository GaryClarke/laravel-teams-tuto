<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;

it('has teams', function () {
    $user = User::factory()->create();

    $team = Team::factory()->create();

    $user->teams()->attach($team);

    expect($user->teams)
        ->toHaveCount(2)
        ->last()->name->toBe($team->name);
});
