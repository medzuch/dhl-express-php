<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Dto\EarlyShipmentScreening;

use Medzuch\DhlExpress\Support\HydrationHelper;

final class EarlyShipmentScreeningResponseHydrator
{
    /**
     * @param array<string, mixed> $body
     */
    public function hydrate(array $body): EarlyShipmentScreeningResponse
    {
        $warnings = [];
        $rawWarnings = $body['warnings'] ?? null;
        if (is_array($rawWarnings)) {
            foreach ($rawWarnings as $warning) {
                if (is_string($warning)) {
                    $warnings[] = $warning;
                }
            }
        }

        return new EarlyShipmentScreeningResponse(
            status: HydrationHelper::stringField($body, 'status'),
            warnings: $warnings,
        );
    }
}
