<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Policies\TeamPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class TeamMemberController extends Controller
{
    public function __construct(readonly private Redirector $redirectRouter)
    {
    }

    public function destroy(
        Request $request,
        TeamPolicy $teamPolicy,
        Team $team,
        User $member
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($teamPolicy->removeTeamMember($user, $team, $member), 403);

        $team->members()->detach($member);

        // Set current team to another team
        $newCurrentTeam = $member->fresh()->teams()->first();

        $member->current_team_id = $newCurrentTeam->id;

        $member->save();

        return $this->redirectRouter->route('team.edit');
    }
}
