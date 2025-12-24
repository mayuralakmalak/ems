# Booth Replacement Feature - Implementation Complete ✅

## Implementation Summary

The booth replacement feature has been successfully implemented with all requirements met.

## Current State Analysis

### ✅ What Already Exists:
1. **Basic Replace Method**: There's a `replace()` method in `BookingController` (line 1115) that handles basic booth replacement
2. **Route**: Route exists at `/bookings/{id}/replace` (POST)
3. **Booking Details Page**: Shows booth information at `/bookings/{id}`

### ❌ What's Missing:
1. **"Replace Booth" Button**: No button on booking details page
2. **Replacement Floor Plan Page**: No dedicated page showing filtered floor plan
3. **Category & Configuration Filtering**: Current replace method doesn't validate same category and booth configuration
4. **Service/Item Preservation**: Additional services and items are not explicitly preserved
5. **Payment & Badge Mapping**: Payments and badges are not explicitly mapped to new booth

## Requirements Breakdown

### User Flow:
1. ✅ Exhibitor goes to "My Bookings" list (`/bookings`)
2. ✅ Selects booking details page (`/bookings/{id}`)
3. ❌ **MISSING**: "Replace Booth" button on booking details page
4. ❌ **MISSING**: New page showing floor plan with filtered booths (same category + same booth configuration)
5. ❌ **MISSING**: Updated replace logic to preserve services, items, payments, and badges

### Technical Requirements:

#### 1. Booth Matching Criteria:
- **Same Category**: `booth->category` must match (e.g., "Premium", "Standard", "Economy")
- **Same Booth Configuration**: Need to clarify what "booth configuration" means:
  - **Option A**: `booth_type` (Raw/Orphand) + `sides_open` (1/2/3/4)
  - **Option B**: `booth_type` + `sides_open` + `size_sqft` (exact match)
  - **Option C**: `exhibition_booth_size_id` (if this represents configuration)
  
  **QUESTION**: What exactly defines "same booth configuration"? 
  - Same `booth_type` (Raw/Orphand)?
  - Same `sides_open` (1/2/3/4)?
  - Same `size_sqft`?
  - Or combination of these?

#### 2. Data to Preserve:
- ✅ **Additional Services**: `booking_services` table (already linked by `booking_id`, so automatically preserved)
- ✅ **Additional Items**: `included_item_extras` (JSON field in bookings table, automatically preserved)
- ✅ **Payments**: `payments` table (already linked by `booking_id`, automatically preserved)
- ✅ **Badges**: `badges` table (already linked by `booking_id`, automatically preserved)

**NOTE**: Since services, payments, and badges are linked by `booking_id` (not `booth_id`), they will automatically be preserved when we update the booking's `booth_id`. However, we should verify this is the intended behavior.

#### 3. Price Handling:
- Current implementation calculates price difference
- **QUESTION**: Should we:
  - **Option A**: Adjust `total_amount` by price difference (current approach)
  - **Option B**: Keep `total_amount` same, only change `booth_id`
  - **Option C**: Recalculate total including services/items with new booth price

## Implementation Plan

### Step 1: Add "Replace Booth" Button
- **File**: `resources/views/frontend/bookings/show.blade.php`
- **Location**: In the "Booth Details" section or action buttons area
- **Condition**: Show only if booking is confirmed/approved (similar to additional services section)

### Step 2: Create Replacement Floor Plan Page
- **Route**: `GET /bookings/{id}/replace-booth`
- **Controller Method**: `showReplaceBooth($id)`
- **View**: `resources/views/frontend/bookings/replace-booth.blade.php`
- **Logic**: 
  - Load booking with current booth
  - Filter booths by:
    - Same `exhibition_id`
    - Same `category`
    - Same booth configuration (as defined)
    - `is_available = true`
    - `is_booked = false`
    - Exclude current booth
  - Show floor plan similar to booking page but filtered

### Step 3: Update Replace Method
- **File**: `app/Http/Controllers/Frontend/BookingController.php`
- **Method**: `replace()`
- **Updates**:
  - Validate new booth has same category and configuration
  - Preserve all services, items, payments, badges (verify they're linked by booking_id)
  - Update booth_id only
  - Handle price difference if needed
  - Update old booth availability
  - Update new booth availability

### Step 4: Create Replacement Confirmation Page/View
- Show summary of what will be replaced
- List preserved services, items, payments, badges
- Confirm replacement

## Questions for Confirmation:

1. **Booth Configuration Definition**: What exactly defines "same booth configuration"?
   - Same `booth_type` (Raw/Orphand)?
   - Same `sides_open` (1/2/3/4)?
   - Same `size_sqft`?
   - Or all of the above?

2. **Price Handling**: When replacing booth:
   - Should we adjust `total_amount` by price difference?
   - Or keep `total_amount` same?
   - Should we recalculate including services?

3. **Booking Status**: Should booth replacement be allowed for:
   - Only confirmed bookings?
   - Only approved bookings?
   - Any status except cancelled?

4. **Old Booth Availability**: When replacing:
   - Should old booth become immediately available?
   - Or should it require admin approval?

5. **Additional Services/Items**: Since they're linked by `booking_id`:
   - They will automatically stay with the booking (correct behavior?)
   - Or should we verify/display this to user?

6. **Floor Plan Display**: Should the replacement floor plan:
   - Show only available booths matching criteria?
   - Highlight current booth differently?
   - Show booked booths as unavailable?

## Files to Modify/Create:

### Modify:
1. `app/Http/Controllers/Frontend/BookingController.php`
   - Add `showReplaceBooth($id)` method
   - Update `replace($id)` method

2. `resources/views/frontend/bookings/show.blade.php`
   - Add "Replace Booth" button

### Create:
3. `resources/views/frontend/bookings/replace-booth.blade.php`
   - Floor plan page for booth replacement

### Routes:
4. `routes/web.php`
   - Add `GET /bookings/{id}/replace-booth` route (if not exists)

## Implementation Complete ✅

### Confirmed Requirements:
1. ✅ **Booth Configuration**: Same `size_sqft` (not booth_type or sides_open)
2. ✅ **Category**: Same `category` (Premium/Standard/Economy)
3. ✅ **Price**: Keep `total_amount` the same (don't adjust for price difference)
4. ✅ **Booking Status**: Any status except cancelled
5. ✅ **Old Booth**: Immediately available
6. ✅ **Data Preservation**: Services, payments, badges automatically preserved (linked by booking_id)

### Files Created/Modified:

#### Created:
1. `resources/views/frontend/bookings/replace-booth.blade.php` - Replacement floor plan page

#### Modified:
1. `app/Http/Controllers/Frontend/BookingController.php`
   - Added `showReplaceBooth($id)` method
   - Updated `replace($id)` method with proper validation
2. `resources/views/frontend/bookings/show.blade.php`
   - Added "Replace Booth" button in booth details section
3. `routes/web.php`
   - Added `GET /bookings/{id}/replace-booth` route

### Features Implemented:

1. ✅ **Replace Booth Button**: Added to booking details page (only shows for non-cancelled bookings)
2. ✅ **Replacement Floor Plan Page**: Shows filtered booths matching:
   - Same category
   - Same size_sqft
   - Available and not booked
   - Excludes current booth
3. ✅ **Validation**: Ensures new booth matches category and size_sqft
4. ✅ **Data Preservation**: All services, items, payments, and badges automatically preserved
5. ✅ **Booth Availability**: Old booth becomes immediately available, new booth marked as booked
6. ✅ **User Experience**: Clear information banner, current booth highlighting, selected booth confirmation

### Testing Checklist:

- [ ] Test replace booth button appears on booking details page
- [ ] Test replacement page shows only matching booths
- [ ] Test booth selection and replacement submission
- [ ] Test validation (category and size_sqft matching)
- [ ] Test old booth becomes available
- [ ] Test new booth becomes booked
- [ ] Test services, items, payments, badges are preserved
- [ ] Test total_amount remains unchanged
- [ ] Test cancelled bookings cannot replace booth
- [ ] Test floor selection (if applicable)

