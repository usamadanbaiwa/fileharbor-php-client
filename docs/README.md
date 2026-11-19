# PHP client documentation

The PHP client wraps FileHarbor's account file-management API. It can connect to either FileHarbor server implementation. It does not install a server or manage administrator sessions.

| Guide | What you will find |
| --- | --- |
| [Getting started](getting-started.md) | Requirements, Composer installation, configuration, and the first upload |
| [Working with files](usage.md) | Uploads, listing pages, directory management, and deletion |
| [API reference](api.md) | Exact method signatures, defaults, return fields, and scopes |
| [Symfony integration](symfony.md) | Standalone components, dependency injection, and mock clients |
| [Errors and troubleshooting](errors.md) | Exception properties, transport errors, and common failures |
| [Runnable example](../examples/README.md) | Upload from a local checkout |
| [Contributing](../CONTRIBUTING.md) | Development setup, checks, and pull requests |
| [Security](../SECURITY.md) | Reporting vulnerabilities and protecting credentials |
| [Changelog](../CHANGELOG.md) | Changes awaiting release |


Start with getting started, then use the API reference while implementing your integration. All PHP snippets assume Composer's autoloader is loaded. Unless a snippet constructs a client, `$client` refers to an already configured `FileHarbor\Client\FileHarborClient`.

The [FileHarbor server repository](https://github.com/usamadanbaiwa/FileHarbor) contains hosting instructions and the HTTP contract. Server deployment, account provisioning, storage permissions, and allowed formats belong to the server configuration.
