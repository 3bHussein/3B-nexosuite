# E-commerce ERP Commerce Suite

**E-commerce ERP Commerce Suite** is a PHP + MySQL installer-generated platform that combines:

- Responsive multi-purpose e-commerce storefront
- B2B and B2C sales workflows
- ERP invoices, quotations, customers, procurement, and finance
- Inventory synchronization between website products and ERP stock
- Employee access controls and permission roles
- Homepage Builder, product image upload, and rich-text content editing
- SEO settings, page metadata controls, and editable `robots.txt`

---

## Main Capabilities

### Frontend Commerce
- Responsive homepage with customizable sections
- Mobile-first header and fullscreen mobile menu drawer
- Product listing, product detail pages, cart, and checkout structure
- Services, downloads, contact, booking, and blog pages
- Customer account, order area, downloads, and support area

### Content Management
- Homepage Builder for hero, promo, category, featured, B2B/B2C, new arrival, service, trust, and newsletter sections
- Product image upload and gallery upload
- Rich text editor for product descriptions and blog posts
- Blog publishing and article editing

### SEO Management
- Global SEO defaults
- Per-page SEO controls for:
  - Homepage
  - Products
  - Services
  - Downloads
  - Blog
  - Contact
  - Booking
  - Cart
  - Checkout
  - Login / Account
  - Registration
- Meta title, description, keywords, robots, and canonical URL controls
- Open Graph and Twitter meta tags
- Dynamic SEO fallbacks for product and blog detail pages
- Admin-editable `robots.txt` file

### ERP
- Command center dashboard
- CRM and customer management
- Quotations and invoice conversion
- Finance, receivables, payments, and expenses
- Stock, inventory movements, low-stock logic
- Suppliers and purchase orders
- Employee access, roles, and activity log

---

## Installation

1. Upload the generated project files to your PHP server.
2. Open `installer.php` in the browser.
3. Select the business edition:
   - Automotive
   - Electronics
   - Food Industry
   - General Trading
4. Enter database credentials.
5. Create the administrator account.
6. Complete installation.
7. Remove or rename `installer.php` after installation.

---

## Recommended First Setup

After installation:

1. Go to **Admin > Settings** and confirm store details.
2. Go to **Admin > Homepage Builder** to customize the main landing page.
3. Go to **Admin > Products** to add product images and catalog content.
4. Go to **Admin > Blog** to publish rich-text articles.
5. Go to **Admin > Settings > SEO** to configure metadata and `robots.txt`.
6. Go to **ERP > Employee Access** and configure roles and staff access where required.

---

## SEO Usage

### Global SEO Defaults
Use **Admin > Settings > Global SEO Defaults** to define:
- Default title suffix
- Default meta description
- Default keywords
- Default robots directive
- Default Open Graph image URL or path

### Page SEO Manager
Use **Admin > Settings > Page SEO Manager** to define:
- Meta title
- Meta description
- Keywords
- Robots meta tag
- Canonical URL

### robots.txt
Use **Admin > Settings > robots.txt Editor**. Saving the page writes the content to:

```text
/robots.txt
```

The default file blocks admin, employee, account, checkout, and payment areas from crawling.

---

## Product Media and Rich Text

### Product Photos
In **Admin > Products > Add/Edit Product**:
- Upload a main product photo
- Upload multiple gallery photos
- Use manual URLs or filenames when required

### Rich Text Editor
The editor supports:
- Bold, italic, underline
- Headings
- Paragraphs
- Bullet and numbered lists
- Links
- Clear formatting

The rich editor is available in:
- Product full description
- Blog excerpt
- Blog article content

---

## Security Notes

- Remove or rename `installer.php` after installation.
- Use strong admin and employee passwords.
- Keep PHP, MySQL, and hosting software patched.
- Review role permissions before giving staff access.
- Back up the database and uploads directory regularly.

---

## Core Directory Structure

```text
e-commerce-erp/
├── admin/
├── assets/
├── blog/
├── downloads/
├── employee/
├── includes/
├── products/
├── services/
├── uploads/
├── user/
├── robots.txt
├── README.md
├── config.php
├── index.php
└── installer.php
```

---

## License

Customize for your deployment. Review legal, hosting, tax, and payment requirements before production use.