<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session()->has('loginId')) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return redirect('/admin/login')->with('fail', 'You have to login first');
            }
        }
        return $next($request);
    }
}
