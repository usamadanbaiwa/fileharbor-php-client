<?php
declare(strict_types=1);

if (!isset($argv[1])) {
    throw new InvalidArgumentException('Usage: php examples/deleteFile.php assets/photo.png');
}

$client = require __DIR__ . '/bootstrap.php';

$file_source = $argv[1];

$deleted_file = $client->deleteFile($file_source);

print_r($deleted_file);
