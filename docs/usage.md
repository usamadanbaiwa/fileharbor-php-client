# Working with files

## Upload a local file

```php
$client->createDirectory('assets/products');
$file = $client->upload(
    '/absolute/path/product.png',
    folder: 'assets/products',
    fileName: 'product-42.png',
);
```

`filePath` is a readable local file. `folder` is relative to the account's storage root. Omit it to upload to the root. `fileName` overrides the multipart filename; omitting it keeps the local basename. Keep the filename extension consistent with the content type.

Symfony Mime builds the multipart boundary and file MIME header, and HttpClient streams the body. Do not add a multipart `Content-Type` header manually. Leave the file unchanged until the call completes.

The destination folder must exist. Directory creation creates missing parents and succeeds if the directory already exists. The server enforces its size and format settings; client MIME detection does not replace server validation. FileHarbor defaults to a 25 MiB file limit and JPEG, PNG, WebP, PDF, and MP4 support, subject to the operator's configuration.

The returned array contains `file_name`, `source`, `mime_type`, and `size` in bytes. See the [API reference](api.md) for response examples.

## List files and folders

```php
$listing = $client->listFiles(folder: 'assets', page: 1, limit: 25);
foreach ($listing['items'] as $item) {
    echo $item['type'] . ': ' . $item['source'] . PHP_EOL;
}
```

`listFiles()` returns both files and directories immediately inside the requested folder. `listDirectories()` returns directories only. Neither method walks into nested directories. Use an empty folder string for the account root.

Fetch every page explicitly when needed:

```php
$page = 1;
$limit = 100;
do {
    $listing = $client->listFiles(folder: 'assets', page: $page, limit: $limit);
    foreach ($listing['items'] as $item) {
        // Save or process this item in your application.
    }
    $hasNextPage = $page * $limit < $listing['pagination']['total'];
    $page++;
} while ($hasNextPage);
```

Page numbers start at 1. Pages beyond the end return an empty item list. Listings have no snapshot guarantee: uploads or deletions during pagination can shift entries between pages. Avoid mutating the folder while collecting a complete inventory. Directory `size` is a filesystem value, not the sum of its contents.

## Delete a file or directory

```php
$client->deleteFile('assets/products/product-42.png');
$client->deleteDirectory('assets/products');
```

Deletion uses the exact account-relative source. Directory deletion is empty-only by default. A missing path is an error, so repeating a successful deletion is not silently treated as success. The account root cannot be deleted.

When connecting to the **Node.js FileHarbor server**, you can explicitly request permanent deletion of a directory and its contents:

```php
$client->deleteDirectory('assets/products', recursive: true);
```

The PHP server currently supports empty-directory deletion only. The SDK forwards the flag; it does not implement recursive deletion itself. Do not rely on this flag when connecting to the PHP server. Confirm ownership and intent in your application's workflow before deleting data.

## File delivery

The remote FileHarbor server or its configured web server/CDN serves file content. This SDK manages files and returns metadata such as `source`; it does not download file bytes or construct public delivery URLs. Store the returned source in your application and follow your server deployment's delivery configuration.

Public delivery has separate access and caching behavior from management requests. Rotating a management API key does not revoke public files, and cached copies may outlive deletion at the origin.

## Paths and operation boundaries

FileHarbor sources use forward slashes, for example `assets/products/photo.png`. The server rejects absolute paths, backslashes, dot segments, empty path segments, and unsafe filenames. The current server contract allows ASCII letters, digits, dots, underscores, and hyphens, with an ASCII letter or digit at the start of each segment. Segment and path limits are enforced on the server.

The SDK provides no rename, move, overwrite, batch upload, or authenticated download operation. Use the supported methods individually and keep application records synchronized with their results. See [errors](errors.md) before adding retry behavior.
