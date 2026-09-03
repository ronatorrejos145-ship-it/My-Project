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

### 2. Technical Surveys, Site Inspections & Field Feasibility (Phase 7)
- `technical_surveys`: Technical survey master record. Sequential numbering `SUR-YYYY-XXXXXX` via `NumberSequenceService`. Stores application and customer references, dispatched technician, supervisor, survey type (`NEW_INSTALLATION`, `UPGRADE_ASSESSMENT`, `RELOCATION`, `RE_SURVEY`), lifecycle status (`DRAFT`, `ASSIGNED`, `SCHEDULED`, `ON_SITE`, `IN_PROGRESS`, `PENDING_TECHNICAL_REVIEW`, `APPROVED`, `REJECTED`, `RESURVEY_REQUIRED`), priority, GPS arrival coordinates, verification status, distance in meters, line of sight status (`CLEAR`, `PARTIAL`, `BLOCKED`), installation complexity (`EASY`, `NORMAL`, `MODERATE`, `DIFFICULT`, `VERY_DIFFICULT`), site safety assessment (`SAFE`, `CAUTION`, `UNSAFE`), technical recommendations, and supervisor approval decisions.
- `technical_survey_status_histories`: Immutable history of survey status transitions.
- `technical_survey_assignments`: Technician assignment and dispatch history log.
- `technical_survey_checklist_templates` & `technical_survey_checklist_items`: Configurable site inspection checklist templates and items.
- `technical_survey_responses`: Technician responses to site inspection checklist items.
- `technical_survey_measurements`: Field signal measurements (`OPTICAL_POWER`, `RSSI`, `SNR`, `LATENCY_MS`, `NOISE_FLOOR`) with acceptance statuses (`PASS`, `FAIL`, `MARGINAL`).
- `technical_survey_photos`: Secure field photo metadata (`FACADE`, `MOUNTING_LOCATION`, `ROOF`, `CABLE_ROUTE`, `LINE_OF_SIGHT`, `HAZARD`).
- `technical_survey_materials`: Material estimations (`item_id`, `estimated_quantity`, `unit`).
- `technical_survey_equipment`: Recommended equipment models (`asset_model_id`, `quantity`).
- `technical_survey_signatures`: Signatures and acknowledgments (`TECHNICIAN`, `CUSTOMER`, `SUPERVISOR`).
- Hand-off: Approved technical surveys update `service_applications` status to `APPROVED` / `READY_FOR_INSTALLATION` for Phase 8 field installation work order dispatch.

### 3. GIS, Infrastructure Mapping & Location Intelligence (Phase 6)
- `network_towers`: Telecom towers, monopoles, rooftop antenna masts.
- `distribution_points`: Fiber splitters, cabinets, distribution boxes (`code`, `name`, `dp_type`, `capacity`, `parent_node_id`, `latitude`, `longitude`, `status`, `notes`).
- `location_histories`: Immutable audit log of coordinate movement changes.
- `service_areas`: Enhanced with `boundary_geojson`, `color_code`, `geometry_version`.
- `gis_imports`: Audit log for imported CSV/GeoJSON coordinate files.
- Spatial Bounding-Box APIs: `/api/gis/viewport`, `/api/gis/nearby`, `/api/gis/geojson/service-areas`.

### 4. Online Customer Applications & Serviceability Engine (Phase 5)
- `service_applications`: Online service application master record. Sequential numbering `APP-YYYY-XXXXXX`.
- `service_application_status_histories`: Immutable history of application lifecycle status transitions.
- `serviceability_checks`: Audit log of technical and commercial serviceability evaluations using the **Haversine formula** GPS distance calculation engine.
- `application_documents`: Attachment metadata for required applicant IDs, proof of address, etc.

### 5. Product Catalog & Service Package Versioning (Phase 4)
- `service_categories`: Configurable broadband service categories (`HOME`, `BUSINESS`, `CORPORATE`, `PREPAID`, `PUBLIC_WIFI`, `DEDICATED`).
- `service_packages`: Broadband plans storing speeds, technology, FUP policies, and commercial terms.
- `service_package_versions`: Historical price and speed versioning table.
- `package_features` & `package_feature_service_package`: Reusable feature tags.
- `promotions` & `promotion_service_package`: Campaign promotions.
- `package_equipment_requirements`: Required hardware items per package.
- Availability Matrices (`service_package_branch`, `service_package_service_area`, `service_package_customer_type`).

### 6. Customer Management & CRM 360 (Phase 3)
- `customers`: Master subscriber accounts with `customer_number` (`CUST-XXXXXX`) and `account_number` (`ACC-XXXXXX`).
- `customer_status_histories`, `customer_contacts`, `customer_address_histories`, `customer_documents`, `customer_notes`, `customer_tags`, `customer_referrals`, `customer_assignments`, `customer_consents`, `customer_activities`.
- `leads`, `lead_status_histories`, `lead_activities`: CRM sales lead pipeline management and atomic conversion into subscriber accounts.

### 7. Geography & Locations
- `regions`, `provinces`, `cities_municipalities`, `barangays`, `addresses`, `locations`.

### 8. Network Infrastructure
- `network_nodes`, `access_points`, `network_devices`, `network_interfaces`.

### 9. Hardware Assets & Technical Tools
- `asset_categories`, `asset_models`, `assets`, `asset_histories`, `tool_categories`, `tools`.

### 10. Warehouse & Supply Chain
- `warehouses`, `warehouse_locations`, `item_categories`, `items`, `suppliers`, `item_supplier`.

### 11. Operations, Support & Finance
- `ticket_categories`, `ticket_priorities`, `ticket_statuses`, `sla_definitions`, `work_orders`, `accounts`, `transaction_types`, `number_sequences`.

---

## Currency & Coordinates Standard
1. **Monetary Values**: Stored exclusively as `DECIMAL(15,2)` or `DECIMAL(8,4)` for tax rates. Floating-point values (`FLOAT`, `DOUBLE`) are forbidden.
2. **Geographic Coordinates**: Stored as `DECIMAL(10,7)` for precision up to ~11mm without floating-point rounding errors.

---

## Centralized Concurrency-Safe Numbering
All sequential business numbers are generated via `App\Services\NumberSequenceService` using `SELECT ... FOR UPDATE` row locks inside a DB transaction to guarantee race-condition-free unique sequence increments.
