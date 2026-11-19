<?php
declare(strict_types=1);

namespace FileHarbor\Client;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FileHarborClient
{
    private readonly string $apiBase;
    private readonly string $apiKey;
    private readonly int $timeoutMs;
    private readonly HttpClientInterface $httpClient;

    public function __construct(
        string $baseUrl,
        string $accountId,
        #[\SensitiveParameter] string $apiKey,
        int $timeoutMs = 60000,
        ?HttpClientInterface $httpClient = null,
    ) {
        $baseUrl = self::baseUrl($baseUrl);

        $validAccountId = preg_match('/^[A-Za-z0-9_-]{22}$/D', $accountId);

        if ($validAccountId !== 1) {
            throw new \InvalidArgumentException('accountId must be the 22-character FileHarbor account ID.');
        }

        $invalidKeyCharacters = preg_match('/[\s\x00-\x1f\x7f]/', $apiKey);

        if ($apiKey === '' || $invalidKeyCharacters === 1) {
            throw new \InvalidArgumentException('apiKey must be a nonempty token without whitespace or control characters.');
        }

        if ($timeoutMs < 1 || $timeoutMs > 2147483647) {
            throw new \InvalidArgumentException('timeoutMs must be between 1 and 2147483647.');
        }

        if ($httpClient === null) {
            $httpClient = HttpClient::create();
        }

        $this->apiBase = $baseUrl . '/fileharbor/' . $accountId . '/api';
        $this->apiKey = $apiKey;
        $this->timeoutMs = $timeoutMs;
        $this->httpClient = $httpClient;
    }

    /** @return array{file_name: string, source: string, mime_type: string, size: int} */
    public function upload(string $filePath, string $folder = '', ?string $fileName = null): array
    {
        $path = realpath($filePath);

        if ($path === false) {
            throw new \InvalidArgumentException('Upload must be a readable local file.');
        }

        if (!is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException('Upload must be a readable local file.');
        }

        $file = DataPart::fromPath($path);

        $fields = [
            'folder' => $folder,
            'file' => $file,
        ];

        if ($fileName !== null) {
            $fields['file_name'] = $fileName;
        }

        $uploadedFile = $this->request('POST', '/upload', $fields, multipart: true);

        return $uploadedFile;
    }

    /** @return array{items: array, pagination: array{page: int, limit: int, total: int}} */
    public function listFiles(string $folder = '', int $page = 1, int $limit = 50): array
    {
        $files = $this->listing('/files', $folder, $page, $limit);

        return $files;
    }

    /** @return array{items: array, pagination: array{page: int, limit: int, total: int}} */
    public function listDirectories(string $folder = '', int $page = 1, int $limit = 50): array
    {
        $directories = $this->listing('/directories', $folder, $page, $limit);

        return $directories;
    }

    /** @return array{created: true} */
    public function createDirectory(string $source): array
    {
        $body = [
            'source' => $source,
        ];

        $createdDirectory = $this->request('POST', '/directories', $body);

        return $createdDirectory;
    }

    /** @return array{deleted: true} */
    public function deleteFile(string $source): array
    {
        $body = [
            'source' => $source,
        ];

        $deletedFile = $this->request('DELETE', '/files', $body);

        return $deletedFile;
    }

    /** Recursive deletion requires the Node.js FileHarbor server. @return array{deleted: true} */
    public function deleteDirectory(string $source, bool $recursive = false): array
    {
        $body = [
            'source' => $source,
            'recursive' => $recursive,
        ];

        $deletedDirectory = $this->request('DELETE', '/directories', $body);

        return $deletedDirectory;
    }

    public function __debugInfo(): array
    {
        $details = [
            'apiBase' => $this->apiBase,
            'apiKey' => '[redacted]',
            'timeoutMs' => $this->timeoutMs,
        ];

        return $details;
    }

    private static function baseUrl(string $value): string
    {
        $parts = parse_url($value);
        $errorMessage = 'Base URLs must use HTTP(S), without credentials, query, or fragment.';

        if ($parts === false) {
            throw new \InvalidArgumentException($errorMessage);
        }

        if (!isset($parts['host']) || !isset($parts['scheme'])) {
            throw new \InvalidArgumentException($errorMessage);
        }

        $validScheme = in_array($parts['scheme'], ['http', 'https'], true);

        if (!$validScheme) {
            throw new \InvalidArgumentException($errorMessage);
        }

        $forbiddenParts = ['user', 'pass', 'query', 'fragment'];

        foreach ($forbiddenParts as $part) {
            if (isset($parts[$part])) {
                throw new \InvalidArgumentException($errorMessage);
            }
        }

        $invalidCharacters = preg_match('/[\s\x00-\x1f\x7f]/', $value);

        if ($invalidCharacters === 1) {
            throw new \InvalidArgumentException($errorMessage);
        }

        $value = rtrim($value, '/');
        $path = '';

        if (isset($parts['path'])) {
            $path = rtrim($parts['path'], '/');
        }

        $containsApiPath = preg_match('~/fileharbor(?:/.*)?$|/api$~', $path);

        if ($containsApiPath === 1) {
            throw new \InvalidArgumentException('baseUrl must be the server base URL without /fileharbor or /api; the client constructs the API path.');
        }

        return $value;
    }

    private function listing(string $path, string $folder, int $page, int $limit): array
    {
        $validPage = $page >= 1 && $page <= 1000000;
        $validLimit = $limit >= 1 && $limit <= 100;

        if (!$validPage || !$validLimit) {
            throw new \InvalidArgumentException('page must be 1–1000000 and limit must be 1–100.');
        }

        $parameters = [
            'folder' => $folder,
            'page' => $page,
            'limit' => $limit,
        ];

        $query = http_build_query(
            $parameters,
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        $requestPath = $path . '?' . $query;
        $listing = $this->request('GET', $requestPath);

        return $listing;
    }

    private function request(string $method, string $path, ?array $body = null, bool $multipart = false): array
    {
        $authorization = 'Bearer ' . $this->apiKey;

        $headers = [
            'Authorization' => $authorization,
            'Accept' => 'application/json',
        ];

        $timeoutSeconds = $this->timeoutMs / 1000;

        $options = [
            'max_redirects' => 0,
            'timeout' => $timeoutSeconds,
            'max_duration' => $timeoutSeconds,
        ];

        if ($body !== null) {
            if ($multipart) {
                $form = new FormDataPart($body);

                $preparedHeaders = $form->getPreparedHeaders();
                $formHeaders = $preparedHeaders->toArray();

                $headers = array_merge($headers, $formHeaders);
                $options['body'] = $form->bodyToIterable();
            } else {
                $options['json'] = $body;
            }
        }

        $options['headers'] = $headers;

        $requestUrl = $this->apiBase . $path;
        $startedAt = hrtime(true);


        try {
            $response = $this->httpClient->request($method, $requestUrl, $options);

            $status = $response->getStatusCode();

            // Consume even error responses so FileHarbor codes remain available to callers.
            $responseBody = $response->getContent(false);


        } catch (TimeoutExceptionInterface) {
            throw new FileHarborException('FileHarbor request timed out.', 'TIMEOUT');
        } catch (TransportExceptionInterface) {
            // Allow for the transport's millisecond rounding when checking the budget.
            $elapsedNanoseconds = hrtime(true) - $startedAt;
            $elapsedMilliseconds = $elapsedNanoseconds / 1_000_000;
            $timeoutThreshold = $this->timeoutMs - 1;

            if ($elapsedMilliseconds >= $timeoutThreshold) {
                throw new FileHarborException('FileHarbor request timed out.', 'TIMEOUT');
            }

            throw new FileHarborException('FileHarbor request failed.', 'NETWORK_ERROR');
        }

        $data = $this->parseResponse($responseBody, $status);

        return $data;
    }

    private function parseResponse(string $responseBody, int $status): array
    {
        $errorMessage = 'Unexpected response from FileHarbor.';

        try {
            $envelope = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new FileHarborException($errorMessage, 'INVALID_RESPONSE', $status);
        }

        if (!is_array($envelope)) {
            throw new FileHarborException($errorMessage, 'INVALID_RESPONSE', $status);
        }

        $success = null;

        if (isset($envelope['success'])) {
            $success = $envelope['success'];
        }

        if ($success === false && isset($envelope['error'])) {
            $error = $envelope['error'];

            if (is_array($error) && isset($error['code'], $error['message'])) {
                $errorCode = $error['code'];
                $message = $error['message'];

                if (is_string($errorCode) && is_string($message)) {
                    throw new FileHarborException($message, $errorCode, $status);
                }
            }
        }

        if ($status < 200 || $status >= 300) {
            throw new FileHarborException($errorMessage, 'INVALID_RESPONSE', $status);
        }

        if ($success !== true) {
            throw new FileHarborException($errorMessage, 'INVALID_RESPONSE', $status);
        }

        if (!isset($envelope['data']) || !is_array($envelope['data'])) {
            throw new FileHarborException($errorMessage, 'INVALID_RESPONSE', $status);
        }

        $data = $envelope['data'];

        return $data;
    }
}
