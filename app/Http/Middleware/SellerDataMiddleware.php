<?php

namespace App\Http\Middleware;

use Closure;
use App\CPU\Helpers;
use App\Services\SellerService;
use Illuminate\Support\Facades\Auth;

class SellerDataMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('seller')->check()) {
            $sellerService = new SellerService();
            // Share data with the views or perform other logic
            view()->share('loggedSeller', $sellerService->getSeller(Auth::guard('seller')->id()));
        }
        return $next($request);
    }
}
