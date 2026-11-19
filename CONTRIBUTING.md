# Contributing to the PHP client

Bug fixes and documentation improvements are welcome. Discuss substantial API or dependency changes before implementing them. Use [SECURITY.md](SECURITY.md) for vulnerabilities instead of public issues.

## Set up a checkout

Use PHP 8.3+ and Composer. Install dependencies inside this folder:

```sh
composer install
```

The client uses Symfony HttpClient, HttpClient contracts, Mime, and phpdotenv. Keep code compatible with the declared PHP and dependency constraints. cURL is optional; preserve support for Symfony's native PHP stream transport.

## Repository layout

| Path | Purpose |
| --- | --- |
| `src/` | Public client and exception classes |
| `tests/.gitkeep` | Placeholder preserving the empty tests directory |
| `examples/` | Runnable examples and setup instructions |
| `docs/` | Installation, usage, API, integration, and troubleshooting guides |
| `tools/lint.php` | PHP syntax checks |

Keep this package self-contained. Do not import sibling client or server source into this package. The server's HTTP contract is the compatibility boundary.

## Run checks

```sh
composer validate --strict
composer lint
```

Or run `composer check` to execute both checks in order. `composer run --list` describes the available package commands.

`composer lint` runs PHP syntax checks; it is not a formatter or static analyzer. There is currently no automated test suite or Composer test command. The tests directory contains only a placeholder.

When changing HTTP behavior, describe how you verified routes, JSON/multipart fields, authentication, and error handling. Use a development account for manual checks and report the result in the pull request.

No runtime check is normally needed for prose-only changes. Check local documentation links and keep snippets consistent with the public signatures. If a check cannot run, report the command and the environmental limitation in the pull request.

## Code and API conventions

- Use `declare(strict_types=1);`, PSR-4 namespaces, four-space indentation, and explicit types.
- Keep public names consistent with the existing camelCase methods and named parameters.
- Preserve server response fields such as `file_name` and `mime_type`.
- Keep HTTP and multipart mechanics in Symfony components.
- Keep credentials out of error messages, logs, fixtures, and documentation.
- Do not add automatic mutation retries or destructive behavior implicitly.
- Explain compatibility differences between server implementations in documentation.

Named parameters are part of the PHP API: renaming a public parameter can break callers. Changes to return shapes, exceptions, timeout semantics, or defaults need a clear compatibility explanation. Document any limitation introduced by a change.

## Submit a pull request

Describe the concrete problem, resulting behavior, and checks run. Include a minimal reproduction for bug fixes and an example for new public methods. Keep changes focused and update the relevant docs, examples, and [Unreleased changelog](CHANGELOG.md).

Do not include `.env`, real account keys, user uploads, `vendor/`, or generated archives. `composer.lock` is currently ignored for this library checkout; consumers resolve dependencies through their application's lockfile. Maintainers should verify the supported dependency ranges before release.

Contributions are provided under this project's [MIT license](LICENSE).
