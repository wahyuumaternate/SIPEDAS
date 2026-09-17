<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * Loloskan request jika user memiliki salah satu permission yang diberikan.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        abort_unless(
            $user && collect($permissions)->contains(fn (string $permission) => $user->hasPermission($permission)),
            403
        );

        return $next($request);
    }
}
