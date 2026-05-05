<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Type of registration number that identifies a shipping party.
 *
 * Twenty-nine codes drawn from the OpenAPI inline enum and
 * Reference Data Guide section 7. Each code carries country and
 * shipping-role applicability constraints (e.g. CNP is BR-only,
 * SDT is shipper-only) — those constraints belong in the
 * shipment builder, not the enum, since they depend on the wider
 * request context.
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
    case IE = 'IE';
    case INN = 'INN';
    case KPP = 'KPP';
    case OGR = 'OGR';
    case OKP = 'OKP';
    case MRN = 'MRN';
    case SDT = 'SDT';
    case FTZ = 'FTZ';
    case DAN = 'DAN';
    case TAN = 'TAN';
    case DTF = 'DTF';
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
}
