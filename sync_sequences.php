<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$tables = [
    'attendance_records',
    'employees',
    'users',
    'leave_applications',
    'payslips',
    'payroll_items',
    'payroll_runs',
    'daily_work_diaries',
    'snd_customers',
    'snd_orders',
    'snd_visits',
    'sfm_commitments',
    'sfm_commitment_details',
    'tour_plans',
    'tour_claims',
    'hr_loans',
    'hr_records',
    'hr_record_events',
    'hr_record_files',
    'hr_record_participants',
    'factory_setups',
    'factory_production',
    'factory_safety',
    'vouchers',
    'chart_of_accounts',
    'trainings',
    'notices',
    'shift_exchanges',
    'short_leaves',
    'late_requests',
    'outwork_applications',
    'overtime_rate_logs',
];

foreach ($tables as $table) {
    try {
        $max = DB::table($table)->max('id');
        if ($max) {
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), {$max})");
            echo "Synced {$table} -> max id: {$max}\n";
        }
    } catch (\Throwable $e) {
        echo "Skip {$table}: " . $e->getMessage() . "\n";
    }
}
