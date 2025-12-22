# Sponsorship Feature - Comprehensive Implementation Plan

## Overview
This document outlines the complete implementation plan for the sponsorship feature, allowing users to view sponsorship options, see deliverables for each type, and make bookings with payments.

## Architecture Overview

### Database Structure

#### 1. `sponsorships` Table (Enhanced)
- **Purpose**: Stores sponsorship packages available for each exhibition
- **Key Fields**:
  - `id`, `exhibition_id`, `name`, `description`
  - `deliverables` (JSON) - Array of deliverables included
  - `price` (decimal) - Sponsorship cost
  - `image` (string) - Optional image for the package
  - `tier` (string) - Bronze, Silver, Gold, Platinum, etc.
  - `is_active` (boolean) - Whether package is available
  - `max_available` (integer, nullable) - Limit on number of sponsorships
  - `current_count` (integer) - Current bookings count
  - `display_order` (integer) - For sorting

#### 2. `sponsorship_bookings` Table (Enhanced)
- **Purpose**: Tracks user bookings for sponsorships
- **Key Fields**:
  - `id`, `sponsorship_id`, `user_id`, `exhibition_id`
  - `booking_number` (string, unique) - Unique booking identifier
  - `amount` (decimal) - Total amount
  - `paid_amount` (decimal) - Amount paid so far
  - `status` (enum) - pending, confirmed, paid, cancelled
  - `payment_status` (enum) - pending, partial, paid, refunded
  - `booking_id` (nullable) - Link to booth booking if exists
  - `contact_emails` (JSON) - Contact emails
  - `contact_numbers` (JSON) - Contact numbers
  - `logo` (string) - Sponsor logo
  - `approved_by` (nullable) - Admin who approved
  - `approved_at` (nullable) - Approval timestamp

#### 3. `sponsorship_payments` Table (New)
- **Purpose**: Track payments for sponsorship bookings
- **Key Fields**:
  - `id`, `sponsorship_booking_id`, `user_id`
  - `payment_number` (string, unique)
  - `amount` (decimal)
  - `payment_method` (enum) - online, offline, wallet
  - `status` (enum) - pending, completed, failed, refunded
  - `transaction_id` (string, nullable)
  - `receipt_file` (string, nullable)
  - `paid_at` (datetime, nullable)
  - `notes` (text, nullable)

## Feature Components

### 1. Frontend - User Viewing & Booking

#### A. Sponsorship Listing Page (`/sponsorships`)
- **Features**:
  - Display all active sponsorships for an exhibition
  - Group by tier (Bronze, Silver, Gold, etc.)
  - Show price, deliverables, and benefits
  - "View Details" button for each package
  - "Book Now" button

#### B. Sponsorship Details Page (`/sponsorships/{id}`)
- **Features**:
  - Full description of sponsorship
  - Complete list of deliverables with icons
  - Pricing information
  - Terms and conditions
  - "Book This Package" button

#### C. Sponsorship Booking Page (`/sponsorships/{id}/book`)
- **Features**:
  - Form to collect:
    - Contact information (emails, phone numbers)
    - Logo upload (optional)
    - Additional requirements/notes
  - Summary of sponsorship details
  - Total amount display
  - "Proceed to Payment" button

#### D. Sponsorship Payment Page (`/sponsorships/{bookingId}/payment`)
- **Features**:
  - Payment method selection (Online, Offline, Wallet)
  - Payment amount display
  - Integration with payment gateway
  - Payment proof upload (for offline)
  - "Complete Payment" button

#### E. Sponsorship Booking Confirmation (`/sponsorships/bookings/{id}`)
- **Features**:
  - Booking summary
  - Payment status
  - Download receipt/invoice
  - View deliverables checklist

### 2. Admin - Sponsorship Management

#### A. Sponsorship CRUD (`/admin/sponsorships`)
- **Features**:
  - List all sponsorships (with filters by exhibition)
  - Create new sponsorship package
  - Edit existing packages
  - Activate/Deactivate packages
  - Delete packages (soft delete recommended)

#### B. Sponsorship Booking Management (`/admin/sponsorship-bookings`)
- **Features**:
  - View all sponsorship bookings
  - Filter by status, exhibition, date
  - Approve/Reject bookings
  - View booking details
  - Manage payments
  - Send communications

#### C. Sponsorship Analytics (`/admin/sponsorships/analytics`)
- **Features**:
  - Revenue from sponsorships
  - Popular tiers
  - Booking trends
  - Conversion rates

### 3. Payment Integration

#### Payment Flow:
1. User selects sponsorship → Creates `sponsorship_booking` (status: pending)
2. User proceeds to payment → Creates `sponsorship_payment` (status: pending)
3. Payment processed → Updates payment status
4. On successful payment → Updates `sponsorship_booking` (status: confirmed, payment_status: paid)
5. Send confirmation emails

#### Payment Methods:
- **Online**: Payment gateway integration (Razorpay, Stripe, etc.)
- **Offline**: Manual payment with proof upload
- **Wallet**: Use user's wallet balance

### 4. Email Notifications

#### Emails to Send:
1. **Sponsorship Booking Confirmation** (to user)
   - Booking details
   - Payment instructions
   - Next steps

2. **Payment Receipt** (to user)
   - Payment confirmation
   - Receipt download link

3. **New Sponsorship Booking Notification** (to admin)
   - New booking alert
   - Booking details
   - Action required

4. **Sponsorship Approval** (to user)
   - Approval confirmation
   - Deliverables timeline

## Implementation Steps

### Phase 1: Database & Models
1. ✅ Create/Update migrations for enhanced tables
2. ✅ Update Sponsorship model with relationships
3. ✅ Update SponsorshipBooking model with relationships
4. ✅ Create SponsorshipPayment model

### Phase 2: Admin Management
1. ✅ Create Admin SponsorshipController (CRUD)
2. ✅ Create admin views for sponsorship management
3. ✅ Create admin views for booking management
4. ✅ Add routes for admin operations

### Phase 3: Frontend Booking Flow
1. ✅ Enhance frontend SponsorshipController
2. ✅ Create booking flow views
3. ✅ Create payment integration
4. ✅ Add routes for frontend operations

### Phase 4: Payment Integration
1. ✅ Create SponsorshipPaymentController
2. ✅ Integrate with existing payment gateway
3. ✅ Handle payment callbacks
4. ✅ Create payment views

### Phase 5: Email & Notifications
1. ✅ Create sponsorship booking email templates
2. ✅ Create payment receipt email templates
3. ✅ Integrate email sending in controllers

### Phase 6: Testing & Refinement
1. ✅ Test complete booking flow
2. ✅ Test payment processing
3. ✅ Test admin management
4. ✅ UI/UX refinements

## Key Relationships

```
Exhibition
  ├── hasMany Sponsorship
  └── hasMany SponsorshipBooking

Sponsorship
  ├── belongsTo Exhibition
  └── hasMany SponsorshipBooking

SponsorshipBooking
  ├── belongsTo Sponsorship
  ├── belongsTo Exhibition
  ├── belongsTo User
  ├── belongsTo Booking (optional - if linked to booth booking)
  └── hasMany SponsorshipPayment

SponsorshipPayment
  ├── belongsTo SponsorshipBooking
  └── belongsTo User
```

## Security Considerations

1. **Authorization**: Only authenticated users can book sponsorships
2. **Validation**: Validate all input data
3. **Payment Security**: Use secure payment gateways
4. **File Uploads**: Validate and secure logo uploads
5. **Admin Access**: Restrict admin routes to admin role only

## UI/UX Considerations

1. **Clear Pricing**: Display prices prominently
2. **Deliverables Visibility**: Show all deliverables clearly
3. **Progress Indicators**: Show booking progress
4. **Responsive Design**: Mobile-friendly interface
5. **Loading States**: Show loading during payment processing
6. **Error Handling**: Clear error messages

## Future Enhancements

1. **Discount Codes**: Apply discounts to sponsorships
2. **Payment Plans**: Installment payments for high-value sponsorships
3. **Custom Packages**: Allow admins to create custom sponsorship packages
4. **Analytics Dashboard**: Detailed analytics for sponsorships
5. **Automated Deliverables**: Track and notify about deliverables fulfillment

