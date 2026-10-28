<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * A declaration note on an export declaration (MyDHL API 3.4.0).
 *
 * `value` is a string up to 700 characters. Up to 3 notes are
 * allowed per export declaration.
 */
final readonly class DeclarationNote
{
    public function __construct(
        public string $value,
    ) {
        if (strlen($value) > 700) {
            throw new \InvalidArgumentException(
                'DeclarationNote value must be at most 700 characters.',
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['value' => $this->value];
    }
}
