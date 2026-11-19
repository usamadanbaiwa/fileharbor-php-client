# PHP client examples

## Setup

From this package checkout, install dependencies:

```sh
composer install
```

Copy the checkout's root `.env.example` to `.env` and fill in `FILEHARBOR_BASE_URL`, `FILEHARBOR_ACCOUNT_ID`, and `FILEHARBOR_API_KEY`. Keep an existing `.env` if you already configured one. Supply the server address only; the client adds the account API route.

Each script loads `bootstrap.php`, which uses `vlucas/phpdotenv` to read the root `.env` regardless of your current working directory. Runtime variables take priority. A missing `.env` is allowed when runtime variables are configured. The bootstrap reads runtime values without changing the process-wide environment. Your application can use the same approach shown in [getting started](../docs/getting-started.md).

## One script for each operation

Run these commands from the package root:

| Script | Command | Scope |
| --- | --- | --- |
| [Create directory](createDirectory.php) | `php examples/createDirectory.php assets` | `directories:create` |
| [Upload a file](uploadFiles.php) | `php examples/uploadFiles.php ./photo.png assets profile.png` | `files:upload` |
| [List files and directories](listFiles.php) | `php examples/listFiles.php assets 1 25` | `files:list` |
| [List directories only](listDirectories.php) | `php examples/listDirectories.php assets 1 25` | `directories:list` |
| [Delete a file](deleteFile.php) | `php examples/deleteFile.php assets/profile.png` | `files:delete` |
| [Delete an empty directory](deleteDirectory.php) | `php examples/deleteDirectory.php assets` | `directories:delete` |

`uploadFiles.php` uploads **one** local file per invocation using the SDK's `upload()` method. Its folder and remote filename arguments are optional. Without a folder, it uploads to the account root. With a folder, create the folder first. There is no separate create-file endpoint.

List scripts default to the account root, page 1, and limit 50 when arguments are omitted. Directory creation requires a source. Delete scripts require an exact source and perform real deletions. The remote server handles serving files; there is no download or public-URL example.

Upload a local file with an optional folder and remote filename:

```sh
php examples/uploadFiles.php /path/to/photo.png assets profile.png
```

The script uploads only; create the destination directory separately. It prints returned metadata and leaves the file on the server. Repeating a filename can return `FILE_EXISTS`; uploads never overwrite files.

Directory deletion is empty-only by default. To permanently delete a folder and its contents on the **Node.js FileHarbor server**, pass the explicit flag:

```sh
php examples/deleteDirectory.php assets --recursive
```

The PHP server does not support recursive deletion. Do not rely on the flag there.

Use a development account and a file format permitted by that server. The script lets exceptions reach the CLI; production applications should apply their own [error-handling policy](../docs/errors.md).

For additional examples, see [file operations](../docs/usage.md) and [Symfony service integration](../docs/symfony.md).

