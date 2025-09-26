<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCommitteeOrCoordinator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if user is authenticated and has lecturer profile
        if (!$user || !$user->lecturer) {
            abort(403, 'Access denied. Lecturer profile required.');
        }

        // Check if lecturer is committee member or coordinator
        if (!$user->lecturer->isCommittee && !$user->lecturer->isCoordinator) {
            abort(403, 'Access denied. Only committee members and coordinators can access placement applications.');
        }

        return $next($request);
    }
}
