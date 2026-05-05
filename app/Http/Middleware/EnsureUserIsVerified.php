<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class EnsureUserIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        //$hasVerifiedEmail = $user->email ? $user->hasVerifiedEmail() : true;
        $hasVerifiedPhone = $user->phone ? $user->phone_verified_at !== null : true;

        if (!( $hasVerifiedPhone)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'You must verify your phone number or email.'], 403)
                : Redirect::route('verification.notice');
        }

        return $next($request);
    }
}
