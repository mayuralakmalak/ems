# Exception Report Implementation

## Overview
The Exception Report feature allows admins to generate comprehensive reports of all overrides they have made for selected clients. The report can only be generated after the exhibition end date has passed.

## Features Implemented

### 1. Exception Report Page
- **Route**: `/admin/reports/exception`
- **Access**: Admin and Sub Admin roles
- **Features**:
  - Select from exhibitions that have ended
  - Select specific clients or all clients
  - View exception report preview
  - Generate PDF report
  - Print report

### 2. Database Structure
- **Table**: `admin_exceptions`
- **Fields**:
  - `user_id`: The client/user for whom the exception was made
  - `booking_id`: Associated booking (nullable)
  - `exhibition_id`: Associated exhibition
  - `exception_type`: Type of override (e.g., `price_override`, `badge_override`, `due_date_override`)
  - `description`: Human-readable description
  - `old_value`: Previous value (JSON)
  - `new_value`: New value (JSON)
  - `created_by`: Admin who made the override
  - `created_at`, `updated_at`: Timestamps

### 3. Model: AdminException
- Located at: `app/Models/AdminException.php`
- Relationships:
  - `user()`: Belongs to User (client)
  - `booking()`: Belongs to Booking
  - `exhibition()`: Belongs to Exhibition
  - `createdBy()`: Belongs to User (admin)

### 4. Helper Method for Logging Exceptions
The `AdminException` model includes a static helper method to easily log exceptions:

```php
AdminException::log(
    userId: $userId,
    bookingId: $bookingId, // nullable
    exhibitionId: $exhibitionId, // nullable
    exceptionType: 'price_override',
    description: 'Admin manually adjusted booking price',
    oldValue: ['total_amount' => 50000],
    newValue: ['total_amount' => 45000],
    createdBy: auth()->id() // optional, defaults to current user
);
```

## Usage Examples

### Example 1: Logging a Price Override
```php
use App\Models\AdminException;

// When admin manually changes booking price
AdminException::log(
    userId: $booking->user_id,
    bookingId: $booking->id,
    exhibitionId: $booking->exhibition_id,
    exceptionType: 'price_override',
    description: 'Admin reduced booking price by ₹5,000',
    oldValue: ['total_amount' => $booking->getOriginal('total_amount')],
    newValue: ['total_amount' => $request->total_amount]
);
```

### Example 2: Logging a Badge Override
```php
// When admin adds extra badges beyond the limit
AdminException::log(
    userId: $user->id,
    bookingId: $booking->id,
    exhibitionId: $exhibition->id,
    exceptionType: 'badge_override',
    description: 'Admin added 2 additional badges beyond standard limit',
    oldValue: ['badge_count' => 5],
    newValue: ['badge_count' => 7]
);
```

### Example 3: Logging a Due Date Override
```php
// When admin extends payment due date
AdminException::log(
    userId: $user->id,
    bookingId: $booking->id,
    exhibitionId: $exhibition->id,
    exceptionType: 'due_date_override',
    description: 'Admin extended payment due date by 7 days',
    oldValue: ['due_date' => $originalDueDate->format('Y-m-d')],
    newValue: ['due_date' => $newDueDate->format('Y-m-d')]
);
```

## Exception Types
The system supports various exception types. Common types include:
- `price_override`: Price adjustments
- `badge_override`: Badge count changes
- `due_date_override`: Payment due date extensions
- `discount_override`: Additional discounts applied
- `cancellation_override`: Cancellation policy exceptions
- `document_override`: Document requirement waivers
- `service_override`: Additional service approvals
- `payment_override`: Payment method/terms changes
- `booking_status_override`: Manual status changes
- `booth_override`: Booth assignment changes

## Accessing the Exception Report

1. Navigate to **Reports & Analytics** in the admin panel
2. Click the **"Exception Report"** button
3. Select an exhibition that has ended
4. Select specific clients (or leave unchecked for all clients)
5. Click **"Generate PDF Report"** or **"Print Report"**

## Validation Rules

- Exception reports can **only** be generated for exhibitions where `end_date < current_date`
- If an exhibition hasn't ended, the system will show a warning message
- The report filters exceptions by:
  - Exhibition ID (required)
  - Client IDs (optional - if none selected, shows all clients)

## Report Output

The exception report includes:
- Exhibition details (name, venue, dates)
- For each client:
  - Client name and company
  - All exceptions/overrides made
  - Date and time of each override
  - Exception type
  - Description
  - Old value vs New value
  - Associated booking number
  - Admin who made the override

## Integration Points

To fully utilize this feature, you should log exceptions in the following admin actions:

1. **BookingController** (`app/Http/Controllers/Admin/BookingController.php`):
   - When updating booking prices
   - When changing booking status
   - When applying discounts

2. **PaymentController** (`app/Http/Controllers/Admin/PaymentController.php`):
   - When adjusting payment amounts
   - When extending due dates

3. **BadgeController** (if exists):
   - When adding extra badges
   - When approving badge requests

4. **DocumentController** (`app/Http/Controllers/Admin/DocumentController.php`):
   - When waiving document requirements

## Future Enhancements

- Excel export functionality
- Email report delivery
- Scheduled report generation
- Exception type filtering
- Date range filtering
- Export to CSV

## Notes

- The exception logging is currently not automatically integrated into all admin override actions
- You need to manually add `AdminException::log()` calls wherever admins make overrides
- The report will only show exceptions that have been logged using the `AdminException::log()` method

