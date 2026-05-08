<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\PackageReferenceTypeCode;

/**
 * One customer reference attached to a shipment package.
 *
 * Mirrors `supermodelIoLogisticsExpressPackageReference`. The DHL spec
 * narrows `typeCode` to a 14-code subset for `POST /shipments`
 * (AAO, CU, FF, FN, IBC, LLR, OBC, PRN, ACP, ACS, ACR, CDN, STD, CO).
 * We reuse the broader {@see PackageReferenceTypeCode} enum (81 cases
 * from the reference workbook); DHL surfaces an out-of-subset code as
 * a server-side {@see \Medzuch\DhlExpress\Exception\DhlValidationException}.
 */
final readonly class PackageReference
{
    public function __construct(
        public string $value,
        public ?PackageReferenceTypeCode $typeCode = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = [
            'value' => $this->value,
        ];

        if ($this->typeCode !== null) {
            $payload['typeCode'] = $this->typeCode->value;
        }

        return $payload;
    }
}
