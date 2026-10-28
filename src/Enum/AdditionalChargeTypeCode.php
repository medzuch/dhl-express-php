<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type code for an additional charge line on an export declaration.
 *
 * Matches the `additionalCharges[].typeCode` enum in
 * `specs/dhl/dpdhl-express-api-3.4.0_9.yaml`. Used inside
 * {@see \Medzuch\DhlExpress\Dto\Shipment\AdditionalCharge} to
 * classify costs beyond the line-item goods value — freight, fuel
 * surcharge, insurance, VAT, and so on.
 */
enum AdditionalChargeTypeCode: string
{
    case Admin = 'admin';
    case Delivery = 'delivery';
    case Documentation = 'documentation';
    case Expedite = 'expedite';
    case Export = 'export';
    case Freight = 'freight';
    case FuelSurcharge = 'fuel_surcharge';
    case Logistic = 'logistic';
    case Other = 'other';
    case Packaging = 'packaging';
    case Pickup = 'pickup';
    case Handling = 'handling';
    case Vat = 'vat';
    case Insurance = 'insurance';
    case ReverseCharge = 'reverse_charge';
}
