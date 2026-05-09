<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\Pickup\CreatePickupRequest;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupResponse;
use Medzuch\DhlExpress\Dto\Pickup\CreatePickupResponseHydrator;
use Medzuch\DhlExpress\Dto\Pickup\UpdatePickupRequest;
use Medzuch\DhlExpress\Dto\Pickup\UpdatePickupResponse;
use Medzuch\DhlExpress\Dto\Pickup\UpdatePickupResponseHydrator;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;

/**
 * Pickup endpoint — create, update, and cancel DHL Express pickup bookings.
 *
 * Three operations:
 * - {@see self::create()} — `POST /pickups`
 * - {@see self::update()} — `PATCH /pickups/{dispatchConfirmationNumber}`
 * - {@see self::cancel()} — `DELETE /pickups/{dispatchConfirmationNumber}`
 *
 * Note: there is no `GET /pickups` in DHL API 3.2.2 — pickup confirmation
 * numbers are returned by {@see self::create()} and tracked by the caller.
 */
final class PickupApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
        private readonly CreatePickupResponseHydrator $createHydrator = new CreatePickupResponseHydrator(),
        private readonly UpdatePickupResponseHydrator $updateHydrator = new UpdatePickupResponseHydrator(),
    ) {
    }

    /**
     * Book a new pickup via `POST /pickups`.
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function create(CreatePickupRequest $request): CreatePickupResponse
    {
        $httpRequest = $this->requestBuilder->build(
            'POST',
            '/pickups',
            jsonBody: $request->toArray(),
        );

        $body = $this->transport->send($httpRequest);

        return $this->createHydrator->hydrate($body);
    }

    /**
     * Update an existing pickup booking via
     * `PATCH /pickups/{dispatchConfirmationNumber}`.
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function update(UpdatePickupRequest $request): UpdatePickupResponse
    {
        $httpRequest = $this->requestBuilder->build(
            'PATCH',
            '/pickups/' . rawurlencode($request->dispatchConfirmationNumber),
            jsonBody: $request->toArray(),
        );

        $body = $this->transport->send($httpRequest);

        return $this->updateHydrator->hydrate($body);
    }

    /**
     * Cancel a pickup booking via
     * `DELETE /pickups/{dispatchConfirmationNumber}`.
     *
     * Returns void — DHL responds with 200 and an empty body on success.
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function cancel(
        string $dispatchConfirmationNumber,
        string $requestorName,
        string $reason,
    ): void {
        $httpRequest = $this->requestBuilder->build(
            'DELETE',
            '/pickups/' . rawurlencode($dispatchConfirmationNumber),
            queryParams: [
                'requestorName' => $requestorName,
                'reason' => $reason,
            ],
        );

        $this->transport->send($httpRequest);
    }
}
