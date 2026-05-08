<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Hydrates the POST `/shipments` JSON response into the typed
 * {@see CreateShipmentResponse} graph.
 *
 * Stateless instance class (mirrors
 * {@see \Medzuch\DhlExpress\Dto\Rate\RatesResponseHydrator}). Defensive
 * parsing throughout — DHL's response in `validateDataOnly=true` mode
 * leaves several otherwise-required fields empty, so every accessor
 * tolerates absence and falls back to a safe default.
 */
final class CreateShipmentResponseHydrator
{
    /**
     * @param array<string, mixed> $body
     */
    public function hydrate(array $body): CreateShipmentResponse
    {
        $packages = [];
        $rawPackages = $body['packages'] ?? null;
        if (is_array($rawPackages)) {
            foreach ($rawPackages as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                /** @var array<string, mixed> $entry */
                $packages[] = $this->hydratePackage($entry);
            }
        }

        $documents = [];
        $rawDocuments = $body['documents'] ?? null;
        if (is_array($rawDocuments)) {
            foreach ($rawDocuments as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                /** @var array<string, mixed> $entry */
                $documents[] = $this->hydrateDocument($entry);
            }
        }

        return new CreateShipmentResponse(
            shipmentTrackingNumber: $this->stringField($body, 'shipmentTrackingNumber'),
            packages: $packages,
            documents: $documents,
            cancelPickupUrl: $this->nullableStringField($body, 'cancelPickupUrl'),
            trackingUrl: $this->nullableStringField($body, 'trackingUrl'),
            dispatchConfirmationNumber: $this->nullableStringField($body, 'dispatchConfirmationNumber'),
        );
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function hydratePackage(array $raw): PackageResult
    {
        $documents = [];
        $rawDocuments = $raw['documents'] ?? null;
        if (is_array($rawDocuments)) {
            foreach ($rawDocuments as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                /** @var array<string, mixed> $entry */
                $documents[] = $this->hydrateDocument($entry);
            }
        }

        return new PackageResult(
            trackingNumber: $this->stringField($raw, 'trackingNumber'),
            referenceNumber: $this->intField($raw, 'referenceNumber'),
            trackingUrl: $this->nullableStringField($raw, 'trackingUrl'),
            volumetricWeight: $this->floatField($raw, 'volumetricWeight'),
            documents: $documents,
        );
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function hydrateDocument(array $raw): ShipmentDocument
    {
        return new ShipmentDocument(
            imageFormat: $this->stringField($raw, 'imageFormat'),
            content: $this->stringField($raw, 'content'),
            typeCode: $this->stringField($raw, 'typeCode'),
        );
    }

    /**
     * @param array<string, mixed> $source
     */
    private function stringField(array $source, string $key): string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<string, mixed> $source
     */
    private function nullableStringField(array $source, string $key): ?string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function floatField(array $source, string $key): ?float
    {
        $value = $source[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function intField(array $source, string $key): ?int
    {
        $value = $source[$key] ?? null;

        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return (int) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
