<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Logging;

use Medzuch\DhlExpress\Tests\Integration\Logging\JsonLineFileLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

final class JsonLineFileLoggerTest extends TestCase
{
    private string $logPath = '';

    protected function setUp(): void
    {
        $this->logPath = sys_get_temp_dir() . '/dhl-test-logger-' . uniqid('', true) . '/run.log';
    }

    protected function tearDown(): void
    {
        if (is_file($this->logPath)) {
            unlink($this->logPath);
        }
        $directory = dirname($this->logPath);
        if (is_dir($directory)) {
            rmdir($directory);
        }
    }

    public function testWritesEachLogCallAsOneJsonLine(): void
    {
        $logger = new JsonLineFileLogger($this->logPath);

        $logger->debug('first message', ['a' => 1]);
        $logger->debug('second message', ['b' => 'two']);

        $lines = $this->readLines();
        self::assertCount(2, $lines);

        $first = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('debug', $first['level']);
        self::assertSame('first message', $first['message']);
        self::assertSame(['a' => 1], $first['context']);
        self::assertArrayHasKey('ts', $first);

        $second = json_decode($lines[1], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('second message', $second['message']);
        self::assertSame(['b' => 'two'], $second['context']);
    }

    public function testTruncatesFileOnFirstWriteOfEachInstance(): void
    {
        $seed = new JsonLineFileLogger($this->logPath);
        $seed->info('stale');

        $logger = new JsonLineFileLogger($this->logPath);
        $logger->info('fresh');

        $lines = $this->readLines();
        self::assertCount(1, $lines);
        $record = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('fresh', $record['message']);
    }

    public function testCreatesIntermediateDirectories(): void
    {
        $deepPath = sys_get_temp_dir() . '/dhl-test-logger-' . uniqid('', true) . '/a/b/c/run.log';

        try {
            $logger = new JsonLineFileLogger($deepPath);
            $logger->log(LogLevel::WARNING, 'deep');

            self::assertFileExists($deepPath);
        } finally {
            if (is_file($deepPath)) {
                unlink($deepPath);
            }
            for ($dir = dirname($deepPath); str_starts_with($dir, sys_get_temp_dir() . '/dhl-test-logger-'); $dir = dirname($dir)) {
                if (is_dir($dir)) {
                    @rmdir($dir);
                }
            }
        }
    }

    /**
     * @return list<string>
     */
    private function readLines(): array
    {
        $contents = file_get_contents($this->logPath);
        self::assertNotFalse($contents);
        $lines = array_values(array_filter(explode("\n", $contents), static fn (string $line): bool => $line !== ''));

        return $lines;
    }
}
