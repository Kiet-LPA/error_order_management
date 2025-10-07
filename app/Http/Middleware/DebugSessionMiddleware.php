<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only log for rental car routes
        if (str_contains($request->path(), 'rental')) {
            \Log::info('Rental request debug', [
                'path' => $request->path(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'Not authenticated',
                'session_id' => session()->getId(),
                'session_driver' => config('session.driver'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $request->headers->all(),
                'cookies' => $request->cookies->all()
            ]);
        }

        return $next($request);
    }
}
