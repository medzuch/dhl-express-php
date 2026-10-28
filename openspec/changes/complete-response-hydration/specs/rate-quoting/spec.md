# Spec Delta

## Purpose

Covers quoting DHL Express rates and landed costs and listing available products, including the price, capability and service-rule data returned for each product.

## ADDED Requirements

### Requirement: Quoted products expose value-added service rules
Each product returned by rates, landed cost and product listing SHALL expose its mutually exclusive service code groups (rule name, service codes) and its service code dependency rule groups (dependent service code; rules with name, description, condition and required service codes).

#### Scenario: Mutually exclusive services
- **WHEN** a product has a mutually exclusive group `Exclusive Services` with codes `II` and `IB`
- **THEN** the product exposes one group with that name and both codes

#### Scenario: No rule groups
- **WHEN** a product has no rule groups
- **THEN** the product exposes empty lists for both rule kinds

### Requirement: Product capabilities are typed
Pickup capabilities (next business day, local cut-off date and time, earliest and latest pickup, same-day outbound cut-off, origin service and facility area codes, additional days, day of week) SHALL be exposed as a typed object, not an associative array.

#### Scenario: Pickup cut-off
- **WHEN** a product returns `pickupCapabilities.localCutoffDateAndTime: "2026-10-28T17:00:00"`
- **THEN** the product's pickup capabilities expose that cut-off as a typed field

### Requirement: Price breakdowns and items are typed
Breakdown price details (price type, rate, base price) and landed-cost item-level results SHALL be exposed as typed objects, not associative arrays. Rate responses SHALL expose `pricingDate` when present.

#### Scenario: Landed cost items
- **WHEN** a landed-cost response contains two items, each with a duty breakdown
- **THEN** the product exposes two typed items, each with its breakdown entries

#### Scenario: Pricing date
- **WHEN** a rate product has `pricingDate: "2026-10-28"`
- **THEN** the product exposes that pricing date

### Requirement: Product listing exposes weight, breakdown and capabilities
Products returned by the product listing SHALL expose weight, price breakdown, and pickup and delivery capabilities in addition to the identification fields.

#### Scenario: Delivery capability
- **WHEN** a listed product returns `deliveryCapabilities.totalTransitDays: "2"`
- **THEN** the product exposes total transit days 2
