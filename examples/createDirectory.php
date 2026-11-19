<?php
declare(strict_types=1);

if (!isset($argv[1])) {
    throw new InvalidArgumentException('Usage: php examples/createDirectory.php assets/products');
}

$client = require __DIR__ . '/bootstrap.php';

// Read the folder name from the command-line argument.
$folder_name = $argv[1];

$create_folder = $client->createDirectory($folder_name);

print_r($create_folder);
