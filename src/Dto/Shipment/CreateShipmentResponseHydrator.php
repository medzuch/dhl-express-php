<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

use Medzuch\DhlExpress\Support\HydrationHelper;

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
            shipmentTrackingNumber: HydrationHelper::stringField($body, 'shipmentTrackingNumber'),
            packages: $packages,
            documents: $documents,
            cancelPickupUrl: HydrationHelper::nullableStringField($body, 'cancelPickupUrl'),
            trackingUrl: HydrationHelper::nullableStringField($body, 'trackingUrl'),
            dispatchConfirmationNumber: HydrationHelper::nullableStringField($body, 'dispatchConfirmationNumber'),
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
            trackingNumber: HydrationHelper::stringField($raw, 'trackingNumber'),
            referenceNumber: HydrationHelper::intField($raw, 'referenceNumber'),
            trackingUrl: HydrationHelper::nullableStringField($raw, 'trackingUrl'),
            volumetricWeight: HydrationHelper::floatField($raw, 'volumetricWeight'),
            documents: $documents,
        );
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function hydrateDocument(array $raw): ShipmentDocument
    {
        return new ShipmentDocument(
            imageFormat: HydrationHelper::stringField($raw, 'imageFormat'),
            content: HydrationHelper::stringField($raw, 'content'),
            typeCode: HydrationHelper::stringField($raw, 'typeCode'),
        );
    }

}
