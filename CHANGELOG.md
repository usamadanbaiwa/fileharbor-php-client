# Changelog

User-visible changes are recorded here. Changes awaiting the next release appear under Unreleased.

## Unreleased

### Changed

- Configure the client with `baseUrl`, `accountId`, and `apiKey`; it constructs all account API routes automatically.
- Examples read `FILEHARBOR_BASE_URL`, `FILEHARBOR_ACCOUNT_ID`, and `FILEHARBOR_API_KEY` from the runtime or root `.env`.
- Reject malformed account IDs and base URLs that already contain the API prefix.

### Removed

- Automated tests and test commands; retained the empty `tests/` directory with `.gitkeep`.
- Full API URL configuration and public-file URL configuration/helper. File delivery belongs to the remote server.

### Added

- Separate runnable examples for creation, upload, listing, and deletion.
- `vlucas/phpdotenv` and a shared example bootstrap that loads the root `.env` while preserving runtime variables.
- PHP 8.3+ client for FileHarbor's account file-management API.
- Local-file uploads using Symfony HttpClient and Symfony Mime.
- Paginated file and directory listings, directory creation, and deletion methods.
- API exceptions with stable error codes and HTTP status, plus transport and protocol errors.
- Optional Symfony `HttpClientInterface` injection.
- Installation, usage, API, Symfony, troubleshooting, contribution, and security documentation.
- Composer package description, discovery keywords, author attribution, and project homepage.
- Client-specific source, issue tracker, documentation, and security-policy links in Composer metadata.
- Described Composer commands and a combined `composer check` command.
- Composer and Git archive rules that retain documentation and examples while excluding development files and private environment configuration.
- Release preparation and package inspection guidance.

### Compatibility notes

- Works with the PHP and Node.js FileHarbor server implementations.
- Recursive directory deletion requires the Node.js server; the PHP server currently supports empty directories only.
- Symfony 7.4 or 8.x is selected according to the consuming project's PHP version and dependency constraints.
