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
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        // Configure the CSRF token validation middleware.
        // $middleware->validateCsrfTokens([
        //     '/admin/*',
        //     '/api/*',
        //     '/wechat/*',
        // ]);
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
