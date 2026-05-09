<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\Address\AddressValidateResponse;
use Medzuch\DhlExpress\Dto\Address\ServiceArea;
use Medzuch\DhlExpress\Dto\Address\ValidatedAddress;
use Medzuch\DhlExpress\Enum\AddressValidationType;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Support\HydrationHelper;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PostalCode;

/**
 * Address domain endpoint.
 *
 * Targets `GET /address-validate` to check whether DHL Express has
 * pickup or delivery coverage at a given address, and to resolve
 * the address into DHL's service-area dictionary.
 */
final class AddressApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
    ) {
    }

    /**
     * Validates an address for pickup or delivery capability.
     *
     * `cityName` and `countyName` are optional — DHL resolves the
     * address from country + postal code where possible. When
     * `strictValidation` is left null the server-side default
     * (true) applies.
     *
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function validate(
        AddressValidationType $type,
        CountryCode $countryCode,
        ?PostalCode $postalCode = null,
        ?string $cityName = null,
        ?string $countyName = null,
        ?bool $strictValidation = null,
    ): AddressValidateResponse {
        $params = [
            'type' => $type->value,
            'countryCode' => $countryCode->value,
        ];

        if ($postalCode !== null) {
            $params['postalCode'] = $postalCode->value;
        }
        if ($cityName !== null) {
            $params['cityName'] = $cityName;
        }
        if ($countyName !== null) {
            $params['countyName'] = $countyName;
        }
        if ($strictValidation !== null) {
            $params['strictValidation'] = $strictValidation ? 'true' : 'false';
        }

        $request = $this->requestBuilder->build(
            'GET',
            '/address-validate',
            queryParams: $params,
        );

        $body = $this->transport->send($request);

        return $this->hydrate($body);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function hydrate(array $body): AddressValidateResponse
    {
        return new AddressValidateResponse(
            warnings: HydrationHelper::stringList($body['warnings'] ?? null),
            addresses: $this->hydrateAddresses($body['address'] ?? null),
        );
    }

    /**
     * @return list<ValidatedAddress>
     */
    private function hydrateAddresses(mixed $rawAddresses): array
    {
        if (!is_array($rawAddresses)) {
            return [];
        }

        $addresses = [];
        foreach ($rawAddresses as $rawAddress) {
            if (!is_array($rawAddress)) {
                continue;
            }

            /** @var array<string, mixed> $rawAddress */
            $addresses[] = new ValidatedAddress(
                countryCode: HydrationHelper::stringField($rawAddress, 'countryCode'),
                postalCode: HydrationHelper::stringField($rawAddress, 'postalCode'),
                cityName: HydrationHelper::stringField($rawAddress, 'cityName'),
                countyName: HydrationHelper::stringField($rawAddress, 'countyName'),
                serviceArea: $this->hydrateServiceArea($rawAddress['serviceArea'] ?? null),
            );
        }

        return $addresses;
    }

    private function hydrateServiceArea(mixed $raw): ?ServiceArea
    {
        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        return new ServiceArea(
            code: HydrationHelper::stringField($raw, 'code'),
            description: HydrationHelper::stringField($raw, 'description'),
            gmtOffset: HydrationHelper::stringField($raw, 'GMTOffset'),
        );
    }

}
