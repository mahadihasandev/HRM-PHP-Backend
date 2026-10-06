<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'HRM API'),
        'version' => '1.0.0',
        'status' => 'healthy',
        'docs' => url('/api/v1/health'),
    ]);
});
