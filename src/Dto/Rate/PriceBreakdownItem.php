<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Rate;

/**
 * One line of the rolled-up `totalPriceBreakdown.priceBreakdown` —
 * `typeCode` is one of DHL's three-to-five-letter charge codes
 * (`STTXA` total tax, `STDIS` total discount, `SPRQT` shipment
 * weight charge, …). Charge dictionaries live in the reference
 * workbook; we keep the typeCode as a free string here.
 */
final readonly class PriceBreakdownItem
{
    public function __construct(
        public string $typeCode,
        public float $price,
    ) {
    }
}
