<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use DateTimeImmutable;
use Medzuch\DhlExpress\Enum\InvoiceFunction;

/**
 * Invoice block inside an export declaration.
 *
 * `number` is required (1–35 chars). `date` is serialized as
 * `YYYY-MM-DD`. `function` describes whether the invoice is for an
 * import, export, or both directions.
 *
 * The two MyDHL API 3.4.0 invoice schemas differ, and both reject unknown
 * keys. {@see self::toArray()} produces the Create Shipment shape, which
 * never contains `function`. {@see self::toInvoiceDataArray()} produces
 * the invoice-data upload shape, which requires `function` and has no
 * signature or summary fields.
 *
 * The optional signature and summary fields (3.4.0) let DHL print a
 * signed, fully populated paperless-trade invoice:
 * `signatureName` / `signatureTitle` (1–35 chars), `signatureImage`
 * (base64), `instructions` (one entry, ≤ 300 chars),
 * `customerDataTextEntries` (≤ 6 entries, ≤ 45 chars each),
 * `totalNetWeight` / `totalGrossWeight` (≥ 0) and `termsOfPayment`.
 * They are Create Shipment only.
 */
final readonly class ExportInvoice
{
    /**
     * @param list<InvoiceReference> $customerReferences
     * @param list<string>           $instructions
     * @param list<string>           $customerDataTextEntries
     */
    public function __construct(
        public string $number,
        public DateTimeImmutable $date,
        public ?InvoiceFunction $function = null,
        public array $customerReferences = [],
        public ?IndicativeCustomsValues $indicativeCustomsValues = null,
        public ?PreCalculatedTotalValues $preCalculatedTotalValues = null,
        public ?string $signatureName = null,
        public ?string $signatureTitle = null,
        public ?string $signatureImage = null,
        public array $instructions = [],
        public array $customerDataTextEntries = [],
        public ?float $totalNetWeight = null,
        public ?float $totalGrossWeight = null,
        public ?string $termsOfPayment = null,
    ) {
        $this->assertLength('signatureName', $signatureName, 35);
        $this->assertLength('signatureTitle', $signatureTitle, 35);
        $this->assertLength('signatureImage', $signatureImage, 1048576);

        if (count($instructions) > 1) {
            throw new \InvalidArgumentException('ExportInvoice instructions accepts at most 1 entry.');
        }
        foreach ($instructions as $instruction) {
            $this->assertLength('instructions', $instruction, 300);
        }

        if (count($customerDataTextEntries) > 6) {
            throw new \InvalidArgumentException('ExportInvoice customerDataTextEntries accepts at most 6 entries.');
        }
        foreach ($customerDataTextEntries as $entry) {
            if (strlen($entry) > 45) {
                throw new \InvalidArgumentException(
                    'ExportInvoice customerDataTextEntries entries must be at most 45 characters.',
                );
            }
        }

        if ($totalNetWeight !== null && $totalNetWeight < 0) {
            throw new \InvalidArgumentException('ExportInvoice totalNetWeight must not be negative.');
        }
        if ($totalGrossWeight !== null && $totalGrossWeight < 0) {
            throw new \InvalidArgumentException('ExportInvoice totalGrossWeight must not be negative.');
        }
    }

    /**
     * Create Shipment shape (`supermodelIoLogisticsExpressShipmentExportDeclaration`
     * invoice): never contains `function`.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = $this->commonPayload(includeFunction: false);

        if ($this->signatureName !== null) {
            $payload['signatureName'] = $this->signatureName;
        }
        if ($this->signatureTitle !== null) {
            $payload['signatureTitle'] = $this->signatureTitle;
        }
        if ($this->signatureImage !== null) {
            $payload['signatureImage'] = $this->signatureImage;
        }
        if ($this->instructions !== []) {
            $payload['instructions'] = $this->instructions;
        }
        if ($this->customerDataTextEntries !== []) {
            $payload['customerDataTextEntries'] = $this->customerDataTextEntries;
        }
        if ($this->totalNetWeight !== null) {
            $payload['totalNetWeight'] = $this->totalNetWeight;
        }
        if ($this->totalGrossWeight !== null) {
            $payload['totalGrossWeight'] = $this->totalGrossWeight;
        }
        if ($this->termsOfPayment !== null) {
            $payload['termsOfPayment'] = $this->termsOfPayment;
        }

        return $payload;
    }

    /**
     * Invoice-data upload shape (`supermodelIoLogisticsExpressExportDeclaration`):
     * includes `function`, excludes the Create-Shipment-only fields.
     *
     * @return array<string, mixed>
     */
    public function toInvoiceDataArray(): array
    {
        return $this->commonPayload(includeFunction: true);
    }

    /**
     * Names of the set fields that only the Create Shipment invoice
     * schema accepts (the signature and summary fields).
     *
     * @return list<string>
     */
    public function shipmentOnlyFields(): array
    {
        $fields = [
            'signatureName' => $this->signatureName !== null,
            'signatureTitle' => $this->signatureTitle !== null,
            'signatureImage' => $this->signatureImage !== null,
            'instructions' => $this->instructions !== [],
            'customerDataTextEntries' => $this->customerDataTextEntries !== [],
            'totalNetWeight' => $this->totalNetWeight !== null,
            'totalGrossWeight' => $this->totalGrossWeight !== null,
            'termsOfPayment' => $this->termsOfPayment !== null,
        ];

        return array_keys(array_filter($fields));
    }

    /**
     * @return array<string, mixed>
     */
    private function commonPayload(bool $includeFunction): array
    {
        $payload = [
            'number' => $this->number,
            'date' => $this->date->format('Y-m-d'),
        ];

        if ($includeFunction && $this->function !== null) {
            $payload['function'] = $this->function->value;
        }
        if ($this->customerReferences !== []) {
            $payload['customerReferences'] = array_map(
                static fn (InvoiceReference $ref): array => $ref->toArray(),
                $this->customerReferences,
            );
        }
        if ($this->indicativeCustomsValues !== null) {
            $payload['indicativeCustomsValues'] = $this->indicativeCustomsValues->toArray();
        }
        if ($this->preCalculatedTotalValues !== null) {
            $payload['preCalculatedTotalValues'] = $this->preCalculatedTotalValues->toArray();
        }

        return $payload;
    }

    private function assertLength(string $field, ?string $value, int $max): void
    {
        if ($value === null) {
            return;
        }

        $length = strlen($value);
        if ($length < 1 || $length > $max) {
            throw new \InvalidArgumentException(
                sprintf('ExportInvoice %s must be 1–%d characters, got %d.', $field, $max, $length),
            );
        }
    }
}
