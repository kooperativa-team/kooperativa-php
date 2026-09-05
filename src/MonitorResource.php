<?php

declare(strict_types=1);

namespace Kooperativa;

/** Webhook monitors under /monitors. */
final class MonitorResource
{
    public function __construct(private HttpClient $http)
    {
    }

    /** List all active webhook monitors for the workspace. */
    public function list(): array
    {
        return $this->http->get('/monitors');
    }

    /**
     * Subscribe to change events on a profile or company URL.
     *
     * $type must be "person" or "company". $webhookUrl must be HTTPS.
     *
     * @param array<int, string>|null $events
     */
    public function create(
        string $type,
        string $subjectUrl,
        string $webhookUrl,
        ?string $label = null,
        ?array $events = null
    ): array {
        $body = [
            'type' => $type,
            'subject_url' => $subjectUrl,
            'webhook_url' => $webhookUrl,
        ];
        if ($label !== null) {
            $body['label'] = $label;
        }
        if ($events !== null) {
            $body['events'] = $events;
        }
        return $this->http->post('/monitors', $body);
    }

    /** Stop monitoring and delete the monitor. */
    public function delete(string $id): array
    {
        return $this->http->delete('/monitors', ['id' => $id]);
    }
}
