<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsMiddleware
{
    /**
     * Force HTTPS in staging and production environments.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() || config('app.force_https', false)) {
            // Check if request is not secure and forwarded proto is not https
            $isHttps = $request->isSecure() || $request->header('X-Forwarded-Proto') === 'https';

            if (! $isHttps) {
                return redirect()->secure($request->getRequestUri(), Response::HTTP_MOVED_PERMANENTLY);
            }
        }

        return $next($request);
    }
}
