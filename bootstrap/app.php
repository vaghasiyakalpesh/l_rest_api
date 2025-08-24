<?php

use App\Http\Middleware\BypassMaintenanceForApi;
use App\Providers\AuthServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        AuthServiceProvider::class
    ])
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(
            PreventRequestsDuringMaintenance::class,
            BypassMaintenanceForApi::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Handle NotFoundHttpException with dynamic model detection
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {

                $path = $request->path();
                $method = $request->method();

                // Pattern to match API resource routes: /api/v1/{resource}/{id}
                if (
                    preg_match('#api/v1/([a-zA-Z]+)/\d+#', $path, $matches) &&
                    in_array($method, ['GET', 'PUT', 'PATCH', 'DELETE'])
                ) {

                    $resourceName = $matches[1]; // e.g., 'bookings', 'users', 'orders'
    
                    // Convert plural resource name to singular model name
                    $modelName = ucfirst(rtrim($resourceName, 's')); // 'bookings' -> 'Booking'
    
                    return response()->json([
                        'success' => false,
                        'message' => "{$modelName} not found",
                        'error' => "The requested {$modelName} was not found",
                        'status_code' => 404
                    ], 404);
                }

                // Default route not found response
                return response()->json([
                    'success' => false,
                    'message' => 'Route not found',
                    'error' => 'The requested endpoint does not exist',
                    'status_code' => 404
                ], 404);
            }
        });

        // Handle ValidationException
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'status_code' => 422
                ], 422);
            }
        });

        // Handle AuthenticationException
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error' => 'Please login to access this route',
                    'status_code' => 401
                ], 401);
            }
        });

        // Handle AuthorizationException
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'error' => $e->getMessage() ?: 'You do not have permission to perform this action',
                    'status_code' => 403
                ], 403);
            }
        });

        // Handle NotFoundHttpException (for route not found)
        // $exceptions->render(function (NotFoundHttpException $e, $request) {
        //     if ($request->expectsJson() || $request->is('api/*')) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Route not found',
        //             'error' => 'The requested endpoint does not exist',
        //             'status_code' => 404
        //         ], 404);
        //     }
        // });
    
        // Handle all other exceptions
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {

                // Debug information in development
                if (config('app.debug')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Internal server error',
                        'error' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                        'status_code' => 500
                    ], 500);
                }

                // Production error response
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error',
                    'error' => 'Something went wrong. Please try again later.',
                    'status_code' => 500
                ], 500);
            }
        });

    })->create();