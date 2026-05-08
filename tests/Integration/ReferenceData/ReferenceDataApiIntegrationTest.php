<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Tests\Integration\ReferenceData;

use Medzuch\DhlExpress\Enum\ComparisonOperator;
use Medzuch\DhlExpress\Enum\ReferenceDataset;
use Medzuch\DhlExpress\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresEnvironmentVariable;

/**
 * Sandbox checks for the /reference-data endpoint.
 *
 * Compares a small slice of the live response against the bundled
 * `dhl_reference_data.xlsx` workbook to catch drift between the
 * static enums we ship and what the live API currently accepts.
 */
#[Group('integration')]
#[RequiresEnvironmentVariable('DHL_API_KEY')]
#[RequiresEnvironmentVariable('DHL_API_SECRET')]
final class ReferenceDataApiIntegrationTest extends IntegrationTestCase
{
    public function testFetchesIncotermDataset(): void
    {
        $client = $this->makeClient();

        $response = $client->referenceData()->lookup(ReferenceDataset::Incoterm);

        self::assertNotSame([], $response->referenceData);
        $entry = $response->referenceData[0];
        self::assertSame('incoterm', $entry->datasetName);
        self::assertNotSame([], $entry->data);

        $codes = [];
        foreach ($entry->data as $row) {
            foreach ($row as $attribute) {
                if ($attribute->attribute === 'incoterm') {
                    $codes[] = $attribute->value;
                }
            }
        }

        // Cross-check that DHL still recognises the staples we encode in src/Enum/Incoterm.php.
        self::assertContains('DAP', $codes);
        self::assertContains('DDP', $codes);
        self::assertContains('EXW', $codes);
    }

    public function testFiltersIncotermByValue(): void
    {
        $client = $this->makeClient();

        $response = $client->referenceData()->lookup(
            ReferenceDataset::Incoterm,
            filterByValue: 'DAP',
            filterByAttribute: 'incoterm',
            comparisonOperator: ComparisonOperator::Equal,
        );

        self::assertNotSame([], $response->referenceData);
        $rows = $response->referenceData[0]->data;
        self::assertNotSame([], $rows);

        $allDapMatches = array_filter(
            $rows,
            static function (array $row): bool {
                foreach ($row as $attribute) {
                    if ($attribute->attribute === 'incoterm' && $attribute->value === 'DAP') {
                        return true;
                    }
                }

                return false;
            },
        );
        self::assertCount(count($rows), $allDapMatches, 'filterByValue=DAP should restrict to DAP rows only.');
    }
}
