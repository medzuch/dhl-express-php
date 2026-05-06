<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\ServicePoint\GeoLocation;
use Medzuch\DhlExpress\Dto\ServicePoint\OpeningTime;
use Medzuch\DhlExpress\Dto\ServicePoint\ServicePoint;
use Medzuch\DhlExpress\Dto\ServicePoint\ServicePointAddress;
use Medzuch\DhlExpress\Dto\ServicePoint\ServicePointFindResponse;
use Medzuch\DhlExpress\Enum\DayOfWeek;
use Medzuch\DhlExpress\Enum\ServicePointType;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PostalCode;

/**
 * Service Point lookup endpoint.
 *
 * Targets `GET /servicepoints` to find DHL Express facilities a
 * customer can use as pickup or drop-off points. The DHL endpoint
 * accepts a long list of mutually-exclusive search parameters; this
 * facade exposes the most commonly-used combinations:
 *
 * - by **address**: country + postal + optional city / free-form address text
 * - by **geo**: latitude + longitude
 * - by **identifier**: `servicePointID` (e.g. `BRU001`)
 *
 * The DHL response surfaces many more attributes than this DTO
 * captures (capabilities, partner, capacity, …); the most useful
 * subset is hydrated and the rest can be added incrementally when a
 * concrete caller needs them.
 */
final class ServicePointApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
    ) {
    }

    /**
     * @param int|null $resultLimit Maximum number of service points to return (DHL caps at 50)
     *
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function find(
        ?CountryCode $countryCode = null,
        ?PostalCode $postalCode = null,
        ?string $cityName = null,
        ?string $address = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $servicePointId = null,
        ?int $resultLimit = null,
    ): ServicePointFindResponse {
        $params = [];

        if ($countryCode !== null) {
            $params['countryCode'] = $countryCode->value;
        }
        if ($postalCode !== null) {
            $params['postalCode'] = $postalCode->value;
        }
        if ($cityName !== null) {
            $params['city'] = $cityName;
        }
        if ($address !== null) {
            $params['address'] = $address;
        }
        if ($latitude !== null) {
            $params['latitude'] = (string) $latitude;
        }
        if ($longitude !== null) {
            $params['longitude'] = (string) $longitude;
        }
        if ($servicePointId !== null) {
            $params['servicePointID'] = $servicePointId;
        }
        if ($resultLimit !== null) {
            $params['servicePointResults'] = (string) $resultLimit;
        }

        $request = $this->requestBuilder->build(
            'GET',
            '/servicepoints',
            queryParams: $params,
        );

        $body = $this->transport->send($request);

        return $this->hydrate($body);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function hydrate(array $body): ServicePointFindResponse
    {
        $rawSearchAddress = $body['searchAddress'] ?? null;
        $searchAddress = is_string($rawSearchAddress) ? $rawSearchAddress : '';

        $servicePoints = [];
        $rawServicePoints = $body['servicePoints'] ?? null;
        if (is_array($rawServicePoints)) {
            foreach ($rawServicePoints as $raw) {
                if (!is_array($raw)) {
                    continue;
                }
                /** @var array<string, mixed> $raw */
                $servicePoints[] = $this->hydrateServicePoint($raw);
            }
        }

        return new ServicePointFindResponse(
            searchAddress: $searchAddress,
            servicePoints: $servicePoints,
        );
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function hydrateServicePoint(array $raw): ServicePoint
    {
        $rawType = $this->stringField($raw, 'servicePointType');

        return new ServicePoint(
            facilityId: $this->stringField($raw, 'facilityId'),
            serviceAreaCode: $this->stringField($raw, 'serviceAreaCode'),
            servicePointName: $this->stringField($raw, 'servicePointName'),
            localName: $this->stringField($raw, 'localName'),
            servicePointType: ServicePointType::tryFrom($rawType),
            rawServicePointType: $rawType,
            address: $this->hydrateAddress($raw['address'] ?? null),
            geoLocation: $this->hydrateGeoLocation($raw['geoLocation'] ?? null),
            distance: $this->stringField($raw, 'distance'),
            shippingCutOffTime: $this->stringField($raw, 'shippingCutOffTime'),
            openingHours: $this->hydrateOpeningHours($raw['openingHours'] ?? null),
        );
    }

    private function hydrateAddress(mixed $raw): ?ServicePointAddress
    {
        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        return new ServicePointAddress(
            addressLine1: $this->stringField($raw, 'addressLine1'),
            addressLine2: $this->stringField($raw, 'addressLine2'),
            addressLine3: $this->stringField($raw, 'addressLine3'),
            city: $this->stringField($raw, 'city'),
            zipCode: $this->stringField($raw, 'zipCode'),
            state: $this->stringField($raw, 'state'),
            country: $this->stringField($raw, 'country'),
            countryDivisionCode: $this->stringField($raw, 'countryDivisionCode'),
        );
    }

    private function hydrateGeoLocation(mixed $raw): ?GeoLocation
    {
        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        return new GeoLocation(
            latitude: $this->floatField($raw, 'latitude'),
            longitude: $this->floatField($raw, 'longitude'),
        );
    }

    /**
     * @return list<OpeningTime>
     */
    private function hydrateOpeningHours(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        /** @var array<string, mixed> $raw */
        $rawHours = $raw['openingHours'] ?? null;
        if (!is_array($rawHours)) {
            return [];
        }

        $hours = [];
        foreach ($rawHours as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            /** @var array<string, mixed> $entry */
            $rawDay = $this->stringField($entry, 'dayOfWeek');
            $hours[] = new OpeningTime(
                dayOfWeek: DayOfWeek::tryFrom($rawDay),
                rawDayOfWeek: $rawDay,
                openingTime: $this->stringField($entry, 'openingTime'),
                closingTime: $this->stringField($entry, 'closingTime'),
            );
        }

        return $hours;
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
}
