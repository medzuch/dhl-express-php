<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\InvoiceFunction;
use PHPUnit\Framework\TestCase;

final class InvoiceFunctionTest extends TestCase
{
    public function testAllCasesHaveUniqueValues(): void
    {
        $values = array_map(static fn (InvoiceFunction $case): string => $case->value, InvoiceFunction::cases());
        self::assertCount(count($values), array_unique($values), 'Duplicate backing values found');
    }

    public function testExpectedCasesExist(): void
    {
        self::assertSame('import', InvoiceFunction::Import->value);
        self::assertSame('export', InvoiceFunction::Export->value);
        self::assertSame('both', InvoiceFunction::Both->value);
    }

    public function testCanBeCreatedFromValue(): void
    {
        self::assertSame(InvoiceFunction::Import, InvoiceFunction::from('import'));
        self::assertSame(InvoiceFunction::Export, InvoiceFunction::from('export'));
        self::assertSame(InvoiceFunction::Both, InvoiceFunction::from('both'));
    }
}
