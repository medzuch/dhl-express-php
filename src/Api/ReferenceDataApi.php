<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Api;

use Medzuch\DhlExpress\Dto\ReferenceData\ReferenceData;
use Medzuch\DhlExpress\Dto\ReferenceData\ReferenceDataAttribute;
use Medzuch\DhlExpress\Dto\ReferenceData\ReferenceDataResponse;
use Medzuch\DhlExpress\Enum\ComparisonOperator;
use Medzuch\DhlExpress\Enum\ReferenceDataset;
use Medzuch\DhlExpress\Exception\DhlApiException;
use Medzuch\DhlExpress\Exception\DhlNetworkException;
use Medzuch\DhlExpress\Http\HttpTransport;
use Medzuch\DhlExpress\Http\RequestBuilder;
use Medzuch\DhlExpress\Support\HydrationHelper;

/**
 * Reference data domain endpoint.
 *
 * Targets `GET /reference-data` to retrieve the same lookup
 * tables that ship as sheets in `dhl_reference_data.xlsx`. The
 * library bundles the workbook for offline use, so this endpoint
 * is mostly useful for verifying that local enums match the
 * live DHL service or for fetching datasets we deliberately did
 * not encode (e.g. `country`, `countryPostalcodeFormat`).
 */
final readonly class ReferenceDataApi
{
    public function __construct(
        private RequestBuilder $requestBuilder,
        private HttpTransport $transport,
    ) {
    }

    /**
     * Looks up a reference dataset, optionally filtered.
     *
     * Pass `ReferenceDataset::All` to retrieve every dataset in
     * one call. `filterByValue` and `filterByAttribute` form the
     * primary filter clause; `queryString` carries up to three
     * additional `attribute:value:operator` triples joined by
     * comma.
     *
     * @throws DhlApiException for DHL-side errors
     * @throws DhlNetworkException for transport-level failures
     */
    public function lookup(
        ReferenceDataset $dataset,
        ?string $filterByValue = null,
        ?string $filterByAttribute = null,
        ?ComparisonOperator $comparisonOperator = null,
        ?string $queryString = null,
    ): ReferenceDataResponse {
        $params = [
            'datasetName' => $dataset->value,
        ];

        if ($filterByValue !== null) {
            $params['filterByValue'] = $filterByValue;
        }
        if ($filterByAttribute !== null) {
            $params['filterByAttribute'] = $filterByAttribute;
        }
        if ($comparisonOperator !== null) {
            $params['comparisonOperator'] = $comparisonOperator->value;
        }
        if ($queryString !== null) {
            $params['queryString'] = $queryString;
        }

        $request = $this->requestBuilder->build('GET', '/reference-data', queryParams: $params);

        $body = $this->transport->send($request);

        return $this->hydrate($body);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function hydrate(array $body): ReferenceDataResponse
    {
        return new ReferenceDataResponse(
            referenceData: $this->hydrateReferenceData($body['referenceData'] ?? null),
            warnings: HydrationHelper::stringList($body['warnings'] ?? null),
        );
    }

    /**
     * @return list<ReferenceData>
     */
    private function hydrateReferenceData(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $items = [];
        foreach ($raw as $rawItem) {
            if (!is_array($rawItem)) {
                continue;
            }

            /** @var array<string, mixed> $rawItem */
            $items[] = new ReferenceData(
                datasetName: HydrationHelper::stringField($rawItem, 'datasetName'),
                dataSetCaptions: HydrationHelper::stringField($rawItem, 'dataSetCaptions'),
                data: $this->hydrateRows($rawItem['data'] ?? null),
            );
        }

        return $items;
    }

    /**
     * @return list<list<ReferenceDataAttribute>>
     */
    private function hydrateRows(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $rows = [];
        foreach ($raw as $rawRow) {
            if (!is_array($rawRow)) {
                continue;
            }

            $row = [];
            foreach ($rawRow as $rawPair) {
                if (!is_array($rawPair)) {
                    continue;
                }

                /** @var array<string, mixed> $rawPair */
                $row[] = new ReferenceDataAttribute(
                    attribute: HydrationHelper::stringField($rawPair, 'attribute'),
                    value: HydrationHelper::stringField($rawPair, 'value'),
                );
            }

            $rows[] = $row;
        }

        return $rows;
    }

}
