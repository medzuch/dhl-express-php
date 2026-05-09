<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

/**
 * Hydrates the `POST /pickups` JSON response into {@see CreatePickupResponse}.
 */
final class CreatePickupResponseHydrator
{
    /**
     * @param array<string, mixed> $body
     */
    public function hydrate(array $body): CreatePickupResponse
    {
        $numbers = [];
        $rawNumbers = $body['dispatchConfirmationNumbers'] ?? null;
        if (is_array($rawNumbers)) {
            foreach ($rawNumbers as $entry) {
                if (is_string($entry)) {
                    $numbers[] = $entry;
                }
            }
        }

        $warnings = [];
        $rawWarnings = $body['warnings'] ?? null;
        if (is_array($rawWarnings)) {
            foreach ($rawWarnings as $entry) {
                if (is_string($entry)) {
                    $warnings[] = $entry;
                }
            }
        }

        return new CreatePickupResponse(
            dispatchConfirmationNumbers: $numbers,
            readyByTime: $this->nullableString($body, 'readyByTime'),
            nextPickupDate: $this->nullableString($body, 'nextPickupDate'),
            warnings: $warnings,
        );
    }

    /**
     * @param array<string, mixed> $source
     */
    private function nullableString(array $source, string $key): ?string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : null;
    }
}
