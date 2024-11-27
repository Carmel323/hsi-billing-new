<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;


class Partner
{
    
    public function handle(Request $request, Closure $next): Response
    {

        if (!Session::has('loginPartner')) {

            return redirect()->route('incorrect.partner.user');
        }

        return $next($request);



    }
}
