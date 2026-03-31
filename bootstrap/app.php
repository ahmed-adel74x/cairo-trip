<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',   // ← THIS was missing before
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Unauthenticated → bilingual JSON
        $exceptions->render(function (
            AuthenticationException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                $arMessages = require base_path('lang/ar/messages.php');
                $enMessages = require base_path('lang/en/messages.php');

                return response()->json([
                    'success' => false,
                    'message' => [
                        'ar' => $arMessages['unauthenticated'],
                        'en' => $enMessages['unauthenticated'],
                    ],
                ], 401);
            }
        });

        // 404 → bilingual JSON
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                $arMessages = require base_path('lang/ar/messages.php');
                $enMessages = require base_path('lang/en/messages.php');

                return response()->json([
                    'success' => false,
                    'message' => [
                        'ar' => $arMessages['not_found'],
                        'en' => $enMessages['not_found'],
                    ],
                ], 404);
            }
        });

    })->create();