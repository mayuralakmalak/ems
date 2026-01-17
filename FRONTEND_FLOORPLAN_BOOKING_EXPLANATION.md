# Frontend Floorplan Booking Page - Complete Explanation

## Overview
The frontend floorplan booking page allows public users and authenticated exhibitors to view and interact with exhibition hall plans, select booths, and proceed to booking. The system has two views: **public view** (for non-authenticated users) and **authenticated view** (for logged-in exhibitors).

---

## Architecture & Flow

### 1. **Route & Controller Entry Point**

**Route:** `GET /exhibitions/{id}/floorplan`
- **Public Route:** `floorplan.show.public` (line 29 in routes/web.php)
- **Authenticated Route:** `floorplan.show` (line 309 in routes/web.php)

**Controller:** `App\Http\Controllers\Frontend\FloorplanController@show`

### 2. **Controller Logic Flow**

```php
FloorplanController::show($id)
    ↓
1. Load Exhibition with Floors & Booths
    - Loads active floors (is_active = true)
    - Orders floors by floor_number
    - Loads all booths for the exhibition
    ↓
2. Determine Selected Floor
    - Gets floor_id from query parameter (?floor_id=X)
    - If not provided, defaults to first active floor
    - Filters booths by selected floor
    ↓
3. Determine Booth Status
    - Reserved: Bookings with approval_status='pending'
    - Booked: Bookings with approval_status='approved' AND status='confirmed'
    - Available: Not in reserved or booked lists
    ↓
4. Check Authentication
    - If authenticated → Show 'frontend.floorplan.show' (exhibitor view)
    - If not authenticated → Show 'frontend.floorplan.public' (public view)
```

---

## Data Processing

### **Booth Status Determination**

The controller determines booth status through a multi-step process:

#### Step 1: Collect Reserved Booth IDs
```php
// Reserved = Pending bookings (waiting for admin approval)
$reservedBookings = Booking::where('exhibition_id', $id)
    ->where('approval_status', 'pending')
    ->whereNotIn('status', ['cancelled', 'rejected'])
    ->get();

// Extract booth IDs from:
// 1. Primary booth_id
// 2. selected_booth_ids array (supports both formats):
//    - Simple: [1, 2, 3]
//    - Object: [{'id': 1, 'name': 'B001'}, ...]
```

#### Step 2: Collect Booked Booth IDs
```php
// Booked = Approved bookings (confirmed)
$bookedBookings = Booking::where('exhibition_id', $id)
    ->where('approval_status', 'approved')
    ->where('status', 'confirmed')
    ->get();

// Same extraction logic as reserved booths
```

#### Step 3: Status Priority (in Blade Template)
```php
Priority Order:
1. Booked (highest priority)
2. Reserved
3. Merged (if is_merged = true AND is_available = true)
4. Available (default)
```

### **Booth Filtering by Floor**

```php
if ($selectedFloor) {
    $exhibition->booths = $exhibition->booths->filter(function($booth) use ($selectedFloorId) {
        return $booth->floor_id == $selectedFloorId;
    });
}
```

Only booths belonging to the selected floor are displayed.

---

## View Components

### **A. Public View** (`frontend.floorplan.public`)

**Layout:** `layouts.frontend`

**Structure:**
```
┌─────────────────────────────────────────────────────┐
│  Back to Exhibition Button                          │
├──────────────┬──────────────────────────────────────┤
│              │                                      │
│  LEFT PANEL  │      CENTER PANEL                   │
│  (Filters)   │      (Floorplan Canvas)             │
│              │                                      │
│  - Booth Size│  - Background Image                 │
│  - Price     │  - Booths (positioned absolutely)   │
│  - Status    │  - Color-coded by status            │
│              │                                      │
│  Legend:     │                                      │
│  - Available │                                      │
│  - Reserved  │                                      │
│  - Booked    │                                      │
│  - Merged    │                                      │
│              │                                      │
│  Login CTA   │                                      │
└──────────────┴──────────────────────────────────────┘
```

**Features:**
1. **Read-only view** - No booth selection
2. **Filters** - Size, Price Range, Status checkboxes
3. **Hover tooltips** - Shows booth name, size, price, status
4. **Login CTA** - Prompts users to login for booking

**Booth Rendering:**
```php
@foreach($exhibition->booths as $booth)
    // Skip merged original booths (hidden)
    if ($booth->parent_booth_id !== null && !$booth->is_split) {
        continue;
    }
    
    // Determine status and colors
    $status = 'available' | 'reserved' | 'booked' | 'merged'
    $bgColor = '#28a745' | '#ffc107' | '#dc3545' | '#20c997'
    
    // Render booth div with absolute positioning
    <div class="booth-item" 
         style="position: absolute;
                left: {{ $booth->position_x }}px;
                top: {{ $booth->position_y }}px;
                width: {{ $booth->width }}px;
                height: {{ $booth->height }}px;">
        {{ $booth->name }}
        {{ $booth->size_sqft }} sq meter
    </div>
@endforeach
```

**JavaScript Functionality:**
- Price range slider updates display value
- Hover tooltips show booth details
- Filter checkboxes (basic implementation, no actual filtering in public view)

---

### **B. Authenticated View** (`frontend.floorplan.show`)

**Layout:** `layouts.exhibitor`

**Structure:**
```
┌─────────────────────────────────────────────────────┐
│  LEFT PANEL          │  RIGHT PANEL                 │
│  (350px width)       │  (Flex: 1)                   │
├──────────────────────┼──────────────────────────────┤
│                      │  Filter Buttons:              │
│  Booking Summary     │  [All] [Available] [Booked]  │
│  - Booking items     │  [Reserved]                   │
│  - Status badges     │                               │
│  - Amounts           │  ┌────────────────────────┐  │
│                      │  │                        │  │
│  Payment & Invoices  │  │  Floorplan Canvas      │  │
│  - Payment items     │  │  - Background Image    │  │
│  - Payment numbers   │  │  - Booths (absolute)   │  │
│  - Amounts           │  │  - Color-coded         │  │
│                      │  │                        │  │
│  Selected Booth Info │  │                        │  │
│  (Hidden by default) │  │                        │  │
│  - Booth details     │  │                        │  │
│  - Proceed to Book   │  │                        │  │
│                      │  └────────────────────────┘  │
└──────────────────────┴──────────────────────────────┘
```

**Features:**
1. **Interactive booth selection** - Click to select/deselect
2. **Multi-select support** - Ctrl/Cmd + Click for multiple booths
3. **Filter buttons** - Show/hide booths by status
4. **Selected booth panel** - Shows details and total price
5. **Booking summary** - User's existing bookings
6. **Payment history** - User's payment records
7. **Proceed to Book button** - Redirects to booking interface

**Booth Selection Logic:**
```javascript
// Click handler
booth.addEventListener('click', function(e) {
    if (e.ctrlKey || e.metaKey) {
        // Multi-select mode
        toggleBoothSelection(this);
    } else {
        // Single-select mode (clear others first)
        clearSelection();
        toggleBoothSelection(this);
    }
});

// Toggle selection
function toggleBoothSelection(booth) {
    const boothId = booth.getAttribute('data-booth-id');
    const status = booth.getAttribute('data-booth-status');
    
    // Prevent selection of booked/reserved booths
    if (status === 'booked' || status === 'reserved') {
        alert('This booth is already ' + status);
        return;
    }
    
    // Toggle selection
    if (selectedBooths.includes(boothId)) {
        selectedBooths.splice(index, 1);
        booth.classList.remove('booth-selected');
    } else {
        selectedBooths.push(boothId);
        booth.classList.add('booth-selected');
    }
    
    updateSelectedBoothInfo();
    updateActionButtons();
}
```

**Filter Functionality:**
```javascript
// Filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        
        document.querySelectorAll('.booth-item').forEach(booth => {
            const status = booth.getAttribute('data-booth-status');
            
            if (filter === 'all' || status === filter) {
                booth.style.display = 'flex';
            } else {
                booth.style.display = 'none';
            }
        });
    });
});
```

**Proceed to Book:**
```javascript
document.getElementById('proceedToBookBtn').addEventListener('click', function() {
    if (selectedBooths.length > 0) {
        // Redirect with booth IDs as query parameter
        window.location.href = `/bookings/book/${exhibitionId}?booths=${selectedBooths.join(',')}`;
    }
});
```

---

## Booth Status Colors

| Status | Background Color | Border Color | Meaning |
|-------|-----------------|--------------|---------|
| **Available** | `#28a745` (Green) | `#1e7e34` | Available for booking |
| **Reserved** | `#ffc107` (Yellow/Orange) | `#d39e00` | Pending admin approval |
| **Booked** | `#dc3545` (Red) | `#b02a37` | Confirmed booking |
| **Merged** | `#20c997` (Teal) | `#17a2b8` | Merged booth (available) |
| **Selected** | `#17a2b8` (Blue) | `#007bff` | Currently selected by user |

---

## Floor Selection (Multi-Floor Support)

### **Controller Logic:**
```php
// Get selected floor from query parameter
$selectedFloorId = request()->query('floor_id');

// Default to first active floor if not specified
if (!$selectedFloor && $exhibition->floors->isNotEmpty()) {
    $selectedFloor = $exhibition->floors->first();
    $selectedFloorId = $selectedFloor->id;
}

// Filter booths by selected floor
if ($selectedFloor) {
    $exhibition->booths = $exhibition->booths->filter(function($booth) use ($selectedFloorId) {
        return $booth->floor_id == $selectedFloorId;
    });
}
```

### **Frontend Floor Selector:**
```javascript
// Floor selection handler (in booking interface)
function setupFloorSelection() {
    const floorSelect = document.getElementById('floorSelect');
    if (floorSelect) {
        floorSelect.addEventListener('change', function() {
            const selectedFloorId = this.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('floor_id', selectedFloorId);
            // Remove booth selections when changing floors
            currentUrl.searchParams.delete('booths');
            // Reload page with new floor
            window.location.href = currentUrl.toString();
        });
    }
}
```

**Note:** The public view doesn't have floor selection UI, but the authenticated booking interface (`book.blade.php`) does.

---

## Background Image Display

### **Image Source Priority:**
```php
// 1. Check floorplan_images array
$floorplanImages = is_array($exhibition->floorplan_images ?? null)
    ? $exhibition->floorplan_images
    : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);

// 2. Use first image
$primaryFloorplanImage = $floorplanImages[0] ?? null;
```

### **Rendering:**
```html
@if($primaryFloorplanImage)
    <img src="{{ asset('storage/' . $primaryFloorplanImage) }}" 
         id="floorplanImage" 
         style="position: absolute; 
                top: 0; 
                left: 0; 
                max-width: 100%; 
                height: auto; 
                z-index: 1;">
@endif
```

**Z-Index Layering:**
- Background Image: `z-index: 1`
- Booths Container: `z-index: 2`
- Individual Booths: `z-index: 10` (hover: `z-index: 20`)

---

## Booth Positioning

### **Absolute Positioning:**
```php
style="position: absolute;
       left: {{ $booth->position_x ?? ($loop->index % 5) * 120 }}px;
       top: {{ $booth->position_y ?? floor($loop->index / 5) * 100 }}px;
       width: {{ $booth->width ?? 100 }}px;
       height: {{ $booth->height ?? 80 }}px;"
```

**Fallback Logic:**
- If `position_x` is null → Calculate based on loop index: `(index % 5) * 120`
- If `position_y` is null → Calculate based on loop index: `floor(index / 5) * 100`
- Default size: 100px × 80px

**Note:** This fallback is only used if booths weren't properly positioned in the admin interface.

---

## Data Attributes

Each booth div includes these data attributes:

```html
data-booth-id="{{ $booth->id }}"
data-booth-name="{{ $booth->name }}"
data-booth-size="{{ $booth->size_sqft }}"
data-booth-price="{{ $booth->price }}"
data-booth-category="{{ $booth->category }}"
data-booth-type="{{ $booth->booth_type }}"
data-booth-sides="{{ $booth->sides_open }}"
data-booth-status="{{ $status }}"
```

These are used by JavaScript for:
- Selection tracking
- Filtering
- Displaying booth details
- Preventing selection of unavailable booths

---

## Merged Booths Handling

### **Hiding Original Booths:**
```php
// Skip merged original booths (they are hidden)
if ($booth->parent_booth_id !== null && !$booth->is_split) {
    continue; // Don't render this booth
}
```

**Logic:**
- If `parent_booth_id` is set AND `is_split` is false → Original booth from merge (hide it)
- If `parent_booth_id` is set AND `is_split` is true → Split child booth (show it)
- If `is_merged` is true AND `is_available` is true → Merged booth (show it with teal color)

---

## Integration with Booking Interface

### **Flow:**
```
Floorplan Page (public/show)
    ↓
User selects booths (authenticated only)
    ↓
Click "Proceed to Book"
    ↓
Redirect to: /bookings/book/{exhibitionId}?booths=1,2,3
    ↓
BookingController@book loads pre-selected booths
    ↓
User completes booking form
```

**Query Parameter Format:**
- Single booth: `?booths=1`
- Multiple booths: `?booths=1,2,3`

**Booking Interface Integration:**
```javascript
// Pre-select booths from query parameter
const urlParams = new URLSearchParams(window.location.search);
const boothIds = urlParams.get('booths');
if (boothIds) {
    const ids = boothIds.split(',');
    ids.forEach(id => {
        // Pre-select booths in booking interface
        ensureBoothSelection(id);
    });
}
```

---

## Key Differences: Public vs Authenticated View

| Feature | Public View | Authenticated View |
|---------|------------|-------------------|
| **Booth Selection** | ❌ No | ✅ Yes (click to select) |
| **Multi-select** | ❌ No | ✅ Yes (Ctrl/Cmd + Click) |
| **Filters** | ✅ Basic (UI only) | ✅ Functional (show/hide) |
| **Selected Booth Panel** | ❌ No | ✅ Yes |
| **Proceed to Book** | ❌ No | ✅ Yes |
| **Booking Summary** | ❌ No | ✅ Yes |
| **Payment History** | ❌ No | ✅ Yes |
| **Login CTA** | ✅ Yes | ❌ No |
| **Floor Selection** | ❌ No | ✅ Yes (in booking interface) |

---

## JavaScript Event Flow

### **Public View:**
1. **Page Load** → Render booths with absolute positioning
2. **Hover** → Show tooltip with booth details
3. **Price Slider** → Update display value

### **Authenticated View:**
1. **Page Load** → Render booths, load booking/payment data
2. **Booth Click** → Toggle selection, update panel
3. **Filter Click** → Show/hide booths by status
4. **Proceed to Book** → Redirect with booth IDs
5. **Floor Change** → Reload page with new floor_id

---

## Summary

The frontend floorplan booking page is a **read-only visualization** for public users and an **interactive selection interface** for authenticated exhibitors. It:

1. **Loads exhibition data** with floors and booths
2. **Determines booth status** from booking records
3. **Filters booths** by selected floor
4. **Renders booths** with absolute positioning over background image
5. **Color-codes booths** by availability status
6. **Enables selection** (authenticated users only)
7. **Integrates with booking flow** via query parameters

The system supports **multi-floor exhibitions**, **merged booths**, and **real-time status updates** based on booking records.
