<?php

declare(strict_types=1);

/**
 * Create a domestic CZ shipment in DHL Express sandbox validation mode.
 *
 * Uses validateDataOnly=true so no real shipment is produced — safe
 * to run repeatedly against the sandbox.
 *
 * Usage:
 *   DHL_API_KEY=x DHL_API_SECRET=y DHL_ACCOUNT_NUMBER=z php examples/create-shipment.php
 */

require_once __DIR__ . '/bootstrap.php';

use Medzuch\DhlExpress\Builder\CreateShipmentBuilder;
use Medzuch\DhlExpress\Dto\Common\Account;
use Medzuch\DhlExpress\Dto\Shipment\ContactAddress;
use Medzuch\DhlExpress\Dto\Shipment\ImageOption;
use Medzuch\DhlExpress\Dto\Shipment\OutputImageProperties;
use Medzuch\DhlExpress\Dto\Shipment\Package;
use Medzuch\DhlExpress\Dto\Shipment\Pickup;
use Medzuch\DhlExpress\Enum\AccountTypeCode;
use Medzuch\DhlExpress\Enum\DimensionUnit;
use Medzuch\DhlExpress\Enum\Incoterm;
use Medzuch\DhlExpress\Enum\LabelEncodingFormat;
use Medzuch\DhlExpress\Enum\UnitSystem;
use Medzuch\DhlExpress\Enum\WeightUnit;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\InvalidRequestException;
use Medzuch\DhlExpress\ValueObject\AccountNumber;
use Medzuch\DhlExpress\ValueObject\CountryCode;
use Medzuch\DhlExpress\ValueObject\Dimensions;
use Medzuch\DhlExpress\ValueObject\PhoneNumber;
use Medzuch\DhlExpress\ValueObject\PostalCode;
use Medzuch\DhlExpress\ValueObject\Weight;

$client = makeClient();
$accountNumber = accountNumber();

try {
    $request = (new CreateShipmentBuilder())
        ->withShipper(new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('14800'),
            cityName: 'Prague',
            addressLine1: 'Vaclavske namesti 1',
            phone: new PhoneNumber('+420 222 333 444'),
            companyName: 'Acme s.r.o.',
            fullName: 'Jan Novak',
        ))
        ->withReceiver(new ContactAddress(
            countryCode: new CountryCode('CZ'),
            postalCode: new PostalCode('60200'),
            cityName: 'Brno',
            addressLine1: 'Namesti Svobody 10',
            phone: new PhoneNumber('+420 555 666 777'),
            companyName: 'Receiver s.r.o.',
            fullName: 'Petr Dvorak',
        ))
        ->withPlannedShippingDate(new DateTimeImmutable('+2 days'))
        ->withProductCode('N')
        ->withPickup(new Pickup(false))
        ->withIsCustomsDeclarable(false)
        ->withContentDescription('Books')
        ->withUnitSystem(UnitSystem::Metric)
        ->withIncoterm(Incoterm::DAP)
        ->withAccount(new Account(AccountTypeCode::Shipper, new AccountNumber($accountNumber)))
        ->withPackage(new Package(
            weight: new Weight(1.0, WeightUnit::KG),
            dimensions: new Dimensions(20.0, 15.0, 10.0, DimensionUnit::CM),
        ))
        ->withOutputImageProperties(new OutputImageProperties(
            encodingFormat: LabelEncodingFormat::Pdf,
            imageOptions: [new ImageOption(typeCode: 'label', isRequested: true)],
        ))
        ->build();

    $response = $client->shipments()->create($request, validateDataOnly: true);
} catch (InvalidRequestException $e) {
    fwrite(STDERR, "Builder validation failed:\n");
    foreach ($e->errors() as ['field' => $field, 'message' => $msg]) {
        fwrite(STDERR, "  {$field}: {$msg}\n");
    }
    exit(1);
} catch (DhlApiException $e) {
    fwrite(STDERR, "DHL API error [{$e->dhlErrorCode}]: {$e->getMessage()}\n");
    exit(1);
}

echo 'Tracking number:      ' . ($response->shipmentTrackingNumber ?: '(validation mode — no tracking number)') . PHP_EOL;
echo 'Dispatch confirmation: ' . ($response->dispatchConfirmationNumber ?: '(validation mode)') . PHP_EOL;
echo 'Documents returned:   ' . count($response->documents) . PHP_EOL;
