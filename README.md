# kooperativa/sdk

Official PHP SDK for the [Kooperativa](https://kooperativa.io) API. Enrich and search professional profiles and companies, track hiring signals and job changes, and manage webhook monitors.

## Installation

Not on Packagist yet, so Composer needs to be told where to find it. Add this repository entry to your project's `composer.json`, then require the package as usual:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/kooperativa-team/kooperativa-php"
    }
  ]
}
```

```bash
composer require kooperativa/sdk:dev-main
```

Requires a Kooperativa API key. Get one from your [account dashboard](https://kooperativa.io/api-keys).

## Usage

```php
<?php
require 'vendor/autoload.php';

use Kooperativa\Kooperativa;

$kooperativa = new Kooperativa($_ENV['KOOPERATIVA_API_KEY']);

$profile = $kooperativa->person->enrich(username: 'satyanadella');
echo $profile['data']['full_name'];
```

## Methods

**Account**
- `health()` — API liveness probe, no auth required
- `me()` — license status and usage breakdown

**Person** (`$kooperativa->person`)
- `enrich(linkedinUrl: null, username: null, id: null)`
- `enrichRealtime(linkedinUrl: null, username: null)` — from the live source, **metered**, see below
- `check(linkedinUrl: null, username: null, id: null)`
- `search(array $filters = [])` — e.g. `title`, `location`, `industry`, `seniority`
- `bulkEnrich(array $profiles)` — up to 100 identifiers per call
- `colleagues(string $id, page: 1, perPage: 25)`
- `similar(string $id, page: 1, perPage: 25)`
- `jobChanges(days: 90, companyId: null, page: 1, perPage: 25)`

**Company** (`$kooperativa->company`)
- `enrich(linkedinUrl: null, username: null, companyId: null, id: null)`
- `enrichRealtime(linkedinUrl: null, username: null)` — from the live source, **metered**, see below
- `check(linkedinUrl: null, username: null, companyId: null, id: null)`
- `search(array $filters = [])` — e.g. `country`, `industry`, `minStaff`, `maxStaff`
- `currentEmployees(string $companyId, page: 1, perPage: 25)`
- `pastEmployees(string $companyId, page: 1, perPage: 25)`
- `headcountBySeniority(string $companyId)`
- `hiringSignals(string $companyId, days: 90, page: 1, perPage: 25)`

**Monitors** (`$kooperativa->monitors`, webhooks)
- `list()`
- `create(string $type, string $subjectUrl, string $webhookUrl, label: null, events: null)`
- `delete(string $id)`

Every method returns the parsed JSON response as an associative array. Errors throw `Kooperativa\KooperativaApiError` with `getStatus()` and `getApiCode()`.

## Realtime enrichment and billing

Everything above is included in the flat license, with no per-request charge, except the two `enrichRealtime` methods. Those read from the live source rather than from our data lake, and are metered at **$0.001 per call** on top of the license, which is still required.

Three things are worth knowing before you call them in a loop:

- **A miss still costs.** A call is billed whenever the live source actually answered, so a `404` costs the same as a hit, because the lookup happened either way. Only a `503`, meaning we could not reach the source at all, is not billed.
- **A billed call can land in your `catch`.** A `404` throws `KooperativaApiError`, so a call you handle as a failure has still been charged. If you are counting spend, count calls, not successes.
- **There is no cache in front of them.** Calling `enrichRealtime` twice for the same person bills twice. The result is written back to the data lake though, so a following plain `enrich` is free and returns what the realtime call just returned.

Reach for `enrich` first: it is included, and roughly 4x faster. Use `enrichRealtime` when the record is missing from the lake, or when its `fetched_at` is not recent enough for what you are doing.

Full parameter reference: [docs.kooperativa.io](https://docs.kooperativa.io).

## License

MIT
