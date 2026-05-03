<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Http;

use Medzuch\DhlExpress\Http\MessageReferenceGenerator;
use Medzuch\DhlExpress\Http\RandomMessageReferenceGenerator;
use Medzuch\DhlExpress\ValueObject\MessageReference;
use PHPUnit\Framework\TestCase;

final class RandomMessageReferenceGeneratorTest extends TestCase
{
    public function testImplementsTheGeneratorInterface(): void
    {
        $generator = new RandomMessageReferenceGenerator();

        self::assertInstanceOf(MessageReferenceGenerator::class, $generator);
    }

    public function testProducesAMessageReferenceInstance(): void
    {
        $generator = new RandomMessageReferenceGenerator();

        $reference = $generator->generate();

        self::assertInstanceOf(MessageReference::class, $reference);
    }

    public function testProducesUuidV4Format(): void
    {
        $generator = new RandomMessageReferenceGenerator();

        $reference = $generator->generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $reference->value,
        );
    }

    public function testGeneratesDistinctValuesAcrossInvocations(): void
    {
        $generator = new RandomMessageReferenceGenerator();

        $values = [];
        for ($i = 0; $i < 32; $i++) {
            $values[] = $generator->generate()->value;
        }

        self::assertCount(32, array_unique($values));
    }
}
