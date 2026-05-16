<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Enum\LabelTextPosition;

/**
 * One entry in `package.labelText` (max 6).
 *
 * Requires the ECOM26_84CI_003 transport-label template on the
 * matching `imageOptions[].typeCode=label` entry. All three fields are
 * required per spec.
 */
final readonly class LabelText
{
    public function __construct(
        public LabelTextPosition $position,
        public string $caption,
        public string $value,
    ) {
    }

    /**
     * @return array{position: string, caption: string, value: string}
     */
    public function toArray(): array
    {
        return [
            'position' => $this->position->value,
            'caption' => $this->caption,
            'value' => $this->value,
        ];
    }
}
