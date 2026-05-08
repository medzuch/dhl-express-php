<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Pickup configuration for a create-shipment request.
 *
 * Phase 4a only models the no-pickup case (`isRequested=false`) and the
 * presence-of-flag case (`isRequested=true` with no pickup-time
 * details). Pickup time / address / requestor details are deferred to
 * Phase 4b alongside the broader customs / customer-detail flow.
 */
final readonly class Pickup
{
    public function __construct(
        public bool $isRequested,
    ) {
    }

    /**
     * @return array{isRequested: bool}
     */
    public function toArray(): array
    {
        return [
            'isRequested' => $this->isRequested,
        ];
    }
}
