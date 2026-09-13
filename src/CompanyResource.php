<?php

declare(strict_types=1);

namespace Kooperativa;

/** Endpoints under /company and /companies. */
final class CompanyResource
{
    public function __construct(private HttpClient $http)
    {
    }

    /** Full company profile lookup. Provide exactly one identifier. */
    public function enrich(
        ?string $linkedinUrl = null,
        ?string $username = null,
        ?string $companyId = null,
        ?string $id = null
    ): array {
        return $this->http->get('/company', [
            'linkedin_url' => $linkedinUrl,
            'username' => $username,
            'company_id' => $companyId,
            'id' => $id,
        ]);
    }

    /**
     * Same shape as enrich(), but read from the live source instead of the data
     * lake, and written back to it, so a following enrich() returns this result.
     *
     * Provide exactly one of linkedinUrl or username. Neither companyId nor id is
     * accepted, because an internal id means nothing to a source that has never
     * seen our data lake.
     *
     * Metered: $0.001 per call on top of the flat license, the only endpoint
     * pair that is. A call is billed whenever the live source answered, so a 404
     * costs the same as a hit, and note that a 404 throws KooperativaApiError,
     * meaning a call that lands in your catch block has still been billed. A 503
     * is never billed.
     */
    public function enrichRealtime(?string $linkedinUrl = null, ?string $username = null): array
    {
        return $this->http->get('/company/realtime', [
            'linkedin_url' => $linkedinUrl,
            'username' => $username,
        ]);
    }

    /** Cheap existence check before a full lookup. Throws KooperativaApiError (404) if not held. */
    public function check(
        ?string $linkedinUrl = null,
        ?string $username = null,
        ?string $companyId = null,
        ?string $id = null
    ): array {
        return $this->http->get('/company/check', [
            'linkedin_url' => $linkedinUrl,
            'username' => $username,
            'company_id' => $companyId,
            'id' => $id,
        ]);
    }

    /**
     * Filtered search across the company data lake. At least one filter is required.
     *
     * Common filters: query, country, city, industry, minStaff,
     * maxStaff, page, perPage. See docs.kooperativa.io.
     *
     * @param array<string, mixed> $filters
     */
    public function search(array $filters = []): array
    {
        return $this->http->post('/companies/search', self::toSnakeCaseKeys($filters));
    }

    /** People currently working at a company. */
    public function currentEmployees(string $companyId, int $page = 1, int $perPage = 25): array
    {
        return $this->http->get('/company/current-employees', [
            'company_id' => $companyId,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /** People who previously worked at a company, with their past role there. */
    public function pastEmployees(string $companyId, int $page = 1, int $perPage = 25): array
    {
        return $this->http->get('/company/past-employees', [
            'company_id' => $companyId,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /** Breakdown of a company's indexed profiles by seniority level. */
    public function headcountBySeniority(string $companyId): array
    {
        return $this->http->get('/company/headcount-by-seniority', ['company_id' => $companyId]);
    }

    /** People who recently joined this company, a growth/expansion signal. */
    public function hiringSignals(string $companyId, int $days = 90, int $page = 1, int $perPage = 25): array
    {
        return $this->http->get('/company/hiring-signals', [
            'company_id' => $companyId,
            'days' => $days,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    /** @param array<string, mixed> $params */
    private static function toSnakeCaseKeys(array $params): array
    {
        $result = [];
        foreach ($params as $key => $value) {
            $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            $result[$snake] = $value;
        }
        return $result;
    }
}
