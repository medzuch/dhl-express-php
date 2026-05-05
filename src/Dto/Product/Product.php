<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Product;

/**
 * One DHL product available for the requested shipment.
 *
 * Phase 3b ships the identification surface (codes, name,
 * customer-agreement flag); the breakdown / value-added-service
 * tables and weight echo land alongside the rating DTOs in
 * Phase 3d, where they are actually consumed.
 */
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
