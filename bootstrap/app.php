<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Psr\Log\LogLevel;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        // $middleware->authenticateSessions();
        $middleware->throttleWithRedis();
        $middleware->alias([
            'abilities' => Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
        $middleware->web(append: [
        ]);
        $middleware->api(prepend: [
        ]);
        // 后台中间件组
        $middleware->appendToGroup('admin', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        // Configure the CSRF token validation middleware.
        $middleware->validateCsrfTokens([
            '/admin/*',
            '/api/*',
            '/wechat/*',
        ]);
        // Configure the cookie encryption middleware.
        // $middleware->encryptCookies([
        //     //
        // ]);
        // Configure the trusted proxies for the application.
        $middleware->trustProxies([
            '10.0.0.0/8',
            '100.64.0.0/10',
            '172.16.0.0/16',
            '192.168.0.0/16',
        ]);
        // Configure the URL signature validation middleware.
        // $middleware->validateSignatures([
        //     'fbclid',
        //     'utm_campaign',
        //     'utm_content',
        //     'utm_medium',
        //     'utm_source',
        //     'utm_term',
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->level(\PDOException::class, LogLevel::CRITICAL);
    })->create();
