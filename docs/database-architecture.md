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

### 2. Online Customer Applications & Serviceability Engine (Phase 5)
- `service_applications`: Online service application master record. Sequential numbering `APP-YYYY-XXXXXX` via `NumberSequenceService`. Stores applicant details, requested package and version, installation address, exact GPS coordinates (`DECIMAL(10,7)`), location source (`MAP_PIN`, `GPS`, `GEOCODE`), and lifecycle status (`DRAFT`, `SUBMITTED`, `UNDER_REVIEW`, `SERVICEABILITY_CHECK`, `REQUIRES_SURVEY`, `PENDING_DOCUMENTS`, `APPROVED`, `REJECTED`, `CANCELLED`).
- `service_application_status_histories`: Immutable history of application lifecycle status transitions.
- `serviceability_checks`: Audit log of technical and commercial serviceability evaluations. Evaluates Commercial Availability (package status, active version, branch, service area) and Technical Serviceability using precise **Haversine formula** GPS distance calculations in meters to nearest `network_nodes`, `access_points`, or NanoBox `network_devices`. Result statuses: `SERVICEABLE`, `REQUIRES_TECHNICAL_SURVEY`, `OUT_OF_COVERAGE`, `CAPACITY_UNAVAILABLE`, `PACKAGE_UNAVAILABLE`. Supports supervisor overrides with immutable audit logs.
- `application_documents`: Attachment metadata for required applicant IDs, proof of address, etc.

### 3. Product Catalog & Service Package Versioning (Phase 4)
- `service_categories`: Configurable broadband service categories (`HOME`, `BUSINESS`, `CORPORATE`, `PREPAID`, `PUBLIC_WIFI`, `DEDICATED`).
- `service_packages`: Broadband plans storing speeds (`download_speed`, `upload_speed`, `speed_guaranteed`, `burst_speed`), technology (`FIBER`, `WIRELESS`, `FTTH`, `RADIO`), FUP policies (`fup_enabled`, `fup_threshold_gb`, `fup_action`), and commercial terms.
- `service_package_versions`: Historical price and speed versioning table. Protects old invoices and active subscriber contracts from historical pricing corruption when plan prices are revised.
- `package_features` & `package_feature_service_package`: Reusable feature tags (`BOOLEAN`, `TEXT`, `NUMBER`, `AMOUNT`, `PERCENTAGE`, `LIMIT`).
- `promotions` & `promotion_service_package`: Campaign promotions (`FREE_INSTALLATION`, `DISCOUNT`, `FIRST_MONTH_FREE`).
- `package_equipment_requirements`: Required hardware items per package (`asset_model_id`, `quantity`, `is_required`, `is_included`).
- Availability Matrices:
  - `service_package_branch`
  - `service_package_service_area`
  - `service_package_customer_type`

### 4. Customer Management & CRM 360 (Phase 3)
- `customers`: Master subscriber accounts. Auto-generated `customer_number` (`CUST-XXXXXX`) and `account_number` (`ACC-XXXXXX`).
- `customer_status_histories`: Immutable log of lifecycle status changes.
- `customer_contacts`: Multiple authorized contacts per customer.
- `customer_address_histories`: Address movement and relocation history logs.
- `customer_documents`: Private, non-public document metadata with SHA-256 file checksums.
- `customer_notes`: Internal CRM notes with department-level visibility controls.
- `customer_tags` & `customer_customer_tag`: Configurable subscriber tags (`VIP`, `BUSINESS`, `NEW`, `HIGH_PRIORITY`).
- `customer_referrals`: Referral relationships between subscribers.
- `customer_assignments`: Historical employee and branch account manager assignment logs.
- `customer_consents`: Tracking accepted terms, privacy policies, and agreements.
- `customer_activities`: Universal Customer 360 activity timeline events.
- `leads`, `lead_status_histories`, `lead_activities`: CRM sales lead pipeline management and atomic conversion into subscriber accounts.

### 5. Geography & Locations
- `regions`, `provinces`, `cities_municipalities`, `barangays`: Reusable hierarchical Philippine geographical master data.
- `addresses`: Universal address repository. `latitude`, `longitude` (`DECIMAL(10,7)`), `coordinate_accuracy` (`DECIMAL(8,2)`).
- `locations`: Polymorphic location records for network devices, nodes, towers, and branch hubs.
- `service_areas`: Broadband service coverage zones linked to branches.

### 6. Network Infrastructure
- `network_nodes`: Core POPs, distribution POPs, access nodes, and relay towers.
- `access_points`: Wireless sector APs, GPON units with SSID, frequency, and capacity.
- `network_devices`: MikroTik CCR/CRS routers, switches, NanoBoxes, ONUs, and gateways.
- `network_interfaces`: Ethernet, Fiber, Wireless, VLAN, and Bridge interfaces attached to devices.

### 7. Hardware Assets & Technical Tools
- `asset_categories`, `asset_models`: Hardware category and manufacturer model definitions.
- `assets`: Hardware inventory (CPE ONTs, routers, switches). Uses `DECIMAL(15,2)` for `purchase_cost`.
- `asset_histories`: Immutable status, location, and assignment transition logs.
- `tool_categories`, `tools`: Technical installation equipment.

### 8. Warehouse & Supply Chain
- `warehouses`, `warehouse_locations`, `item_categories`, `items`, `suppliers`, `item_supplier`.

### 9. Operations, Support & Finance
- `ticket_categories`, `ticket_priorities`, `ticket_statuses`, `sla_definitions`: Customer service SLA rules.
- `work_orders`: Field technical work order foundation for installations and repairs.
- `accounts`: Chart of Accounts for financial ledger integrity.
- `transaction_types`: Master financial transaction definitions with default Debit/Credit account FKs.
- `number_sequences`: Concurrency-safe sequence rules (`CUST-XXXXXX`, `INV-XXXXXX`, `WO-XXXXXX`, `LEAD-XXXXXX`, `APP-YYYY-XXXXXX`).

---

## Currency & Coordinates Standard
1. **Monetary Values**: Stored exclusively as `DECIMAL(15,2)` or `DECIMAL(8,4)` for tax rates. Floating-point values (`FLOAT`, `DOUBLE`) are forbidden.
2. **Geographic Coordinates**: Stored as `DECIMAL(10,7)` for precision up to ~11mm without floating-point rounding errors.

---

## Centralized Concurrency-Safe Numbering
All sequential business numbers are generated via `App\Services\NumberSequenceService` using `SELECT ... FOR UPDATE` row locks inside a DB transaction to guarantee race-condition-free unique sequence increments.
