<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Make sure stateful Sanctum requests work for the SPA on a
        // different origin during dev. Token-based requests don't need it
        // but enabling it doesn't hurt.
        $middleware->statefulApi();

        // Sanctum tokens are sent in the Authorization header — no CSRF
        // protection needed for API endpoints since they don't use
        // session cookies.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force every error response on /api/* into a consistent JSON
        // envelope. The SPA can rely on { message, errors? } shape always.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            // Validation: 422 with field errors.
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }

            // Auth failure: 401, generic message.
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Route-model-binding miss or explicit abort(404).
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'Resource not found.',
                ], 404);
            }

            // Any other HTTP-flavoured exception (403, 405, 429, ...).
            if ($e instanceof HttpException) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Request failed.',
                ], $e->getStatusCode());
            }

            // Anything else is a real bug. In production we don't want
            // stack traces leaking, so we hide details unless APP_DEBUG.
            $payload = ['message' => 'Server error.'];
            if (config('app.debug')) {
                $payload['exception'] = get_class($e);
                $payload['detail']    = $e->getMessage();
            }
            return response()->json($payload, 500);
        });
    })->create();
