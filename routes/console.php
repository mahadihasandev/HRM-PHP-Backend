<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('hrm:sync-employee-users', function () {
    $employees = \Illuminate\Support\Facades\DB::table('employees')->get();
    $created = 0;
    $existing = 0;
    foreach ($employees as $emp) {
        $email = $emp->email ?: ($emp->employee_full_id . '@smarterp.biz');
        $user = \App\Models\User::where('email', $email)->first();
        if (!$user) {
            \App\Models\User::create([
                'name' => $emp->name,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ]);
            $created++;
        } else {
            $existing++;
        }
    }
    $this->info("Synced users: {$created} created, {$existing} existing. Total: " . count($employees));
})->purpose('Ensure all employees have user accounts with credentials');

