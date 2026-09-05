<?php

declare(strict_types=1);

namespace Kooperativa;

/**
 * Internal HTTP client. Not part of the public API surface.
 *
 * @internal
 */
final class HttpClient
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /** @param array<string, mixed> $query */
    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, $query);
    }

    /** @param array<string, mixed> $body */
    public function post(string $path, array $body = []): array
    {
        return $this->request('POST', $path, [], $body);
    }

    /** @param array<string, mixed> $query */
    public function delete(string $path, array $query = []): array
    {
        return $this->request('DELETE', $path, $query);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $query = [], ?array $body = null): array
    {
        $url = $this->baseUrl . $path;
        $query = self::dropEmpty($query);
        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $headers = ['Authorization: Bearer ' . $this->apiKey];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $error = curl_error($ch);
            throw new KooperativaApiError(0, "Request failed: {$error}");
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data = $responseBody !== '' ? json_decode($responseBody, true) : null;

        if ($status < 200 || $status >= 300) {
            $message = is_array($data) && isset($data['error']) ? (string) $data['error'] : $responseBody;
            $apiCode = is_array($data) && isset($data['code']) ? (string) $data['code'] : null;
            throw new KooperativaApiError($status, $message, $apiCode);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Drop null/empty values so they are omitted from the query string,
     * rather than sent as literal empty parameters.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private static function dropEmpty(array $params): array
    {
        return array_filter($params, static fn ($value) => $value !== null && $value !== '');
    }
}
