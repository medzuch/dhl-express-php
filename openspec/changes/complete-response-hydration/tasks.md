# Tasks

Prerequisite: `adopt-mydhl-api-3-4-0` is merged. TDD per CLAUDE.md: test and fixture first, then hydrator and DTO.

## 1. Shared value DTOs

- [ ] 1.1 Add the `ServiceArea`, `Dimensions` and `Measurement` DTOs under `Dto/Common`; verify with construction/serialization unit tests

## 2. Tracking

- [ ] 2.1 Add full and minimal tracking fixtures (3.4.0 shape, including pieces, GMTOffset and signedBy) under `tests/Fixtures/Tracking/`; verify the JSON parses in a fixture-loader test
- [ ] 2.2 Extract `TrackingResponseHydrator` from `TrackingApi` without changing behaviour; verify the existing `TrackingApiTest` passes unchanged
- [ ] 2.3 Extend `ShipmentEvent` with gmtOffset, serviceAreas and signedBy; verify with hydrator tests for both fixtures
- [ ] 2.4 Add `TrackingParty` and the shipment-level fields to `TrackingResponse`; verify with hydrator tests (full fixture populated, minimal fixture yields null/empty, and a shipper reference with the retired code `AAO` hydrates with a null enum and raw string `AAO`)
- [ ] 2.5 Add `TrackedPiece` with events, weights, dimensions and references; verify with a piece-view hydrator test
- [ ] 2.6 Add the README tracking section showing pieces and the event GMT offset; verify the snippet compiles

## 3. Create Shipment response

- [ ] 3.1 Add a full Create Shipment 201 fixture; verify it parses
- [ ] 3.2 Add `warnings`, `url`, `onDemandDeliveryURL` and `EstimatedDeliveryDate` to `CreateShipmentResponse`; verify with hydrator tests
- [ ] 3.3 Add `ShipmentCharge` + `ShipmentChargeBreakdown`; verify with a hydrator test for two breakdown lines
- [ ] 3.4 Add `BarcodeInfo` + `TrackingNumberBarcode`; verify with a hydrator test
- [ ] 3.5 Add `ShipmentDetail` (service areas, routing, pickup details, VAS); verify with a hydrator test using the full fixture and a minimal fixture without `shipmentDetails`

## 4. Invoice upload

- [ ] 4.1 Add `UploadInvoiceDataResponse` and change `InvoiceApi::uploadInvoiceData()` to return it; verify with `InvoiceApiTest` cases for status with warnings and without; add a CHANGELOG migration note

## 5. Rates, landed cost, products

- [ ] 5.1 Add `ServiceCodeMutuallyExclusiveGroup`, `ServiceCodeDependencyRuleGroup` and `ServiceCodeDependencyRule`, and hydrate them for rates and products; verify with `RatesResponseHydratorTest` and product tests for present and absent groups
- [ ] 5.2 Replace `QuotedProduct::$pickupCapabilities` with a typed `PickupCapability` and add `pricingDate`; verify with hydrator tests and PHPStan level max
- [ ] 5.3 Replace `QuotedProduct::$rawItems` with `list<LandedCostItem>` and `BreakdownDetail::$priceBreakdown` with `list<PriceBreakdownDetail>`; verify with a landed-cost hydrator test with two items
- [ ] 5.4 Replace the raw arrays in `LandedCost\LineItem` (`goodsCharacteristics`, `additionalQuantityDefinitions`) with typed DTOs; verify with `LandedCostRequestBuilderTest` payload assertions
- [ ] 5.5 Extend `Product` with weight, breakdown, and pickup/delivery capabilities, plus the network type enum with raw fallback; verify with a `ProductsApiTest` full-fixture case
- [ ] 5.6 Check that no `array<string, mixed>` remains on public DTO properties; verify with `grep -rn "array<string, mixed>" src/Dto | grep -v -i "hydrator\|toArray"` returning nothing

## 6. Service points

- [ ] 6.1 Add a full service point fixture (capabilities, contact, limitations, holidays, capacity status, plus promotion/translations to prove they are ignored); verify it parses
- [ ] 6.2 Add `ServicePointCapabilities`, `ServicePointContactDetails`, `ShipmentLimitations`, `PieceLimitations`, holiday periods, timeZone, workWeekStart, locatedAt and `CapacityStatus` to `ServicePoint`; verify with `ServicePointApiTest` hydration assertions for each group and the ignored marketing fields

## 7. Error reporting

- [ ] 7.1 Add `?string $invocationId` to `DhlApiException` and populate it in `DhlErrorMapper`; verify with mapper tests for present, absent and lower-case header names across 400/404/422/500

## 8. Integration

- [ ] 8.1 Extend the sandbox integration tests to assert the newly hydrated fields for tracking, create shipment (with `getRateEstimates`) and rates; verify `make test-integration` with `DHL_API_KEY`
- [ ] 8.2 Run `make check`; verify it is green with ≥ 90% line coverage on `src/`, and that the CHANGELOG lists every BREAKING type change with a migration line

## Workflow follow-up

- Archive after release, following `adopt-mydhl-api-3-4-0`.
