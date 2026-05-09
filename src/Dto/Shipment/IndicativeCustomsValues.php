<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Indicative customs duty and tax values on an export invoice.
 *
 * All values are optional (min 0). At least one field should be
 * populated when this object is present.
 */
final readonly class IndicativeCustomsValues
{
    public function __construct(
        public ?float $importCustomsDutyValue = null,
        public ?float $importTaxesValue = null,
        public ?float $totalWithImportDutiesAndTaxes = null,
    ) {
    }

    /**
     * @return array<string, float>
     */
    public function toArray(): array
    {
        $result = [];

        if ($this->importCustomsDutyValue !== null) {
            $result['importCustomsDutyValue'] = $this->importCustomsDutyValue;
        }
        if ($this->importTaxesValue !== null) {
            $result['importTaxesValue'] = $this->importTaxesValue;
        }
        if ($this->totalWithImportDutiesAndTaxes !== null) {
            $result['totalWithImportDutiesAndTaxes'] = $this->totalWithImportDutiesAndTaxes;
        }

        return $result;
    }
}
