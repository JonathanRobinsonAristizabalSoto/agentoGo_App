<?php
// Quick syntax check
$files = [
    'app/Models/AuditLog.php',
    'app/Auditable.php',
    'database/migrations/2026_05_22_194758_create_audit_logs_table.php',
];

$errors = [];
foreach ($files as $file) {
    $fullPath = base_path($file);
    if (file_exists($fullPath)) {
        $output = shell_exec("php -l " . escapeshellarg($fullPath) . " 2>&1");
        if (strpos($output, 'Parse error') !== false || strpos($output, 'Syntax error') !== false) {
            $errors[] = "$file: " . $output;
        }
    }
}

if (empty($errors)) {
    echo "✓ All files have valid syntax\n";
} else {
    echo "✗ Syntax errors found:\n";
    foreach ($errors as $error) {
        echo "  $error\n";
    }
}
