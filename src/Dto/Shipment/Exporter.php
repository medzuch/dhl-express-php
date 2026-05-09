<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Exporter identification on an export declaration.
 *
 * At least one of `id` or `code` must be provided. Both are optional
 * in isolation but at least one is required when this block is used.
 */
final readonly class Exporter
{
    public function __construct(
        public ?string $id = null,
        public ?string $code = null,
    ) {
        if ($this->id === null && $this->code === null) {
            throw new \InvalidArgumentException(
                'Exporter requires at least one of id or code.',
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $result = [];

        if ($this->id !== null) {
            $result['id'] = $this->id;
        }
        if ($this->code !== null) {
            $result['code'] = $this->code;
        }

        return $result;
    }
}
