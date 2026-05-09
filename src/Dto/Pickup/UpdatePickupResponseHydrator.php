<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use Medzuch\DhlExpress\Support\HydrationHelper;

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
        return new UpdatePickupResponse(
            dispatchConfirmationNumber: HydrationHelper::stringField($body, 'dispatchConfirmationNumber'),
            readyByTime: HydrationHelper::nullableStringField($body, 'readyByTime'),
            nextPickupDate: HydrationHelper::nullableStringField($body, 'nextPickupDate'),
            warnings: HydrationHelper::stringList($body['warnings'] ?? null),
        );
    }
}
