## Exhibitor Booking Flow – Functional Specification

This document describes the **Exhibitor Booking Flow** and related modules of the Exhibition Management System. It is written in functional terms (what the system must do) and organized module-wise for implementation and review.

---

## 1. System Overview

The Exhibition Management System is a web platform for:

- **Exhibitors**: Registering, booking spaces (stalls/booths), purchasing add-on services, managing documents, payments, badges, and communications.
- **Organizers/Admins**: Configuring exhibitions, floor plans, pricing and discounts, managing approvals, financials, communications, and reporting.

The system supports:

- Multiple exhibitions (local and international).
- Web-based responsive UI.
- Optional mobile app integration (TBD).

---

## 2. Core Architecture Overview

### 2.1 Frontend Modules

- **Public Landing Page**
  - Show active and upcoming exhibitions.
  - Display event highlights, schedules, and key information.
  - Provide registration and login links for exhibitors.

- **Exhibitor Registration & Login Portal**
  - Registration with company and contact details.
  - Login methods:
    - Email + password.
    - OTP-based login (mobile-based).
  - Email verification for new registrations.
  - Password recovery via email and/or SMS.

- **Booking Management Dashboard (Exhibitor)**
  - List of available exhibitions.
  - Entry point to floorplans and booking flows.
  - View, modify, or cancel existing bookings.
  - View booking statuses and history.

- **Payment Interface**
  - Secure payment page (integrated with payment gateways).
  - Supports:
    - Cards, UPI, Net banking, Wallets.
    - RTGS/NEFT (with receipt upload).
  - Shows:
    - Instalment breakdown.
    - Additional gateway charges (e.g. 2.5%).
    - Refund and cancellation notes.
  - Generates and displays receipts and invoices.

- **Document Management UI**
  - Upload and manage:
    - Certificates, IDs, catalogues, proofs, booth designs, etc.
  - Show:
    - Upload limits (max file size, count).
    - Verification status (Pending / Approved / Rejected).
  - Allow download of:
    - User’s uploaded documents.
    - Exhibition manual PDF uploaded by admin.

- **Communication Center / Webmail**
  - In-app messaging between Exhibitor and Admin.
  - Notification count visible until user has replied.
  - When conversation loop is closed, thread moves to **Archive**.
  - No delete option; messages are never removed.
  - Left panel folders:
    - Inbox
    - Pending
    - Completed
    - Archive

- **Exhibitor Dashboard**
  - Centralized area for exhibitors to:
    - See all current and past bookings with statuses.
    - View and download invoices, receipts, challans, possession letters.
    - Track payments, instalments, refunds, and wallet balance.
    - Manage and upload documents.
    - Purchase and manage additional services.
    - Manage sponsorship bookings and payments.
    - Store up to **5 email IDs** and **5 contact numbers**.

### 2.2 Backend Modules

- **Authentication & Authorization Service**
  - User roles:
    - Admin
    - Exhibitor
    - Staff (e.g., Accountant)
  - Handles:
    - Login with email/password and OTP.
    - Password reset (email/SMS).
    - Session management and security.
  - Role-based access control:
    - Admin: All modules.
    - Accountant: Payment/financial modules only.
    - Other roles: Configurable (TBD).

- **Booking Management Engine**
  - Manages:
    - Booth availability and statuses.
    - Price calculations.
    - Booking creation, modification, cancellation, and replacement.
  - Ensures:
    - Real-time updates of floorplan availability.
    - Consistent handling of partial/initial payments and instalments.

- **Payment Gateway Integration Layer**
  - Integrates with external gateways for online payments.
  - Handles gateway surcharges and success/failure callbacks.
  - Manages RTGS/NEFT reference details and challan generation.

- **Notification & Communication Service**
  - Sends:
    - Email and SMS notifications.
    - In-app notifications (with unread counts).
  - Types of messages:
    - Registration, verification, login-related notifications.
    - Booking confirmations, modifications, cancellations.
    - Payment receipts, due-date reminders, refund confirmations.
    - Document status updates.
    - Admin tasks and exception alerts.

- **Document Storage & Management Service**
  - Secure storage (e.g., cloud storage).
  - Version control for documents.
  - Expiry tracking (where applicable).
  - Metadata for file size, upload count, and verification state.

- **Reporting & Analytics Engine**
  - Generates:
    - Booking reports.
    - Financial and revenue reports.
    - Space utilization and service-usage reports.
    - Exception and override reports.
    - Post-event summaries.

- **Integration Layer (APIs)**
  - RESTful APIs with:
    - Authentication and authorization.
    - Rate limiting.
  - Integrations:
    - Payment gateways.
    - Cloud storage.
    - Email/SMS gateways.
    - Analytics tools.
    - Mobile app ↔ web sync (TBD).

---

## 3. User Management Module

### 3.1 User Types

- **Admin**
  - Full access to all modules and configurations.

- **Exhibitor**
  - Access to own bookings, documents, payments, badges, and communication.

- **Staff (e.g., Accountant)**
  - Limited access (e.g., financial and payment-related modules only).

### 3.2 Registration Flow

1. Exhibitor opens registration page and fills:
   - Company details.
   - Authorized person contact details.
   - Email and mobile number.
2. System sends verification email.
3. On verification:
   - User account is activated.
   - Login credentials are defined (password set).
4. Multi-level access:
   - Assigned based on role (Admin, Exhibitor, Staff).

### 3.3 Login & Account Access

- **Login Methods**
  - Email + password.
  - OTP-based login (OTP to registered mobile).

- **Password Recovery**
  - Via email and/or SMS link/OTP.

- **Post-Booking Auto-Creation**
  - Certain booking flows can allow:
    - User to make an initial payment (e.g., 10%) without pre-registration.
    - After payment, system auto-creates login credentials.
    - Credentials are emailed to user.
  - Initial payment percentage is configurable by admin.
    - Changes affect only new bookings, not existing ones.

### 3.4 Exhibitor Capabilities

- View and update company profile.
- Cancel existing stall, book additional stall, or replace existing stall.
- Upload/replace brochures (up to 5 files).
- Purchase extra items (e.g., chairs, tables, extra services):
  - List of items, pricing and images managed by admin.
  - Purchase allowed only before a configurable cut-off date (set by admin per exhibition).

---

## 4. Exhibition Booking Module

### 4.1 Exhibition Details Page

Each exhibition has a public details page containing:

- Event overview, dates, and venue information.
- **Visual floorplan** of the event venue.
- List of booth categories:
  - Standard
  - Premium
  - Economy
- Filters for:
  - Area range.
  - Category (Standard/Premium/Economy).
  - Booth type (Raw/Orphand).

### 4.2 Booth Types

- **Raw Booth**
  - Only space is allocated.
  - No physical setup included.

- **Orphand Booth**
  - Complete setup included as defined by admin.
  - Different pricing from Raw booths.

### 4.3 Price Calculation

Price for each booth is determined by:

- **Base price**:
  - Area in sq. ft × sq. ft rate.
  - Sq. ft rate defined by admin per exhibition/zone/category.
- **Side openness price variation**:
  - 1-side open.
  - 2-side open.
  - 3-side open.
  - 4-side open.
  - Each configuration has admin-defined percentage increase/decrease.
- **Booth type**:
  - Raw vs Orphand (separate base rates).
- **Discounts & Overrides**:
  - Special discounts for selected exhibitors (percentage, not fixed).
  - Option for free booths.
  - Other admin overrides (tracked in exception reports).

### 4.4 Booking Flow (Exhibitor)

1. Exhibitor visits exhibition details page.
2. They open the floorplan and see:
   - Available vs booked booths.
   - Booth size, type (Raw/Orphand), category, side-open configuration.
   - Price breakdown.
3. Exhibitor selects one or more booths.
4. Exhibitor completes booking form:
   - Company & contact details (if not already filled).
   - Selection of add-on items/services (e.g., chairs, tables, utilities).
5. Exhibitor accepts terms & conditions.
6. Exhibitor proceeds to payment:
   - Initial percentage payment (e.g., 10%) required to confirm the booking.
   - Remaining payments as per instalment schedule.
7. On successful payment:
   - Booking is confirmed (or temporarily reserved based on rules).
   - Invoice/receipt is generated and emailed.
   - If user was not registered:
     - System generates login credentials and emails them.

### 4.5 Post-Booking Requirements

- Exhibitor uploads company logo:
  - Displayed on visual floorplan for their booked booth(s) as "booked by" marker.
- Exhibitor can:
  - Book additional stalls.
  - Request cancellation of existing stalls.
  - Request stall replacement/upgrade as per policies.

---

## 5. Space & Venue Management Module (Admin)

### 5.1 Floorplan Management

- Upload and manage floorplans per exhibition.
- Define:
  - Booth IDs and coordinates.
  - Areas, categories, booth types, side-open configurations.
  - Initial statuses (available/booked/blocked).

### 5.2 Visual Stall Variations

- Admin can upload visual references for:
  - How each stall looks for each side-open configuration.
  - Each stall type and scheme.
- Frontend:
  - Shows these variations at booking time for exhibitor clarity.

### 5.3 Combining and Splitting Stalls

- **Combine**:
  - Admin can merge two stalls into a single larger stall.
  - New stall naming rule example:
    - Merge D1 and D2 → `D1D2`.
- **Split**:
  - Admin can split larger stall into smaller stalls.
  - Example naming pattern:
    - Split D1 into D1 and D12 (pattern to be finalized and standardized).

### 5.4 Real-time Availability & Pricing

- System keeps floorplan updated:
  - Shows which stalls are free, reserved, booked.
  - Applies correct pricing and discounts.

---

## 6. Stall / Booth Management Module

### 6.1 Stall Schemes

- Stall schemes are defined per stall size (e.g., 9 sq m, 18 sq m).
- For each scheme, admin specifies:
  - Included items:
    - Example:
      - 9 sq m → 1 table, 2 chairs, 2 lights.
      - 18 sq m → 2 tables, 4 chairs, 4 lights.
  - Admin can add/modify/delete included items (one-time setup).
  - Upload images for included items (tables, chairs, lights, etc.).

### 6.2 Payment Parts & Schedule

- For each stall (or scheme), admin defines:
  - Number of payment parts (e.g., 2 parts, 3 parts).
  - Initial due dates for each part.
- Rules:
  - Number of parts is fixed once created (cannot be changed later).
  - Due dates for each part can be modified in future if needed.
  - Exhibitor must clear each part payment by due date.

### 6.3 Outcome States

- After payment of first part:
  - Booth is reserved.
- After all parts are paid:
  - Booth is fully confirmed.
  - Possession letter can be generated and approved by admin.

---

## 7. Payment Processing Module

### 7.1 Supported Payment Methods

- Online:
  - Credit/debit cards.
  - UPI.
  - Net banking.
  - Wallets.
- Offline / Semi-Online:
  - RTGS/NEFT.

### 7.2 Business Rules

- **Initial Payment**
  - Minimum percentage (e.g., 10%) of total amount required to secure a stall.
  - This percentage is configurable by admin.

- **Gateway Surcharge**
  - Additional 2.5% if payment is made via payment gateway (online).

- **RTGS/NEFT Flow**
  - When user picks RTGS/NEFT:
    - System generates a challan.
    - User gets 48 hours to:
      - Complete payment.
      - Upload payment receipt.
    - Admin reviews and marks transaction as completed.
  - If not marked completed within 48 hours:
    - Booking is automatically cancelled.
    - Admin receives an email and an action item:
      - Option to release stall (making it available again).
  - Additional requirement:
    - When challan is generated, it should also be emailed to admin.

### 7.3 Instalments & Refunds

- Multi-part payments handled as per defined schedule.
- For each payment:
  - Receipt and invoice are generated.
  - Status updated in exhibitor dashboard and admin financial dashboard.
- Refunds and cancellations are handled by the **Booking & Cancellation Management Module** (see section 15).

### 7.4 Possession Letter

- Once the exhibitor has made full payment:
  - System creates a manual approval task for admin.
  - Admin can approve and issue possession letter.
  - Possession letter becomes downloadable for exhibitor.

---

## 8. Document Management Module

### 8.1 Document Types

Typical document types:

- Company registration certificates.
- Identity proofs.
- Catalogues and marketing materials.
- Booth designs.
- Any other regulatory or compliance documents needed.

### 8.2 Upload Rules

- Configurable limits:
  - Maximum file size per upload.
  - Maximum number of uploads per document type.
- Exhibitor can:
  - Upload, replace, or delete (if not yet verified) documents.

### 8.3 Verification Workflow

- Documents have statuses:
  - Pending verification.
  - Approved.
  - Rejected (with reason).
- Admin/staff review uploaded documents and change status.
- History/version tracking where applicable.

### 8.4 Access & Audits

- Exhibitor and admin can download documents anytime (subject to role).
- System retains documents for audit and regulatory checks.

### 8.5 Exhibition Manual

- Admin uploads a PDF manual for each exhibition.
- Manual is accessible to all registered exhibitors of that exhibition.

---

## 9. Exhibitor Dashboard Module

### 9.1 Dashboard Overview

The exhibitor dashboard provides:

- Summary of:
  - Active bookings.
  - Upcoming payments and due dates.
  - Pending documents and tasks.
  - Notifications and messages.
- Quick links to:
  - Book new stalls.
  - View floorplans.
  - Manage documents.
  - Manage services and badges.

### 9.2 Bookings & Payments

- View list of all bookings with:
  - Exhibition name.
  - Booth numbers.
  - Status (Reserved/Confirmed/Cancelled).
  - Payment progress and due amounts.
- Payment actions:
  - Pay instalments.
  - View/download invoices and receipts.
  - View refund/wallet transactions.

### 9.3 Sponsorships

- Exhibitor can:
  - View sponsorship options and associated deliverables.
  - Select sponsorship type.
  - Complete booking and payment for sponsorships.

### 9.4 Contacts

- Exhibitor can:
  - Manage up to 5 email addresses.
  - Manage up to 5 contact numbers.

---

## 10. Communication & Notification Module

### 10.1 Email & SMS Notifications

- Trigger points:
  - Registration and verification.
  - Successful and failed logins (optional).
  - Booking confirmation, modification, and cancellation.
  - Payment receipts and reminders.
  - Document status changes.
  - Admin actions (refunds, credits, overrides).
- SMS:
  - Uses OTP registration with DLT (DLT setup is client responsibility with telecom provider).

### 10.2 In-App Messaging (Webmail / Chatbox)

- Exhibitor ↔ Admin messaging with:
  - Message threads.
  - Internal notes (optional).
- Notification count:
  - Shows number of unread messages.
  - Count persists until user replies.
- Folders:
  - Inbox
  - Pending
  - Completed
  - Archive
- Archiving:
  - When a conversation loop is closed, thread moves to Archive.
  - No deletion – messages are permanently retained.

---

## 11. Exhibitor Services Module

### 11.1 Service Types

- Booth utilities (electricity, internet, etc.).
- Furniture items (tables, chairs, shelves).
- Catering services.
- Promotional/marketing packages.
- Badge-related additional services.

### 11.2 Admin Management

- Admin can:
  - Define services with:
    - Name, description.
    - Pricing (flat or per unit).
    - Availability windows.
  - Upload images for services.
  - Enable or disable services per exhibition.

### 11.3 Exhibitor Interface

- Exhibitor can:
  - Browse available services per exhibition.
  - View service descriptions and images.
  - Add services during booking or later from dashboard.
  - Pay for services via online or configured offline methods.

---

## 12. Badge & Access Management Module

### 12.1 Badge Categories

- **Primary**
  - High-level/top management.
  - Example: If admin sets 2 primary badges, exhibitor receives 2 primary badges.

- **Secondary**
  - Mid-level staff.
  - Example: If admin sets 1 secondary badge, exhibitor receives 1 secondary badge.

- **Additional**
  - Additional staff members beyond predefined limits.
  - Only allowed access to enter the hall.

### 12.2 Limits & Pricing

- Default limits:
  - Top management: up to 2 badges.
  - Normal staff: up to 3 badges.
- Additional badges:
  - Allowed beyond the limits.
  - Cost defined by admin.
  - Payment:
    - Online (with 2.5% surcharge).
    - Offline (as configured).

### 12.3 Badge Assignment

- Exhibitor can:
  - Generate and download badges.
  - Assign each badge to a staff member (name, role, photo, etc.).
  - Configure whether badges are:
    - Valid for all days.
    - Or assigned to different people on different days.
- Admin can:
  - Override and add additional badges for selected exhibitors.

### 12.4 Photo Requirements

- For business owner/employer:
  - Photo upload is mandatory after booking.

### 12.5 QR Code & Access Control

- Each badge includes a unique QR code.
- Used for:
  - Entry scanning at gates.
  - Tracking check-in and check-out times.
- Integration with mobile app:
  - Optional mobile app can scan QR codes for:
    - Entry access.
    - Food stalls.
    - Other on-site services (TBD).

---

## 13. Admin Panel

### 13.1 Exhibition Management

- Admin can:
  - Create new exhibitions with:
    - Name, description, venue, and dates.
    - Timeline for bookings and payments.
  - Configure:
    - Floorplans and stall layouts.
    - Booth categories (Standard/Premium/Economy).
    - Booth types (Raw/Orphand).
    - Stall schemes and included items.
    - Sq. ft based pricing and side-open variations.
  - Manage:
    - Lists of blocked or reserved stalls.
    - Cut-off dates for add-on purchases.

### 13.2 Special Pricing & Discounts

- Admin can:
  - Apply special **percentage discounts** to specific exhibitors.
    - Only visible to that exhibitor when they log in.
  - Mark stalls as:
    - Discounted.
    - Free.
  - Assign special pricing for individual booths or exhibitors.

### 13.3 Role & Access Management

- Admin configures:
  - Roles (Admin, Accountant, Staff, etc.).
  - Access rights per module.
- System enforces:
  - Only authorized roles can access sensitive modules (e.g., payments).

### 13.4 Exhibitor Listing

- Admin can:
  - View all exhibitors.
  - Filter by exhibition, status, payment state, etc.
  - Access exhibitor details, documents, and bookings.

---

## 14. Financial Management Module

### 14.1 Financial Dashboard

- Shows:
  - Total payments received per exhibition.
  - Outstanding balances and overdues.
  - Refunds and wallet credits.
  - Revenue breakdown by:
    - Exhibition.
    - Category.
    - Date range, etc.

### 14.2 Wallet & Refund Management

- For cancellations or changes:
  - Admin can:
    - Refund full or partial amount.
    - Credit full or partial amount to exhibitor wallet.
  - Wallet rules:
    - Wallet balance can be used **only** for:
      - Booking new stalls.
    - Cannot be used for other services or sponsorships (unless later allowed explicitly).
  - Admin enters:
    - Refund/credit amount.
    - System:
      - Sends automatic email to exhibitor.
      - Updates wallet and booking records.

### 14.3 Challan Generation (RTGS/NEFT)

- For RTGS/NEFT transactions:
  - System generates a challan with:
    - Bank details.
    - Amount.
    - Due time window.
  - Challan is:
    - Downloadable by exhibitor.
    - Emailed to admin as well.

---

## 15. Reporting & Analytics Module

### 15.1 Standard Reports

- **Booking Reports**
  - Bookings per exhibition.
  - Stall-wise and category-wise bookings.
  - Cancellations and replacements.

- **Financial Reports**
  - Total revenue and by source (stalls, services, sponsorships).
  - Outstanding balances.
  - Refund analytics.

- **Space Utilization**
  - Occupied vs available stalls.
  - Utilization by day, hall, and category.

- **Service Usage**
  - Utilities, furniture, catering and promotions usage.

- **Exhibitor Demographics & Performance**
  - Distribution by industry, geography, stall size, etc. (TBD).

### 15.2 Exception Report

- Lists overrides made by admin, e.g.:
  1. Price override.
  2. Number of badge override.
  3. Due date override.
  4–10. Other override types (TBD, up to total of 10 types).
- Report is:
  - Filterable by exhibitor, exhibition, date range.
  - Exportable (e.g., CSV/PDF).

### 15.3 Post-Event Reports

- After event closure, system can generate:
  - Total bookings and revenue summary.
  - Space utilization stats.
  - Service usage data.
  - Exhibitor performance insights.

### 15.4 Admin Checklist

- Admin-configurable checklist of deliverables, e.g.:
  - Exhibitor gift.
  - Photo capture.
  - Certificate upload.
  - Food stall coupons.
  - Memento.
  - Water coupons.
- Features:
  - Admin can add/modify/delete items in checklist.
  - Checklist status visible on:
    - Exhibitor side.
    - Admin side.

---

## 16. Booking & Cancellation Management Module

### 16.1 Cancellation Flows

- Exhibitor can:
  - Request cancellation of booked stall(s).
  - Request replacement of stall(s) with cancellation charges as per policy.

- On cancellation request:
  - System applies cancellation charges based on business rules.
  - Remaining amount is:
    - Refunded (full/partial), or
    - Credited to wallet (full/partial).
  - Exhibitor sees message:
    - **"Request for cancellation has been seen. Plz contact contact no."**

### 16.2 Admin Actions on Cancellation

- Admin receives cancellation request in back office.
- Admin decides:
  - Refund amount.
  - Credit amount.
  - Whether to release the stall back to availability.
- System:
  - Records admin decision.
  - Sends appropriate email to exhibitor.
  - Updates booking, wallet, and stall availability.

---

## 17. Integrations & Mobile Responsiveness

### 17.1 External Integrations

- **Payment Gateways**
  - For online payments and transaction callbacks.

- **Cloud Storage**
  - For document and media storage (floorplans, images, PDFs).

- **Email/SMS Providers**
  - For transactional and notification messages.
  - DLT registration for SMS to be handled by client.

- **Analytics**
  - For usage tracking and performance metrics.

- **Mobile App (TBD)**
  - Shared data via REST APIs.
  - Features may include:
    - QR code scanning for badges and food stalls.
    - Entering staff details from mobile.
    - OTP login for on-site staff/exhibitors.

### 17.2 Mobile Responsiveness

- Web application must be fully responsive:
  - Usable on desktops, tablets, and smartphones.
  - Floorplans, dashboards, forms, and reports must be mobile-friendly.

---

## 18. High-Level Non-Functional Requirements (NFRs) – Draft

> These are indicative; they can be expanded during technical design.

- **Security**
  - Role-based access control (RBAC).
  - Secure session management.
  - Data encryption in transit (HTTPS).

- **Scalability**
  - Capable of handling multiple concurrent exhibitions and high traffic during peak booking periods.

- **Reliability**
  - Robust error handling and transaction integrity for bookings and payments.

- **Auditability**
  - Logging of key operations (bookings, cancellations, overrides, refunds).
  - Retention of communication and document histories.

- **Usability**
  - Intuitive flows for exhibitors and admins.
  - Clear status indicators (for bookings, payments, documents, badges).


