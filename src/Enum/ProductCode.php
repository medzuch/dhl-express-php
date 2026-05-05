<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express product code.
 *
 * The 36 single-character wire codes that identify a DHL Express
 * shipping product, drawn from the `productCode` sheet of
 * `dhl_reference_data.xlsx`. Wire values are single chars (digits
 * or uppercase letters); case names are descriptive PascalCase
 * because single-character case names hurt readability.
 *
 * Where a product family covers both document and non-document
 * variants, the suffix Doc / NonDoc disambiguates. Express
 * Worldwide ships in four variants (DOX, ECX, WPX, DOM) that share
 * the family name and are disambiguated by their well-known
 * three-letter content code instead.
 *
 * Each product carries its own weight / dimension / delivery-time
 * constraints — those tables live in the workbook, not here.
 */
enum ProductCode: string
{
    case LogisticsServices = '0';
    case ExpressDomestic1200 = '1';
    case AcsXcelerate = '2';
    case ExpressWorldwideDom = '3';
    case SamedayJetline = '4';
    case SamedaySprintline = '5';
    case AirCapacitySales = '6';
    case ExpressEasyDoc = '7';
    case ExpressEasyNonDoc = '8';
    case ParcelProductDoc = '9';
    case EconomyBreakbulk = 'A';
    case ExpressBreakbulk = 'B';
    case MedicalExpressDoc = 'C';
    case ExpressWorldwideDox = 'D';
    case Express900NonDoc = 'E';
    case FreightWorldwide = 'F';
    case EconomySelectDomestic = 'G';
    case EconomySelectNonDoc = 'H';
    case ExpressDomestic900 = 'I';
    case Placeholder = 'J';
    case Express900Doc = 'K';
    case Express1030Doc = 'L';
    case Express1030NonDoc = 'M';
    case ExpressDomestic = 'N';
    case ExpressDomestic1030 = 'O';
    case ExpressWorldwideWpx = 'P';
    case MedicalExpressNonDoc = 'Q';
    case Globalmail = 'R';
    case SameDay = 'S';
    case Express1200Doc = 'T';
    case ExpressWorldwideEcx = 'U';
    case ParcelProductNonDoc = 'V';
    case EconomySelectDoc = 'W';
    case ExpressEnvelope = 'X';
    case Express1200NonDoc = 'Y';
    case DutiesAndTaxes = 'Z';
}
