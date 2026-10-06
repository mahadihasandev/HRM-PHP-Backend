<?php

/**
 * Intelligent Data Migration Script: SQLite -> Supabase PostgreSQL
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'users',
    'employees',
    'email_verifications',
    'permissions',
    'employee_permissions',
    'attendance_records',
    'leave_applications',
    'notices',
    'payslips',
    'hr_loans',
    'tour_plans',
    'tour_claims',
    'snd_customers',
    'snd_orders',
    'snd_visits',
    'sfm_commitments',
    'sfm_commitment_details',
    'chart_of_accounts',
    'vouchers',
    'daily_work_diaries',
    'trainings',
    'overtime_rate_logs',
    'personal_access_tokens',
];

echo "=== SQLite to Supabase PostgreSQL Migration ===\n\n";

$sqlitePdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pgHost = env('DB_HOST', 'aws-0-ap-southeast-2.pooler.supabase.com');
$pgPort = env('DB_PORT', '5432');
$pgDb   = env('DB_DATABASE', 'postgres');
$pgUser = env('DB_USERNAME', 'postgres.atmtnbgnukawgbnogsas');
$pgPass = env('DB_PASSWORD', 'arnobhexisol777');

echo "Connecting to Supabase PostgreSQL at {$pgHost}:{$pgPort} as {$pgUser}...\n";

try {
    $pgPdo = new PDO("pgsql:host={$pgHost};port={$pgPort};dbname={$pgDb};sslmode=require", $pgUser, $pgPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✓ Connected to Supabase PostgreSQL successfully!\n\n";
} catch (Exception $e) {
    echo "✗ Failed to connect to Supabase PostgreSQL: " . $e->getMessage() . "\n";
    exit(1);
}

// Disable foreign key constraints during import
$pgPdo->exec("SET session_replication_role = 'replica';");

$totalMigrated = 0;

foreach ($tables as $table) {
    try {
        $check = $sqlitePdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'")->fetch();
        if (!$check) {
            continue;
        }

        $rows = $sqlitePdo->query("SELECT * FROM \"{$table}\"")->fetchAll(PDO::FETCH_ASSOC);
        $count = count($rows);

        if ($count === 0) {
            echo "[-] Table '{$table}' is empty. Skipped.\n";
            continue;
        }

        // Get target table columns and data types in PostgreSQL
        $colsStmt = $pgPdo->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = ? AND table_schema = 'public'");
        $colsStmt->execute([$table]);
        $pgColumns = $colsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        if (empty($pgColumns)) {
            echo "[!] Target table '{$table}' does not exist in PostgreSQL. Skipped.\n";
            continue;
        }

        // Clean destination table
        $pgPdo->exec("DELETE FROM \"{$table}\"");

        // Filter SQLite columns that actually exist in PostgreSQL table
        $srcCols = array_keys($rows[0]);
        $validCols = array_values(array_filter($srcCols, fn($c) => isset($pgColumns[$c])));

        if (empty($validCols)) {
            continue;
        }

        $colList = implode(', ', array_map(fn($c) => "\"{$c}\"", $validCols));
        $placeholders = implode(', ', array_fill(0, count($validCols), '?'));

        $insertSql = "INSERT INTO \"{$table}\" ({$colList}) VALUES ({$placeholders})";
        $insertStmt = $pgPdo->prepare($insertSql);

        $inserted = 0;
        foreach ($rows as $row) {
            $values = [];
            foreach ($validCols as $col) {
                $val = $row[$col];
                $type = $pgColumns[$col] ?? 'text';

                if ($val === null) {
                    $values[] = null;
                } elseif ($type === 'boolean') {
                    $values[] = ($val == 1 || $val === '1' || $val === true || $val === 't') ? 'true' : 'false';
                } elseif (in_array($type, ['integer', 'bigint', 'smallint']) && is_numeric($val)) {
                    $values[] = (int) $val;
                } elseif (in_array($type, ['numeric', 'double precision', 'real']) && is_numeric($val)) {
                    $values[] = (float) $val;
                } else {
                    $values[] = $val;
                }
            }
            $insertStmt->execute($values);
            $inserted++;
        }

        // Reset PostgreSQL sequence if table has auto-increment ID
        try {
            $maxId = $pgPdo->query("SELECT MAX(id) FROM \"{$table}\"")->fetchColumn();
            if ($maxId !== null && is_numeric($maxId)) {
                $seqName = "{$table}_id_seq";
                $pgPdo->exec("SELECT setval('\"{$seqName}\"', {$maxId}, true)");
            }
        } catch (Exception $e) {
            // Sequence reset not applicable for this table
        }

        echo "[✓] Migrated {$inserted} records into '{$table}'\n";
        $totalMigrated += $inserted;
    } catch (Exception $e) {
        echo "[!] Error migrating table '{$table}': " . $e->getMessage() . "\n";
    }
}

// Re-enable foreign key checks
$pgPdo->exec("SET session_replication_role = 'origin';");

echo "\n============================================\n";
echo "SUCCESS! Total {$totalMigrated} records migrated to Supabase PostgreSQL.\n";
echo "============================================\n";
