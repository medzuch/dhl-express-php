<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

/**
 * Typed response from `POST /pickups`.
 *
 * Mirrors `supermodelIoLogisticsExpressPickupResponse`. A single
 * request can yield multiple dispatch confirmation numbers when DHL
 * splits a consolidated pickup into sub-pickups.
 *
 * @property list<string> $dispatchConfirmationNumbers
 * @property list<string> $warnings
 */
final readonly class CreatePickupResponse
{
    /**
     * @param list<string> $dispatchConfirmationNumbers
     * @param list<string> $warnings
     */
    public function __construct(
        public array $dispatchConfirmationNumbers,
        public ?string $readyByTime,
        public ?string $nextPickupDate,
        public array $warnings,
    ) {
    }
}
