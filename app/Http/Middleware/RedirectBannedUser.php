<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBannedUser
{
    /**
     * Handle an incoming request.
     *
     * Restrict banned users to only the appeal routes.
     * Non-banned users and guests are unaffected.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isBanned() && ! $request->is('appeal')) {
            return redirect()->route('appeal.create');
        }

        return $next($request);
    }
}
