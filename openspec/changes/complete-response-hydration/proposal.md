# Proposal

## Why

Several response hydrators keep only a fraction of what DHL returns. In a few places the library sends a request flag and then discards the data the flag asks for:
- `requestGMTOffsetPerEvent`, but `GMTOffset` is dropped.
- `getRateEstimates`, but `shipmentCharges` is dropped.
- `estimatedDeliveryDate`, but the estimate is dropped.
- piece tracking views, but `pieces[]` is dropped.

Some public DTOs also expose raw `array<string, mixed>`, which breaks the project's "typed DTOs only" rule. These are the response-side gaps from the 3.4.0 gap audit that the version migration itself does not cover.

## What Changes

- **Tracking:**
  - Expose shipment timestamp, product code, shipper/receiver details (name, postal address, service areas), total weight and unit, shipper references, number of pieces, pieces (with their own events, weights, dimensions and references), estimated delivery date, child shipment numbers and controlled-access data codes.
  - Events gain `GMTOffset`, service areas and `signedBy`.
- **Create Shipment response:** expose `warnings`, `shipmentCharges` (with service breakdown), `estimatedDeliveryDate`, `onDemandDeliveryURL`, `barcodeInfo`, `shipmentDetails` (service areas, routing codes, pickup details, value-added services) and `url`.
- **BREAKING** Standalone invoice-data upload returns a result with `status` and `warnings` instead of `void`.
- **Rates, landed cost and products:**
  - Expose `serviceCodeMutuallyExclusiveGroups`, `serviceCodeDependencyRuleGroups` and `pricingDate`.
  - **BREAKING** Replace raw-array `pickupCapabilities`, `rawItems` and `priceBreakdown` with typed DTOs.
  - The products listing exposes weight, breakdown, and pickup/delivery capabilities.
- **Service points:** expose capabilities, contact details, shipment limitations (per shipment and per piece), holiday opening hours, time zone and capacity status. Marketing fields (`promotion`, `translations`, map culture) stay out.
- **Errors:** `DhlApiException` carries the `Invocation-Id` response header, which DHL support asks for.

## Capabilities

### New Capabilities

- `shipment-tracking`: tracking data returned to callers. Introduced by `adopt-mydhl-api-3-4-0`; this change adds requirements.
- `shipment-creation`: Create Shipment results. Introduced by `adopt-mydhl-api-3-4-0`; this change adds requirements.
- `invoice-data-upload`: uploading commercial invoice data and the result DHL returns.
- `rate-quoting`: rate, landed-cost and product-capability responses.
- `service-point-lookup`: finding DHL service points and the details returned for each.
- `api-error-reporting`: the diagnostic information carried by DHL API exceptions.

### Modified Capabilities

_None at the time of writing. If `adopt-mydhl-api-3-4-0` is archived first, the `shipment-tracking` and `shipment-creation` deltas append ADDED requirements to those specs; no existing requirement is modified._

## Impact

- **Code:**
  - Updated: `Dto/Tracking/*`, `TrackingApi` hydration, `Dto/Shipment/CreateShipmentResponse*`, `InvoiceApi`, `Dto/Rate/*`, `Dto/Product/*`, `Dto/ServicePoint/*`, `Exception/{DhlApiException, DhlErrorMapper}`, `HttpTransport` (passing the header into errors).
  - New: response DTOs for each nested structure.
- **Tests:** hydrator and API unit tests with full-response JSON fixtures taken from the 3.4.0 spec examples.
- **Callers:** additive, except `InvoiceApi::uploadInvoiceData()` (return type) and the three raw-array properties (type changes). These are released together in one minor version.
- **Ordering:** after `adopt-mydhl-api-3-4-0`. Independent of `encrypted-document-images`.
