<?php

declare(strict_types=1);

use App\Http\Middleware\ForceHttpsMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => null);

        // Trust all reverse proxies (essential for Render / Vercel / Cloudflare / Load Balancers)
        $middleware->trustProxies(at: '*');

        // Apply TLS enforcement and security headers globally
        $middleware->append(ForceHttpsMiddleware::class);
        $middleware->append(SecurityHeadersMiddleware::class);

        // Exempt stateless API routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'hrm/*',
            'v1/*',
        ]);

        // API group rate limiting
        $middleware->api(prepend: [
            // API specific middleware
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// Ensure storage path points to writable /tmp directory when deployed on Vercel or serverless
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || env('VERCEL')) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
