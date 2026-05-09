<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use Medzuch\DhlExpress\Support\HydrationHelper;

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
        return new CreatePickupResponse(
            dispatchConfirmationNumbers: HydrationHelper::stringList($body['dispatchConfirmationNumbers'] ?? null),
            readyByTime: HydrationHelper::nullableStringField($body, 'readyByTime'),
            nextPickupDate: HydrationHelper::nullableStringField($body, 'nextPickupDate'),
            warnings: HydrationHelper::stringList($body['warnings'] ?? null),
        );
    }
}
