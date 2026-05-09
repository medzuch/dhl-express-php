<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;

/**
 * Request body for `PATCH /shipments/{id}/add-piece`.
 *
 * Mirrors `supermodelIoLogisticsExpressAddPieceRequest`. Packages are
 * nested under `content.packages` in the wire format; all other fields
 * are at the root level.
 *
 * @see \Medzuch\DhlExpress\Api\ShipmentApi::addPiece()
 */
final readonly class AddPieceRequest
{
    /**
     * @param list<Account>         $accounts  1–3 accounts
     * @param list<AddPiecePackage> $packages  1–999 packages
     */
    public function __construct(
        public DateTimeImmutable $originalPlannedShippingDate,
        public string $productCode,
        public array $accounts,
        public array $packages,
        public ?AddPieceOutputImageProperties $outputImageProperties = null,
        public ?bool $getRateEstimates = null,
    ) {
    }

    /**
     * Serialize to the DHL wire shape for `PATCH /shipments/{id}/add-piece`.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'originalPlannedShippingDate' => $this->originalPlannedShippingDate->format('Y-m-d'),
            'productCode' => $this->productCode,
            'accounts' => array_map(
                static fn (Account $account): array => $account->toArray(),
                $this->accounts,
            ),
            'content' => [
                'packages' => array_map(
                    static fn (AddPiecePackage $package): array => $package->toArray(),
                    $this->packages,
                ),
            ],
        ];

        if ($this->outputImageProperties !== null) {
            $payload['outputImageProperties'] = $this->outputImageProperties->toArray();
        }
        if ($this->getRateEstimates !== null) {
            $payload['getRateEstimates'] = $this->getRateEstimates;
        }

        return $payload;
    }
}
