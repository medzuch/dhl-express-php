<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\DocumentFunction;
use PHPUnit\Framework\TestCase;

final class DocumentFunctionTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (DocumentFunction $case): string => $case->value, DocumentFunction::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testExpectedCasesExist(): void
    {
        $values = array_map(static fn (DocumentFunction $case): string => $case->value, DocumentFunction::cases());

        self::assertEqualsCanonicalizing(['import', 'export', 'both'], $values);
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(DocumentFunction::Import, DocumentFunction::from('import'));
        self::assertSame(DocumentFunction::Export, DocumentFunction::from('export'));
        self::assertSame(DocumentFunction::Both, DocumentFunction::from('both'));
    }
}
