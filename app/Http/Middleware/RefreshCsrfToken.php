<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RefreshCsrfToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();

        if (!$session->token()) {
            $session->regenerateToken();
        }

        return $next($request);
    }
}


