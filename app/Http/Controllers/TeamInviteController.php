<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TeamInviteStoreRequest;
use App\Http\Requests\TeamMemberDestroyRequest;
use App\Mail\TeamInvitation;
use App\Models\Team;
use App\Models\TeamInvite;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamInviteController extends Controller
{
    public function __construct(
        readonly private Redirector $redirectRouter,
        readonly private Mailer $mailer,
        readonly private UrlGenerator $urlGenerator,
    ) {
    }

    public function store(TeamInviteStoreRequest $request, Team $team): RedirectResponse
    {
        $invite = $team->invites()->create([
            'email' => $request->input('email'),
            'token' => Str::random(30),
        ]);

        $url = $this->urlGenerator->temporarySignedRoute(
            'team.invites.accept',
            CarbonImmutable::now()->addDay(),
            ['token' => $invite->token] // query string items
        );

        $this->mailer->to($request->input('email'))->send(new TeamInvitation($invite, $url));

        return $this->redirectRouter->back()->with('status', Team::STATUS_INVITED);
    }

    public function destroy(TeamMemberDestroyRequest $request, Team $team, TeamInvite $invite): RedirectResponse
    {
        $invite->delete();

        return $this->redirectRouter->route('team.edit');
    }

    public function accept(Request $request): RedirectResponse
    {
        dd($request->input('token'));
    }
}
