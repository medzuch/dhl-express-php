<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Enum\UnitSystem;

/**
 * Request body for `PATCH /shipments/{id}/upload-invoice-data`.
 *
 * Uploads structured invoice/customs data (as opposed to image files)
 * for Paperless Trade (PLT). The export declarations are nested under
 * `content.exportDeclaration` in the wire format.
 */
final readonly class UploadInvoiceDataRequest
{
    /**
     * @param list<ExportDeclaration> $exportDeclarations
     * @param list<Account>           $accounts
     */
    public function __construct(
        public array $exportDeclarations,
        public string $currency,
        public UnitSystem $unitOfMeasurement,
        public array $accounts = [],
        public ?DateTimeImmutable $plannedShipDate = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->plannedShipDate !== null) {
            $payload['plannedShipDate'] = $this->plannedShipDate->format('Y-m-d');
        }

        if ($this->accounts !== []) {
            $payload['accounts'] = array_map(
                static fn (Account $account): array => $account->toArray(),
                $this->accounts,
            );
        }

        $payload['content'] = [
            'exportDeclaration' => array_map(
                static fn (ExportDeclaration $declaration): array => $declaration->toArray(),
                $this->exportDeclarations,
            ),
            'currency' => $this->currency,
            'unitOfMeasurement' => $this->unitOfMeasurement->value,
        ];

        return $payload;
    }
}
