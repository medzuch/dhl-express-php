<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Address;

/**
 * Response from `GET /address-validate`.
 *
 * `warnings` carries any DHL-side notices; `addresses` is the list
 * of resolved address candidates — empty when `strictValidation`
 * is true and no exact match was found.
 */
final readonly class AddressValidateResponse
{
    /**
     * @param list<string>           $warnings
     * @param list<ValidatedAddress> $addresses
     */
    public function __construct(
        public array $warnings,
        public array $addresses,
    ) {
    }
}
