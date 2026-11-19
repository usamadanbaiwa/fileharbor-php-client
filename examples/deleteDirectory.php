<?php
declare(strict_types=1);

if (!isset($argv[1])) {
    throw new InvalidArgumentException('Usage: php examples/deleteDirectory.php assets [--recursive]');
}

if (count($argv) > 3) {
    throw new InvalidArgumentException('Usage: php examples/deleteDirectory.php assets [--recursive]');
}

$folder_name = $argv[1];
$recursive = false;

if (isset($argv[2])) {
    if ($argv[2] !== '--recursive') {
        throw new InvalidArgumentException('Usage: php examples/deleteDirectory.php assets [--recursive]');
    }

    $recursive = true;
}

$client = require __DIR__ . '/bootstrap.php';

// Empty-only by default. --recursive permanently removes contents on supported servers.
$deleted_folder = $client->deleteDirectory(
    $folder_name,
    recursive: $recursive,
);

print_r($deleted_folder);
