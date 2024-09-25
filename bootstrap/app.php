<?php

use App\Http\Middleware\AddRequestContext;
use App\Http\Middleware\ApiJsonFormatter;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->api([
                ApiJsonFormatter::class,
            ])
            ->append(AddRequestContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $responseData = [
                    'code' => $statusCode = 404,
                    'status' => Response::$statusTexts[$statusCode] ?? 'Not Found',
                    'error' => $e->getMessage(),
                ];
                if (!App::isProduction()) {
                    $responseData['meta'] = [
                        'message' => $e->getMessage(),
                        'exception' => NotFoundHttpException::class,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ];
                }

                return response()->json($responseData, $statusCode);
            }

            return false;
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $responseData = [
                    'code' => $statusCode = 401,
                    'status' => Response::$statusTexts[$statusCode] ?? 'Unauthenticated',
                    'error' => $e->getMessage(),
                ];
                if (!in_array(App::environment(), ['production', 'prod'])) {
                    $responseData['meta'] = [
                        'message' => $e->getMessage(),
                        'exception' => AuthenticationException::class,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ];
                }

                return response()->json($responseData, $statusCode);
            }

            return false;
        });
    })->create();
