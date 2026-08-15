<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCustomerAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('customer')) {
            return redirect()->guest(url('/login'));
        }

        return $next($request);
    }
}
