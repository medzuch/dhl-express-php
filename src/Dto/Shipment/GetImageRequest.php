<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Query parameters for `GET /shipments/{id}/get-image`.
 *
 * Either `shipperAccountNumber` or `payerAccountNumber` must be
 * provided (at least one is required by DHL).
 */
final readonly class GetImageRequest
{
    /**
     * @param list<string> $typeCodes
     */
    public function __construct(
        public ?string $shipperAccountNumber = null,
        public ?string $payerAccountNumber = null,
        public array $typeCodes = [],
        public ?string $pickupYearAndMonth = null,
        public ?string $encodingFormat = null,
        public ?bool $allInOnePDF = null,
        public ?bool $compressedPackage = null,
    ) {
        if ($this->shipperAccountNumber === null && $this->payerAccountNumber === null) {
            throw new \InvalidArgumentException(
                'GetImageRequest requires at least one of shipperAccountNumber or payerAccountNumber.',
            );
        }
    }

    /**
     * @return array<string, string|list<string>>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->shipperAccountNumber !== null) {
            $params['shipperAccountNumber'] = $this->shipperAccountNumber;
        }
        if ($this->payerAccountNumber !== null) {
            $params['payerAccountNumber'] = $this->payerAccountNumber;
        }
        if ($this->typeCodes !== []) {
            $params['typeCode'] = $this->typeCodes;
        }
        if ($this->pickupYearAndMonth !== null) {
            $params['pickupYearAndMonth'] = $this->pickupYearAndMonth;
        }
        if ($this->encodingFormat !== null) {
            $params['encodingFormat'] = $this->encodingFormat;
        }
        if ($this->allInOnePDF !== null) {
            $params['allInOnePDF'] = $this->allInOnePDF ? 'true' : 'false';
        }
        if ($this->compressedPackage !== null) {
            $params['compressedPackage'] = $this->compressedPackage ? 'true' : 'false';
        }

        return $params;
    }
}
