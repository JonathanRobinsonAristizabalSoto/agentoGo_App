<?php
$dirPath = 'app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests';
if (!is_dir($dirPath)) {
    mkdir($dirPath, 0755, true);
}
echo "Directory created or already exists: " . realpath($dirPath);
