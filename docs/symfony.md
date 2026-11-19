# Symfony integration

The SDK uses standalone Symfony HttpClient and Mime components. A plain PHP application can construct `FileHarborClient` directly; the full Symfony framework is optional.

## Use Symfony's HTTP client service

In a Symfony application with this package installed, register the SDK in `config/services.yaml`:

```yaml
services:
    FileHarbor\Client\FileHarborClient:
        arguments:
            $baseUrl: '%env(FILEHARBOR_BASE_URL)%'
            $accountId: '%env(FILEHARBOR_ACCOUNT_ID)%'
            $apiKey: '%env(FILEHARBOR_API_KEY)%'
            $httpClient: '@http_client'
```

Configure the server base URL, account ID, and API key using your application's environment or secret-management configuration. The client constructs the account API prefix automatically.

Your application service can then receive the SDK through constructor injection:

```php
<?php
declare(strict_types=1);

namespace App\Service;

use FileHarbor\Client\FileHarborClient;

final class ProductFiles
{
    public function __construct(private readonly FileHarborClient $files)
    {
    }

    public function upload(string $localPath): array
    {
        $this->files->createDirectory('products');

        return $this->files->upload($localPath, folder: 'products');
    }
}
```

The SDK applies its Bearer key, JSON accept header, timeout, total duration, and redirect policy per request. It constructs an absolute account API URL from your base URL and account ID, so an injected client's `base_uri` is not needed for SDK routing. Inject a client whose default headers, authentication, proxies, and retry settings are appropriate for FileHarbor.

The SDK does not wrap an injected client to remove retry behavior. If your application configures retries, consider that an upload or deletion may complete before a connection fails. See [error handling](errors.md).

## Mock the HTTP boundary

Symfony's mock client lets you test your application without contacting a live FileHarbor account:

```php
<?php
declare(strict_types=1);

use FileHarbor\Client\FileHarborClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$httpClient = new MockHttpClient(new MockResponse(
    '{"success":true,"data":{"created":true}}',
    ['http_code' => 201],
));

$client = new FileHarborClient(
    baseUrl: 'https://files.example.com',
    accountId: 'abcdefghijklmnopqrstuv',
    apiKey: 'test-key',
    httpClient: $httpClient,
);

$result = $client->createDirectory('assets');
// $result === ['created' => true]
```

Use fake credentials and matching response envelopes. The SDK handles HTTP error envelopes itself; a 4xx response should include `success: false` and an `error` object with string `code` and `message` fields.

For component details, see [Symfony HttpClient documentation](https://symfony.com/doc/7.4/http_client.html).
