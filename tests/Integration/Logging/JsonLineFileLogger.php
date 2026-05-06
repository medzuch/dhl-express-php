<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Logging;

use DateTimeImmutable;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Stringable;

/**
 * Append-only JSON-lines PSR-3 logger used by integration tests so
 * the actual DHL request and response payloads land on disk for
 * inspection. One file per test method, truncated on first write so
 * each run starts clean.
 *
 * Lives under `tests/Integration/` because it is test-only scaffolding;
 * the library itself does not ship a file logger.
 */
final class JsonLineFileLogger extends AbstractLogger
{
    private bool $truncated = false;

    public function __construct(private readonly string $filePath)
    {
        $directory = dirname($this->filePath);
        if (!is_dir($directory) && !mkdir($directory, 0o755, true) && !is_dir($directory)) {
            throw new RuntimeException("Unable to create log directory: {$directory}");
        }
    }

    /**
     * @param array<mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $record = [
            'ts' => (new DateTimeImmutable())->format(DATE_ATOM),
            'level' => (string) $level,
            'message' => (string) $message,
            'context' => $context,
        ];

        $line = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        if ($line === false) {
            $line = json_encode([
                'ts' => $record['ts'],
                'level' => $record['level'],
                'message' => $record['message'],
                'context' => ['_encoding_error' => 'context contained non-encodable values'],
            ]);
        }

        $flags = $this->truncated ? FILE_APPEND : 0;
        file_put_contents($this->filePath, $line . "\n", $flags);
        $this->truncated = true;
    }
}
