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
    $response = $client->tracking()->getByTrackingNumber(new TrackingNumber($trackingNumber));
} catch (DhlNetworkException $e) {
    fwrite(STDERR, "Network error: {$e->getMessage()}\n");
    exit(1);
} catch (DhlApiException $e) {
    fwrite(STDERR, "DHL API error: {$e->getMessage()}\n");
    exit(1);
}

echo "Shipment: {$response->shipmentTrackingNumber}" . PHP_EOL;
echo "Status:   {$response->status} — {$response->description}" . PHP_EOL;
echo PHP_EOL;

foreach ($response->events as $event) {
    echo sprintf(
        '  [%s %s] %s (%s)' . PHP_EOL,
        $event->date,
        $event->time,
        $event->description,
        $event->typeCode,
    );
}
