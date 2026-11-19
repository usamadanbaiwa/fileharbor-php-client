# Getting started

## Requirements

- PHP 8.3 or newer with JSON support.
- Composer to install the SDK and its dependencies.
- A running FileHarbor server and an account API key.
- Network access from your application to that server.

The package uses `symfony/http-client`, `symfony/http-client-contracts`, `symfony/mime`, and `vlucas/phpdotenv`. Composer resolves compatible Symfony 7.4 or 8.x versions for your PHP runtime. cURL is optional; Symfony also provides a PHP stream transport. MIME detection depends on the guessers available in the PHP environment, typically Fileinfo. The server performs its own content validation.

## Install from Packagist

Run this command inside your application:

```sh
composer require fileharbor/client
```

Load your application's `vendor/autoload.php` before using the client. Composer installs the SDK and its dependencies.

## Use a local checkout

You can download or clone this repository and work with the local source instead of installing the SDK from Packagist. Inside that checkout, run:

```sh
composer install
```

You do not need `composer require fileharbor/client` to run the local examples. Composer is still needed to install Symfony and phpdotenv and generate `vendor/autoload.php`. Copying the source alone does not install those dependencies.

Configure the checkout's `.env`, then run its [operation examples](../examples/README.md). A standalone script using the checkout can load that checkout's `vendor/autoload.php`.

### Connect a local checkout to another application

For an application that already uses Composer, a path repository lets it use your local SDK source. These instructions use sibling folders:

```text
projects/
  my-application/
  fileharbor-php-client/
```

Run these commands inside `my-application`:

```sh
composer config repositories.fileharbor path ../fileharbor-php-client
composer require fileharbor/client:@dev
```

The repository setting tells Composer where to find this package. `@dev` permits the development checkout without changing the application's global minimum stability. Composer may symlink the checkout, so keep it available while developing. A production deployment must include the installed package files, rather than a broken link to a developer's machine.

## Configure an account

Create an account and API key in your FileHarbor administration panel. For the example below, grant `directories:create`, `files:upload`, and `files:list`. Use only the scopes your application needs.

```dotenv
FILEHARBOR_BASE_URL=https://files.example.com
FILEHARBOR_ACCOUNT_ID=YOUR_22_CHARACTER_ACCOUNT_ID
FILEHARBOR_API_KEY=YOUR_API_KEY
```

Replace the placeholders with your server address, the 22-character account ID from the panel, and that account's API key. Supply only the server base URL; the client constructs `/fileharbor/{accountId}/api` automatically. Do not append `/fileharbor` or `/api`. For a deployment under a subdirectory, include that subdirectory in the base URL.

The root [`.env.example`](../.env.example) lists the same variables. For the checkout's CLI examples, copy it to `.env` and replace the placeholders. Do not overwrite an existing configured `.env`. The examples load that file automatically through their shared bootstrap, using `vlucas/phpdotenv`.

In your own application, load `.env` explicitly as below, or use your framework's configuration. The client constructor does not automatically load files. `getenv()` reads process variables, not `.env` files. The phpdotenv repository below reads both runtime and file values without calling `putenv()`; existing runtime values take priority. See [phpdotenv usage](https://github.com/vlucas/phpdotenv#usage).

## Upload your first file

Place a permitted file such as `photo.png` next to this application script:

```php
<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use FileHarbor\Client\FileHarborClient;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;

$environment = RepositoryBuilder::createWithDefaultAdapters()
    ->addReader(PutenvAdapter::class)
    ->immutable()
    ->make();
Dotenv::create($environment, __DIR__)->safeLoad();

$baseUrl = $environment->get('FILEHARBOR_BASE_URL');
$accountId = $environment->get('FILEHARBOR_ACCOUNT_ID');
$apiKey = $environment->get('FILEHARBOR_API_KEY');
if (!$baseUrl || !$accountId || !$apiKey) {
    throw new RuntimeException('Configure the FileHarbor base URL, account ID, and key.');
}

$client = new FileHarborClient(
    baseUrl: $baseUrl,
    accountId: $accountId,
    apiKey: $apiKey,
);

$client->createDirectory('assets');
$file = $client->upload(__DIR__ . '/photo.png', folder: 'assets');
echo $file['source'] . PHP_EOL;

$listing = $client->listFiles(folder: 'assets');
print_r($listing['items']);

```

Store the returned `source` in your application's record for the file. FileHarbor does not associate files with your application's users or business records. A second upload with the same filename returns a conflict; use a new filename when storing another version.

Continue with [working with files](usage.md), or run the checkout's [upload example](../examples/README.md).
