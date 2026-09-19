<?php

namespace App\Http\Middleware;

use App\Services\VisitorTrackerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitorMetrics
{
    /**
     * Handle an incoming request and track unique visitor sessions.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->isMethod('GET') &&
            ! $request->is('api/*') &&
            ! $request->is('up') &&
            ! $request->expectsJson() &&
            ! $request->ajax()
        ) {
            try {
                if ($request->hasSession() && ! $request->session()->has('counted_visit')) {
                    $request->session()->put('counted_visit', true);
                    VisitorTrackerService::recordVisit();
                }
            } catch (\Throwable $e) {
                // Fail-safe: never block user requests if session/analytics fails
            }
        }

        return $next($request);
    }
}
