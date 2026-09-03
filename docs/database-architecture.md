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

### 2. Customer Management & CRM 360 (Phase 3)
- `customers`: Master subscriber accounts. Auto-generated `customer_number` (`CUST-XXXXXX`) and `account_number` (`ACC-XXXXXX`). Supports individual, residential, family, business, corporate, and government customer types.
- `customer_status_histories`: Immutable log of lifecycle status changes (`PROSPECT`, `PENDING_VERIFICATION`, `VERIFIED`, `APPROVED`, `ACTIVE`, `TEMPORARILY_INACTIVE`, `SUSPENDED`, `TERMINATED`, `ARCHIVED`).
- `customer_contacts`: Multiple authorized contacts per customer with authorization levels (`FULL`, `BILLING_ONLY`, `TECHNICAL_ONLY`).
- `customer_address_histories`: Address movement and relocation history logs.
- `customer_documents`: Private, non-public document metadata (`VALID_ID`, `PROOF_OF_ADDRESS`, `BUSINESS_REG`, `CONTRACT`, `APPLICATION`) with verification status tracking (`PENDING`, `VERIFIED`, `REJECTED`, `EXPIRED`) and SHA-256 file checksums.
- `customer_notes`: Internal CRM notes with department-level visibility controls.
- `customer_tags` & `customer_customer_tag`: Configurable subscriber tags (`VIP`, `BUSINESS`, `NEW`, `HIGH_PRIORITY`).
- `customer_referrals`: Referral relationships between subscribers.
- `customer_assignments`: Historical employee and branch account manager assignment logs.
- `customer_consents`: Tracking accepted terms, privacy policies, and agreements with timestamps, IP address, and user agent.
- `customer_activities`: Universal Customer 360 activity timeline events.
- `leads`, `lead_status_histories`, `lead_activities`: Full CRM sales lead pipeline management and atomic conversion into subscriber accounts.

### 3. Geography & Locations
- `regions`, `provinces`, `cities_municipalities`, `barangays`: Reusable hierarchical Philippine geographical master data.
- `addresses`: Universal address repository. `latitude`, `longitude` (`DECIMAL(10,7)`), `coordinate_accuracy` (`DECIMAL(8,2)`).
- `locations`: Polymorphic location records for network devices, nodes, towers, and branch hubs.
- `service_areas`: Broadband service coverage zones linked to branches.
- `service_area_geographic_area`: Pivot linking service areas to barangays/municipalities.

### 4. Network Infrastructure
- `network_nodes`: Core POPs, distribution POPs, access nodes, and relay towers. `parent_node_id` FK for hierarchical topology.
- `access_points`: Wireless sector APs, 5GHz/60GHz/GPON units with SSID, frequency, and capacity.
- `network_devices`: MikroTik CCR/CRS routers, switches, NanoBoxes, ONUs, and gateways. Stores management IP, MAC address, and serial number.
- `network_interfaces`: Ethernet, Fiber, Wireless, VLAN, and Bridge interfaces attached to devices.

### 5. Hardware Assets & Technical Tools
- `asset_categories`, `asset_models`: Hardware category and manufacturer model definitions.
- `assets`: Hardware inventory (CPE ONTs, routers, switches). Uses `DECIMAL(15,2)` for `purchase_cost`.
- `asset_histories`: Immutable status, location, and assignment transition logs.
- `tool_categories`, `tools`: Technical installation equipment (Fiber fusion splicers, OTDR meters, crimpers).

### 6. Warehouse & Supply Chain
- `warehouses`: Storage facilities mapped to branches.
- `warehouse_locations`: Specific aisles, racks, shelves, and storage bins.
- `item_categories`, `items`: Catalog of cable, connectors, ONTs, and consumables with `unit_cost` (`DECIMAL(15,2)`).
- `suppliers`, `item_supplier`: Procurement vendors and pricing matrix.

### 7. Products, Pricing & Billing
- `billing_cycles`: Recurring billing definitions (Daily, Weekly, Monthly, Yearly).
- `taxes`: Configurable tax rates (12% VAT) using `DECIMAL(8,4)` rates.
- `payment_methods`: Configurable collection methods (Cash, GCash, Maya, Bank Transfer).
- `service_packages`: ISP speed broadband plans (`base_price`, `installation_fee`, `deposit_amount` as `DECIMAL(15,2)`).
- `service_package_versions`: Price version history to protect old invoices against price adjustments.
- `package_features`, `discounts`: Promotional discount codes and feature matrix.

### 8. Operations, Support & Finance
- `ticket_categories`, `ticket_priorities`, `ticket_statuses`, `sla_definitions`: Customer service SLA rules.
- `work_orders`: Field technical work order foundation for installations and repairs.
- `accounts`: Chart of Accounts (Asset, Liability, Equity, Revenue, Expense) for financial ledger integrity.
- `transaction_types`: Master financial transaction definitions with default Debit/Credit account FKs.
- `number_sequences`: Concurrency-safe sequence rules (`CUST-XXXXXX`, `INV-XXXXXX`, `WO-XXXXXX`, `LEAD-XXXXXX`).

---

## Currency & Coordinates Standard
1. **Monetary Values**: Stored exclusively as `DECIMAL(15,2)` or `DECIMAL(8,4)` for tax rates. Floating-point values (`FLOAT`, `DOUBLE`) are forbidden.
2. **Geographic Coordinates**: Stored as `DECIMAL(10,7)` for precision up to ~11mm without floating-point rounding errors.

---

## Centralized Concurrency-Safe Numbering
All sequential business numbers are generated via `App\Services\NumberSequenceService` using `SELECT ... FOR UPDATE` row locks inside a DB transaction to guarantee race-condition-free unique sequence increments.
