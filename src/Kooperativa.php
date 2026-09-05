<?php

declare(strict_types=1);

namespace Kooperativa;

/**
 * Official Kooperativa API client.
 *
 * Example:
 * ```php
 * $kooperativa = new Kooperativa('kk_live_...');
 * $profile = $kooperativa->person->enrich(username: 'satyanadella');
 * echo $profile['data']['full_name'];
 * ```
 */
final class Kooperativa
{
    private const DEFAULT_BASE_URL = 'https://kooperativa.io/api/v1';

    private HttpClient $http;

    public readonly PersonResource $person;
    public readonly CompanyResource $company;
    public readonly MonitorResource $monitors;

    public function __construct(string $apiKey, string $baseUrl = self::DEFAULT_BASE_URL)
    {
        if ($apiKey === '') {
            throw new \InvalidArgumentException('Kooperativa: apiKey is required');
        }

        $this->http = new HttpClient($apiKey, $baseUrl);
        $this->person = new PersonResource($this->http);
        $this->company = new CompanyResource($this->http);
        $this->monitors = new MonitorResource($this->http);
    }

    /** No-auth liveness probe. Use to verify connectivity before a batch job. */
    public function health(): array
    {
        return $this->http->get('/health');
    }

    /** Account info: license status and usage breakdown. */
    public function me(): array
    {
        return $this->http->get('/me');
    }
}
