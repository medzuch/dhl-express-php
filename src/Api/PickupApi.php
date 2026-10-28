<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\Pickup\CancelPickupResponse;
use Medzuch\DhlExpress\Dto\Pickup\CancelPickupResponseHydrator;
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
 * Note: there is no `GET /pickups` in DHL API 3.4.0 — pickup confirmation
 * numbers are returned by {@see self::create()} and tracked by the caller.
 */
final readonly class PickupApi
{
    public function __construct(
        private RequestBuilder $requestBuilder,
        private HttpTransport $transport,
        private CreatePickupResponseHydrator $createHydrator = new CreatePickupResponseHydrator(),
        private UpdatePickupResponseHydrator $updateHydrator = new UpdatePickupResponseHydrator(),
        private CancelPickupResponseHydrator $cancelHydrator = new CancelPickupResponseHydrator(),
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
     * DHL answers 200 when the pickup is cancelled, was already cancelled
     * or has already been completed; the returned
     * {@see CancelPickupResponse} carries DHL's outcome text (empty when
     * the body is empty).
     *
     * @throws DhlApiException     for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function cancel(
        string $dispatchConfirmationNumber,
        string $requestorName,
        string $reason,
    ): CancelPickupResponse {
        $httpRequest = $this->requestBuilder->build(
            'DELETE',
            '/pickups/' . rawurlencode($dispatchConfirmationNumber),
            queryParams: [
                'requestorName' => $requestorName,
                'reason' => $reason,
            ],
        );

        $body = $this->transport->send($httpRequest);

        return $this->cancelHydrator->hydrate($body);
    }
}
