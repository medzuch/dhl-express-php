<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use InvalidArgumentException;
use Medzuch\DhlExpress\Dto\Identifier\IdentifierGroup;
use Medzuch\DhlExpress\Dto\Identifier\IdentifierResponse;
use Medzuch\DhlExpress\Enum\IdentifierType;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Support\HydrationHelper;
use Medzuch\DhlExpress\ValueObject\AccountNumber;

/**
 * Identifier domain endpoint.
 *
 * Targets `GET /identifiers` to pre-allocate DHL Express Breakbulk
 * or Loose Break Bulk identifiers (SID, PID, HUID, ASID3..24) for
 * an account. Requires DHL-side authorization on the account.
 */
final class IdentifierApi
{
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly HttpTransport $transport,
    ) {
    }

    /**
     * Allocates `$size` identifiers of `$type` against `$account`.
     *
     * @throws InvalidArgumentException when size is not a positive integer
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function allocate(
        AccountNumber $account,
        IdentifierType $type,
        int $size,
    ): IdentifierResponse {
        if ($size < 1) {
            throw new InvalidArgumentException('Identifier allocation size must be a positive integer.');
        }

        $request = $this->requestBuilder->build(
            'GET',
            '/identifiers',
            queryParams: [
                'accountNumber' => $account->value,
                'type' => $type->value,
                'size' => (string) $size,
            ],
        );

        $body = $this->transport->send($request);

        return $this->hydrate($body);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function hydrate(array $body): IdentifierResponse
    {
        return new IdentifierResponse(
            warnings: HydrationHelper::stringList($body['warnings'] ?? null),
            identifiers: $this->hydrateGroups($body['identifiers'] ?? null),
        );
    }

    /**
     * @return list<IdentifierGroup>
     */
    private function hydrateGroups(mixed $rawGroups): array
    {
        if (!is_array($rawGroups)) {
            return [];
        }

        $groups = [];
        foreach ($rawGroups as $rawGroup) {
            if (!is_array($rawGroup)) {
                continue;
            }

            $typeCode = $rawGroup['typeCode'] ?? null;
            if (!is_string($typeCode)) {
                continue;
            }

            $type = IdentifierType::tryFrom($typeCode);
            if ($type === null) {
                continue;
            }

            $groups[] = new IdentifierGroup(
                typeCode: $type,
                list: HydrationHelper::stringList($rawGroup['list'] ?? null),
            );
        }

        return $groups;
    }

}
