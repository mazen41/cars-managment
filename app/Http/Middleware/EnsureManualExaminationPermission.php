<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureManualExaminationPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $inspector = $request->auth_inspector ?? $request->user()?->carInspector;

        if ($inspector && !$inspector->canUseManualExaminations()) {
            abort(403, 'Manual examinations are not enabled for this inspection center.');
        }

        return $next($request);
    }
}
