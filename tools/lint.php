<?php
declare(strict_types=1);

$failed = false;
foreach (['src', 'tools', 'examples'] as $directory) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../' . $directory)) as $file) {
        if ($file->getExtension() === 'php') {
            passthru(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()), $status);
            $failed = $failed || $status !== 0;
        }
    }
}
exit($failed ? 1 : 0);
