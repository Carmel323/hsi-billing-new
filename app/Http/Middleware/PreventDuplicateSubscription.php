<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $currentUrl = $request->url();
        $refererUrl = $request->server('HTTP_REFERER');

        dd($currentUrl, $refererUrl);
        // Check if the request is coming from the same page (indicating a back button click)
        if ($request->server('HTTP_REFERER') === $request->url()) {
            // Redirect the user to another route
            return redirect()->route('partner.plans');
        }

        return $next($request);
    }
}
