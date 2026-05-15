<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\ServicePoint\GeoLocation;
use Medzuch\DhlExpress\Dto\ServicePoint\OpeningTime;
use Medzuch\DhlExpress\Dto\ServicePoint\ServicePoint;
use Medzuch\DhlExpress\Dto\ServicePoint\ServicePointAddress;
use Medzuch\DhlExpress\Dto\ServicePoint\ServicePointFindCriteria;
use Medzuch\DhlExpress\Dto\ServicePoint\ServicePointFindResponse;
use Medzuch\DhlExpress\Enum\DayOfWeek;
use Medzuch\DhlExpress\Enum\ServicePointType;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Support\HydrationHelper;

/**
 * Service Point lookup endpoint.
 *
 * Targets `GET /servicepoints` to find DHL Express facilities a
 * customer can use as pickup or drop-off points. The spec exposes
 * 30+ query parameters spanning five search modes (address, geo,
 * servicePointID, idf, placeId), capability and capacity filters,
 * weight/dimension constraints, GDPR encoding flags, and SCMS
 * capacity-management hooks.
 *
 * Construct the call via
 * {@see \Medzuch\DhlExpress\Builder\ServicePointFindCriteriaBuilder}:
 * the builder enforces cross-field rules (search-mode mutual
 * exclusivity, weight/weightUom pairing, HH:MM time format, etc.)
 * client-side so callers see a structured
 * {@see \Medzuch\DhlExpress\Exception\InvalidRequestException} rather
 * than a server 400.
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
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function find(ServicePointFindCriteria $criteria): ServicePointFindResponse
    {
        $request = $this->requestBuilder->build(
            'GET',
            '/servicepoints',
            queryParams: $criteria->toQueryParams(),
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
        $rawType = HydrationHelper::stringField($raw, 'servicePointType');

        return new ServicePoint(
            facilityId: HydrationHelper::stringField($raw, 'facilityId'),
            serviceAreaCode: HydrationHelper::stringField($raw, 'serviceAreaCode'),
            servicePointName: HydrationHelper::stringField($raw, 'servicePointName'),
            localName: HydrationHelper::stringField($raw, 'localName'),
            servicePointType: ServicePointType::tryFrom($rawType),
            rawServicePointType: $rawType,
            address: $this->hydrateAddress($raw['address'] ?? null),
            geoLocation: $this->hydrateGeoLocation($raw['geoLocation'] ?? null),
            distance: HydrationHelper::stringField($raw, 'distance'),
            shippingCutOffTime: HydrationHelper::stringField($raw, 'shippingCutOffTime'),
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
            addressLine1: HydrationHelper::stringField($raw, 'addressLine1'),
            addressLine2: HydrationHelper::stringField($raw, 'addressLine2'),
            addressLine3: HydrationHelper::stringField($raw, 'addressLine3'),
            city: HydrationHelper::stringField($raw, 'city'),
            zipCode: HydrationHelper::stringField($raw, 'zipCode'),
            state: HydrationHelper::stringField($raw, 'state'),
            country: HydrationHelper::stringField($raw, 'country'),
            countryDivisionCode: HydrationHelper::stringField($raw, 'countryDivisionCode'),
        );
    }

    private function hydrateGeoLocation(mixed $raw): ?GeoLocation
    {
        if (!is_array($raw)) {
            return null;
        }

        /** @var array<string, mixed> $raw */
        return new GeoLocation(
            latitude: HydrationHelper::floatField($raw, 'latitude'),
            longitude: HydrationHelper::floatField($raw, 'longitude'),
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
            $rawDay = HydrationHelper::stringField($entry, 'dayOfWeek');
            $hours[] = new OpeningTime(
                dayOfWeek: DayOfWeek::tryFrom($rawDay),
                rawDayOfWeek: $rawDay,
                openingTime: HydrationHelper::stringField($entry, 'openingTime'),
                closingTime: HydrationHelper::stringField($entry, 'closingTime'),
            );
        }

        return $hours;
    }
}
