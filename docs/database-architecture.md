# ISP Platform Database Architecture & Master Data Specification

## Overview
This document specifies the core domain database architecture, schema normalization, relationships, data types, and numbering rules for the Internet Service Provider (ISP) / WiFi Service Management Platform.

---

## Domain Architecture Schema Summary

### 1. Organization
- `companies`: Multi-tenant / multi-company legal entities.
- `branches`: Physical branch offices, service centers, and hubs. `company_id` FK (Restrict), `latitude`/`longitude` (`DECIMAL(10,7)`).
- `departments`: Functional departments (Admin, Finance, Technical, NOC, Warehouse, Customer Service, etc.).
- `positions`: Job title positions mapped to departments (`department_id` FK).
- `employees`: Enhanced HR records with `branch_id`, `position_id`, and hierarchical `supervisor_id` FKs.

### 2. Inventory, Procurement & Technical Tools (Phase 10)
- `stock_balances`: On hand, reserved, damaged, and in-transit stock balances per item, warehouse, and location (`quantity_on_hand`, `quantity_reserved`, `quantity_damaged`, `quantity_in_transit`).
- `inventory_transactions`: Immutable movement ledger (`INV_TX-YYYY-XXXXXX`) recording all stock changes (`RECEIPT`, `ISSUE`, `RETURN`, `TRANSFER_IN`, `TRANSFER_OUT`, `RESERVATION`, `ADJUSTMENT_IN`, `ADJUSTMENT_OUT`, `DAMAGE`, `LOSS`).
- `stock_reservations`: Stock reservation records for work orders and installations.
- `material_requests` & `material_request_items`: Internal material requisition workflow (`DRAFT`, `SUBMITTED`, `APPROVED`, `PARTIALLY_FULFILLED`, `FULFILLED`, `REJECTED`).
- `inventory_transfers` & `inventory_transfer_items`: Inter-warehouse and branch stock transfer tracking (`REQUESTED`, `IN_TRANSIT`, `RECEIVED`).
- `stocktakes` & `stocktake_items`: Physical inventory audit counting and variance reconciliation.
- `purchase_requests` & `purchase_request_items`: Procurement purchase requests (`PR-YYYY-XXXXXX`) with department priority and approval tracking.
- `supplier_quotations`: Supplier quotations and price comparisons.
- `purchase_orders` & `purchase_order_items`: Purchase order master (`PO-YYYY-XXXXXX`) with supplier line items and delivery terms.
- `goods_receipts` & `goods_receipt_items`: Goods receiving vouchers (`GR-YYYY-XXXXXX`) with over-receiving protection and partial/full PO receipt handling.
- `tool_checkouts`, `tool_inspections`, `tool_calibrations`: Technical field tool checkout/check-in tracking, condition inspections, and calibration due alerts.

### 3. Technical Equipment & Asset Management (Phase 9)
- `assets`: Master serialized equipment records (`AST-YYYY-XXXXXX`) storing serial numbers, MAC addresses, warranty, condition, and status (`AVAILABLE`, `RESERVED`, `ASSIGNED`, `INSTALLED`, `IN_REPAIR`, `DAMAGED`, `LOST`, `RETIRED`, `DISPOSED`).
- `asset_status_histories`, `asset_assignments`, `asset_transfers`, `asset_verifications`, `asset_audit_sessions`, `asset_replacements`, `asset_retirements`, `asset_disposals`, `asset_incidents`, `asset_documents`, `asset_photos`, `asset_interfaces`.

### 4. Installation Management, Work Orders & Technician Dispatch (Phase 8)
- `installation_work_orders`: Master field installation work order record (`INSTALL-YYYY-XXXXXX`).
- `installation_status_histories`, `installation_assignments`, `installation_schedules`, `installation_checklist_templates/sections/items/responses`, `installation_photos`, `installation_materials`, `installation_equipment`, `installation_tools`, `installation_tests`, `installation_failures`, `installation_acceptances`, `installation_supervisor_reviews`, `installation_handoffs`.

### 5. Technical Surveys, Site Inspections & Field Feasibility (Phase 7)
- `technical_surveys`: Technical survey master record (`SUR-YYYY-XXXXXX`).

### 6. GIS, Infrastructure Mapping & Location Intelligence (Phase 6)
- `network_towers`, `distribution_points`, `location_histories`, `service_areas`, `gis_imports`.

### 7. Online Customer Applications & Serviceability Engine (Phase 5)
- `service_applications` (`APP-YYYY-XXXXXX`), `serviceability_checks`.

### 8. Product Catalog & Service Package Versioning (Phase 4)
- `service_categories`, `service_packages`, `service_package_versions`, `package_features`, `promotions`.

### 9. Customer Management & CRM 360 (Phase 3)
- `customers` (`CUST-XXXXXX`, `ACC-XXXXXX`), `leads`.

### 10. Geography, Network & Warehouse
- `regions`, `provinces`, `cities_municipalities`, `barangays`, `addresses`, `locations`, `network_nodes`, `warehouses`, `warehouse_locations`, `items`, `suppliers`, `tools`.

---

## Currency & Coordinates Standard
1. **Monetary Values**: Stored exclusively as `DECIMAL(15,2)` or `DECIMAL(8,4)` for tax rates. Floating-point values (`FLOAT`, `DOUBLE`) are forbidden.
2. **Geographic Coordinates**: Stored as `DECIMAL(10,7)` for precision up to ~11mm without floating-point rounding errors.

---

## Centralized Concurrency-Safe Numbering
All sequential business numbers are generated via `App\Services\NumberSequenceService` using `SELECT ... FOR UPDATE` row locks inside a DB transaction to guarantee race-condition-free unique sequence increments.
