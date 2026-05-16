<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\Epod\EpodDocument;
use Medzuch\DhlExpress\Dto\Epod\EpodResponse;
use Medzuch\DhlExpress\Enum\EpodContent;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Support\HydrationHelper;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;

/**
 * Electronic Proof of Delivery (EPoD) endpoint.
 *
 * Targets `GET /shipments/{shipmentTrackingNumber}/proof-of-delivery`
 * to fetch one or more POD documents (PDF / images) for a delivered
 * shipment. The DHL response carries each document as base64 text;
 * {@see EpodDocument::decodeContent()} returns the raw bytes ready
 * to write to disk.
 */
final readonly class EpodApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
    ) {
    }

    /**
     * Fetches the electronic proof of delivery for a shipment.
     *
     * `shipperAccountNumber` is optional per the DHL spec — DHL resolves
     * the shipper from the tracking number when omitted. Pass it
     * explicitly only when your API credentials cover multiple accounts
     * and you need to disambiguate.
     *
     * `content` is optional — DHL defaults to `epod-summary` when
     * unspecified.
     *
     * @throws DhlApiException for DHL-side errors (404 when no POD exists yet)
     * @throws DhlNetworkException for transport-level failures
     */
    public function get(
        TrackingNumber $trackingNumber,
        ?AccountNumber $shipperAccountNumber = null,
        ?EpodContent $content = null,
    ): EpodResponse {
        $params = [];

        if ($shipperAccountNumber !== null) {
            $params['shipperAccountNumber'] = $shipperAccountNumber->value;
        }

        if ($content !== null) {
            $params['content'] = $content->value;
        }

        $request = $this->requestBuilder->build(
            'GET',
            '/shipments/' . $trackingNumber->value . '/proof-of-delivery',
            queryParams: $params,
        );

        $body = $this->transport->send($request);

        return $this->hydrate($body);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function hydrate(array $body): EpodResponse
    {
        $documents = [];
        $rawDocuments = $body['documents'] ?? null;

        if (is_array($rawDocuments)) {
            foreach ($rawDocuments as $rawDocument) {
                if (!is_array($rawDocument)) {
                    continue;
                }

                /** @var array<string, mixed> $rawDocument */
                $documents[] = new EpodDocument(
                    encodingFormat: HydrationHelper::stringField($rawDocument, 'encodingFormat'),
                    content: HydrationHelper::stringField($rawDocument, 'content'),
                    typeCode: HydrationHelper::stringField($rawDocument, 'typeCode'),
                );
            }
        }

        return new EpodResponse(documents: $documents);
    }

}
