<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Dto\Shipment;

use InvalidArgumentException;
use Medzuch\DhlExpress\Dto\Shipment\LineItemWeight;
use PHPUnit\Framework\TestCase;

final class LineItemWeightTest extends TestCase
{
    public function testNetValueOnlySerializesNetValue(): void
    {
        self::assertSame(['netValue' => 1.5], (new LineItemWeight(netValue: 1.5))->toArray());
    }

    public function testGrossValueOnlySerializesGrossValue(): void
    {
        self::assertSame(['grossValue' => 1.8], (new LineItemWeight(grossValue: 1.8))->toArray());
    }

    public function testBothValuesSerialize(): void
    {
        self::assertSame(
            ['netValue' => 1.5, 'grossValue' => 1.8],
            (new LineItemWeight(netValue: 1.5, grossValue: 1.8))->toArray(),
        );
    }

    public function testNeitherValueIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('netValue or grossValue');

        new LineItemWeight();
    }
}
