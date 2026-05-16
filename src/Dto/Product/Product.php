<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Product;

/** One DHL product available for the requested shipment. */
final readonly class Product
{
    public function __construct(
        public string $productName,
        public string $productCode,
        public string $localProductCode,
        public string $localProductCountryCode,
        public string $networkTypeCode,
        public bool $isCustomerAgreement,
    ) {
    }
}
