<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use FileHarbor\Client\FileHarborClient;

// Read process variables too, without writing to the process-wide environment.
$repository_builder = RepositoryBuilder::createWithDefaultAdapters();
$repository_builder = $repository_builder->addReader(PutenvAdapter::class);
$repository_builder = $repository_builder->immutable();

$repository = $repository_builder->make();

$environment_directory = dirname(__DIR__);
$dotenv = Dotenv::create($repository, $environment_directory);
$dotenv->safeLoad();

$baseUrl = $repository->get('FILEHARBOR_BASE_URL');
$accountId = $repository->get('FILEHARBOR_ACCOUNT_ID');
$apiKey = $repository->get('FILEHARBOR_API_KEY');

if (!$baseUrl || !$accountId || !$apiKey) {
    throw new InvalidArgumentException('Set FILEHARBOR_BASE_URL, FILEHARBOR_ACCOUNT_ID, and FILEHARBOR_API_KEY in the root .env or runtime environment.');
}

$client = new FileHarborClient(
    baseUrl: $baseUrl,
    accountId: $accountId,
    apiKey: $apiKey,
);

return $client;
