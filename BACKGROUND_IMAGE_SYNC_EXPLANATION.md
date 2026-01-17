# Background Image Sync: Admin Panel → Frontend Booking Page

## Current Situation Analysis

### **Admin Panel (How Background Image is Set)**

#### 1. **Storage Location**
- **Database Field:** `floors.background_image` (in `floors` table)
- **Storage Path:** `storage/app/public/floors/backgrounds/`
- **Upload Location:** Step 2 (Floor Management) - `resources/views/admin/exhibitions/step2.blade.php`

#### 2. **Upload Process (Step 2)**
```php
// In ExhibitionController@step3Store (line 327-339)
if (isset($floorData['background_image']) && $floorData['background_image']->isValid()) {
    // Store new background image
    $backgroundImagePath = $floorData['background_image']->store('floors/backgrounds', 'public');
    $updateData['background_image'] = $backgroundImagePath;
    
    // Save to database
    $floor->update($updateData);
}
```

**Result:** Image is saved to `floors/backgrounds/{filename}` and path stored in `floors.background_image`

#### 3. **Loading in Admin Panel (Step 3)**
```javascript
// In step3.blade.php (line 528)
window.floorplanEditor.loadBackgroundImage(floor.background_image || null);
```

**JavaScript Function:**
```javascript
// In admin-floorplan-step2.js (line 274-326)
loadBackgroundImage(backgroundImagePath) {
    if (backgroundImagePath) {
        const imageUrl = `/storage/${backgroundImagePath.replace(/^\/+/, '')}`;
        
        // Create/update SVG image element
        bgImage.setAttribute('href', imageUrl);
        bgImage.setAttribute('x', '0');
        bgImage.setAttribute('y', '0');
        bgImage.setAttribute('width', this.canvasConfig.width);
        bgImage.setAttribute('height', this.canvasConfig.height);
    }
}
```

**Key Points:**
- Admin loads from `floor.background_image` (from Floor model)
- Image URL: `/storage/floors/backgrounds/{filename}`
- Each floor has its own background image
- Image is loaded dynamically when floor is selected

---

### **Frontend Booking Page (Current Implementation)**

#### 1. **Current Source**
```php
// In public.blade.php and show.blade.php (lines 93-96, 381-384)
$floorplanImages = is_array($exhibition->floorplan_images ?? null)
    ? $exhibition->floorplan_images
    : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);
$primaryFloorplanImage = $floorplanImages[0] ?? null;
```

**Problem:**
- ❌ Uses `$exhibition->floorplan_images` (from **exhibition** table)
- ❌ Uses `$exhibition->floorplan_image` (from **exhibition** table)
- ❌ **NOT** using `$selectedFloor->background_image` (from **floor** table)

#### 2. **Controller Already Has the Data**
```php
// In FloorplanController@show (lines 24-36)
$selectedFloorId = request()->query('floor_id');
$selectedFloor = null;

if ($selectedFloorId) {
    $selectedFloor = $exhibition->floors->firstWhere('id', $selectedFloorId);
}

// Default to first active floor
if (!$selectedFloor && $exhibition->floors->isNotEmpty()) {
    $selectedFloor = $exhibition->floors->first();
    $selectedFloorId = $selectedFloor->id;
}

// Controller passes $selectedFloor to view
return view('frontend.floorplan.public', compact(..., 'selectedFloor', ...));
```

**Good News:**
- ✅ Controller already loads `$selectedFloor`
- ✅ Controller already passes `$selectedFloor` to view
- ✅ `$selectedFloor` has access to `background_image` field

---

## The Solution: How to Sync Them

### **Step-by-Step Explanation**

#### **Step 1: Change Image Source in Views**

**Current Code (WRONG):**
```php
// public.blade.php & show.blade.php
@php
    $floorplanImages = is_array($exhibition->floorplan_images ?? null)
        ? $exhibition->floorplan_images
        : (array) ($exhibition->floorplan_image ? [$exhibition->floorplan_image] : []);
    $primaryFloorplanImage = $floorplanImages[0] ?? null;
@endphp
```

**New Code (CORRECT):**
```php
// public.blade.php & show.blade.php
@php
    // Priority: Use floor's background_image (from admin panel)
    // Fallback: Use exhibition's floorplan_images (legacy support)
    $backgroundImage = null;
    
    if ($selectedFloor && $selectedFloor->background_image) {
        // Use the background image set in admin panel for this floor
        $backgroundImage = $selectedFloor->background_image;
    } elseif (is_array($exhibition->floorplan_images ?? null) && !empty($exhibition->floorplan_images)) {
        // Fallback to exhibition's floorplan_images (legacy)
        $backgroundImage = $exhibition->floorplan_images[0];
    } elseif ($exhibition->floorplan_image) {
        // Fallback to exhibition's floorplan_image (legacy)
        $backgroundImage = $exhibition->floorplan_image;
    }
@endphp
```

#### **Step 2: Update Image Rendering**

**Current Code:**
```html
@if($primaryFloorplanImage)
    <img src="{{ asset('storage/' . $primaryFloorplanImage) }}" 
         id="floorplanImage" 
         style="position: absolute; top: 0; left: 0; max-width: 100%; height: auto; z-index: 1;">
@endif
```

**New Code:**
```html
@if($backgroundImage)
    <img src="{{ asset('storage/' . ltrim($backgroundImage, '/')) }}" 
         id="floorplanImage" 
         style="position: absolute; top: 0; left: 0; max-width: 100%; height: 100%; object-fit: contain; z-index: 1;">
@endif
```

**Changes:**
- Use `$backgroundImage` instead of `$primaryFloorplanImage`
- Add `ltrim($backgroundImage, '/')` to handle paths with/without leading slash
- Use `object-fit: contain` to maintain aspect ratio (or `cover` to fill)

---

## Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    ADMIN PANEL (Step 2)                      │
│                                                               │
│  User uploads background image for Floor                     │
│         ↓                                                     │
│  Image saved to: storage/app/public/floors/backgrounds/      │
│         ↓                                                     │
│  Path stored in: floors.background_image                     │
│         ↓                                                     │
│  Example: "floors/backgrounds/floor1-bg.jpg"                 │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│              ADMIN PANEL (Step 3)                            │
│                                                               │
│  JavaScript loads: floor.background_image                   │
│         ↓                                                     │
│  URL: /storage/floors/backgrounds/floor1-bg.jpg              │
│         ↓                                                     │
│  Displayed in SVG canvas with grid overlay                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│           FRONTEND BOOKING PAGE (Current - WRONG)           │
│                                                               │
│  Tries to load: $exhibition->floorplan_images                │
│         ↓                                                     │
│  Problem: This is from exhibition table, NOT floor table   │
│         ↓                                                     │
│  Result: Shows wrong image (or no image)                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│           FRONTEND BOOKING PAGE (After Fix - CORRECT)        │
│                                                               │
│  Loads: $selectedFloor->background_image                     │
│         ↓                                                     │
│  URL: /storage/floors/backgrounds/floor1-bg.jpg              │
│         ↓                                                     │
│  Same image as admin panel! ✅                               │
└─────────────────────────────────────────────────────────────┘
```

---

## Data Flow Comparison

### **Admin Panel Flow:**
```
Step 2: Upload Image
    ↓
Store in: floors.background_image = "floors/backgrounds/floor1-bg.jpg"
    ↓
Step 3: Load Image
    ↓
JavaScript: loadBackgroundImage(floor.background_image)
    ↓
Display: /storage/floors/backgrounds/floor1-bg.jpg
```

### **Frontend Current Flow (WRONG):**
```
Controller: Loads $selectedFloor (has background_image)
    ↓
View: Ignores $selectedFloor->background_image
    ↓
View: Uses $exhibition->floorplan_images (different source!)
    ↓
Display: Wrong image or no image
```

### **Frontend Fixed Flow (CORRECT):**
```
Controller: Loads $selectedFloor (has background_image)
    ↓
View: Uses $selectedFloor->background_image
    ↓
Display: /storage/floors/backgrounds/floor1-bg.jpg
    ↓
Same image as admin panel! ✅
```

---

## Key Differences

| Aspect | Admin Panel | Frontend (Current) | Frontend (After Fix) |
|--------|-----------|-------------------|---------------------|
| **Source** | `floor.background_image` | `exhibition.floorplan_images` | `floor.background_image` |
| **Table** | `floors` table | `exhibitions` table | `floors` table |
| **Storage** | `floors/backgrounds/` | `floorplans/` | `floors/backgrounds/` |
| **Per Floor** | ✅ Yes (each floor has own) | ❌ No (exhibition-wide) | ✅ Yes (each floor has own) |
| **Matches Admin** | ✅ N/A | ❌ No | ✅ Yes |

---

## Implementation Details

### **Files to Modify:**

1. **`resources/views/frontend/floorplan/public.blade.php`**
   - Line 92-100: Change image source logic
   - Use `$selectedFloor->background_image` instead of `$exhibition->floorplan_images`

2. **`resources/views/frontend/floorplan/show.blade.php`**
   - Line 380-388: Change image source logic
   - Use `$selectedFloor->background_image` instead of `$exhibition->floorplan_images`

### **No Controller Changes Needed:**
- ✅ Controller already loads `$selectedFloor`
- ✅ Controller already passes `$selectedFloor` to view
- ✅ Just need to use it in the view!

### **Fallback Strategy:**
```php
// Priority order:
1. $selectedFloor->background_image (from admin panel) ← PRIMARY
2. $exhibition->floorplan_images[0] (legacy support)
3. $exhibition->floorplan_image (legacy support)
4. null (no image)
```

This ensures:
- ✅ New floors use admin-set background images
- ✅ Old exhibitions without floor background images still work
- ✅ Backward compatibility maintained

---

## Image Path Handling

### **Admin Panel:**
```javascript
// JavaScript (admin-floorplan-step2.js)
const imageUrl = `/storage/${backgroundImagePath.replace(/^\/+/, '')}`;
// Result: /storage/floors/backgrounds/floor1-bg.jpg
```

### **Frontend (After Fix):**
```php
// PHP Blade
$imagePath = ltrim($selectedFloor->background_image, '/');
$imageUrl = asset('storage/' . $imagePath);
// Result: /storage/floors/backgrounds/floor1-bg.jpg
```

**Why `ltrim()`?**
- Database might store: `"floors/backgrounds/floor1-bg.jpg"` (no leading slash)
- Database might store: `"/floors/backgrounds/floor1-bg.jpg"` (with leading slash)
- `ltrim()` ensures consistent path format

---

## Multi-Floor Support

### **Current Behavior:**
- Each floor can have its own background image
- Admin sets background image per floor in Step 2
- Frontend should show the background image for the **selected floor**

### **Implementation:**
```php
// When user selects a different floor:
// URL: /exhibitions/1/floorplan?floor_id=2
// Controller loads: $selectedFloor (floor_id = 2)
// View shows: $selectedFloor->background_image (for floor 2)
```

**Result:**
- ✅ Floor 1 → Shows Floor 1's background image
- ✅ Floor 2 → Shows Floor 2's background image
- ✅ Each floor can have different background images

---

## Summary

### **The Problem:**
- Admin panel stores background image in `floors.background_image`
- Frontend tries to load from `exhibitions.floorplan_images`
- **They're different sources!**

### **The Solution:**
1. Change frontend views to use `$selectedFloor->background_image`
2. Add fallback to `$exhibition->floorplan_images` for legacy support
3. Use same path format as admin panel: `/storage/floors/backgrounds/{filename}`

### **Result:**
- ✅ Frontend shows the same background image as admin panel
- ✅ Each floor can have its own background image
- ✅ Backward compatible with old exhibitions
- ✅ No controller changes needed (already has the data)

### **Files to Change:**
- `resources/views/frontend/floorplan/public.blade.php` (lines 92-100)
- `resources/views/frontend/floorplan/show.blade.php` (lines 380-388)

### **What NOT to Change:**
- ❌ Controller (already correct)
- ❌ Database structure (already correct)
- ❌ Admin panel (already correct)

The fix is **simple**: Just change the view to use the data that's already being passed to it!
