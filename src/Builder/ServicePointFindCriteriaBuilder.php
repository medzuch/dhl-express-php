<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Builder;

use Medzuch\DhlExpress\Dto\ServicePoint\ServicePointFindCriteria;
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
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\ServiceAreaCode;

/**
 * Fluent builder for {@see ServicePointFindCriteria}.
 *
 * Enforces the cross-field rules DHL's `/servicepoints` endpoint would
 * otherwise reject server-side:
 *
 * - exactly one search mode: address (+ countryCode), lat+lng,
 *   servicePointID, idf, or placeId (+ providerId).
 * - servicePointID must be exactly 6 characters.
 * - `weight` requires `weightUom`; any dimension requires
 *   `dimensionsUom`.
 * - `openBefore` / `openAfter` formatted as `HH:MM`.
 * - `placeId` requires `providerId`.
 *
 * Errors accumulate during {@see self::build()} and surface together
 * as a single {@see InvalidRequestException}.
 */
final class ServicePointFindCriteriaBuilder
{
    private ?string $address = null;
    private ?string $placeId = null;
    private ?string $providerId = null;
    private ?float $latitude = null;
    private ?float $longitude = null;
    private ?string $servicePointId = null;
    private ?string $idf = null;
    private ?CountryCode $countryCode = null;
    private ?LanguageCode $language = null;
    private ?string $languageScriptCode = null;
    private ?string $languageCountryCode = null;
    private ?int $resultLimit = null;
    /** @var list<ServicePointCapability> */
    private array $capabilities = [];
    private ?string $openBefore = null;
    private ?string $openAfter = null;
    /** @var list<ServicePointOpenDay> */
    private array $openDays = [];
    private ?string $weight = null;
    private ?WeightUom $weightUom = null;
    private ?string $length = null;
    private ?string $width = null;
    private ?string $height = null;
    private ?DimensionsUom $dimensionsUom = null;
    private ?string $clientAppCode = null;
    private ?string $sessionToken = null;
    private ?ResultUom $resultUom = null;
    private ?ServiceAreaCode $serviceAreaCode = null;
    /** @var list<ServicePointTypeFilter> */
    private array $servicePointTypes = [];
    private ?string $maxDistance = null;
    private ?int $pieceCountLimit = null;
    private ?YesNoIndicator $importCharges = null;
    private ?string $key = null;
    private ?string $combineParameters = null;
    private ?string $edd = null;
    private ?YesNoIndicator $excludeFullyBooked = null;
    private ?string $shipmentId = null;
    private ?string $pieceId = null;
    private ?ServiceAreaCode $shipmentOriginServiceAreaCode = null;
    private ?TrueFalseFlag $isResultsSpecificCapabRequired = null;
    private ?YesNoIndicator $encrypt = null;
    private ?TrueFalseFlag $b64 = null;
    private ?ServicePointStatus $svpStatus = null;
    private ?TrueFalseFlag $hasMixedUnits = null;

    public function withAddress(string $address, CountryCode $countryCode): self
    {
        $this->address = $address;
        $this->countryCode = $countryCode;

        return $this;
    }

    public function withGeoLocation(float $latitude, float $longitude): self
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        return $this;
    }

    public function withServicePointId(string $servicePointId): self
    {
        $this->servicePointId = $servicePointId;

        return $this;
    }

    public function withIdf(string $idf): self
    {
        $this->idf = $idf;

        return $this;
    }

    public function withPlaceId(string $placeId, string $providerId): self
    {
        $this->placeId = $placeId;
        $this->providerId = $providerId;

        return $this;
    }

    public function withCountryCode(CountryCode $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function withLanguage(LanguageCode $language, ?string $scriptCode = null, ?string $countryCode = null): self
    {
        $this->language = $language;
        $this->languageScriptCode = $scriptCode;
        $this->languageCountryCode = $countryCode;

        return $this;
    }

    public function withResultLimit(int $resultLimit): self
    {
        $this->resultLimit = $resultLimit;

        return $this;
    }

    public function withCapability(ServicePointCapability $capability): self
    {
        $this->capabilities[] = $capability;

        return $this;
    }

    public function withOpenBefore(string $time): self
    {
        $this->openBefore = $time;

        return $this;
    }

    public function withOpenAfter(string $time): self
    {
        $this->openAfter = $time;

        return $this;
    }

    public function withOpenDay(ServicePointOpenDay $day): self
    {
        $this->openDays[] = $day;

        return $this;
    }

    public function withWeight(string $weight, WeightUom $uom): self
    {
        $this->weight = $weight;
        $this->weightUom = $uom;

        return $this;
    }

    public function withDimensions(
        ?string $length,
        ?string $width,
        ?string $height,
        DimensionsUom $uom,
    ): self {
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->dimensionsUom = $uom;

        return $this;
    }

    public function withClientAppCode(string $clientAppCode): self
    {
        $this->clientAppCode = $clientAppCode;

        return $this;
    }

    public function withSessionToken(string $sessionToken): self
    {
        $this->sessionToken = $sessionToken;

        return $this;
    }

    public function withResultUom(ResultUom $resultUom): self
    {
        $this->resultUom = $resultUom;

        return $this;
    }

    public function withServiceAreaCode(ServiceAreaCode $code): self
    {
        $this->serviceAreaCode = $code;

        return $this;
    }

    public function withServicePointType(ServicePointTypeFilter $type): self
    {
        $this->servicePointTypes[] = $type;

        return $this;
    }

    public function withMaxDistance(string $kilometres): self
    {
        $this->maxDistance = $kilometres;

        return $this;
    }

    public function withPieceCountLimit(int $limit): self
    {
        $this->pieceCountLimit = $limit;

        return $this;
    }

    public function withImportCharges(YesNoIndicator $flag): self
    {
        $this->importCharges = $flag;

        return $this;
    }

    public function withKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function withCombineParameters(string $expression): self
    {
        $this->combineParameters = $expression;

        return $this;
    }

    public function withEdd(string $edd): self
    {
        $this->edd = $edd;

        return $this;
    }

    public function withExcludeFullyBooked(YesNoIndicator $flag): self
    {
        $this->excludeFullyBooked = $flag;

        return $this;
    }

    public function withShipmentId(string $shipmentId): self
    {
        $this->shipmentId = $shipmentId;

        return $this;
    }

    public function withPieceId(string $pieceId): self
    {
        $this->pieceId = $pieceId;

        return $this;
    }

    public function withShipmentOriginServiceAreaCode(ServiceAreaCode $code): self
    {
        $this->shipmentOriginServiceAreaCode = $code;

        return $this;
    }

    public function withIsResultsSpecificCapabRequired(TrueFalseFlag $flag): self
    {
        $this->isResultsSpecificCapabRequired = $flag;

        return $this;
    }

    public function withEncrypt(YesNoIndicator $flag): self
    {
        $this->encrypt = $flag;

        return $this;
    }

    public function withB64(TrueFalseFlag $flag): self
    {
        $this->b64 = $flag;

        return $this;
    }

    public function withSvpStatus(ServicePointStatus $status): self
    {
        $this->svpStatus = $status;

        return $this;
    }

    public function withHasMixedUnits(TrueFalseFlag $flag): self
    {
        $this->hasMixedUnits = $flag;

        return $this;
    }

    /**
     * @throws InvalidRequestException when one or more cross-field rules fail
     */
    public function build(): ServicePointFindCriteria
    {
        $errors = [];

        $searchModes = array_filter([
            'address' => $this->address !== null,
            'geo' => $this->latitude !== null || $this->longitude !== null,
            'servicePointId' => $this->servicePointId !== null,
            'idf' => $this->idf !== null,
            'placeId' => $this->placeId !== null,
        ]);

        if (count($searchModes) === 0) {
            $errors[] = [
                'field' => 'searchMode',
                'message' => 'exactly one search mode is required: address+countryCode, lat+lng, servicePointID, idf, or placeId+providerId',
            ];
        } elseif (count($searchModes) > 1) {
            $errors[] = [
                'field' => 'searchMode',
                'message' => sprintf(
                    'only one search mode allowed; got: %s',
                    implode(', ', array_keys($searchModes)),
                ),
            ];
        }

        if ($this->address !== null && $this->countryCode === null) {
            $errors[] = ['field' => 'countryCode', 'message' => 'countryCode is required when searching by address'];
        }
        if ($this->latitude !== null && $this->longitude === null) {
            $errors[] = ['field' => 'longitude', 'message' => 'longitude is required when latitude is set'];
        }
        if ($this->longitude !== null && $this->latitude === null) {
            $errors[] = ['field' => 'latitude', 'message' => 'latitude is required when longitude is set'];
        }
        if ($this->servicePointId !== null && strlen($this->servicePointId) !== 6) {
            $errors[] = ['field' => 'servicePointID', 'message' => 'servicePointID must be exactly 6 characters'];
        }
        if ($this->placeId !== null && $this->providerId === null) {
            $errors[] = ['field' => 'providerId', 'message' => 'providerId is required when placeId is set'];
        }

        if ($this->weight !== null && $this->weightUom === null) {
            $errors[] = ['field' => 'weightUom', 'message' => 'weightUom is required when weight is set'];
        }
        $hasDimension = $this->length !== null || $this->width !== null || $this->height !== null;
        if ($hasDimension && $this->dimensionsUom === null) {
            $errors[] = ['field' => 'dimensionsUom', 'message' => 'dimensionsUom is required when any of length/width/height is set'];
        }

        if ($this->openBefore !== null && !$this->isHhmm($this->openBefore)) {
            $errors[] = ['field' => 'openBefore', 'message' => "openBefore must match HH:MM (00:00-23:59); got '{$this->openBefore}'"];
        }
        if ($this->openAfter !== null && !$this->isHhmm($this->openAfter)) {
            $errors[] = ['field' => 'openAfter', 'message' => "openAfter must match HH:MM (00:00-23:59); got '{$this->openAfter}'"];
        }

        if ($this->languageScriptCode !== null && $this->language === null) {
            $errors[] = ['field' => 'language', 'message' => 'language is required when languageScriptCode is set'];
        }
        if ($this->languageCountryCode !== null && $this->language === null) {
            $errors[] = ['field' => 'language', 'message' => 'language is required when languageCountryCode is set'];
        }

        if ($errors !== []) {
            throw new InvalidRequestException($errors);
        }

        return new ServicePointFindCriteria(
            address: $this->address,
            placeId: $this->placeId,
            providerId: $this->providerId,
            latitude: $this->latitude,
            longitude: $this->longitude,
            servicePointId: $this->servicePointId,
            idf: $this->idf,
            countryCode: $this->countryCode,
            language: $this->language,
            languageScriptCode: $this->languageScriptCode,
            languageCountryCode: $this->languageCountryCode,
            resultLimit: $this->resultLimit,
            capabilities: $this->capabilities,
            openBefore: $this->openBefore,
            openAfter: $this->openAfter,
            openDays: $this->openDays,
            weight: $this->weight,
            weightUom: $this->weightUom,
            length: $this->length,
            width: $this->width,
            height: $this->height,
            dimensionsUom: $this->dimensionsUom,
            clientAppCode: $this->clientAppCode,
            sessionToken: $this->sessionToken,
            resultUom: $this->resultUom,
            serviceAreaCode: $this->serviceAreaCode,
            servicePointTypes: $this->servicePointTypes,
            maxDistance: $this->maxDistance,
            pieceCountLimit: $this->pieceCountLimit,
            importCharges: $this->importCharges,
            key: $this->key,
            combineParameters: $this->combineParameters,
            edd: $this->edd,
            excludeFullyBooked: $this->excludeFullyBooked,
            shipmentId: $this->shipmentId,
            pieceId: $this->pieceId,
            shipmentOriginServiceAreaCode: $this->shipmentOriginServiceAreaCode,
            isResultsSpecificCapabRequired: $this->isResultsSpecificCapabRequired,
            encrypt: $this->encrypt,
            b64: $this->b64,
            svpStatus: $this->svpStatus,
            hasMixedUnits: $this->hasMixedUnits,
        );
    }

    private function isHhmm(string $value): bool
    {
        return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $value) === 1;
    }
}
