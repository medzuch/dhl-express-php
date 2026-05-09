<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express output image template names.
 *
 * Sourced from the `outputImageTemplate` sheet of `dhl_reference_data.xlsx`
 * (serviceName = CreateShipment or AddPiece, deduplicated).
 * The backing value is the exact wire format expected by the API.
 *
 * Transport label templates: ECOM26_84_001 through ECOM26_84RS_001.
 * Waybill document templates: ARCH_* and WAYBILL_DOC_*.
 * Commercial invoice: COMMERCIAL_INVOICE_*.
 * Return invoice: RET_COM_INVOICE_A4_01.
 * Retail invoice: RETAIL_INVOICE_01.
 * Shipment receipt: SHIP_RECPT_A4_RU_002, SHIPRCPT_EN_001.
 * QR code: QR_1_00_LL_PNG_001.
 */
enum OutputImageTemplate: string
{
    // Transport label templates
    case ECOM26_84_001 = 'ECOM26_84_001';
    case ECOM26_84_A4_001 = 'ECOM26_84_A4_001';
    case ECOM_TC_A4 = 'ECOM_TC_A4';
    case ECOM26_A6_002 = 'ECOM26_A6_002';
    case ECOM26_84CI_001 = 'ECOM26_84CI_001';
    case ECOM_A4_RU_002 = 'ECOM_A4_RU_002';
    case ECOM26_84CI_002 = 'ECOM26_84CI_002';
    case ECOM26_84CI_003 = 'ECOM26_84CI_003';
    case ECOM26_84_LBBX_001 = 'ECOM26_84_LBBX_001';
    case ECOM26_64_LBBX_001 = 'ECOM26_64_LBBX_001';
    case ECOM26_64_001 = 'ECOM26_64_001';
    case ECOM26_64_002 = 'ECOM26_64_002';
    case ECOM26_64_004 = 'ECOM26_64_004';
    case ECOM26_84_002 = 'ECOM26_84_002';
    case ECOM26_84_002_CL = 'ECOM26_84_002_CL';
    case ECOM26_84_002_CO = 'ECOM26_84_002_CO';
    case ECOM26_84_003 = 'ECOM26_84_003';
    case ECOM26_A4_001 = 'ECOM26_A4_001';
    case ECOM26_A6_001 = 'ECOM26_A6_001';
    case ECOM26_A6_003 = 'ECOM26_A6_003';
    case ECOM26_A6_004 = 'ECOM26_A6_004';
    case ECOM26_84RS_001 = 'ECOM26_84RS_001';

    // Waybill document templates
    case ARCH_8X4_A4_002 = 'ARCH_8X4_A4_002';
    case ARCH_8X4 = 'ARCH_8X4';
    case ARCH_6X4 = 'ARCH_6X4';
    case ARCH_A4_RU_002 = 'ARCH_A4_RU_002';
    case WAYBILL_DOC_8X4_RS_001 = 'WAYBILL_DOC_8X4_RS_001';

    // Commercial invoice
    case COMMERCIAL_INVOICE_04 = 'COMMERCIAL_INVOICE_04';
    case COMMERCIAL_INVOICE_P_10 = 'COMMERCIAL_INVOICE_P_10';
    case COMMERCIAL_INVOICE_L_10 = 'COMMERCIAL_INVOICE_L_10';

    // Return invoice
    case RET_COM_INVOICE_A4_01 = 'RET_COM_INVOICE_A4_01';

    // Retail invoice
    case RETAIL_INVOICE_01 = 'RETAIL_INVOICE_01';

    // Shipment receipt
    case SHIP_RECPT_A4_RU_002 = 'SHIP_RECPT_A4_RU_002';
    case SHIPRCPT_EN_001 = 'SHIPRCPT_EN_001';

    // QR code
    case QR_1_00_LL_PNG_001 = 'QR_1_00_LL_PNG_001';
}
