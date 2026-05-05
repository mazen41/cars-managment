<?php

namespace App\Exceptions;

use App\Exceptions\Inspector\InspectorException;
use App\Utility\NgeniusUtility;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        // Handle custom inspector exceptions
        if ($e instanceof InspectorException) {
            return $e->render();
        }

        if ($e instanceof Redirectingexception) {
            return redirect()->back();
        }
        else
        {
            if ($e instanceof TooManyRequestsHttpException) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? null;

                if ($retryAfter) {
                    $retryAfterInSeconds = is_numeric($retryAfter) ? (int) $retryAfter : strtotime($retryAfter) - time();
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => translate('Please wait')." ".$retryAfterInSeconds ." ".translate('seconds before retrying'),
                            'retry_after' => $retryAfterInSeconds,
                        ], 429);
                    }

                }
            }

            return parent::render($request, $e);
        }
    }
}
