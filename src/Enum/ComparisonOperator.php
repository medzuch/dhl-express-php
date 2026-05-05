<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Comparison operator for the reference-data filter clause.
 *
 * The three values from the OpenAPI inline `enum` on the
 * `comparisonOperator` query parameter. Default is `equal`
 * when the parameter is omitted.
 */
enum ComparisonOperator: string
{
    case Equal = 'equal';
    case NotEqual = 'notEqual';
    case Contains = 'contains';
}
