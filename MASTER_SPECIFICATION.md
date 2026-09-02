# ISP / WiFi Service Management Platform - Master Specification

## Role & System Overview
You are a senior full-stack software architect, Laravel/PHP engineer, MySQL database architect, ISP billing-system architect, network-management engineer, GIS engineer, cybersecurity engineer, DevOps engineer, UI/UX designer, QA engineer, and technical field-service management specialist.

This system is a COMPLETE PRODUCTION-READY INTERNET SERVICE PROVIDER (ISP) / WIFI SERVICE MANAGEMENT PLATFORM built with Laravel, PHP, MySQL, Blade/Vue, Vite, Tailwind CSS, REST APIs, queues, scheduled jobs, and a responsive modern web interface.

THIS IS NOT A SIMPLE BILLING SYSTEM.

It is a complete operational platform for an Internet/WiFi company covering the entire customer lifecycle, including customer acquisition, package selection, serviceability checking, technical surveying, GPS mapping, installation, equipment assignment, subscriber activation, billing, invoices, payments, receipts, rebates, credits, refunds, accounting ledger, collections, suspension, reconnection, maintenance, customer support, employee management, technical workforce management, inventory, tools, network infrastructure, NOC operations, reporting, administration, security, and customer self-service.

The system must be designed as a real business application, not a demonstration, mockup, prototype, or collection of static pages.

---

## Technology Requirements
- **Framework**: Laravel
- **Language**: PHP 8.3+
- **Database**: MySQL 8+
- **Frontend**: Blade and/or Vue for interactive interfaces, Vite, Tailwind CSS
- **Authentication & Authorization**: Laravel Auth, Sanctum (for REST APIs), RBAC / Policies / Middleware
- **Background & Scheduling**: Laravel queues, Laravel scheduler
- **Notifications**: Laravel notifications (Email, SMS integration architecture, In-app)
- **Data Integrity**: Database transactions, Auditable financial ledger
- **Architecture**: RESTful APIs, Service classes, Form requests, Event-driven architecture
- **GIS**: Leaflet / OpenStreetMap-compatible GIS architecture
- **Documents**: PDF generation for invoices and receipts
- **Deployment Compatibility**: Cloudflare-compatible, Apache/cPanel-compatible, InterServer-compatible

> **Note on Deployment Assets**: The production server must be capable of running the application without requiring a permanently running Node.js development server. Vite must be used for development/building, while production assets should be deployable through the normal Laravel public directory. Do not introduce unnecessary infrastructure dependencies.

---

## Core Business Domains
1. Executive management
2. Super administration
3. Administration
4. Customer service
5. CRM
6. Sales
7. Customer/subscriber management
8. Service package management
9. Online applications
10. Serviceability checking
11. GIS/location management
12. Technical surveys
13. Installations
14. Maintenance
15. Technical work orders
16. Technician dispatch
17. Network infrastructure
18. NOC monitoring
19. MikroTik integration architecture
20. RADIUS integration architecture
21. Customer equipment
22. Company assets
23. Technical tools
24. Warehouse & Inventory
25. Purchasing & Suppliers
26. Employees & Workforce management
27. Finance & Billing (Invoices, Receipts, Payments, Customer ledger, Rebates, Credits, Refunds, Collections, Suspensions, Reconnections, Terminations)
28. Customer portal & Employee portal
29. Customer support & Ticketing
30. Notifications (SMS/Email architecture)
31. Reports & Analytics
32. Audit logging & Security administration

---

## Customer Lifecycle
```
PROSPECT
→ APPLICATION
→ ADDRESS/GPS CAPTURE
→ SERVICEABILITY CHECK
→ TECHNICAL SURVEY
→ APPROVAL
→ INSTALLATION SCHEDULING
→ INSTALLATION
→ EQUIPMENT ASSIGNMENT
→ SERVICE ACTIVATION
→ SUBSCRIPTION
→ BILLING
→ PAYMENT
→ MAINTENANCE/SUPPORT
→ UPGRADE/DOWNGRADE/RELOCATION
→ SUSPENSION IF NECESSARY
→ RECONNECTION
→ TERMINATION
```
*Every important state transition must be recorded in history. Never destroy historical business records simply because the current status changes.*

---

## Multi-Department Architecture & RBAC
Implement a true role and permission system. Departments include:
- SUPER ADMIN
- ADMINISTRATION
- MANAGEMENT
- FINANCE
- CASHIER
- CUSTOMER SERVICE
- SALES
- TECHNICAL
- TECHNICAL SUPERVISOR
- NOC
- WAREHOUSE
- HR
- EMPLOYEE
- CUSTOMER

Do not rely only on hiding menu items. Every protected operation must also be authorized server-side using permissions/policies/middleware.

---

## Customer Management
Complete customer accounts containing:
- Customer number, Account number
- Full legal name, Business name where applicable
- Contact numbers, Email
- Address, Billing address, Installation address (Barangay, Municipality, Province, Postal code)
- GPS latitude, GPS longitude, Location accuracy, Landmark
- Customer status & lifecycle state history
- Documents, Identification records, Notes
- Communication, Subscription, Installation, Maintenance, Billing, Payment, Equipment, and Support histories.

**Customer Lifecycle States**:
- PROSPECT
- PENDING VERIFICATION
- PENDING SURVEY
- PENDING INSTALLATION
- ACTIVE
- GRACE PERIOD
- OVERDUE
- SUSPENDED
- TEMPORARILY DISCONNECTED
- PERMANENTLY DISCONNECTED
- CANCELLED
- ARCHIVED

---

## GIS and Technical Mapping
- Leaflet / OpenStreetMap compatible.
- Store precise geographic coordinates for Customers, Installation locations, Technicians (where permitted), WiFi routers, Access points, NanoBox devices, Towers, Distribution points, Network nodes, Fiber boxes, Maintenance locations, and Service areas.
- Functional capabilities: Search customers/equipment, place/move markers, view coordinates, measure distances, view nearby network nodes, view service areas, customer density, equipment relationships, work orders, and maintenance locations.

---

## Serviceability Engine & Technical Survey
- **Serviceability Evaluation**: Service area, distance from network equipment/AP/NanoBox, available capacity, technical restrictions, existing infrastructure, technical survey requirement.
- **Results**: SERVICEABLE, REQUIRES TECHNICAL SURVEY, OUT OF COVERAGE, CAPACITY UNAVAILABLE.
- **Technical Survey Workflow**: Survey data capture (GPS, signal measurement, line-of-sight, router/AP placement, cable estimate, required materials, difficulty, photos, notes, recommendation, approval status).

---

## Installation System
- Technical work orders with states: PENDING → ASSIGNED → SCHEDULED → EN ROUTE → ON SITE → IN PROGRESS → TESTING → COMPLETED.
- Checklist requirements: Location verified, ID verified, GPS captured, signal tested, node selected, router/NanoBox/AP installed, cable installed, power/internet/speed/latency tested, photos captured, customer acceptance captured.
- Support failed and rescheduled installations.

---

## Equipment, Asset, Tool & Inventory Management
- **Equipment/Assets**: Routers, MikroTik equipment, NanoBox, AP, ONU, Modems, Switches, PoE, Antennas, UPS, etc. Track Serial Number, MAC address, warranty, status, location, assigned employee/customer, installation history.
- **Tools**: Testers, crimping tools, multimeters, drills, fiber tools, ladders, safety equipment. Track Serial number, condition, employee assignment, issue/return dates, damage/loss.
- **Warehouse**: Items, categories, suppliers, stock movements (stock-in/out, transfers, reservations, returns, damaged/lost), reorder levels, purchase orders. Automatic inventory consumption on installations.

---

## Packages, Subscriptions & Billing Engine
- **Service Packages**: Residential, business, prepaid, postpaid, custom. Configure speeds, prices, setup fees, deposits, billing cycles, grace periods, data limits, FUP, contract duration, reconnection fees, service areas.
- **Subscriptions**: Package, start/end dates, billing cycle, status history (activation, suspension, reconnection, upgrade, downgrade, relocation, termination).
- **Billing Engine**: Recurring, daily/weekly/monthly, prepaid/postpaid, proration, installation/activation charges, deposits, discounts, rebates, credits, penalties, reconnection fees, adjustments, taxes. Idempotent invoice generation via scheduled jobs.

---

## Financial Ledger & Payments
- **Auditable Financial Ledger**: Transactions (Invoice, Payment, Credit, Debit, Discount, Rebate, Refund, Adjustment, Penalty, Reversal). Never silently edit or delete financial history; use reversal/correction transactions. Decimal database fields for currency values.
- **Payments**: Cash, Bank transfer, QR payment, GCash/Maya integration, Card gateways, Manual payments. Idempotence for webhooks/callbacks.
- **Invoices & Receipts**: Printable PDF output with complete metadata and breakdown.
- **Rebates, Credits, Refunds**: Threshold-based authorization workflows.
- **Collections & Automated Suspension**: Invoice generated → Reminder → Due date → Grace period → Overdue → Suspension warning → Suspension → Payment verified → Reconnection.

---

## Customer & Employee Portals, Support & CRM
- **Customer Portal**: Dashboard with plan, internet status, balance, next invoice, due date, payment history, receipts, support tickets, installation info, maintenance notices. Actions: Apply, pay bill, request changes, report issues, upload docs.
- **Customer Support / CRM**: Complete lookup, ticket management (No Internet, Slow Speed, Billing, etc.), priority/SLA rules, maintenance field dispatch work orders.

---

## Network, NOC & Integration Abstraction (MikroTik / RADIUS)
- NOC tracking of network nodes, routers, MikroTik devices, APs, NanoBoxes, switches, gateways, interfaces, connected customers.
- Status monitoring: Online/offline, CPU, memory, bandwidth, latency, uptime.
- Abstract service interfaces for subscriber provisioning, activation, suspension, reconnection, bandwidth profile changes, session lookup, usage lookup without hard-coding specific vendor logic.

---

## Security, Firewall & Audit Logging
- Defense in depth: Authentication, password hashing, strong password policies, 2FA for privileged roles, RBAC, CSRF, XSS, SQL injection protection, rate limiting, secure cookies, HTTPS, security headers.
- Cloudflare / reverse proxy compatibility.
- Comprehensive audit logging for sensitive actions (user, action, module, record, state diff, IP, timestamp).

---

## Notification & UI/UX
- **Notifications**: Email, SMS, In-app. Queued where appropriate.
- **UI/UX**: Responsive desktop/tablet/mobile, accessible forms, search, filtering, pagination, sorting, charts, maps, status badges, toast notifications, dark/light theme support. Tailored role-specific department dashboards.

---

## Development & Deployment Rules
- Always inspect existing code before making changes; reuse working functionality.
- Incremental, Laravel-native development (Service classes, Form Requests, Policies, Events/Listeners, Queued Jobs, Database Transactions).
- Automated tests for core business rules (Auth, Billing, Payments, Ledger, Inventory, Lifecycle, SLA).
- Deployable on standard cPanel/Apache/PHP environments with Vite build artifacts in `public/`.
- Execute project development phase-by-phase upon receiving phase prompts.
