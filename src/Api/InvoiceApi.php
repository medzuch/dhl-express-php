<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\Invoice\UploadStandaloneInvoiceDataRequest;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;

/**
 * Invoice endpoints.
 *
 * Currently exposes the standalone `POST /invoices/upload-invoice-data`
 * variant, used when invoice data is uploaded before — or
 * independently of — its shipment. The shipment-bound variant
 * (`PATCH /shipments/{id}/upload-invoice-data`) lives on
 * {@see \Medzuch\DhlExpress\Api\ShipmentApi::uploadInvoiceData()}.
 */
final readonly class InvoiceApi
{
    public function __construct(
        private RequestBuilder $requestBuilder,
        private HttpTransport $transport,
    ) {
    }

    /**
     * Upload commercial-invoice data without (or before) a shipment.
     *
     * `POST /invoices/upload-invoice-data`
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function uploadInvoiceData(UploadStandaloneInvoiceDataRequest $request): void
    {
        $httpRequest = $this->requestBuilder->build(
            'POST',
            '/invoices/upload-invoice-data',
            jsonBody: $request->toArray(),
        );

        $this->transport->send($httpRequest);
    }
}
