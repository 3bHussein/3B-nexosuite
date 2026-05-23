<div align="center">

<img src="assets/3b-nexosuite-logo.svg" alt="3B NexoSuite Logo" width="900">

# 3B NexoSuite ERP

### Modular ERP + E-commerce + CRM + Inventory + Finance Platform

**One system to run sales, stock, finance, service and online business.**

<br>

![Status](https://img.shields.io/badge/status-active-success?style=for-the-badge)
![Version](https://img.shields.io/badge/version-57.5-blue?style=for-the-badge)
![Platform](https://img.shields.io/badge/platform-PHP%20%2B%20MySQL-purple?style=for-the-badge)
![Trial](https://img.shields.io/badge/trial-5%20records-orange?style=for-the-badge)
![Business Profiles](https://img.shields.io/badge/business%20profiles-4-teal?style=for-the-badge)

</div>

---

## Overview

**3B NexoSuite** is a modular ERP and e-commerce platform built for businesses that want to manage daily operations from one connected system.

It combines:

- ERP
- E-commerce
- CRM
- Inventory
- Accounting
- Procurement
- Service operations
- Customer portal
- Trial activation and licensing

The system is designed for companies that need a practical business platform for online sales, stock control, customers, orders, finance, service jobs, suppliers and reporting.

---

## Product Name

### **3B NexoSuite**

The name **NexoSuite** is inspired by connection, business flow, modular apps and one unified operating system for business.

It is short, modern and suitable for a SaaS-style ERP product.

---

## Tagline

> **One system to run sales, stock, finance, service and online business.**

---

## Short Description

**3B NexoSuite** is a modular ERP and e-commerce platform built for businesses that want to manage products, customers, orders, inventory, accounting, CRM, procurement, services and online sales from one connected system.

---

## Full Branding Text

3B NexoSuite is an all-in-one business management platform for ERP, e-commerce, CRM, inventory, accounting, procurement, customer portal and service operations.

It helps businesses control daily operations, manage online sales, track customers, handle stock, create invoices, process orders and grow from one powerful system.

---

## GUI Preview Structure

The system is designed around a modern business dashboard interface.

```text
+-------------------------------------------------------------+
| 3B NexoSuite                                                |
+-------------------------------------------------------------+
| Dashboard | Sales | Products | Customers | Orders | Finance |
+-------------------------------------------------------------+
|                                                             |
|  KPI Cards       Sales Overview       Trial Status           |
|                                                             |
|  Products        Categories           Customers              |
|  Orders          Inventory            CRM Pipeline           |
|  Accounting      Procurement          Reports                |
|                                                             |
+-------------------------------------------------------------+
```

---

## Main System Areas

| Area | Description |
|---|---|
| Website Storefront | Public e-commerce website, products, cart and checkout |
| Admin Panel | Business control center for staff and owner |
| Customer Portal | Customer login, orders, invoices, downloads and documents |
| ERP Modules | Inventory, finance, CRM, procurement, service and reporting |
| Trial Activation | Customer-specific request code and activation flow |
| Deployment Tools | Installer, setup flow, checklist and hosting guide |

---

## Product Family

Use these names for modules, future packages and marketing pages.

| Product Name | Purpose |
|---|---|
| **3B NexoSuite ERP** | Full business management platform |
| **3B NexoSuite Commerce** | E-commerce storefront and online sales |
| **3B NexoSuite CRM** | Leads, customers, opportunities and sales pipeline |
| **3B NexoSuite Inventory** | Products, stock, warehouses and transfers |
| **3B NexoSuite Finance** | Invoices, accounting, tax and reports |
| **3B NexoSuite Portal** | Customer login, documents, invoices and downloads |
| **3B NexoSuite Service** | Job cards, service requests, projects and warranty |
| **3B NexoSuite Business Apps** | Modular app ecosystem |

---

## Supported Business Profiles

3B NexoSuite can be installed for different industries.

| Business Profile | Suitable For |
|---|---|
| **Automotive** | Garage equipment, automotive tools, diagnostic software, service workshops |
| **Electronics** | Mobile shops, accessories, devices, warranties, B2B electronics sales |
| **Food Industry** | Packaged food, beverages, catering, wholesale food operations |
| **General Trading** | Wholesale, B2B/B2C trading, inventory, suppliers and procurement |

---

## Core Modules

### Website & Commerce

- Website storefront
- Homepage builder
- Product catalog
- Category management
- Cart and checkout
- Online orders
- Contact page
- Blog and content pages
- Downloads
- SEO settings

### Sales & CRM

- Customers
- Leads
- Opportunities
- Quotations
- Sales orders
- Follow-ups
- Campaigns
- Pipeline tracking

### Inventory

- Products
- SKUs
- Warehouses
- Branches
- Stock transfers
- Stock counts
- Reorder planning
- Inventory reports

### Finance

- Invoices
- Accounting dashboard
- Tax settings
- Journals
- Bank records
- Budgets
- Cash flow
- Financial reports

### Procurement

- Suppliers
- Purchase orders
- RFQ workflow
- Tender management
- Supplier comparison
- Goods receipt

### Service Operations

- Job cards
- Service requests
- Warranty claims
- Technician workflow
- Projects
- Dispatch
- Customer sign-off

### Customer Portal

- Customer dashboard
- Order history
- Invoices
- Downloads
- Documents
- Service requests
- Support and feedback

### Admin & System

- Settings
- Security
- Backup and restore
- Deployment checklist
- Demo data manager
- Trial activation loader
- Owner activation generator

---

## Trial Activation System

3B NexoSuite includes a customer-specific trial and activation system.

### Trial Limits

In trial mode, the customer can create:

| Area | Trial Limit |
|---|---:|
| Products | 5 |
| Categories | 5 |
| Customers | 5 |
| Orders | 5 |

After the limit is reached, the system displays an activation message and blocks new record creation until activation is completed.

Existing records remain safe and visible.

---

## Activation Message

Recommended message shown to customer:

```text
Please activate your 3B NexoSuite license to continue creating new records.
```

---

## Customer-Specific Request Code

Each installation generates a unique request code.

The request code can be based on:

- Installation UID
- Domain or localhost path
- Database name
- Database prefix
- Shop name
- Shop email
- App encryption key fingerprint

This makes the activation request unique for each customer installation.

---

## Safe License Control

The activation system should be non-destructive.

It should not:

- Delete customer records
- Damage database tables
- Corrupt files
- Encrypt customer data after the trial limit
- Block customers from viewing existing records

It should only block new creation after the trial limit until activation is completed.

---

## Deployment Flow

### Basic Deployment Steps

1. Upload `install.php` to hosting.
2. Create a MySQL database.
3. Open the installer in the browser.
4. Select the business profile.
5. Select the module bundle.
6. Enter database details.
7. Enter store and admin details.
8. Run the installation.
9. Test admin login.
10. Test storefront.
11. Test product, category, customer and order creation.
12. Test activation loader.
13. Delete `install.php` after installation.

---

## Recommended Hosting Requirements

| Requirement | Recommended Value |
|---|---|
| PHP Version | PHP 8.1 or newer |
| Database | MySQL |
| Server | Hostinger, VPS or compatible PHP hosting |
| Web Server | Apache, LiteSpeed or Nginx compatible |
| SSL | Required for production |

---

## Required PHP Extensions

```text
pdo_mysql
mysqli
mbstring
openssl
curl
zip
fileinfo
json
session
gd
intl
```

---

## Suggested Sales Positioning

Use this text in a website, proposal or brochure:

> **3B NexoSuite is a modular ERP and e-commerce platform for growing businesses. It combines online sales, inventory, customers, orders, accounting, CRM, procurement, service workflows and customer portal features into one connected business system.**

---

## Suggested Hero Section

```text
3B NexoSuite ERP

One system to run sales, stock, finance, service and online business.

Manage products, customers, orders, inventory, accounting, CRM, procurement and e-commerce from one powerful modular platform.
```

---

## Suggested CTA Text

```text
Request a Live Demo
```

```text
Start Your Business ERP Setup
```

```text
Activate Your 3B NexoSuite License
```

---

## Suggested GitHub Repository Structure

```text
3b-nexosuite/
├── README.md
├── install.php
├── activation-loader.php
├── owner_activation_generator.php
├── assets/
│   ├── 3b-nexosuite-logo.svg
│   └── 3b-nexosuite-icon.svg
├── docs/
│   ├── HOSTINGER_DEPLOYMENT_GUIDE.md
│   └── HOSTINGER_QUICK_CHECKLIST.txt
└── landing-page/
    └── index.html
```

---

## Suggested Landing Page Sections

1. Hero section
2. Business app modules
3. Supported industries
4. Trial activation system
5. ERP feature comparison
6. Package or pricing section
7. Demo request section
8. Footer

---

## Visual Branding

### Main Brand

**3B NexoSuite**

### Full Product Name

**3B NexoSuite ERP**

### Short Name

**NexoSuite**

### Brand Owner Placeholder

**3B**

### Visual Style

- Modern SaaS
- Clean ERP dashboard style
- Modular business app cards
- Purple/plum and teal color palette
- Professional B2B software look

### Suggested Colors

| Color | Hex |
|---|---|
| Deep Plum | `#4B164C` |
| ERP Purple | `#714B67` |
| Teal | `#00A09D` |
| Dark Navy | `#17113F` |
| Soft Background | `#FBF8FB` |
| Border Line | `#EADFEB` |

---

## Alternative Names Kept for Reference

Other possible names considered:

- 3B OrdoSuite
- 3B FlowERP
- 3B OneGrid
- 3B VeloSuite
- 3B CoreERP
- 3B BizOS
- 3B Modulo
- 3B Opero
- 3B NexoERP
- 3B OrbitSuite

Final selected name:

```text
3B NexoSuite
```

---

## Future Improvements

Recommended next improvements:

- Add branded admin login screen
- Add NexoSuite favicon
- Add customer onboarding wizard
- Add package comparison page
- Add PDF commercial brochure
- Add demo video section
- Add activation owner dashboard
- Add license expiry options
- Add multilingual support
- Add professional landing page screenshots
- Add installer progress UI improvement
- Add module-based pricing logic

---

## Security Notes

- Keep the owner activation generator private.
- Do not upload private activation tools to customer hosting.
- Always delete `install.php` after installation.
- Always enable SSL in production.
- Always use a strong admin password.
- Always backup the database before major changes.
- Test in staging before production use.

---

## Commercial Disclaimer

3B NexoSuite is intended as a commercial ERP and e-commerce platform. It should be tested in a staging environment before production deployment.

Always keep backups before installing, updating or modifying a live customer system.

---

<div align="center">

<img src="assets/3b-nexosuite-icon.svg" alt="3B NexoSuite Icon" width="140">

## 3B NexoSuite

**Modular ERP + E-commerce + CRM + Inventory + Finance Platform**

**One system to run sales, stock, finance, service and online business.**

</div>
