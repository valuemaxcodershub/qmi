<?php

namespace App\Http\Middleware;

use Closure;
use App\CPU\Helpers;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }else{
            // abort(404);
            return redirect()->route('login', ['tab' => 'admin']);
        }
    }
}
