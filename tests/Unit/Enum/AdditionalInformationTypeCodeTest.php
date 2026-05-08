<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Enum;

use Medzuch\DhlExpress\Enum\AdditionalInformationTypeCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdditionalInformationTypeCodeTest extends TestCase
{
    /**
     * @return iterable<string, array{AdditionalInformationTypeCode, string}>
     */
    public static function caseProvider(): iterable
    {
        yield 'allValueAddedServices' => [AdditionalInformationTypeCode::AllValueAddedServices, 'allValueAddedServices'];
        yield 'allValueAddedServicesAndRuleGroups' => [AdditionalInformationTypeCode::AllValueAddedServicesAndRuleGroups, 'allValueAddedServicesAndRuleGroups'];
        yield 'sortCodes' => [AdditionalInformationTypeCode::SortCodes, 'sortCodes'];
    }

    #[DataProvider('caseProvider')]
    public function testBackingValueMatchesDhlCode(AdditionalInformationTypeCode $case, string $expected): void
    {
        self::assertSame($expected, $case->value);
    }

    public function testCoversAllThreeCases(): void
    {
        self::assertCount(3, AdditionalInformationTypeCode::cases());
    }
}
