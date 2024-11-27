<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AlreadyLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // If the user is already logged in and tries to access the login page, redirect them to the home page
        if (Session()->has('loginId') && (url('/login') == $request->url())) {
            return redirect('/');
        } else if (Session()->has('loginId') && (url('/admin/login') == $request->url())) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
