<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Lowercase `true`/`false` string flag used by the `/servicepoints`
 * query parameters `b64`, `isResultsSpecificCapabRequired`, and
 * `hasMixedUnits`. The wire value is the literal string, not a JSON
 * boolean.
 */
enum TrueFalseFlag: string
{
    case True = 'true';
    case False = 'false';
}
