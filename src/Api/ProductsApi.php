<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use DateTimeImmutable;
use Medzuch\DhlExpress\Dto\Product\Product;
use Medzuch\DhlExpress\Dto\Product\ProductsResponse;
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
 * Products domain endpoint.
 *
 * Targets `GET /products` to list the DHL Express products
 * available for a given origin / destination / weight combination.
 * Useful for surfacing product choice in checkout UIs without
 * paying for a full rate quote.
 */
final class ProductsApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
    ) {
    }

    /**
     * Lists available DHL products for the supplied shipment inputs.
     *
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function list(
        AccountNumber $account,
        CountryCode $originCountryCode,
        CountryCode $destinationCountryCode,
        Weight $weight,
        Dimensions $dimensions,
        DateTimeImmutable $plannedShippingDate,
        bool $isCustomsDeclarable,
        UnitSystem $unitOfMeasurement,
        ?PostalCode $originPostalCode = null,
        ?string $originCityName = null,
        ?PostalCode $destinationPostalCode = null,
        ?string $destinationCityName = null,
        ?bool $nextBusinessDay = null,
        ?bool $strictValidation = null,
        ?bool $getAllValueAddedServices = null,
    ): ProductsResponse {
        $params = [
            'accountNumber' => $account->value,
            'originCountryCode' => $originCountryCode->value,
            'destinationCountryCode' => $destinationCountryCode->value,
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
        if ($originCityName !== null) {
            $params['originCityName'] = $originCityName;
        }
        if ($destinationPostalCode !== null) {
            $params['destinationPostalCode'] = $destinationPostalCode->value;
        }
        if ($destinationCityName !== null) {
            $params['destinationCityName'] = $destinationCityName;
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

        $request = $this->requestBuilder->build('GET', '/products', queryParams: $params);

        $body = $this->transport->send($request);

        return $this->hydrate($body);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function hydrate(array $body): ProductsResponse
    {
        $rawProducts = $body['products'] ?? null;
        if (!is_array($rawProducts)) {
            return new ProductsResponse([]);
        }

        $products = [];
        foreach ($rawProducts as $rawProduct) {
            if (!is_array($rawProduct)) {
                continue;
            }

            /** @var array<string, mixed> $rawProduct */
            $products[] = new Product(
                productName: $this->stringField($rawProduct, 'productName'),
                productCode: $this->stringField($rawProduct, 'productCode'),
                localProductCode: $this->stringField($rawProduct, 'localProductCode'),
                localProductCountryCode: $this->stringField($rawProduct, 'localProductCountryCode'),
                networkTypeCode: $this->stringField($rawProduct, 'networkTypeCode'),
                isCustomerAgreement: (bool) ($rawProduct['isCustomerAgreement'] ?? false),
            );
        }

        return new ProductsResponse($products);
    }

    /**
     * @param array<string, mixed> $source
     */
    private function stringField(array $source, string $key): string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : '';
    }
}
