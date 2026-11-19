<?php
declare(strict_types=1);

$folder_name = '';
$page = 1;
$limit = 50;

if (isset($argv[1])) {
    $folder_name = $argv[1];
}

if (isset($argv[2])) {
    $page = filter_var($argv[2], FILTER_VALIDATE_INT);
}

if (isset($argv[3])) {
    $limit = filter_var($argv[3], FILTER_VALIDATE_INT);
}

if ($page === false || $limit === false) {
    throw new InvalidArgumentException('Usage: php examples/listFiles.php [folder] [page] [limit]; page and limit must be integers.');
}

$client = require __DIR__ . '/bootstrap.php';

$files = $client->listFiles(
    folder: $folder_name,
    page: $page,
    limit: $limit,
);

print_r($files);
