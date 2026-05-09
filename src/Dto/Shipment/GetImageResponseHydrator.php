<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Shipment;

/**
 * Hydrates a {@see GetImageResponse} from the raw DHL API response body.
 */
final class GetImageResponseHydrator
{
    /**
     * @param array<string, mixed> $body
     */
    public function hydrate(array $body): GetImageResponse
    {
        /** @var list<array<string, mixed>> $rawDocuments */
        $rawDocuments = $body['documents'] ?? [];

        $documents = array_map(
            static function (array $raw): DocumentImageResult {
                /** @var array<string, mixed> $raw */
                $trackingNumber = $raw['shipmentTrackingNumber'] ?? '';
                $typeCode = $raw['typeCode'] ?? '';
                $encodingFormat = $raw['encodingFormat'] ?? '';
                $content = $raw['content'] ?? '';
                $function = $raw['function'] ?? null;

                return new DocumentImageResult(
                    shipmentTrackingNumber: is_scalar($trackingNumber) ? (string) $trackingNumber : '',
                    typeCode: is_scalar($typeCode) ? (string) $typeCode : '',
                    encodingFormat: is_scalar($encodingFormat) ? (string) $encodingFormat : '',
                    content: is_scalar($content) ? (string) $content : '',
                    function: ($function !== null && is_scalar($function)) ? (string) $function : null,
                );
            },
            $rawDocuments,
        );

        return new GetImageResponse(documents: $documents);
    }
}
