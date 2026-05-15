<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Method by which a Value Added Service is paid for.
 *
 * Marked "for future use" by DHL — the spec defines a single
 * enumerable value (`cash`) reserved for upcoming activation. We
 * expose it for forward compatibility; emitting a `method` today is
 * accepted by the wire schema (`additionalProperties: false`) but
 * has no behavioural effect on rate quoting.
 *
 * Source: `supermodelIoLogisticsExpressValueAddedServicesRates.method`
 * in `docs/dhl/dhl_openapi.yaml`.
 */
enum ValueAddedServiceMethod: string
{
    case Cash = 'cash';
}
