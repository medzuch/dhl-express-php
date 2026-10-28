<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\Pickup;

/**
 * Outcome of `DELETE /pickups/{dispatchConfirmationNumber}` (MyDHL API
 * 3.4.0).
 *
 * DHL answers 200 when the pickup is cancelled, was already cancelled,
 * or has already been completed, reusing the error-response shape for
 * the body. The outcome text is normally in `message` (e.g.
 * "Successfully cancelled.", "Booking already cancelled or completed.").
 * DHL documents the outcomes only in prose, so they stay strings. Every
 * field is null when the server returns an empty body.
 */
final readonly class CancelPickupResponse
{
    public function __construct(
        public ?string $status = null,
        public ?string $title = null,
        public ?string $detail = null,
        public ?string $instance = null,
        public ?string $message = null,
    ) {
    }
}
