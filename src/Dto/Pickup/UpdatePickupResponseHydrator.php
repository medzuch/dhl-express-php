<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

/**
 * Hydrates the `PATCH /pickups/{id}` JSON response into {@see UpdatePickupResponse}.
 */
final class UpdatePickupResponseHydrator
{
    /**
     * @param array<string, mixed> $body
     */
    public function hydrate(array $body): UpdatePickupResponse
    {
        $warnings = [];
        $rawWarnings = $body['warnings'] ?? null;
        if (is_array($rawWarnings)) {
            foreach ($rawWarnings as $entry) {
                if (is_string($entry)) {
                    $warnings[] = $entry;
                }
            }
        }

        return new UpdatePickupResponse(
            dispatchConfirmationNumber: $this->string($body, 'dispatchConfirmationNumber'),
            readyByTime: $this->nullableString($body, 'readyByTime'),
            nextPickupDate: $this->nullableString($body, 'nextPickupDate'),
            warnings: $warnings,
        );
    }

    /**
     * @param array<string, mixed> $source
     */
    private function string(array $source, string $key): string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : '';
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
