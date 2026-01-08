# Step 3 Floor Plan Design & Functionality Analysis

## Overview
Step 3 of the exhibition creation process (`/admin/exhibitions/{id}/step3`) is a comprehensive floor plan management interface that allows administrators to:
1. Manage floor-specific floor plans
2. Create and edit booths visually on a canvas
3. Configure payment schedules
4. Set cut-off dates

---

## Architecture & Components

### 1. **Backend Controller** (`ExhibitionController::step3`)
- **Location**: `app/Http/Controllers/Admin/ExhibitionController.php`
- **Purpose**: Loads exhibition data, active discounts, exhibitors, and floor information
- **Key Data Loaded**:
  - Exhibition with payment schedules, booth sizes, and floors
  - Active discounts (for booth pricing)
  - Exhibitor users (for user-specific pricing)

### 2. **Frontend View** (`step3.blade.php`)
- **Location**: `resources/views/admin/exhibitions/step3.blade.php`
- **Structure**:
  - Floor selection dropdown
  - Floorplan image upload section
  - Interactive SVG canvas editor
  - Payment schedule configuration
  - Cut-off dates section

### 3. **JavaScript Manager** (`AdminFloorplanManager`)
- **Location**: `public/js/admin-floorplan-step2.js`
- **Class**: `AdminFloorplanManager`
- **Purpose**: Handles all interactive floor plan editing functionality

### 4. **CSS Styling** (`admin-floorplan-step2.css`)
- **Location**: `public/css/admin-floorplan-step2.css`
- **Purpose**: Styles the floor plan editor interface

---

## Floor Plan Editor Design

### Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│  Floor Selection Dropdown                                  │
│  [Select Floor for Floor Plan Management]                  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Floorplan Image Upload Section                            │
│  [Upload Background Images] [Preview]                      │
└─────────────────────────────────────────────────────────────┘

┌──────────────┬──────────────────────────────────────────────┐
│              │  Canvas Header                                │
│  Sidebar     │  [Mode] [Booths Count] [Selected Count]      │
│  (350px)     │  [Zoom Controls]                              │
│              ├──────────────────────────────────────────────┤
│  Tools       │                                               │
│  Properties  │         SVG Canvas                            │
│  Settings    │         (Interactive Floor Plan)             │
│  Booth List  │                                               │
│              │                                               │
└──────────────┴──────────────────────────────────────────────┘
```

### Sidebar Components

#### 1. **Tools Section**
- **Select Tool** (🔍): Default mode for selecting and moving booths
- **Edit Hall Tool** (🏢): Edit hall dimensions and grid settings
- **Add Booth Tool** (📦): Draw new booths on canvas
- **Delete Tool** (🗑️): Delete booths by clicking

#### 2. **Hall Properties Panel** (shown when Hall tool is active)
- Hall Width (grid units)
- Hall Height (grid units)
- Grid Size (px)
- Update Hall button

#### 3. **Booth Properties Panel** (shown when booth is selected)
- **Booth ID**: Unique identifier (e.g., B001)
- **Dimensions**: Width, Height (px)
- **Position**: X, Y coordinates
- **Status**: Available, Reserved, Booked
- **Size Category**: Small, Medium, Large
- **Area**: Square feet
- **Booth Size (sq ft)**: Dropdown from exhibition booth sizes
- **Discount**: Optional discount selection
- **Special Price For User**: User-specific pricing
- **Actions**: Save Booth, Delete Booth

#### 4. **Quick Actions**
- Snap to Grid: Align selected booths to grid
- Align Selected: Align multiple booths horizontally
- Duplicate Booth: Copy selected booth
- Merge Selected (2/2): Merge exactly 2 booths
- Generate Grid: Bulk create booths in grid pattern
- Reset Floorplan: Clear all booths

#### 5. **Grid Settings**
- Show Grid: Toggle grid visibility
- Grid Size: Adjustable (10-100px)
- Enable Snap: Toggle snap-to-grid

#### 6. **Booths List**
- Scrollable list of all booths
- Shows booth ID and status
- Click to select booth
- Highlights selected booths

### Canvas Area

#### SVG Canvas
- **ViewBox**: Default `0 0 1200 800`
- **Grid Pattern**: Configurable grid overlay
- **Hall Outline**: Rectangular hall boundary
- **Booths**: Draggable rectangular elements
- **Selection Box**: For multi-select

#### Canvas Controls
- **Zoom In/Out**: Scale view
- **Reset View**: Return to default zoom
- **Fit to Screen**: Auto-fit canvas

---

## Key Functionality

### 1. **Floor Management**
- **Multi-Floor Support**: Each exhibition can have multiple floors
- **Floor-Specific Configs**: Each floor has its own floor plan configuration
- **Floor Selection**: Dropdown to switch between floors
- **Floor Images**: Upload background images per floor
- **Config Storage**: JSON files stored at `floorplans/exhibition_{id}/floor_{floor_id}.json`

### 2. **Booth Creation & Editing**

#### Creating Booths
- **Draw Mode**: Click and drag on canvas to create booth
- **Grid Snapping**: Booths automatically snap to grid
- **Minimum Size**: Enforced (1 grid unit minimum)
- **Hall Bounds**: Booths must be within hall boundaries
- **Auto-ID Generation**: Generates IDs like B001, B002, etc.

#### Editing Booths
- **Select**: Click booth to select
- **Multi-Select**: Ctrl/Cmd + Click for multiple selection
- **Drag**: Click and drag to move (maintains relative positions in multi-select)
- **Properties Panel**: Edit all booth attributes
- **Real-time Updates**: Changes reflect immediately on canvas

#### Booth Properties
- **Position**: X, Y coordinates (snapped to grid)
- **Size**: Width, Height in pixels
- **Status**: Visual color coding
  - Green: Available
  - Orange: Reserved
  - Red: Booked
- **Size Category**: Auto-calculated from dimensions
- **Booth Size**: Links to exhibition booth size configuration
- **Discounts**: Apply active discounts
- **User-Specific Pricing**: Set special prices for specific exhibitors

### 3. **Grid System**
- **Configurable Grid**: Adjustable size (10-100px)
- **Snap to Grid**: Optional snapping for precise alignment
- **Grid Units**: Hall dimensions calculated in grid units
- **Visual Grid**: Toggleable grid pattern overlay

### 4. **Booth Operations**

#### Selection
- **Single Select**: Click booth
- **Multi-Select**: Ctrl/Cmd + Click
- **Box Select**: Drag selection box
- **Visual Feedback**: Selected booths highlighted in blue

#### Movement
- **Drag**: Click and drag to move
- **Multi-Drag**: Move multiple booths maintaining relative positions
- **Grid Snap**: Automatic snapping during drag
- **Boundary Constraints**: Cannot move outside hall

#### Advanced Operations
- **Merge**: Combine 2 adjacent booths into one
- **Split**: Split merged booth back (if supported)
- **Duplicate**: Copy booth with offset
- **Align**: Align multiple booths to same X position
- **Snap to Grid**: Force alignment of selected booths

### 5. **Bulk Operations**

#### Generate Grid
- **Modal Dialog**: Configure grid generation
- **Parameters**:
  - Rows & Columns
  - Booth Width/Height (grid units)
  - Spacing (grid units)
  - Start Position (X, Y)
  - Booth ID Prefix
  - Size Category (optional)
- **Auto-Generation**: Creates booths in grid pattern

### 6. **Data Persistence**

#### Auto-Save
- **Triggered On**:
  - Adding booth
  - Updating booth
  - Deleting booth
  - Dragging booth
- **Debounced**: Prevents excessive API calls
- **Endpoint**: `POST /admin/exhibitions/{id}/floorplan/config`

#### Configuration Structure
```json
{
  "hall": {
    "width": 1200,
    "height": 800,
    "margin": 0
  },
  "grid": {
    "size": 50,
    "show": true,
    "snap": true
  },
  "booths": [
    {
      "id": "B001",
      "x": 100,
      "y": 100,
      "width": 100,
      "height": 80,
      "status": "available",
      "size": "medium",
      "area": 100,
      "sizeId": 1,
      "discountId": null,
      "specialPriceUserId": null
    }
  ],
  "lastUpdated": "2026-01-08 12:00:00"
}
```

#### Database Sync
- **Booths Table**: Booths are synced to database
- **Floor Association**: Booths linked to specific floor
- **Exhibition Association**: Booths linked to exhibition

### 7. **Payment Schedule**
- **Fixed Parts**: 3 payment parts (cannot be changed after creation)
- **Configuration**: Percentage and due date for each part
- **Validation**: Percentages must sum to 100%

### 8. **Cut-off Dates**
- **Add-on Services Cut-off**: Date for add-on service orders
- **Document Upload Deadline**: Deadline for document submissions

---

## Technical Implementation Details

### SVG Coordinate System
- **Screen to SVG**: Uses `getScreenCTM().inverse()` for accurate coordinate transformation
- **ViewBox**: Maintains aspect ratio and scaling
- **Grid Alignment**: All coordinates snapped to grid units

### Event Handling
- **Mouse Events**: mousedown, mousemove, mouseup, click
- **Tool Switching**: Changes cursor and behavior
- **Multi-Select**: Ctrl/Cmd key detection
- **Drag Detection**: Distinguishes between click and drag

### Booth Rendering
- **SVG Elements**: Uses `<g>` groups with `<rect>` and `<text>`
- **Status Colors**: CSS classes for visual status indication
- **Selection Highlight**: Blue border and shadow effect
- **Labels**: Booth ID displayed in center

### Performance Optimizations
- **Debounced Auto-Save**: Prevents excessive API calls
- **Efficient Rendering**: Only updates changed elements
- **Event Delegation**: Efficient event handling

---

## User Workflow

### Typical Workflow
1. **Select Floor**: Choose floor from dropdown
2. **Upload Images** (Optional): Add background floor plan images
3. **Configure Hall**: Set hall dimensions and grid size
4. **Create Booths**:
   - Use "Add Booth" tool to draw booths
   - Or use "Generate Grid" for bulk creation
5. **Edit Booths**: Select and edit properties
6. **Arrange Booths**: Drag to position, align, merge as needed
7. **Configure Payment**: Set payment schedule
8. **Set Cut-offs**: Configure deadline dates
9. **Save**: Form submission saves all data

### Advanced Workflow
1. **Load Existing**: Auto-loads saved configuration
2. **Edit Multiple**: Multi-select and align/merge booths
3. **Bulk Operations**: Generate grid of booths
4. **Fine-Tuning**: Snap to grid, adjust positions
5. **Apply Discounts**: Set discounts and special pricing
6. **Save & Continue**: Proceed to Step 4

---

## Design Patterns

### 1. **State Management**
- **Class-Based**: `AdminFloorplanManager` manages all state
- **Reactive Updates**: UI updates based on state changes
- **Persistent State**: Auto-saves to server

### 2. **Tool Pattern**
- **Mode Switching**: Different tools change behavior
- **Visual Feedback**: Cursor changes per tool
- **Contextual Panels**: Properties shown based on tool/selection

### 3. **Grid-Based Layout**
- **Snap to Grid**: All elements align to grid
- **Grid Units**: Dimensions calculated in grid units
- **Flexible Grid**: Adjustable grid size

### 4. **Multi-Select Pattern**
- **Primary Selection**: One booth is primary (for properties)
- **Multi-Selection**: Multiple booths can be selected
- **Relative Positioning**: Maintains relative positions during drag

---

## CSS Styling Highlights

### Color Scheme
- **Available Booths**: Green (#4caf50)
- **Reserved Booths**: Orange (#ff9800)
- **Booked Booths**: Red (#f44336)
- **Selected Booths**: Blue (#2196f3)
- **Grid Lines**: Light gray (#e0e0e0)

### Layout
- **Sidebar**: Fixed 350px width, scrollable
- **Canvas**: Flexible, fills remaining space
- **Responsive**: Adapts to container size

### Visual Feedback
- **Hover Effects**: Highlight on hover
- **Selection**: Blue border and shadow
- **Dragging**: Reduced opacity during drag
- **Grid Pattern**: Subtle background pattern

---

## API Endpoints

### Load Configuration
- **GET** `/admin/exhibitions/{id}/floorplan/config/{floorId}`
- **Returns**: JSON configuration

### Save Configuration
- **POST** `/admin/exhibitions/{id}/floorplan/config`
- **Body**: Hall, grid, and booths configuration
- **Response**: Success status

### Update Booth Position
- **POST** `/admin/exhibitions/{exhibitionId}/booths/{boothId}/position`
- **Body**: X, Y coordinates

### Merge Booths
- **POST** `/admin/exhibitions/{exhibitionId}/booths/merge`
- **Body**: Array of booth IDs

### Split Booth
- **POST** `/admin/exhibitions/{exhibitionId}/booths/{boothId}/split`
- **Body**: Split configuration

---

## Data Models

### Floor Model
- `id`: Floor ID
- `exhibition_id`: Exhibition reference
- `name`: Floor name
- `floor_number`: Floor number
- `floorplan_images`: Array of image paths
- `floorplan_image`: Primary image path

### Booth Model
- `id`: Booth ID
- `exhibition_id`: Exhibition reference
- `floor_id`: Floor reference
- `booth_id`: Display ID (e.g., "B001")
- `x`, `y`: Position coordinates
- `width`, `height`: Dimensions
- `status`: Available/Reserved/Booked
- `size_id`: Booth size reference
- `discount_id`: Discount reference
- `special_price_user_id`: User-specific pricing

---

## Best Practices Implemented

1. **Auto-Save**: Prevents data loss
2. **Grid Snapping**: Ensures alignment
3. **Validation**: Prevents invalid configurations
4. **Visual Feedback**: Clear indication of state
5. **Multi-Select**: Efficient bulk operations
6. **Responsive Design**: Adapts to screen size
7. **Error Handling**: Graceful error messages
8. **Performance**: Debounced operations

---

## Future Enhancement Opportunities

1. **Undo/Redo**: History management
2. **Copy/Paste**: Duplicate booth configurations
3. **Layers**: Multiple floor plan layers
4. **Templates**: Pre-configured booth layouts
5. **Export/Import**: JSON export/import
6. **Collaborative Editing**: Real-time collaboration
7. **3D View**: Three-dimensional visualization
8. **Path Finding**: Automatic booth arrangement algorithms

---

## Conclusion

The Step 3 floor plan editor is a sophisticated, feature-rich interface that provides administrators with powerful tools to create and manage exhibition floor plans. It combines visual editing with precise control, grid-based alignment, and comprehensive booth management capabilities. The design emphasizes usability, performance, and data integrity through auto-save, validation, and efficient rendering.
