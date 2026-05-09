<?php

declare(strict_types=1);

/**
 * Track a DHL Express shipment.
 *
 * Usage:
 *   DHL_API_KEY=x DHL_API_SECRET=y php examples/track-shipment.php 1234567890
 */

require_once __DIR__ . '/bootstrap.php';

use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\ValueObject\TrackingNumber;

$trackingNumber = $argv[1] ?? null;

if ($trackingNumber === null) {
    fwrite(STDERR, "Usage: php examples/track-shipment.php <tracking-number>\n");
    exit(1);
}

$client = makeClient();

try {
    $response = $client->tracking()->track(new TrackingNumber($trackingNumber));
} catch (DhlNetworkException $e) {
    fwrite(STDERR, "Network error: {$e->getMessage()}\n");
    exit(1);
} catch (DhlApiException $e) {
    fwrite(STDERR, "DHL API error: {$e->getMessage()}\n");
    exit(1);
}

foreach ($response->shipments as $shipment) {
    echo "Shipment: {$shipment->id}" . PHP_EOL;
    echo "Status:   {$shipment->status->description}" . PHP_EOL;
    echo PHP_EOL;

    foreach ($shipment->events as $event) {
        $time = $event->timestamp->format('Y-m-d H:i');
        $location = $event->location->address->cityName ?? 'Unknown';
        $country = $event->location->address->countryCode ?? '';
        echo "  [{$time}] {$event->description} — {$location}, {$country}" . PHP_EOL;
    }
}
