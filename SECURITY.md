# Security policy

## Report a vulnerability privately

Do not put exploitable vulnerabilities, live API keys, private files, or credentials in public issues or pull requests.

Use the hosting repository's **Security → Report a vulnerability** feature if it is enabled. If private reporting is unavailable, ask a maintainer for a private reporting channel without disclosing exploit details. Do not send sensitive reports to an inferred email address.

Include:

- The affected client version or commit, PHP version, Symfony versions, and transport.
- Whether the issue affects this SDK, FileHarbor server, a dependency, or deployment configuration.
- Prerequisites, expected behavior, actual behavior, and the possible impact.
- A minimal reproduction using fake credentials and synthetic files.
- Any suggested mitigation, with secrets removed from logs and traces.

Coordinate disclosure with the maintainer so a fix or mitigation can be prepared. No response-time commitment or vulnerability bounty is currently advertised.

## Supported versions

Include the exact installed package version or development commit in your report. Reports about released versions and development code are welcome. Review the changelog and release notes for security fixes affecting your installation.

## Protect integrations

Keep account keys on the backend and grant only the scopes your application needs. Store secrets in private runtime configuration or a secret manager. Never commit a configured `.env`, expose a key in a browser, or include it in a public URL. Rotate a key immediately if it is exposed.

Configure the server base URL and account ID through trusted application configuration. Do not accept an arbitrary server base URL from an end user: the SDK sends the account key to that destination. Likewise, let your application choose which local files it uploads; a readable local path is not proof that the requesting user is allowed to access it.

Use HTTPS for production API calls and keep TLS verification enabled. The default Symfony client verifies certificates and the SDK disables redirects. Custom injected clients can alter transport behavior, proxying, logging, and retries; review their configuration before use.

`SensitiveParameter` and redacted debug output help reduce accidental key exposure. They do not guarantee that every serializer, debugger, profiler, HTTP trace, or application log will hide credentials. Sanitize diagnostics before sharing them.

## File and deletion boundaries

The server enforces authentication, scopes, account isolation, safe storage paths, content rules, and size limits. The client is not an authorization layer for your application's users. Check user ownership and allowed operations before calling it.

Symfony Mime prepares upload metadata; it does not scan files for malware. File type detection does not establish that content is safe to serve or execute. Apply any additional scanning or moderation required by your application.

Public file URLs require no API key. Rotating or revoking an account key does not revoke an already public URL. Deletion at the origin does not immediately remove copies from browsers or CDNs. Plan public delivery and caching accordingly.

Deletion is permanent at the API level. Recursive directory deletion requires an explicit flag and a server that supports it. Backups and recovery belong to the deployment; the SDK provides no recycle bin. A timed-out mutation may already have completed, so inspect the state before repeating it.

## Dependencies

Keep PHP and the resolved Symfony dependencies patched. Run `composer audit` during maintenance and review advisories relevant to the versions in your application's lockfile. Report dependency vulnerabilities through the relevant upstream project's security process, and notify this project's maintainer when SDK integration changes are needed.
