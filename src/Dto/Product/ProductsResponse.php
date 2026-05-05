<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Product;

/**
 * Response from `GET /products`.
 *
 * Carries the list of DHL products available for the queried
 * origin / destination / weight combination.
 */
final readonly class ProductsResponse
{
    /**
     * @param list<Product> $products
     */
    public function __construct(
        public array $products,
    ) {
    }
}
