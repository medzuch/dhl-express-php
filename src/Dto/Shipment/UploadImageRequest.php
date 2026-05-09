<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;

/**
 * Request body for `PATCH /shipments/{id}/upload-image`.
 *
 * Uploads one or more base64-encoded customs document images
 * (Paperless Trade / PLT flow) to an existing shipment.
 */
final readonly class UploadImageRequest
{
    /**
     * @param list<Account>        $accounts
     * @param list<DocumentImage>  $documentImages
     */
    public function __construct(
        public DateTimeImmutable $originalPlannedShippingDate,
        public array $accounts,
        public string $productCode,
        public array $documentImages,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'originalPlannedShippingDate' => $this->originalPlannedShippingDate->format('Y-m-d'),
            'accounts' => array_map(
                static fn (Account $account): array => $account->toArray(),
                $this->accounts,
            ),
            'productCode' => $this->productCode,
            'documentImages' => array_map(
                static fn (DocumentImage $image): array => $image->toArray(),
                $this->documentImages,
            ),
        ];
    }
}
