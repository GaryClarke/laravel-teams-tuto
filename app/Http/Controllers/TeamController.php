<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SetCurrentTeamRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Models\Team;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TeamController extends Controller
{
    use AuthorizesRequests;

    public function setCurrent(
        SetCurrentTeamRequest $request,
        Team $team,
        Redirector $router
    ): RedirectResponse {
        $user = $request->user(); // Get the currently authenticated user
        $this->authorizeForUser($user, TeamPolicy::SET_CURRENT_TEAM, $team);

        $user->currentTeam()->associate($team); // Associate the user with the new team
        $user->save(); // Save the user model to persist changes

        return $router->back();
    }

    public function edit(Request $request): View
    {
        return view('team.edit', [
            'team' => $request->user()->currentTeam,
        ]);
    }

    public function update(TeamUpdateRequest $request, Team $team, Redirector $router): RedirectResponse
    {
        $newTeamName = $request->get('name');

        $team->update(['name' => $newTeamName]);

        return $router->back()->with('status', Team::STATUS_UPDATED);
    }

    public function leave(
        Request $request,
        Team $team,
        TeamPolicy $teamPolicy,
        Redirector $router
    ): RedirectResponse {
        $user = $request->user();

        // throw new HttpException(403, $message, null, $headers);
        abort_unless($teamPolicy->leaveTeam($user, $team), 403);

        $user->teams()->detach($team);

        // Set current team to another team
        $newCurrentTeam = $user->fresh()->teams()->first();

        $user->current_team_id = $newCurrentTeam->id;

        $user->save();

        return $router->route('dashboard');
    }
}
