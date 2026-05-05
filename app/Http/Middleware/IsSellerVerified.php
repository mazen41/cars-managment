<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsSellerVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::user()->user_type != 'seller') {
            return $this->unverified($request, translate("You are not a seller"));
        }

        if(!Auth::user()->shop->verification_status) {
            return $this->unverified($request, translate("Your store is not verified"));
        }

        return $next($request);
    }

    public function unverified(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()
                ->json([
                    'success' => false,
                    'message' => $message
                ], 403);
        }
        flash()->error($message);
        return redirect()->back();
    }
}
