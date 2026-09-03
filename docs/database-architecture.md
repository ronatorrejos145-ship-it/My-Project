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

### 2. GIS, Infrastructure Mapping & Location Intelligence (Phase 6)
- `network_towers`: Telecom towers, monopoles, rooftop antenna masts. Stores `code`, `name`, `tower_type`, `height_meters`, `owner`, `latitude`, `longitude`, `status`, `notes`.
- `distribution_points`: Fiber splitters, cabinets, distribution boxes (`code`, `name`, `dp_type`, `capacity`, `parent_node_id`, `latitude`, `longitude`, `status`, `notes`).
- `location_histories`: Immutable audit log of coordinate movement changes for network nodes, towers, access points, and subscriber installation locations (`entity_type`, `entity_id`, `previous_latitude`, `previous_longitude`, `new_latitude`, `new_longitude`, `reason`, `changed_by`).
- `service_areas`: Enhanced with `boundary_geojson` (GeoJSON spatial polygon / multipolygon definition), `color_code`, `geometry_version`.
- `gis_imports`: Audit log for imported CSV/GeoJSON coordinate files (`filename`, `file_type`, `records_processed`, `records_imported`, `records_failed`, `error_summary`, `imported_by`).
- Bounding-Box Spatial APIs: `/api/gis/viewport` fetches map layers inside North/South/East/West latitude and longitude viewport bounds to avoid loading entire database datasets at once.
- GeoJSON API: `/api/gis/geojson/service-areas` exports standard FeatureCollection GeoJSON boundaries.

### 3. Online Customer Applications & Serviceability Engine (Phase 5)
- `service_applications`: Online service application master record. Sequential numbering `APP-YYYY-XXXXXX` via `NumberSequenceService`.
- `service_application_status_histories`: Immutable history of application lifecycle status transitions.
- `serviceability_checks`: Audit log of technical and commercial serviceability evaluations. Evaluates Commercial Availability and Technical Serviceability using precise **Haversine formula** GPS distance calculations in meters.
- `application_documents`: Attachment metadata for required applicant IDs, proof of address, etc.

### 4. Product Catalog & Service Package Versioning (Phase 4)
- `service_categories`: Configurable broadband service categories (`HOME`, `BUSINESS`, `CORPORATE`, `PREPAID`, `PUBLIC_WIFI`, `DEDICATED`).
- `service_packages`: Broadband plans storing speeds, technology, FUP policies, and commercial terms.
- `service_package_versions`: Historical price and speed versioning table.
- `package_features` & `package_feature_service_package`: Reusable feature tags (`BOOLEAN`, `TEXT`, `NUMBER`, `AMOUNT`, `PERCENTAGE`, `LIMIT`).
- `promotions` & `promotion_service_package`: Campaign promotions (`FREE_INSTALLATION`, `DISCOUNT`, `FIRST_MONTH_FREE`).
- `package_equipment_requirements`: Required hardware items per package.

### 5. Customer Management & CRM 360 (Phase 3)
- `customers`: Master subscriber accounts with `customer_number` (`CUST-XXXXXX`) and `account_number` (`ACC-XXXXXX`).
- `customer_status_histories`: Immutable log of lifecycle status changes.
- `customer_contacts`, `customer_address_histories`, `customer_documents`, `customer_notes`, `customer_tags`, `customer_referrals`, `customer_assignments`, `customer_consents`, `customer_activities`.
- `leads`, `lead_status_histories`, `lead_activities`: CRM sales lead pipeline management and atomic conversion into subscriber accounts.

### 6. Geography & Locations
- `regions`, `provinces`, `cities_municipalities`, `barangays`: Reusable hierarchical Philippine geographical master data.
- `addresses`: Universal address repository. `latitude`, `longitude` (`DECIMAL(10,7)`), `coordinate_accuracy` (`DECIMAL(8,2)`).
- `locations`: Polymorphic location records for network devices, nodes, towers, and branch hubs.

### 7. Network Infrastructure
- `network_nodes`: Core POPs, distribution POPs, access nodes, and relay towers.
- `access_points`: Wireless sector APs, GPON units with SSID, frequency, and capacity.
- `network_devices`: MikroTik CCR/CRS routers, switches, NanoBoxes, ONUs, and gateways.
- `network_interfaces`: Ethernet, Fiber, Wireless, VLAN, and Bridge interfaces attached to devices.

### 8. Hardware Assets & Technical Tools
- `asset_categories`, `asset_models`, `assets`, `asset_histories`, `tool_categories`, `tools`.

### 9. Warehouse & Supply Chain
- `warehouses`, `warehouse_locations`, `item_categories`, `items`, `suppliers`, `item_supplier`.

### 10. Operations, Support & Finance
- `ticket_categories`, `ticket_priorities`, `ticket_statuses`, `sla_definitions`, `work_orders`, `accounts`, `transaction_types`, `number_sequences`.

---

## Currency & Coordinates Standard
1. **Monetary Values**: Stored exclusively as `DECIMAL(15,2)` or `DECIMAL(8,4)` for tax rates. Floating-point values (`FLOAT`, `DOUBLE`) are forbidden.
2. **Geographic Coordinates**: Stored as `DECIMAL(10,7)` for precision up to ~11mm without floating-point rounding errors.

---

## Centralized Concurrency-Safe Numbering
All sequential business numbers are generated via `App\Services\NumberSequenceService` using `SELECT ... FOR UPDATE` row locks inside a DB transaction to guarantee race-condition-free unique sequence increments.
