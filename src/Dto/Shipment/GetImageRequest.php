<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\GetImageDocumentTypeCode;
use Medzuch\DhlExpress\Enum\GetImageEncodingFormat;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\YearMonth;

/**
 * Query parameters for `GET /shipments/{id}/get-image`.
 *
 * `typeCodes` and `pickupYearAndMonth` are required by the OpenAPI
 * spec. At least one of `shipperAccountNumber` or
 * `payerAccountNumber` must be provided (DHL constraint).
 */
final readonly class GetImageRequest
{
    /**
     * @param non-empty-list<GetImageDocumentTypeCode> $typeCodes
     */
    public function __construct(
        public array $typeCodes,
        public YearMonth $pickupYearAndMonth,
        public ?AccountNumber $shipperAccountNumber = null,
        public ?AccountNumber $payerAccountNumber = null,
        public ?GetImageEncodingFormat $encodingFormat = null,
        public ?bool $allInOnePDF = null,
        public ?bool $compressedPackage = null,
    ) {
        if ($this->shipperAccountNumber === null && $this->payerAccountNumber === null) {
            throw new \InvalidArgumentException(
                'GetImageRequest requires at least one of shipperAccountNumber or payerAccountNumber.',
            );
        }
        // Defensive runtime check for callers that pass an unchecked array
        // despite the `non-empty-list` PHPDoc contract.
        // @phpstan-ignore identical.alwaysFalse
        if ($this->typeCodes === []) {
            throw new \InvalidArgumentException(
                'GetImageRequest requires at least one typeCode.',
            );
        }
    }

    /**
     * Build the query parameter map.
     *
     * The PHP-side field is plural (`typeCodes`) but the wire-side
     * key is singular (`typeCode`), repeated once per value
     * (`?typeCode=waybill&typeCode=commercial-invoice`). The OpenAPI
     * spec declares the parameter as a single `string` with no
     * `explode: true` / `array` annotation, yet both example URLs in
     * the same operation repeat the key — so the implementation
     * follows the examples and DHL's actual behavior.
     *
     * @return array<string, string|list<string>>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->shipperAccountNumber !== null) {
            $params['shipperAccountNumber'] = (string) $this->shipperAccountNumber;
        }
        if ($this->payerAccountNumber !== null) {
            $params['payerAccountNumber'] = (string) $this->payerAccountNumber;
        }

        $params['typeCode'] = array_map(
            static fn (GetImageDocumentTypeCode $code): string => $code->value,
            $this->typeCodes,
        );
        $params['pickupYearAndMonth'] = (string) $this->pickupYearAndMonth;

        if ($this->encodingFormat !== null) {
            $params['encodingFormat'] = $this->encodingFormat->value;
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
