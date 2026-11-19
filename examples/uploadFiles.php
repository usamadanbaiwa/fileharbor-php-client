<?php
declare(strict_types=1);

if (!isset($argv[1])) {
    throw new InvalidArgumentException('Usage: php examples/uploadFiles.php ./photo.png [folder] [fileName]');
}

$client = require __DIR__ . '/bootstrap.php';

$file_path = $argv[1];
$folder_name = '';
$file_name = null;

if (isset($argv[2])) {
    $folder_name = $argv[2];
}

if (isset($argv[3])) {
    $file_name = $argv[3];
}

// Upload one local file; the destination folder must exist.
$uploaded_file = $client->upload(
    $file_path,
    folder: $folder_name,
    fileName: $file_name,
);

print_r($uploaded_file);
