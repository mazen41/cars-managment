<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * The inspector API (and all other API routes) use JWT Bearer token auth,
     * not session/cookie auth, so CSRF protection is irrelevant and must be
     * excluded to avoid false-positive 419 CSRF mismatch errors.
     *
     * @var array
     */
    protected $except = [
        // All API routes: JWT Bearer auth, no session/cookie involved
        'api/*',
        '/api/*',

        // Payment gateway callbacks (third-party POST-backs, no CSRF token possible)
        '/sslcommerz*',
        '/config_content',
        '/paytm*',
        '/payhere*',
        '/stripe*',
        '/iyzico*',
        '/payfast*',
        '/bkash*',
        '/aamarpay*',
        '/mock_payments',
        '/apple-callback',
        '/lnmo*',
        '/rozer*',
        '/phonepe*',
        '/import-data',
    ];
}
