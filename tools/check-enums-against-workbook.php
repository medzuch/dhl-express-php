<?php

declare(strict_types=1);

/**
 * Dev-only drift check: compares every workbook-backed enum in
 * `src/Enum` with its sheet in DHL's reference data workbook and
 * lists the codes missing from, or extra to, each enum.
 *
 * Usage (inside the container, `make shell`):
 *
 *     php tools/check-enums-against-workbook.php [path/to/workbook.xlsx]
 *
 * Exits 0 when every enum matches its sheet (apart from the codes
 * kept under the two-of-three-sources rule, see KEPT_BY_MAJORITY),
 * 1 when any difference is found. Needs only ext-zlib and ext-dom;
 * the xlsx container is read with a minimal built-in zip reader so
 * ext-zip is not required.
 *
 * Not shipped in the Composer package (`/tools export-ignore`).
 */

use Medzuch\DhlExpress\Enum;

require __DIR__ . '/../vendor/autoload.php';

const DEFAULT_WORKBOOK = __DIR__ . '/../specs/dhl/ExpressReferenceData_2026-10-25.xlsx';

/**
 * Enum class => [sheet name, header of the code column].
 */
const ENUM_SHEETS = [
    Enum\CommodityCategory::class => ['commodityCategory', 'commodityCategoryCode'],
    Enum\CustomsDocumentTypeCode::class => ['documentTypeCode', 'customsDocumentTypeCode'],
    Enum\DangerousGoodsContentId::class => ['dangerousGoods', 'contentId'],
    Enum\DangerousGoodsServiceCode::class => ['dangerousGoods', 'serviceCode'],
    Enum\Incoterm::class => ['incoterm', 'incoterm'],
    Enum\InvoiceReferenceTypeCode::class => ['invoiceReferenceType', 'invoiceReferenceType'],
    Enum\LanguageCode::class => ['languageCode', 'languageCode'],
    Enum\LineItemReferenceTypeCode::class => ['invoiceItemReferenceType', 'invoiceItemReferenceType'],
    Enum\OutputImageTemplate::class => ['outputImageTemplate', 'templateName'],
    Enum\PackageReferenceTypeCode::class => ['customerPackageReferenceType', 'packageReferenceType'],
    Enum\PackageTypeCode::class => ['packageTypeCode', 'packageTypeCode'],
    Enum\ProductCode::class => ['productCode', 'productCode'],
    Enum\RegistrationNumberTypeCode::class => ['registrationNumberTypeCode', 'registrationTypeCode'],
    Enum\ServiceCode::class => ['serviceCode', 'serviceCode'],
    Enum\ShipmentReferenceTypeCode::class => ['customerShipmentReferenceType', 'shipmentReferenceType'],
    Enum\TrackingEventCode::class => ['trackingEventCode', 'eventTypeCode'],
    Enum\UnitOfMeasurement::class => ['unitOfMeasurement', 'unitOfMeasurement'],
];

/**
 * Codes deliberately kept although the workbook does not list them,
 * because the OpenAPI spec and the reference data guide both do.
 */
const KEPT_BY_MAJORITY = [
    Enum\PackageTypeCode::class => ['CE1'],
];

/**
 * @return array<string, string> entry name => uncompressed bytes
 */
function readZip(string $path): array
{
    $data = file_get_contents($path);
    if ($data === false) {
        throw new RuntimeException("Cannot read {$path}");
    }

    $eocd = strrpos($data, "PK\x05\x06");
    if ($eocd === false) {
        throw new RuntimeException("{$path} is not a zip archive");
    }

    /** @var array{entries: int, offset: int} $end */
    $end = unpack('ventries/x4/Voffset', $data, $eocd + 10);
    $pos = $end['offset'];
    $files = [];

    for ($i = 0; $i < $end['entries']; $i++) {
        /** @var array{method: int, csize: int, nlen: int, xlen: int, clen: int, local: int} $cd */
        $cd = unpack('vmethod/x8/Vcsize/x4/vnlen/vxlen/vclen/x8/Vlocal', $data, $pos + 10);
        $name = substr($data, $pos + 46, $cd['nlen']);

        /** @var array{nlen: int, xlen: int} $lh */
        $lh = unpack('vnlen/vxlen', $data, $cd['local'] + 26);
        $raw = substr($data, $cd['local'] + 30 + $lh['nlen'] + $lh['xlen'], $cd['csize']);
        $content = $cd['method'] === 8 ? gzinflate($raw) : $raw;
        if ($content === false) {
            throw new RuntimeException("Cannot inflate {$name}");
        }
        $files[$name] = $content;

        $pos += 46 + $cd['nlen'] + $cd['xlen'] + $cd['clen'];
    }

    return $files;
}

function loadXml(string $xml): DOMXPath
{
    $doc = new DOMDocument();
    $doc->loadXML($xml);
    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
    $xpath->registerNamespace('p', 'http://schemas.openxmlformats.org/package/2006/relationships');

    return $xpath;
}

/**
 * @return array<string, list<array<string, string>>> sheet name => rows keyed by header
 */
function readWorkbook(string $path): array
{
    $files = readZip($path);

    $shared = [];
    if (isset($files['xl/sharedStrings.xml'])) {
        $xp = loadXml($files['xl/sharedStrings.xml']);
        foreach ($xp->query('//m:si') ?: [] as $si) {
            $shared[] = $si->textContent;
        }
    }

    $targets = [];
    $rels = loadXml($files['xl/_rels/workbook.xml.rels']);
    foreach ($rels->query('//p:Relationship') ?: [] as $rel) {
        assert($rel instanceof DOMElement);
        $target = ltrim($rel->getAttribute('Target'), '/');
        $targets[$rel->getAttribute('Id')] = str_starts_with($target, 'xl/') ? $target : 'xl/' . $target;
    }

    $sheets = [];
    $wb = loadXml($files['xl/workbook.xml']);
    foreach ($wb->query('//m:sheet') ?: [] as $sheet) {
        assert($sheet instanceof DOMElement);
        $xp = loadXml($files[$targets[$sheet->getAttribute('r:id')]]);
        $grid = [];
        foreach ($xp->query('//m:sheetData/m:row') ?: [] as $row) {
            $cells = [];
            foreach ($xp->query('m:c', $row) ?: [] as $c) {
                assert($c instanceof DOMElement);
                $col = preg_replace('/\d+/', '', $c->getAttribute('r'));
                $type = $c->getAttribute('t');
                $value = match ($type) {
                    's' => $shared[(int) $xp->evaluate('string(m:v)', $c)] ?? '',
                    'inlineStr' => (string) $xp->evaluate('string(m:is)', $c),
                    default => (string) $xp->evaluate('string(m:v)', $c),
                };
                $cells[(string) $col] = trim($value);
            }
            $grid[] = $cells;
        }

        $header = array_shift($grid) ?? [];
        $rows = [];
        foreach ($grid as $cells) {
            $named = [];
            foreach ($header as $col => $name) {
                $named[$name] = $cells[$col] ?? '';
            }
            $rows[] = $named;
        }
        $sheets[$sheet->getAttribute('name')] = $rows;
    }

    return $sheets;
}

$workbook = $argv[1] ?? DEFAULT_WORKBOOK;
$sheets = readWorkbook($workbook);
$drift = false;

printf("Workbook: %s\n\n", realpath($workbook) ?: $workbook);

foreach (ENUM_SHEETS as $enum => [$sheet, $column]) {
    if (!isset($sheets[$sheet])) {
        printf("%-28s sheet '%s' not found\n", short($enum), $sheet);
        $drift = true;
        continue;
    }

    $expected = [];
    foreach ($sheets[$sheet] as $row) {
        $code = $row[$column] ?? '';
        if ($code !== '') {
            $expected[$code] = true;
        }
    }

    $actual = [];
    foreach ($enum::cases() as $case) {
        $actual[(string) $case->value] = true;
    }

    $kept = KEPT_BY_MAJORITY[$enum] ?? [];
    $missing = array_keys(array_diff_key($expected, $actual));
    $extra = array_values(array_diff(array_keys(array_diff_key($actual, $expected)), $kept));
    sort($missing);
    sort($extra);

    if ($missing === [] && $extra === []) {
        printf("%-28s OK (%d codes%s)\n", short($enum), count($actual), $kept === [] ? '' : ', kept by majority: ' . implode(', ', $kept));
        continue;
    }

    $drift = true;
    printf("%-28s DRIFT\n", short($enum));
    if ($missing !== []) {
        printf("    missing from enum (%d): %s\n", count($missing), implode(' ', $missing));
    }
    if ($extra !== []) {
        printf("    not in workbook   (%d): %s\n", count($extra), implode(' ', $extra));
    }
}

exit($drift ? 1 : 0);

function short(string $class): string
{
    return substr($class, (int) strrpos($class, '\\') + 1);
}
