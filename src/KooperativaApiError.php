<?php

declare(strict_types=1);

namespace Kooperativa;

use Exception;

/**
 * Thrown when the Kooperativa API returns a non-2xx response.
 *
 * `getCode()` overloads PHP's built-in numeric exception code, so it holds
 * the HTTP status. Use `getApiCode()` for the API's own stable,
 * machine-readable error code (e.g. "NOT_FOUND", "LICENSE_INACTIVE"),
 * switch on that rather than the message, whose wording may change.
 */
class KooperativaApiError extends Exception
{
    private ?string $apiCode;

    public function __construct(int $status, string $message, ?string $apiCode = null)
    {
        parent::__construct($message, $status);
        $this->apiCode = $apiCode;
    }

    public function getStatus(): int
    {
        return $this->getCode();
    }

    public function getApiCode(): ?string
    {
        return $this->apiCode;
    }
}
