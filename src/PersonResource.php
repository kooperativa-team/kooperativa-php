<?php

declare(strict_types=1);

namespace Kooperativa;

/** Endpoints under /person and /people. */
final class PersonResource
{
    public function __construct(private HttpClient $http)
    {
    }

    /** Full profile lookup. Provide exactly one of linkedinUrl, username, or id. */
    public function enrich(?string $linkedinUrl = null, ?string $username = null, ?string $id = null): array
    {
        return $this->http->get('/person', [
            'linkedin_url' => $linkedinUrl,
            'username' => $username,
            'id' => $id,
        ]);
    }

    /**
     * Same shape as enrich(), but read from the live source instead of the data
     * lake, and written back to it, so a following enrich() returns this result.
     *
     * Provide exactly one of linkedinUrl or username. There is no id parameter,
     * because an internal id means nothing to a source that has never seen our
     * data lake.
     *
     * Metered: $0.001 per call on top of the flat license, the only endpoint
     * pair that is. A call is billed whenever the live source answered, so a 404
     * costs the same as a hit, and note that a 404 throws KooperativaApiError,
     * meaning a call that lands in your catch block has still been billed. A 503
     * is never billed.
     */
    public function enrichRealtime(?string $linkedinUrl = null, ?string $username = null): array
    {
        return $this->http->get('/person/realtime', [
            'linkedin_url' => $linkedinUrl,
            'username' => $username,
        ]);
    }

    /** Cheap existence check before a full lookup. Throws KooperativaApiError (404) if not held. */
    public function check(?string $linkedinUrl = null, ?string $username = null, ?string $id = null): array
    {
        return $this->http->get('/person/check', [
            'linkedin_url' => $linkedinUrl,
            'username' => $username,
            'id' => $id,
        ]);
    }

    /**
     * Filtered search across the people data lake.
     *
     * Common filters: title, location, industry, seniority, company,
     * companyId, city, skills, tenureMinMonths, jobChangedAfter,
     * pastCompany, education, page, perPage. See docs.kooperativa.io.
     *
     * @param array<string, mixed> $filters
     */
    public function search(array $filters = []): array
    {
        return $this->http->post('/people/search', self::toSnakeCaseKeys($filters));
    }

    /**
     * Enrich up to 100 profiles in one call.
     *
     * Each item must have exactly one of "id", "username", or "linkedin_url".
     *
     * @param array<int, array<string, string>> $profiles
     */
    public function bulkEnrich(array $profiles): array
    {
        return $this->http->post('/people/bulk-enrich', ['profiles' => $profiles]);
    }

    /** Current coworkers of a person (everyone at their current company right now). */
    public function colleagues(string $id, int $page = 1, int $perPage = 25): array
    {
        return $this->http->get('/person/colleagues', ['id' => $id, 'page' => $page, 'per_page' => $perPage]);
    }

    /** Lookalike profiles: same seniority, industry, and country. */
    public function similar(string $id, int $page = 1, int $perPage = 25): array
    {
        return $this->http->get('/person/similar', ['id' => $id, 'page' => $page, 'per_page' => $perPage]);
    }

    /** People who recently started a new job, optionally filtered by previous employer. */
    public function jobChanges(int $days = 90, ?string $companyId = null, int $page = 1, int $perPage = 25): array
    {
        return $this->http->get('/person/job-changes', [
            'days' => $days,
            'company_id' => $companyId,
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
