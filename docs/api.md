# API reference

Namespace: `FileHarbor\Client`. Network methods run synchronously and return the API's `data` as an associative array. The outer `success` envelope is consumed by the SDK. The client checks the response envelope, not every nested field's schema.

## Constructor

```text
FileHarborClient(
    string $baseUrl,
    string $accountId,
    string $apiKey,
    int $timeoutMs = 60000,
    ?\Symfony\Contracts\HttpClient\HttpClientInterface $httpClient = null,
)
```

| Parameter | Meaning |
| --- | --- |
| `baseUrl` | Server address, such as `https://files.example.com`; may include a deployment subdirectory |
| `accountId` | The 22-character FileHarbor account ID, containing ASCII letters, digits, underscores, or hyphens |
| `apiKey` | Nonempty Bearer token without whitespace or control characters |
| `timeoutMs` | Integer from 1 to 2147483647; sets idle timeout and total HTTP duration |
| `httpClient` | Optional Symfony HTTP client; defaults to `HttpClient::create()` |

Base URLs must use HTTP or HTTPS and must not contain credentials, a query, a fragment, whitespace, or control characters. Trailing slashes are removed. Use HTTPS outside local development. The client appends `/fileharbor/{accountId}/api` automatically. Do not include `/fileharbor` or `/api` in the base URL; those suffixes are rejected. The constructor validates the account ID format without contacting the server to authenticate the key or account.

## Endpoint mapping and scopes

| Client method | HTTP operation after the generated account API prefix | Scope |
| --- | --- | --- |
| `upload()` | `POST /upload`, multipart | `files:upload` |
| `listFiles()` | `GET /files`, query parameters | `files:list` |
| `deleteFile()` | `DELETE /files`, JSON | `files:delete` |
| `createDirectory()` | `POST /directories`, JSON | `directories:create` |
| `listDirectories()` | `GET /directories`, query parameters | `directories:list` |
| `deleteDirectory()` | `DELETE /directories`, JSON | `directories:delete` |

Every network request sends the configured Bearer key and requests JSON. File serving and downloading are handled by the remote server, outside this SDK.

## Upload

```text
upload(string $filePath, string $folder = '', ?string $fileName = null): array
```

Sends one file part, a `folder` field, and an optional `file_name` override. Example result:

```php
$result = [
    'file_name' => 'profile.png',
    'source' => 'assets/profile.png',
    'mime_type' => 'image/png',
    'size' => 20480,
];
```

The folder must already exist. Existing files are never overwritten. An unreadable local path throws `InvalidArgumentException`; server errors throw `FileHarborException`. File preparation can also raise a Symfony Mime exception if the local file becomes unavailable or MIME preparation fails.

## Listings

```text
listFiles(string $folder = '', int $page = 1, int $limit = 50): array
listDirectories(string $folder = '', int $page = 1, int $limit = 50): array
```

`page` must be 1–1000000 and `limit` must be 1–100. Invalid ranges throw `InvalidArgumentException`. `folder: ''` means the account root. Example result:

```php
$result = [
    'items' => [
        [
            'file_name' => 'profile.png',
            'source' => 'assets/profile.png',
            'type' => 'file',
            'size' => 20480,
        ],
    ],
    'pagination' => ['page' => 1, 'limit' => 50, 'total' => 1],
];
```

`type` is `file` or `directory`. `listFiles()` includes both; `listDirectories()` filters to directories before pagination. Listings are non-recursive and sorted by filename by the server.

## Directory creation

```text
createDirectory(string $source): array
```

Creates missing parents and succeeds for an existing directory. Returns `['created' => true]`.

## Deletion

```text
deleteFile(string $source): array
deleteDirectory(string $source, bool $recursive = false): array
```

Returns `['deleted' => true]`. The JSON body contains `source`; directory deletion also sends the boolean `recursive`. Directory deletion is empty-only by default. `recursive: true` requires the Node.js FileHarbor server; it is not implemented by the PHP server. The account root cannot be deleted.

## Exceptions

```text
FileHarborException extends \RuntimeException
```

| Property or method | Meaning |
| --- | --- |
| `errorCode` | Readonly string: server code or SDK transport/protocol code |
| `status` | Readonly nullable integer: HTTP status when a response was received |
| `getMessage()` | Human-readable error message |
| `getCode()` | Inherited numeric exception code; do not use for FileHarbor API codes |

See [errors and troubleshooting](errors.md) for handling examples and mappings.
