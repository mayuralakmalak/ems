# Admin Floor Image Configuration with Grid System

## Overview
This document explains how the admin-side floor plan background image is configured and aligned with the grid system in the exhibition management system.

## Architecture Overview

### 1. **Coordinate System & Canvas Structure**

The floor plan uses a **three-layer coordinate system**:

```
┌─────────────────────────────────────────────────────────┐
│                    EXPANDED CANVAS                       │
│  (Includes 200px border padding on all sides)           │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │                                                  │   │
│  │         GRID AREA (Hall Area)                   │   │
│  │  (Positioned at offsetX=200, offsetY=200)       │   │
│  │  (Size: width_meters × 50px × height_meters)   │   │
│  │                                                  │   │
│  │  ┌──────────────────────────────────────────┐  │   │
│  │  │  Background Image (covers entire canvas) │  │   │
│  │  │  Grid Pattern (overlay on grid area only)│  │   │
│  │  │  Hall Outline (grid area boundary)       │  │   │
│  │  │  Booths (positioned within grid area)    │  │   │
│  │  └──────────────────────────────────────────┘  │   │
│  │                                                  │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 2. **Key Configuration Objects**

#### Canvas Configuration
```javascript
this.canvasConfig = {
    width: hallConfig.width + (BORDER_PADDING * 2),  // Expanded canvas
    height: hallConfig.height + (BORDER_PADDING * 2),
    offsetX: 200,  // Grid area starts here
    offsetY: 200   // Grid area starts here
}
```

#### Hall Configuration (Grid Area)
```javascript
this.hallConfig = {
    width: widthMeters * 50,   // Converted from meters
    height: heightMeters * 50, // Converted from meters
    margin: 0
}
```

#### Grid Configuration
```javascript
this.gridConfig = {
    size: 50,        // 50px per grid unit (1 meter = 50px)
    show: true,      // Show/hide grid
    snap: true       // Snap booths to grid
}
```

## Step-by-Step Configuration Process

### Step 1: Floor Selection & Dimension Calculation

When a floor is selected in the admin interface:

```javascript
// From step3.blade.php - loadFloorData()
if (floor.width_meters && floor.height_meters) {
    window.floorplanEditor.updateHallDimensionsFromFloor(
        parseFloat(floor.width_meters),
        parseFloat(floor.height_meters)
    );
}
```

**Conversion Formula:**
- **1 meter = 50 pixels** (grid size)
- Width in pixels = `width_meters × 50`
- Height in pixels = `height_meters × 50`

**Example:**
- Floor dimensions: 40m × 16m
- Calculated pixels: 2000px × 800px

### Step 2: Canvas Dimension Update

```javascript
updateCanvasDimensions() {
    // Expand canvas to include border padding
    this.canvasConfig.width = this.hallConfig.width + (this.BORDER_PADDING * 2);
    this.canvasConfig.height = this.hallConfig.height + (this.BORDER_PADDING * 2);
    
    // Grid area offset (centers grid within expanded canvas)
    this.canvasConfig.offsetX = this.BORDER_PADDING;  // 200px
    this.canvasConfig.offsetY = this.BORDER_PADDING;   // 200px
    
    // Update SVG viewBox and dimensions
    this.svg.setAttribute('viewBox', `0 0 ${canvasConfig.width} ${canvasConfig.height}`);
    this.svg.setAttribute('width', canvasConfig.width);
    this.svg.setAttribute('height', canvasConfig.height);
}
```

**Result:**
- For 40m × 16m floor: Canvas = 2400px × 1200px
- Grid area positioned at (200, 200) with size 2000px × 800px

### Step 3: Grid Pattern Creation

```javascript
updateGrid() {
    // Create SVG pattern for grid
    pattern.setAttribute('width', this.gridConfig.size);   // 50px
    pattern.setAttribute('height', this.gridConfig.size);   // 50px
    pattern.setAttribute('patternUnits', 'userSpaceOnUse');
    
    // Grid lines (L-shaped pattern)
    path.setAttribute('d', `M 50 0 L 0 0 0 50`);
    
    // Position grid overlay ONLY over the hall area
    this.gridBg.setAttribute('x', this.canvasConfig.offsetX);      // 200
    this.gridBg.setAttribute('y', this.canvasConfig.offsetY);      // 200
    this.gridBg.setAttribute('width', this.hallConfig.width);      // 2000
    this.gridBg.setAttribute('height', this.hallConfig.height);    // 800
}
```

**Grid Characteristics:**
- **Pattern Size:** 50px × 50px (repeating)
- **Coverage:** Only the hall/grid area (not the border)
- **Position:** Starts at (offsetX, offsetY) = (200, 200)

### Step 4: Background Image Loading

```javascript
loadBackgroundImage(backgroundImagePath) {
    // Create SVG image element
    bgImage = document.createElementNS('http://www.w3.org/2000/svg', 'image');
    bgImage.id = 'floorBackgroundImage';
    
    // Image settings
    bgImage.setAttribute('preserveAspectRatio', 'none');  // Stretch to fit
    bgImage.setAttribute('opacity', '0.7');                // Semi-transparent
    
    // Position: Cover ENTIRE expanded canvas (including border)
    bgImage.setAttribute('x', '0');
    bgImage.setAttribute('y', '0');
    bgImage.setAttribute('width', this.canvasConfig.width);   // Full canvas width
    bgImage.setAttribute('height', this.canvasConfig.height); // Full canvas height
    
    // Image URL
    const imageUrl = `/storage/${backgroundImagePath}`;
    bgImage.setAttribute('href', imageUrl);
}
```

**Key Points:**
1. **Full Canvas Coverage:** Image covers entire expanded canvas (2400×1200px)
2. **Stretch Mode:** `preserveAspectRatio='none'` stretches image to fit exactly
3. **Transparency:** 70% opacity allows grid to show through
4. **Layer Order:** Image is behind grid overlay but above SVG background

### Step 5: Hall Outline Drawing

```javascript
drawHall() {
    // Calculate grid-aligned dimensions
    const hallGridWidth = Math.floor(width / gridSize);
    const hallGridHeight = Math.floor(height / gridSize);
    const hallWidth = hallGridWidth * gridSize;   // Snap to grid
    const hallHeight = hallGridHeight * gridSize; // Snap to grid
    
    // Position at border offset
    const hallX = this.canvasConfig.offsetX;  // 200
    const hallY = this.canvasConfig.offsetY;  // 200
    
    // Draw hall rectangle
    hall.setAttribute('x', hallX);
    hall.setAttribute('y', hallY);
    hall.setAttribute('width', hallWidth);
    hall.setAttribute('height', hallHeight);
}
```

## Layer Stacking Order (Bottom to Top)

1. **SVG Background** (white/default)
2. **Background Image** (`#floorBackgroundImage`) - Covers entire canvas
3. **Grid Pattern** (`#gridBg`) - Only over hall area
4. **Grid Overlay** (`#gridBgOverlay`) - Semi-transparent overlay
5. **Hall Outline** - Boundary rectangle
6. **Booths** - Individual booth rectangles

## Coordinate Transformation

### Screen to SVG Coordinates

```javascript
getSVGPoint(e) {
    const rect = this.svg.getBoundingClientRect();
    const point = this.svg.createSVGPoint();
    point.x = e.clientX;
    point.y = e.clientY;
    
    // Transform using SVG matrix
    const svgPoint = point.matrixTransform(this.svg.getScreenCTM().inverse());
    return svgPoint;
}
```

This ensures accurate coordinate mapping even with zoom/pan transformations.

## Grid Alignment & Snapping

### Snap to Grid Function

```javascript
snapToGridValue(value) {
    return Math.round(value / this.gridConfig.size) * this.gridConfig.size;
}
```

**Example:**
- Input: 127px
- Calculation: `Math.round(127 / 50) * 50 = 2 * 50 = 100px`
- Result: Snapped to 100px (2 grid units)

### Booth Positioning

All booths are automatically snapped to grid:
- **X Position:** `snapToGridValue(x)`
- **Y Position:** `snapToGridValue(y)`
- **Width:** `snapToGridValue(width)`
- **Height:** `snapToGridValue(height)`

## Real-World Example

### Scenario: 40m × 16m Floor with Background Image

1. **Floor Data:**
   ```javascript
   {
       width_meters: 40,
       height_meters: 16,
       background_image: "floors/backgrounds/floor1.jpg"
   }
   ```

2. **Calculated Dimensions:**
   ```javascript
   hallConfig = {
       width: 40 * 50 = 2000px,
       height: 16 * 50 = 800px
   }
   
   canvasConfig = {
       width: 2000 + 400 = 2400px,
       height: 800 + 400 = 1200px,
       offsetX: 200,
       offsetY: 200
   }
   ```

3. **Grid Setup:**
   - Grid size: 50px × 50px
   - Grid units: 40 units wide × 16 units tall
   - Grid position: (200, 200) to (2200, 1000)

4. **Background Image:**
   - Position: (0, 0)
   - Size: 2400px × 1200px (full canvas)
   - Opacity: 70%
   - Stretch mode: Fits entire canvas

5. **Visual Result:**
   ```
   ┌─────────────────────────────────────┐ 2400px
   │  Border (200px)                     │
   │  ┌───────────────────────────────┐  │
   │  │ Border (200px)                 │  │
   │  │ ┌───────────────────────────┐ │  │
   │  │ │ Background Image          │ │  │ 800px
   │  │ │ + Grid Pattern (50×50)    │ │  │
   │  │ │ + Hall Outline             │ │  │
   │  │ │ + Booths (snapped to grid) │ │  │
   │  │ └───────────────────────────┘ │  │
   │  │        2000px                  │  │
   │  └───────────────────────────────┘  │
   │  Border (200px)                    │
   └─────────────────────────────────────┘
           1200px
   ```

## Key Design Decisions

### 1. **Why Border Padding?**
- Provides visual breathing room
- Allows booths to be positioned near edges
- Prevents content from touching canvas boundaries

### 2. **Why 50px Grid Size?**
- **1 meter = 50px** provides good visual scale
- Easy to calculate: `meters × 50 = pixels`
- Grid units are intuitive (40m = 40 grid units)

### 3. **Why Stretch Background Image?**
- Ensures image always fills canvas
- No empty spaces or letterboxing
- Grid overlay provides structure regardless of image aspect ratio

### 4. **Why Grid Only Over Hall Area?**
- Grid represents the usable space
- Border area is for positioning/overflow
- Cleaner visual separation

## Data Flow Summary

```
Floor Selection
    ↓
Load Floor Dimensions (meters)
    ↓
Calculate Pixel Dimensions (meters × 50)
    ↓
Update Canvas Dimensions (+ border padding)
    ↓
Update Grid Pattern (50×50px, positioned over hall area)
    ↓
Load Background Image (full canvas coverage)
    ↓
Draw Hall Outline (grid-aligned boundary)
    ↓
Load/Sync Booths (all snapped to grid)
```

## API Integration

### Save Configuration
```javascript
POST /admin/exhibitions/{id}/floorplan/config
{
    "hall": { width, height, margin },
    "grid": { size, show, snap },
    "booths": [...],
    "floor_id": 123
}
```

### Load Configuration
```javascript
GET /admin/exhibitions/{id}/floorplan/config/{floorId}
Returns: { hall, grid, booths, lastUpdated }
```

## Conclusion

The floor image configuration with grid system follows a **layered, coordinate-based approach**:

1. **Physical dimensions** (meters) → **Pixel dimensions** (meters × 50)
2. **Grid area** → **Expanded canvas** (with border padding)
3. **Background image** → **Full canvas coverage** (stretched)
4. **Grid pattern** → **Hall area only** (50×50px repeating)
5. **All elements** → **Grid-aligned** (snapped to 50px increments)

This ensures precise alignment, consistent scaling, and intuitive spatial relationships between the background image, grid, and booth placements.
