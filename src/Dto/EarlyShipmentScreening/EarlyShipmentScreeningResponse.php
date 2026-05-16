<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\EarlyShipmentScreening;

/**
 * Response from `POST /early-shipment-screening`.
 *
 * Mirrors `supermodelIoLogisticsExpressEarlyShipmentScreeningResponse`.
 * The schema itself is minimal — a `status` description and an
 * optional `warnings[]` list. DHL's narrative documents reference a
 * RED/GREEN screening outcome; that signal is encoded inside the
 * `status` string at the time of writing rather than a discrete
 * field. Callers wanting strong typing on RED/GREEN should parse
 * the string themselves.
 */
final readonly class EarlyShipmentScreeningResponse
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        public string $status,
        public array $warnings = [],
    ) {
    }
}
