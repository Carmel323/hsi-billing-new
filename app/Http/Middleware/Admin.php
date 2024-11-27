<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Admin
{
  public function handle(Request $request, Closure $next)
  {
    if (!Session::has('loginAdmin')) {

      return redirect()->route('incorrect.admin.user');
    }

    return $next($request);
  }
}
