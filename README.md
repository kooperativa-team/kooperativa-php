# kooperativa/sdk

Official PHP SDK for the [Kooperativa](https://kooperativa.io) API. Enrich and search professional profiles and companies, track hiring signals and job changes, and manage webhook monitors.

## Installation

```bash
composer require kooperativa/sdk
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
- `check(linkedinUrl: null, username: null, id: null)`
- `search(array $filters = [])` — e.g. `title`, `location`, `industry`, `seniority`
- `bulkEnrich(array $profiles)` — up to 100 identifiers per call
- `colleagues(string $id, page: 1, perPage: 25)`
- `similar(string $id, page: 1, perPage: 25)`
- `jobChanges(days: 90, companyId: null, page: 1, perPage: 25)`

**Company** (`$kooperativa->company`)
- `enrich(linkedinUrl: null, username: null, companyId: null, id: null)`
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

Full parameter reference: [docs.kooperativa.io](https://docs.kooperativa.io).

## License

MIT
