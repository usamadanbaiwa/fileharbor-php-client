# Errors and troubleshooting

## Handle FileHarbor exceptions

```php
use FileHarbor\Client\FileHarborException;

try {
    $client->deleteFile('assets/missing.png');
} catch (FileHarborException $error) {
    if ($error->errorCode === 'FILE_NOT_FOUND') {
        // Apply your application's missing-file policy.
    } else {
        throw $error;
    }
}
```

Branch on `errorCode`. Messages are intended for people and may change. `status` contains the HTTP status for an API error or unexpected response. Transport errors use `null`, even if a connection had already received some response headers. `getCode()` is PHP's inherited numeric exception code and does not contain the API code.

## SDK errors

| Code | Meaning | What to investigate |
| --- | --- | --- |
| `TIMEOUT` | Idle timeout or total request budget reached | Server load, upload size, network speed, timeout setting |
| `NETWORK_ERROR` | Request or response transfer failed | DNS, connection, proxy, certificates, interrupted transfers |
| `INVALID_RESPONSE` | Unexpected HTTP response or malformed JSON envelope | server base URL, proxy error pages, redirects, server logs |

The SDK does not expose raw unexpected response bodies in its exceptions. Inspect server and proxy logs using sanitized diagnostics. Configuration mistakes, invalid pagination, unreadable local upload paths throw `InvalidArgumentException`. Symfony Mime can raise file-preparation exceptions. PHP type errors indicate values that do not match the declared method signature.

## Common server errors

| HTTP | Typical codes | Meaning |
| --- | --- | --- |
| 400 | `INVALID_REQUEST`, `INVALID_PATH`, `INVALID_FILE` | Check source paths, filenames, and input fields |
| 401 | `UNAUTHORIZED` | Invalid, revoked, disabled, or wrong-account key |
| 403 | `FORBIDDEN` | Account key lacks the required operation scope |
| 404 | `FILE_NOT_FOUND`, `ACCOUNT_NOT_FOUND`, `NOT_FOUND` | Resource or route does not exist |
| 409 | `FILE_EXISTS`, `CONFLICT` | Upload name collision or nonempty directory deletion |
| 413 | `FILE_TOO_LARGE` | Upload exceeds a server or hosting limit |
| 415 | `UNSUPPORTED_FILE_TYPE` | Content or extension is not permitted |
| 429 | `RATE_LIMIT_EXCEEDED` | Server request limit reached |
| 500 | `STORAGE_ERROR` | Server-side storage operation failed |

The SDK preserves server error codes; this table is not a closed enum. A proxy can return HTML instead of a FileHarbor envelope, producing `INVALID_RESPONSE` with its HTTP status.

## A request timed out: can I repeat it?

A failed connection does not prove that the server rejected the operation. An upload may already exist, and a deletion may already have completed. The SDK has no idempotency-key support and adds no retry layer. Inspect the target state before repeating a mutation. If an injected Symfony client has its own retry policy, account for that behavior too.

## Common setup problems

### Composer cannot find `fileharbor/client`

Check that the package name is `fileharbor/client`, that Composer can reach Packagist, and that your PHP version and dependency constraints meet the package requirements. Run the install command from your application folder. If you are using local source instead, follow [local installation](getting-started.md#use-a-local-checkout) and confirm the path points to the checkout containing `composer.json`.

### The autoloader is missing

Run `composer install` in the package checkout before running an example. In your application, load that application's `vendor/autoload.php` after installing the package there.

### Environment values are missing

The CLI examples load the checkout's root `.env` with the included `vlucas/phpdotenv` dependency. In your application, load it explicitly as shown in [getting started](getting-started.md), or pass values from your framework's configuration. The client constructor does not automatically read `.env` files.

`getenv()` does not parse files, and the safe phpdotenv loader does not write loaded values to `getenv()`. Read the phpdotenv repository, `$_ENV`, or `$_SERVER` as appropriate for your loader. The example bootstrap preserves runtime values over file values without using `putenv()`. An existing empty runtime value also takes precedence, so remove it or supply a valid value. PHP-FPM and CLI can have different environments. Check whether each value is present without printing credentials.

### Every request returns 404 or an HTML page

Set `baseUrl` to the server address and `accountId` to the ID from the FileHarbor panel. The SDK constructs `/fileharbor/{accountId}/api`; do not append it yourself. Check the account ID and reverse-proxy routing. Redirects are not followed; configure the final HTTPS endpoint directly.

### A folder upload returns 404

Create the destination folder first using a key with `directories:create`, or arrange for the server operator to create it. Uploading does not create directories automatically.

### A valid-looking file is rejected

The server checks file content and extension against its configured formats. Changing a MIME header or filename does not convert file content. For large files, inspect PHP upload limits, reverse-proxy request limits, and FileHarbor's file-size limit. Some hosts reject streamed multipart bodies without a content length with HTTP 411; check host support before changing the client's buffering behavior.

### How do I retrieve file content?

The SDK uploads, lists, creates directories, and deletes. The remote FileHarbor server or its configured delivery mapping handles file content. There is no download method or public-URL helper in this SDK.

