<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckController extends BaseApiController
{
    /**
     * Return comprehensive application health status.
     */
    public function __invoke(): JsonResponse
    {
        $dbStatus = 'healthy';
        $dbError = null;

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $dbStatus = 'unreachable';
            $dbError = config('app.debug') ? $e->getMessage() : 'Database connection error';
        }

        $redisStatus = 'healthy';
        $redisError = null;

        try {
            $testKey = 'health_ping_' . hrtime(true);
            Cache::put($testKey, 'pong', 5);
            $val = Cache::get($testKey);
            Cache::forget($testKey);

            if ($val !== 'pong') {
                $redisStatus = 'degraded';
            }
        } catch (Throwable $e) {
            $redisStatus = 'unreachable';
            $redisError = config('app.debug') ? $e->getMessage() : 'Cache driver error';
        }

        $isHealthy = $dbStatus === 'healthy' && $redisStatus === 'healthy';

        $response = $this->successResponse([
            'status' => $isHealthy ? 'operational' : 'degraded',
            'environment' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => [
                    'status' => $dbStatus,
                    'error' => $dbError,
                ],
                'cache' => [
                    'status' => $redisStatus,
                    'driver' => config('cache.default'),
                    'error' => $redisError,
                ],
            ],
            'php_version' => PHP_VERSION,
            'framework' => 'Laravel ' . app()->version(),
        ], 'API operational status');
        return $response->setStatusCode($isHealthy ? 200 : 503);
    }
}
