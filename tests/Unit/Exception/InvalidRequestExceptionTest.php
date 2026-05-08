<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Exception;

use Medzuch\DhlExpress\Exception\DhlException;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use PHPUnit\Framework\TestCase;

final class InvalidRequestExceptionTest extends TestCase
{
    public function testExposesAllAccumulatedErrors(): void
    {
        $errors = [
            ['field' => 'shipper', 'message' => 'shipper is required'],
            ['field' => 'packages[0].weight', 'message' => 'metric request cannot carry an LB-weighted package'],
        ];

        $exception = new InvalidRequestException($errors);

        self::assertSame($errors, $exception->errors());
    }

    public function testMessageSummarizesEveryError(): void
    {
        $exception = new InvalidRequestException([
            ['field' => 'shipper', 'message' => 'shipper is required'],
            ['field' => 'plannedShippingDate', 'message' => 'plannedShippingDate is required'],
        ]);

        self::assertStringContainsString('shipper: shipper is required', $exception->getMessage());
        self::assertStringContainsString('plannedShippingDate: plannedShippingDate is required', $exception->getMessage());
    }

    public function testIsSubclassOfDhlException(): void
    {
        $exception = new InvalidRequestException([]);

        self::assertInstanceOf(DhlException::class, $exception);
    }
}
