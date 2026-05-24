<?php
// Quick test runner script
chdir('c:\Users\Aristizabal\OneDrive\Desktop\AgentoGo\apps\api');

echo "Running tests...\n";
passthru('php artisan test --testdox 2>&1');
?>
