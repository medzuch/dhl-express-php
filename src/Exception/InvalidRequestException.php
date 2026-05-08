<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Exception;

/**
 * Thrown by request builders when one or more cross-field rules
 * fail before the request reaches DHL. Distinct from
 * {@see DhlValidationException}, which represents server-side 400 / 422
 * responses.
 *
 * Builders accumulate every problem they detect during `build()` and
 * surface them all at once — the same instance carries the full list
 * via {@see self::errors()} so callers can present every issue without
 * re-running the build.
 */
final class InvalidRequestException extends DhlException
{
    /**
     * @param list<array{field: string, message: string}> $errors
     */
    public function __construct(
        private readonly array $errors,
    ) {
        parent::__construct($this->summarize($errors));
    }

    /**
     * @return list<array{field: string, message: string}>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @param list<array{field: string, message: string}> $errors
     */
    private function summarize(array $errors): string
    {
        if ($errors === []) {
            return 'Invalid request: no errors recorded.';
        }

        $parts = [];
        foreach ($errors as $error) {
            $parts[] = sprintf('%s: %s', $error['field'], $error['message']);
        }

        return 'Invalid request: ' . implode('; ', $parts);
    }
}
