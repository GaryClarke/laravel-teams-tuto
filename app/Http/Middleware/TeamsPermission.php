<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

readonly class TeamsPermission
{
    public function __construct(
        private PermissionRegistrar $permissionRegistrar
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $this->permissionRegistrar->setPermissionsTeamId($user->currentTeam->id);
        }

        return $next($request);
    }
}
