<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Unit\Builder;

use Medzuch\DhlExpress\Builder\ServicePointFindCriteriaBuilder;
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
use PHPUnit\Framework\TestCase;

final class ServicePointFindCriteriaBuilderTest extends TestCase
{
    public function testBuildsAddressSearchSuccessfully(): void
    {
        $criteria = (new ServicePointFindCriteriaBuilder())
            ->withAddress('Václavské náměstí', new CountryCode('CZ'))
            ->withResultLimit(10)
            ->build();

        self::assertSame('Václavské náměstí', $criteria->address);
        self::assertNotNull($criteria->countryCode);
        self::assertSame('CZ', $criteria->countryCode->value);
        self::assertSame(10, $criteria->resultLimit);
    }

    public function testRejectsEmptyCriteria(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('exactly one search mode');

        (new ServicePointFindCriteriaBuilder())->build();
    }

    public function testRejectsMultipleSearchModes(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('only one search mode');

        (new ServicePointFindCriteriaBuilder())
            ->withAddress('Prague', new CountryCode('CZ'))
            ->withServicePointId('BRU001')
            ->build();
    }

    public function testRejectsServicePointIdOfWrongLength(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('servicePointID must be exactly 6 characters');

        (new ServicePointFindCriteriaBuilder())
            ->withServicePointId('TOOLONG')
            ->build();
    }

    public function testRejectsPlaceIdConflictingWithOtherSearchMode(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('only one search mode');

        (new ServicePointFindCriteriaBuilder())
            ->withPlaceId('ChIJxxx', 'google')
            ->withServicePointId('BRU001')
            ->build();
    }

    public function testAcceptsPlaceIdSearch(): void
    {
        $criteria = (new ServicePointFindCriteriaBuilder())
            ->withPlaceId('ChIJxxx', 'google')
            ->build();

        $params = $criteria->toQueryParams();
        self::assertSame('ChIJxxx', $params['placeId']);
        self::assertSame('google', $params['providerId']);
    }

    public function testAcceptsGeoLocation(): void
    {
        $criteria = (new ServicePointFindCriteriaBuilder())
            ->withGeoLocation(50.8467, 4.3499)
            ->build();

        self::assertSame(50.8467, $criteria->latitude);
        self::assertSame(4.3499, $criteria->longitude);
    }

    public function testRejectsInvalidOpenBeforeFormat(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('openBefore must match HH:MM');

        (new ServicePointFindCriteriaBuilder())
            ->withAddress('Prague', new CountryCode('CZ'))
            ->withOpenBefore('14:99')
            ->build();
    }

    public function testRejectsInvalidOpenAfterFormat(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('openAfter must match HH:MM');

        (new ServicePointFindCriteriaBuilder())
            ->withAddress('Prague', new CountryCode('CZ'))
            ->withOpenAfter('25:00')
            ->build();
    }

    public function testAcceptsValidOpenHoursAndOpenDays(): void
    {
        $criteria = (new ServicePointFindCriteriaBuilder())
            ->withAddress('Prague', new CountryCode('CZ'))
            ->withOpenBefore('14:00')
            ->withOpenAfter('09:00')
            ->withOpenDay(ServicePointOpenDay::Monday)
            ->withOpenDay(ServicePointOpenDay::Saturday)
            ->build();

        $params = $criteria->toQueryParams();
        self::assertSame('14:00', $params['openBefore']);
        self::assertSame('09:00', $params['openAfter']);
        self::assertSame('1,6', $params['openDay']);
    }

    public function testAcceptsAllOptionalFields(): void
    {
        $criteria = (new ServicePointFindCriteriaBuilder())
            ->withAddress('Prague', new CountryCode('CZ'))
            ->withLanguage(LanguageCode::Eng, 'Latn', 'GB')
            ->withResultLimit(20)
            ->withCapability(ServicePointCapability::DirectedParcel1)
            ->withCapability(ServicePointCapability::DirectedParcel2)
            ->withServicePointType(ServicePointTypeFilter::City)
            ->withServicePointType(ServicePointTypeFilter::Station)
            ->withMaxDistance('50')
            ->withWeight('10', WeightUom::Kilograms)
            ->withDimensions('30', '20', '10', DimensionsUom::Centimeters)
            ->withPieceCountLimit(2)
            ->withImportCharges(YesNoIndicator::No)
            ->withResultUom(ResultUom::Kilometers)
            ->withServiceAreaCode(new ServiceAreaCode('PRG'))
            ->withClientAppCode('ODD')
            ->withSessionToken('session-123')
            ->withKey('key-abc')
            ->withCombineParameters('(servicePointTypes=STN,CTY)')
            ->withEdd('2026-06-25T22:59:00Z')
            ->withExcludeFullyBooked(YesNoIndicator::Yes)
            ->withShipmentId('6360778572')
            ->withPieceId('JD0081105201831337270')
            ->withShipmentOriginServiceAreaCode(new ServiceAreaCode('CLU'))
            ->withIsResultsSpecificCapabRequired(TrueFalseFlag::False)
            ->withEncrypt(YesNoIndicator::No)
            ->withB64(TrueFalseFlag::False)
            ->withSvpStatus(ServicePointStatus::Active)
            ->withHasMixedUnits(TrueFalseFlag::False)
            ->build();

        $params = $criteria->toQueryParams();

        self::assertSame('eng', $params['language']);
        self::assertSame('Latn', $params['languageScriptCode']);
        self::assertSame('GB', $params['languageCountryCode']);
        self::assertSame('86,87', $params['capability']);
        self::assertSame('CTY,STN', $params['servicePointTypes']);
        self::assertSame('50', $params['maxDistance']);
        self::assertSame('10', $params['weight']);
        self::assertSame('kg', $params['weightUom']);
        self::assertSame('30', $params['length']);
        self::assertSame('20', $params['width']);
        self::assertSame('10', $params['height']);
        self::assertSame('cm', $params['dimensionsUom']);
        self::assertSame('2', $params['pieceCountLimit']);
        self::assertSame('n', $params['importCharges']);
        self::assertSame('km', $params['resultUom']);
        self::assertSame('PRG', $params['serviceAreaCode']);
        self::assertSame('ODD', $params['clientAppCode']);
        self::assertSame('session-123', $params['sessionToken']);
        self::assertSame('key-abc', $params['key']);
        self::assertSame('2026-06-25T22:59:00Z', $params['edd']);
        self::assertSame('y', $params['excludeFullyBooked']);
        self::assertSame('6360778572', $params['shipmentID']);
        self::assertSame('JD0081105201831337270', $params['pieceID']);
        self::assertSame('CLU', $params['shipmentOriginServiceAreaCode']);
        self::assertSame('false', $params['isResultsSpecificCapabRequired']);
        self::assertSame('n', $params['encrypt']);
        self::assertSame('false', $params['b64']);
        self::assertSame('A', $params['svpStatus']);
        self::assertSame('false', $params['hasMixedUnits']);
    }
}
