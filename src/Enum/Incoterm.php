<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * Incoterms 2020 (with the legacy 2010 codes DHL still accepts).
 *
 * Backing values are the three-letter codes DHL uses on the wire.
 * The full list mirrors the `incoterm` field enum in the OpenAPI
 * spec and the Reference Data Guide section 3 — the latter carries
 * the full natural-language definitions if context is needed.
 */
enum Incoterm: string
{
    case EXW = 'EXW';
    case FCA = 'FCA';
    case CPT = 'CPT';
    case CIP = 'CIP';
    case DPU = 'DPU';
    case DAP = 'DAP';
    case DDP = 'DDP';
    case FAS = 'FAS';
    case FOB = 'FOB';
    case CFR = 'CFR';
    case CIF = 'CIF';
    case DAF = 'DAF';
    case DAT = 'DAT';
    case DDU = 'DDU';
    case DEQ = 'DEQ';
    case DES = 'DES';
}
