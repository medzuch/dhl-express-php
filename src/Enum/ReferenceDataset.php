<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Dataset name accepted by the `/reference-data` endpoint.
 *
 * The 20 values from the OpenAPI inline `enum` on the
 * `datasetName` query parameter — these are the same datasets
 * that ship as sheets in `dhl_reference_data.xlsx`. `All`
 * (`'all'`) requests every dataset in one call.
 */
enum ReferenceDataset: string
{
    case Country = 'country';
    case CountryPostalcodeFormat = 'countryPostalcodeFormat';
    case DangerousGoods = 'dangerousGoods';
    case Incoterm = 'incoterm';
    case ProductCode = 'productCode';
    case ServiceCode = 'serviceCode';
    case PackageTypeCode = 'packageTypeCode';
    case DocumentTypeCode = 'documentTypeCode';
    case CustomerShipmentReferenceType = 'customerShipmentReferenceType';
    case CustomerPackageReferenceType = 'customerPackageReferenceType';
    case InvoiceReferenceType = 'invoiceReferenceType';
    case InvoiceItemReferenceType = 'invoiceItemReferenceType';
    case RegistrationNumberTypeCode = 'registrationNumberTypeCode';
    case CommodityCategory = 'commodityCategory';
    case ReturnStatusMessage = 'returnStatusMessage';
    case TrackingEventCode = 'trackingEventCode';
    case UnitOfMeasurement = 'unitOfMeasurement';
    case LanguageCode = 'languageCode';
    case OutputImageTemplate = 'outputImageTemplate';
    case All = 'all';
}
