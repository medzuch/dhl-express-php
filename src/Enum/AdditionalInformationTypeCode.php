<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Extra blocks the caller can ask DHL to include in a rate response.
 *
 * Source: `supermodelIoLogisticsExpressRateRequest.getAdditionalInformation.typeCode`
 * in `specs/dhl/dhl_openapi.yaml`.
 */
enum AdditionalInformationTypeCode: string
{
    case AllValueAddedServices = 'allValueAddedServices';
    case AllValueAddedServicesAndRuleGroups = 'allValueAddedServicesAndRuleGroups';
    case SortCodes = 'sortCodes';
}
