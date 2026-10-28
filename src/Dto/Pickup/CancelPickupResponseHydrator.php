<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

use Medzuch\DhlExpress\Support\HydrationHelper;

/**
 * Hydrates the `DELETE /pickups/{id}` JSON response into {@see CancelPickupResponse}.
 * Tolerates an empty body (all fields null).
 */
final class CancelPickupResponseHydrator
{
    /**
     * @param array<string, mixed> $body
     */
    public function hydrate(array $body): CancelPickupResponse
    {
        return new CancelPickupResponse(
            status: HydrationHelper::nullableStringField($body, 'status'),
            title: HydrationHelper::nullableStringField($body, 'title'),
            detail: HydrationHelper::nullableStringField($body, 'detail'),
            instance: HydrationHelper::nullableStringField($body, 'instance'),
            message: HydrationHelper::nullableStringField($body, 'message'),
        );
    }
}
