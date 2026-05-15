<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Rate\RateRequest;
use Medzuch\DhlExpress\Dto\Rate\RatesResponse;
use Medzuch\DhlExpress\Dto\Rate\RatesResponseHydrator;
use Medzuch\DhlExpress\Enum\EstimatedDeliveryDateTypeCode;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;

/**
 * Rate-quote endpoint.
 *
 * Two flavours:
 *
 * - {@see self::quote()} hits `GET /rates` for a single piece, query
 *   string only — convenient for checkout flows where the caller
 *   already has shipment dimensions on hand.
 * - {@see self::quoteMany()} hits `POST /rates` with a JSON body
 *   produced by {@see \Medzuch\DhlExpress\Builder\RateRequestBuilder}
 *   (or built manually via {@see RateRequest}). This is the
 *   multi-piece variant — DHL's spec internally calls it
 *   `exp-api-rates-many`, hence why the helper is named `quoteMany`.
 *
 * Both return the same {@see RatesResponse} shape. The same shape is
 * returned by `/landed-cost`, so the hydrator is shared via
 * {@see RatesResponseHydrator}.
 */
final class RatesApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
        private readonly RatesResponseHydrator $hydrator = new RatesResponseHydrator(),
    ) {
    }

    /**
     * Quote a single-piece shipment via `GET /rates`.
     *
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function quote(
        AccountNumber $account,
        CountryCode $originCountryCode,
        string $originCityName,
        CountryCode $destinationCountryCode,
        string $destinationCityName,
        Weight $weight,
        Dimensions $dimensions,
        DateTimeImmutable $plannedShippingDate,
        bool $isCustomsDeclarable,
        UnitSystem $unitOfMeasurement,
        ?PostalCode $originPostalCode = null,
        ?PostalCode $destinationPostalCode = null,
        ?bool $nextBusinessDay = null,
        ?bool $strictValidation = null,
        ?bool $getAllValueAddedServices = null,
        ?bool $requestEstimatedDeliveryDate = null,
        ?EstimatedDeliveryDateTypeCode $estimatedDeliveryDateType = null,
    ): RatesResponse {
        $params = [
            'accountNumber' => $account->value,
            'originCountryCode' => $originCountryCode->value,
            'originCityName' => $originCityName,
            'destinationCountryCode' => $destinationCountryCode->value,
            'destinationCityName' => $destinationCityName,
            'weight' => (string) $weight->value,
            'length' => (string) $dimensions->length,
            'width' => (string) $dimensions->width,
            'height' => (string) $dimensions->height,
            'plannedShippingDate' => $plannedShippingDate->format('Y-m-d'),
            'isCustomsDeclarable' => $isCustomsDeclarable ? 'true' : 'false',
            'unitOfMeasurement' => $unitOfMeasurement->value,
        ];

        if ($originPostalCode !== null) {
            $params['originPostalCode'] = $originPostalCode->value;
        }
        if ($destinationPostalCode !== null) {
            $params['destinationPostalCode'] = $destinationPostalCode->value;
        }
        if ($nextBusinessDay !== null) {
            $params['nextBusinessDay'] = $nextBusinessDay ? 'true' : 'false';
        }
        if ($strictValidation !== null) {
            $params['strictValidation'] = $strictValidation ? 'true' : 'false';
        }
        if ($getAllValueAddedServices !== null) {
            $params['getAllValueAddedServices'] = $getAllValueAddedServices ? 'true' : 'false';
        }
        if ($requestEstimatedDeliveryDate !== null) {
            $params['requestEstimatedDeliveryDate'] = $requestEstimatedDeliveryDate ? 'true' : 'false';
        }
        if ($estimatedDeliveryDateType !== null) {
            $params['estimatedDeliveryDateType'] = $estimatedDeliveryDateType->value;
        }

        $request = $this->requestBuilder->build('GET', '/rates', queryParams: $params);
        $body = $this->transport->send($request);

        return $this->hydrator->hydrate($body);
    }

    /**
     * Quote a multi-piece shipment via `POST /rates`.
     *
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function quoteMany(
        RateRequest $request,
        ?bool $strictValidation = null,
    ): RatesResponse {
        $queryParams = [];
        if ($strictValidation !== null) {
            $queryParams['strictValidation'] = $strictValidation ? 'true' : 'false';
        }

        $httpRequest = $this->requestBuilder->build(
            'POST',
            '/rates',
            queryParams: $queryParams !== [] ? $queryParams : null,
            jsonBody: $request->toArray(),
        );

        $body = $this->transport->send($httpRequest);

        return $this->hydrator->hydrate($body);
    }
}
