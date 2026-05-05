<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\ReferenceDataset;
use PHPUnit\Framework\TestCase;

final class ReferenceDatasetTest extends TestCase
{
    public function testCoversNineteenDatasetsPlusAll(): void
    {
        self::assertCount(20, ReferenceDataset::cases());
    }

    public function testRepresentativeDatasetsResolveToTheirBackingValues(): void
    {
        self::assertSame('country', ReferenceDataset::Country->value);
        self::assertSame('incoterm', ReferenceDataset::Incoterm->value);
        self::assertSame('productCode', ReferenceDataset::ProductCode->value);
        self::assertSame('returnStatusMessage', ReferenceDataset::ReturnStatusMessage->value);
        self::assertSame('all', ReferenceDataset::All->value);
    }

    public function testFromCanResolveKnownDataset(): void
    {
        self::assertSame(ReferenceDataset::Country, ReferenceDataset::from('country'));
    }
}
