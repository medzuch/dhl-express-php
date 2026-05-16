<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\ServicePoint;

use Medzuch\DhlExpress\Enum\DimensionsUom;
use Medzuch\DhlExpress\Enum\LanguageCode;
use Medzuch\DhlExpress\Enum\ResultUom;
use Medzuch\DhlExpress\Enum\ServicePointCapability;
use Medzuch\DhlExpress\Enum\ServicePointOpenDay;
use Medzuch\DhlExpress\Enum\ServicePointStatus;
use Medzuch\DhlExpress\Enum\ServicePointTypeFilter;
use Medzuch\DhlExpress\Enum\TrueFalseFlag;
use Medzuch\DhlExpress\Enum\WeightUom;
use Medzuch\DhlExpress\Enum\YesNoIndicator;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\ServiceAreaCode;

/**
 * Search criteria for the `/servicepoints` endpoint.
 *
 * Mirrors the full set of query parameters defined by the spec
 * (`exp-api-servicepoints`). Construct via
 * {@see \Medzuch\DhlExpress\Builder\ServicePointFindCriteriaBuilder}
 * — the builder enforces the cross-field rules DHL would otherwise
 * reject server-side (search-mode mutual exclusivity,
 * weight/weightUom pairing, HH:MM time format, etc.).
 *
 * The DTO is intentionally tolerant of any field combination so the
 * builder is the single source of validation truth and so this DTO
 * can be hand-constructed in tests.
 */
final readonly class ServicePointFindCriteria
{
    /**
     * @param list<ServicePointCapability> $capabilities
     * @param list<ServicePointOpenDay>    $openDays
     * @param list<ServicePointTypeFilter> $servicePointTypes
     */
    public function __construct(
        public ?string $address = null,
        public ?string $placeId = null,
        public ?string $providerId = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $servicePointId = null,
        public ?string $idf = null,
        public ?CountryCode $countryCode = null,
        public ?LanguageCode $language = null,
        public ?string $languageScriptCode = null,
        public ?string $languageCountryCode = null,
        public ?int $resultLimit = null,
        public array $capabilities = [],
        public ?string $openBefore = null,
        public ?string $openAfter = null,
        public array $openDays = [],
        public ?string $weight = null,
        public ?WeightUom $weightUom = null,
        public ?string $length = null,
        public ?string $width = null,
        public ?string $height = null,
        public ?DimensionsUom $dimensionsUom = null,
        public ?string $clientAppCode = null,
        public ?string $sessionToken = null,
        public ?ResultUom $resultUom = null,
        public ?ServiceAreaCode $serviceAreaCode = null,
        public array $servicePointTypes = [],
        public ?string $maxDistance = null,
        public ?int $pieceCountLimit = null,
        public ?YesNoIndicator $importCharges = null,
        public ?string $key = null,
        public ?string $combineParameters = null,
        public ?string $edd = null,
        public ?YesNoIndicator $excludeFullyBooked = null,
        public ?string $shipmentId = null,
        public ?string $pieceId = null,
        public ?ServiceAreaCode $shipmentOriginServiceAreaCode = null,
        public ?TrueFalseFlag $isResultsSpecificCapabRequired = null,
        public ?YesNoIndicator $encrypt = null,
        public ?TrueFalseFlag $b64 = null,
        public ?ServicePointStatus $svpStatus = null,
        public ?TrueFalseFlag $hasMixedUnits = null,
    ) {
    }

    /**
     * Render to the wire-name keyed query-parameter array consumed by
     * {@see \Medzuch\DhlExpress\Http\RequestBuilder}. Multi-value
     * params (`capability`, `openDay`, `servicePointTypes`) collapse
     * to CSV per spec.
     *
     * @return array<string, string>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->address !== null) {
            $params['address'] = $this->address;
        }
        if ($this->placeId !== null) {
            $params['placeId'] = $this->placeId;
        }
        if ($this->providerId !== null) {
            $params['providerId'] = $this->providerId;
        }
        if ($this->latitude !== null) {
            $params['latitude'] = (string) $this->latitude;
        }
        if ($this->longitude !== null) {
            $params['longitude'] = (string) $this->longitude;
        }
        if ($this->servicePointId !== null) {
            $params['servicePointID'] = $this->servicePointId;
        }
        if ($this->idf !== null) {
            $params['idf'] = $this->idf;
        }
        if ($this->countryCode !== null) {
            $params['countryCode'] = $this->countryCode->value;
        }
        if ($this->language !== null) {
            $params['language'] = $this->language->value;
        }
        if ($this->languageScriptCode !== null) {
            $params['languageScriptCode'] = $this->languageScriptCode;
        }
        if ($this->languageCountryCode !== null) {
            $params['languageCountryCode'] = $this->languageCountryCode;
        }
        if ($this->resultLimit !== null) {
            $params['servicePointResults'] = (string) $this->resultLimit;
        }
        if ($this->capabilities !== []) {
            $params['capability'] = implode(',', array_map(
                static fn (ServicePointCapability $c): string => $c->value,
                $this->capabilities,
            ));
        }
        if ($this->openBefore !== null) {
            $params['openBefore'] = $this->openBefore;
        }
        if ($this->openAfter !== null) {
            $params['openAfter'] = $this->openAfter;
        }
        if ($this->openDays !== []) {
            $params['openDay'] = implode(',', array_map(
                static fn (ServicePointOpenDay $d): string => $d->value,
                $this->openDays,
            ));
        }
        if ($this->weight !== null) {
            $params['weight'] = $this->weight;
        }
        if ($this->weightUom !== null) {
            $params['weightUom'] = $this->weightUom->value;
        }
        if ($this->length !== null) {
            $params['length'] = $this->length;
        }
        if ($this->width !== null) {
            $params['width'] = $this->width;
        }
        if ($this->height !== null) {
            $params['height'] = $this->height;
        }
        if ($this->dimensionsUom !== null) {
            $params['dimensionsUom'] = $this->dimensionsUom->value;
        }
        if ($this->clientAppCode !== null) {
            $params['clientAppCode'] = $this->clientAppCode;
        }
        if ($this->sessionToken !== null) {
            $params['sessionToken'] = $this->sessionToken;
        }
        if ($this->resultUom !== null) {
            $params['resultUom'] = $this->resultUom->value;
        }
        if ($this->serviceAreaCode !== null) {
            $params['serviceAreaCode'] = $this->serviceAreaCode->value;
        }
        if ($this->servicePointTypes !== []) {
            $params['servicePointTypes'] = implode(',', array_map(
                static fn (ServicePointTypeFilter $t): string => $t->value,
                $this->servicePointTypes,
            ));
        }
        if ($this->maxDistance !== null) {
            $params['maxDistance'] = $this->maxDistance;
        }
        if ($this->pieceCountLimit !== null) {
            $params['pieceCountLimit'] = (string) $this->pieceCountLimit;
        }
        if ($this->importCharges !== null) {
            $params['importCharges'] = $this->importCharges->value;
        }
        if ($this->key !== null) {
            $params['key'] = $this->key;
        }
        if ($this->combineParameters !== null) {
            $params['combineParameters'] = $this->combineParameters;
        }
        if ($this->edd !== null) {
            $params['edd'] = $this->edd;
        }
        if ($this->excludeFullyBooked !== null) {
            $params['excludeFullyBooked'] = $this->excludeFullyBooked->value;
        }
        if ($this->shipmentId !== null) {
            $params['shipmentID'] = $this->shipmentId;
        }
        if ($this->pieceId !== null) {
            $params['pieceID'] = $this->pieceId;
        }
        if ($this->shipmentOriginServiceAreaCode !== null) {
            $params['shipmentOriginServiceAreaCode'] = $this->shipmentOriginServiceAreaCode->value;
        }
        if ($this->isResultsSpecificCapabRequired !== null) {
            $params['isResultsSpecificCapabRequired'] = $this->isResultsSpecificCapabRequired->value;
        }
        if ($this->encrypt !== null) {
            $params['encrypt'] = $this->encrypt->value;
        }
        if ($this->b64 !== null) {
            $params['b64'] = $this->b64->value;
        }
        if ($this->svpStatus !== null) {
            $params['svpStatus'] = $this->svpStatus->value;
        }
        if ($this->hasMixedUnits !== null) {
            $params['hasMixedUnits'] = $this->hasMixedUnits->value;
        }

        return $params;
    }
}
