<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * A free-text remark on an export declaration.
 *
 * `value` is a string up to 500 characters. Up to 3 remarks are
 * allowed per export declaration.
 */
final readonly class ExportRemark
{
    public function __construct(
        public string $value,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['value' => $this->value];
    }
}
