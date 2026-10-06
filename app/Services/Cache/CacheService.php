<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache duration constants (in seconds).
     */
    public const TTL_SHORT = 300;       // 5 minutes
    public const TTL_MEDIUM = 3600;     // 1 hour
    public const TTL_LONG = 86400;      // 24 hours
    public const TTL_WEEK = 604800;     // 7 days

    /**
     * Retrieve or remember an item in the cache.
     * Supports tagging when supported by the cache store (e.g., Redis).
     *
     * @param string $key
     * @param int $seconds
     * @param Closure $callback
     * @param array<string> $tags
     * @return mixed
     */
    public function remember(string $key, int $seconds, Closure $callback, array $tags = []): mixed
    {
        if (!empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->remember($key, $seconds, $callback);
        }

        return Cache::remember($key, $seconds, $callback);
    }

    /**
     * Put an item in cache.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @param array<string> $tags
     * @return bool
     */
    public function put(string $key, mixed $value, int $seconds, array $tags = []): bool
    {
        if (!empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->put($key, $value, $seconds);
        }

        return Cache::put($key, $value, $seconds);
    }

    /**
     * Retrieve an item from cache.
     *
     * @param string $key
     * @param mixed $default
     * @param array<string> $tags
     * @return mixed
     */
    public function get(string $key, mixed $default = null, array $tags = []): mixed
    {
        if (!empty($tags) && $this->supportsTags()) {
            return Cache::tags($tags)->get($key, $default);
        }

        return Cache::get($key, $default);
    }

    /**
     * Invalidate a single cache key.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Invalidate all cache items tagged with given tags (Redis only).
     *
     * @param array<string>|string $tags
     * @return bool
     */
    public function flushTags(array|string $tags): bool
    {
        if ($this->supportsTags()) {
            return Cache::tags((array) $tags)->flush();
        }

        return false;
    }

    /**
     * Check if the current cache driver supports tags.
     */
    public function supportsTags(): bool
    {
        return Cache::supportsTags();
    }
}
