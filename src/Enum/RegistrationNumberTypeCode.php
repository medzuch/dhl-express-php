<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type of registration number that identifies a shipping party.
 *
 * 25 codes drawn from the `registrationNumberTypeCode` sheet of
 * `dhl_reference_data.xlsx`. Each code carries country and
 * shipping-role applicability constraints (e.g. CNP is BR-only,
 * SDT is shipper-only, SUB is DE-only) — those constraints belong
 * in the shipment builder, not the enum, since they depend on the
 * wider request context.
 */
enum RegistrationNumberTypeCode: string
{
    case VAT = 'VAT';
    case EIN = 'EIN';
    case SSN = 'SSN';
    case EOR = 'EOR';
    case DUN = 'DUN';
    case FED = 'FED';
    case STA = 'STA';
    case CNP = 'CNP';
    case SDT = 'SDT';
    case FTZ = 'FTZ';
    case DAN = 'DAN';
    case TAN = 'TAN';
    case DTF = 'DTF';
    case DUT = 'DUT';
    case RGP = 'RGP';
    case NID = 'NID';
    case PAS = 'PAS';
    case MID = 'MID';
    case IMS = 'IMS';
    case EIC = 'EIC';
    case FTN = 'FTN';
    case CIC = 'CIC';
    case PEP = 'PEP';
    case FII = 'FII';
    case SUB = 'SUB';
}
