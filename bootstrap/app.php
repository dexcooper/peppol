<?php

use App\Http\Middleware\AlwaysAcceptJson;
use App\Http\Middleware\CamelCase;
use \App\Http\Middleware\UserBelongsToCompany;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', AlwaysAcceptJson::class);
        $middleware->prependToGroup('api', CamelCase::class);

        $middleware->alias([
            'company' => UserBelongsToCompany::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $handler = new \App\Exceptions\Handler(app());
        $exceptions->render(function (Throwable $e, $request) use ($handler) {
            return $handler->render($request, $e);
        });
    })->create();
