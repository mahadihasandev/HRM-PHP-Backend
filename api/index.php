<?php

declare(strict_types=1);

// Normalize script name so Laravel route matching resolves exact URIs
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Ensure writable directories exist in /tmp on ephemeral serverless execution
$tmpDirectories = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/app/public',
    '/tmp/storage/app/private',
    '/tmp/storage/framework',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/bootstrap',
    '/tmp/bootstrap/cache',
];

foreach ($tmpDirectories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Forward execution to public/index.php
require __DIR__ . '/../public/index.php';
