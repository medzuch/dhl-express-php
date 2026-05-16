<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * One recipient of a shipment-created email notification.
 *
 * Mirrors an item of `shipmentNotification[]` (spec lines 12549–12598).
 * DHL accepts up to 5 entries; only `email` is currently a valid
 * channel type, so the DTO encodes it as a literal default.
 */
final readonly class ShipmentNotification
{
    public function __construct(
        public string $receiverId,
        public ?string $languageCode = null,
        public ?string $languageCountryCode = null,
        public ?string $bespokeMessage = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = [
            'typeCode' => 'email',
            'receiverId' => $this->receiverId,
        ];

        if ($this->languageCode !== null) {
            $payload['languageCode'] = $this->languageCode;
        }
        if ($this->languageCountryCode !== null) {
            $payload['languageCountryCode'] = $this->languageCountryCode;
        }
        if ($this->bespokeMessage !== null) {
            $payload['bespokeMessage'] = $this->bespokeMessage;
        }

        return $payload;
    }
}
