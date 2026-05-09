<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

/**
 * Typed response from `PATCH /pickups/{dispatchConfirmationNumber}`.
 *
 * Mirrors `supermodelIoLogisticsExpressUpdatePickupResponse`.
 *
 * @property list<string> $warnings
 */
final readonly class UpdatePickupResponse
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        public string $dispatchConfirmationNumber,
        public ?string $readyByTime,
        public ?string $nextPickupDate,
        public array $warnings,
    ) {
    }
}
