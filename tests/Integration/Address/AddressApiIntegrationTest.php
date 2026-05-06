<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\Address;

use Medzuch\DhlExpress\Dto\Address\AddressValidateResponse;
use Medzuch\DhlExpress\Enum\AddressValidationType;
use Medzuch\DhlExpress\Exception\DhlValidationException;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use PHPUnit\Framework\Attributes\Group;

/**
 * Sandbox checks for the /address-validate endpoint.
 *
 * Uses Prague (CZ / 14800) — DHL has both pickup and delivery
 * coverage there, so the response should always include at least
 * one validated address.
 */
#[Group('integration')]
final class AddressApiIntegrationTest extends IntegrationTestCase
{
    public function testValidatesPickupForPrague(): void
    {
        $client = $this->makeClient();

        $response = $client->address()->validate(
            AddressValidationType::Pickup,
            new CountryCode('CZ'),
            new PostalCode('14800'),
        );

        self::assertInstanceOf(AddressValidateResponse::class, $response);
        self::assertNotSame([], $response->addresses);
        $first = $response->addresses[0];
        self::assertSame('CZ', $first->countryCode);
        self::assertSame('14800', $first->postalCode);
    }

    public function testValidatesDeliveryForPragueAndResolvesServiceArea(): void
    {
        $client = $this->makeClient();

        $response = $client->address()->validate(
            AddressValidationType::Delivery,
            new CountryCode('CZ'),
            new PostalCode('14800'),
        );

        self::assertNotSame([], $response->addresses);
        // DHL routes Prague through the PRG service area.
        self::assertNotNull($response->addresses[0]->serviceArea);
        self::assertSame('PRG', $response->addresses[0]->serviceArea->code);
    }

    public function testStrictValidationRejectsUnresolvablePostalCode(): void
    {
        $client = $this->makeClient();

        try {
            $client->address()->validate(
                AddressValidationType::Pickup,
                new CountryCode('CZ'),
                new PostalCode('00000'),
                strictValidation: true,
            );
            self::fail('Expected DhlValidationException');
        } catch (DhlValidationException $exception) {
            // 3007: The origin location is invalid.
            self::assertSame(400, $exception->httpStatus);
            self::assertNotNull($exception->dhlMessage);
            self::assertStringContainsString('3007', $exception->dhlMessage);
        }
    }
}
