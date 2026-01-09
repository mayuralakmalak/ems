// Copied from admin-floorplan.js
class AdminFloorplanManager {
    constructor() {
        this.svg = document.getElementById('adminSvg');
        this.hallGroup = document.getElementById('adminHallGroup');
        this.boothsGroup = document.getElementById('adminBoothsGroup');
        this.gridBg = document.getElementById('gridBg');
        this.gridBgOverlay = document.getElementById('gridBgOverlay');
        this.canvasWrapper = document.getElementById('canvasWrapper');

        this.booths = [];
        this.selectedBooths = new Set();
        this.currentTool = 'select';
        this.currentZoom = 1;
        this.isDragging = false;
        this.dragStart = null;
        this.dragBoothStart = null;
        this.selectedBooth = null;
        this.isDrawingBooth = false;
        this.drawingStart = null;
        this.selectionBox = document.getElementById('selectionBox');
        this.isSelecting = false;
        this.selectStart = null;
        this.boothStartPositions = new Map(); // Store original positions for multi-drag
        this.hallBounds = null; // Will be set when hall is drawn
        this.autoSaveTimeout = null; // For debouncing auto-save

        // Dynamic dimensions will be calculated from floor meters (1 meter = 50px)
        // Default fallback values if no floor dimensions available
        this.hallConfig = {
            width: 2000, // Will be updated dynamically from floor dimensions
            height: 800, // Will be updated dynamically from floor dimensions
            margin: 0 // Will align to grid
        };

        // Store floor dimensions in meters
        this.floorDimensions = {
            widthMeters: null,
            heightMeters: null
        };

        this.gridConfig = {
            size: 50,
            show: true,
            snap: true
        };

        // Calculate hall dimensions in grid units
        this.hallGridUnits = {
            width: Math.floor(this.hallConfig.width / this.gridConfig.size),
            height: Math.floor(this.hallConfig.height / this.gridConfig.size)
        };

        const idEl = document.getElementById('exhibitionId');
        this.exhibitionId = idEl ? idEl.value : null;

        // Get current floor ID
        const floorIdEl = document.getElementById('currentFloorId');
        this.currentFloorId = floorIdEl ? floorIdEl.value : null;
        this.init();
    }

    updateCanvasDimensions() {
        if (!this.svg) return;
        // Set SVG viewBox to match hall dimensions for proper scaling
        this.svg.setAttribute('viewBox', `0 0 ${this.hallConfig.width} ${this.hallConfig.height}`);

        // Set SVG width and height to match hall dimensions for proper rendering
        this.svg.setAttribute('width', this.hallConfig.width);
        this.svg.setAttribute('height', this.hallConfig.height);

        // Update canvas wrapper to allow scrolling when dimensions exceed viewport
        // Account for current zoom level if zoom is applied
        const zoom = this.currentZoom || 1;
        const scaledWidth = this.hallConfig.width * zoom;
        const scaledHeight = this.hallConfig.height * zoom;

        if (this.canvasWrapper) {
            this.canvasWrapper.style.width = `${Math.max(scaledWidth, this.hallConfig.width)}px`;
            this.canvasWrapper.style.height = `${Math.max(scaledHeight, this.hallConfig.height)}px`;
        }

        // Preserve zoom transform if it exists
        if (zoom !== 1 && this.svg) {
            this.svg.style.transform = `scale(${zoom})`;
            this.svg.style.transformOrigin = 'top left';
        }

        // Update background image dimensions if it exists
        this.updateBackgroundImageDimensions();
    }

    // Calculate pixel dimensions from meters (1 meter = 50px grid size)
    calculateDimensionsFromMeters(widthMeters, heightMeters) {
        const gridSize = this.gridConfig.size; // 50px per meter
        const widthPx = Math.round((widthMeters || 40) * gridSize); // Default 40m if not provided
        const heightPx = Math.round((heightMeters || 16) * gridSize); // Default 16m if not provided

        return { widthPx, heightPx };
    }

    // Update hall dimensions from floor meters
    updateHallDimensionsFromFloor(widthMeters, heightMeters) {
        if (!widthMeters || !heightMeters) {
            console.warn('Floor dimensions not provided, using defaults');
            return;
        }

        // Store floor dimensions
        this.floorDimensions.widthMeters = widthMeters;
        this.floorDimensions.heightMeters = heightMeters;

        // Calculate pixel dimensions (meters × 50px per meter)
        const { widthPx, heightPx } = this.calculateDimensionsFromMeters(widthMeters, heightMeters);

        // Update hall config
        this.hallConfig.width = widthPx;
        this.hallConfig.height = heightPx;

        // Recalculate grid units
        this.hallGridUnits = {
            width: Math.floor(this.hallConfig.width / this.gridConfig.size),
            height: Math.floor(this.hallConfig.height / this.gridConfig.size)
        };

        // Update canvas and redraw
        this.updateCanvasDimensions();
        this.updateGrid();
        this.drawHall();

        // Update background image dimensions if it exists
        this.updateBackgroundImageDimensions();

        // Update hall properties display if visible
        this.updateHallPropertiesDisplay();
    }

    // Update background image dimensions
    updateBackgroundImageDimensions() {
        const bgImage = this.svg ? this.svg.querySelector('#floorBackgroundImage') : null;
        if (bgImage && this.hallConfig.width && this.hallConfig.height) {
            bgImage.setAttribute('width', this.hallConfig.width);
            bgImage.setAttribute('height', this.hallConfig.height);
        }
    }

    // Update hall properties display values
    updateHallPropertiesDisplay() {
        const hallWidthPx = document.getElementById('hallWidthPx');
        const hallHeightPx = document.getElementById('hallHeightPx');
        const hallWidthGrid = document.getElementById('hallWidthGrid');
        const hallHeightGrid = document.getElementById('hallHeightGrid');

        if (hallWidthPx) hallWidthPx.textContent = this.hallConfig.width;
        if (hallHeightPx) hallHeightPx.textContent = this.hallConfig.height;
        if (hallWidthGrid) hallWidthGrid.value = this.hallGridUnits.width;
        if (hallHeightGrid) hallHeightGrid.value = this.hallGridUnits.height;
    }

    async init() {
        // Initialize grid first
        this.updateGrid();
        this.drawHall();
        this.updateCanvasDimensions();
        this.setupEventListeners();
        this.updateBoothsList();
        this.updateCounts();

        // Initialize merge button
        const mergeBtn = document.getElementById('mergeBooths');
        if (mergeBtn) {
            mergeBtn.disabled = true;
        }

        // Ensure grid is visible
        setTimeout(() => {
            this.updateGrid();
        }, 100);

        // Optional: load saved config if available (only if no floor_id is set, otherwise it will be loaded when floor is selected)
        if (!this.currentFloorId) {
            await this.loadConfiguration();
        }
    }

    // Draw hall outline - aligned to grid
    drawHall() {
        this.hallGroup.innerHTML = '';

        const { width, height } = this.hallConfig;
        const gridSize = this.gridConfig.size;

        // Align hall to grid - calculate grid-aligned dimensions
        const hallGridWidth = Math.floor(width / gridSize);
        const hallGridHeight = Math.floor(height / gridSize);
        const hallWidth = hallGridWidth * gridSize;
        const hallHeight = hallGridHeight * gridSize;

        // Center the hall if needed
        const hallX = (width - hallWidth) / 2;
        const hallY = (height - hallHeight) / 2;

        const hall = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        hall.setAttribute('x', hallX);
        hall.setAttribute('y', hallY);
        hall.setAttribute('width', hallWidth);
        hall.setAttribute('height', hallHeight);
        hall.setAttribute('class', 'hall-outline');
        hall.setAttribute('rx', '4');
        hall.setAttribute('data-hall', 'true');

        hall.addEventListener('click', (e) => {
            if (this.currentTool === 'hall') {
                this.showHallProperties();
            }
        });

        this.hallGroup.appendChild(hall);

        // Entrance - aligned to grid
        const entranceWidth = Math.round(80 / gridSize) * gridSize;
        const entranceX = (width - entranceWidth) / 2;
        const entrance = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        entrance.setAttribute('x', entranceX);
        entrance.setAttribute('y', hallY - 5);
        entrance.setAttribute('width', entranceWidth);
        entrance.setAttribute('height', 10);
        entrance.setAttribute('fill', '#333');
        this.hallGroup.appendChild(entrance);

        const entranceLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        entranceLabel.setAttribute('x', width / 2);
        entranceLabel.setAttribute('y', hallY - 10);
        entranceLabel.setAttribute('text-anchor', 'middle');
        entranceLabel.setAttribute('font-size', '14');
        entranceLabel.setAttribute('font-weight', 'bold');
        entranceLabel.textContent = 'ENTRANCE';
        this.hallGroup.appendChild(entranceLabel);

        // Store hall bounds for booth validation
        this.hallBounds = {
            x: hallX,
            y: hallY,
            width: hallWidth,
            height: hallHeight
        };
    }

    // Load and display floor background image
    loadBackgroundImage(backgroundImagePath) {
        if (!this.svg) return;

        // Find or create background image element
        let bgImage = this.svg.querySelector('#floorBackgroundImage');

        if (backgroundImagePath) {
            // Construct the full URL to the image
            const imageUrl = `/storage/${backgroundImagePath.replace(/^\/+/, '')}`;

            if (!bgImage) {
                // Create SVG image element if it doesn't exist
                bgImage = document.createElementNS('http://www.w3.org/2000/svg', 'image');
                bgImage.id = 'floorBackgroundImage';
                bgImage.setAttribute('preserveAspectRatio', 'none'); // Stretch to fit exactly
                bgImage.setAttribute('opacity', '0.7'); // Slightly transparent to show grid

                // Insert after defs but before gridBgOverlay (behind everything else)
                const defs = this.svg.querySelector('defs');
                const gridBgOverlay = this.svg.querySelector('#gridBgOverlay');
                if (defs && gridBgOverlay) {
                    this.svg.insertBefore(bgImage, gridBgOverlay);
                } else if (defs && defs.nextSibling) {
                    this.svg.insertBefore(bgImage, defs.nextSibling);
                } else {
                    // Insert after defs
                    if (defs) {
                        defs.parentNode.insertBefore(bgImage, defs.nextSibling);
                    } else {
                        this.svg.insertBefore(bgImage, this.svg.firstChild);
                    }
                }
            }

            // Set image attributes to cover entire canvas
            // Use both href (modern) and xlink:href (legacy) for compatibility
            bgImage.setAttribute('href', imageUrl);
            bgImage.setAttributeNS('http://www.w3.org/1999/xlink', 'href', imageUrl);
            bgImage.setAttribute('x', '0');
            bgImage.setAttribute('y', '0');
            // Use current hall dimensions or fallback
            const width = this.hallConfig.width || 2000;
            const height = this.hallConfig.height || 800;
            bgImage.setAttribute('width', width);
            bgImage.setAttribute('height', height);
            bgImage.style.display = 'block';
        } else {
            // Remove the background image if no path is provided
            if (bgImage) {
                bgImage.remove();
            }
        }
    }

    // Update grid pattern
    updateGrid() {
        let pattern = document.getElementById('gridPattern');
        if (!pattern) {
            // Create pattern if it doesn't exist
            let defsEl = this.svg.querySelector('defs');
            if (!defsEl) {
                defsEl = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
                this.svg.insertBefore(defsEl, this.svg.firstChild);
            }
            pattern = document.createElementNS('http://www.w3.org/2000/svg', 'pattern');
            pattern.id = 'gridPattern';
            pattern.setAttribute('width', this.gridConfig.size);
            pattern.setAttribute('height', this.gridConfig.size);
            pattern.setAttribute('patternUnits', 'userSpaceOnUse');

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', `M ${this.gridConfig.size} 0 L 0 0 0 ${this.gridConfig.size}`);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', '#e0e0e0');
            path.setAttribute('stroke-width', '1');
            pattern.appendChild(path);

            defsEl.appendChild(pattern);
        } else {
            pattern.setAttribute('width', this.gridConfig.size);
            pattern.setAttribute('height', this.gridConfig.size);
            const path = pattern.querySelector('path');
            if (path) {
                path.setAttribute('d', `M ${this.gridConfig.size} 0 L 0 0 0 ${this.gridConfig.size}`);
            }
        }

        if (this.gridBg) {
            this.gridBg.style.display = this.gridConfig.show ? 'block' : 'none';
            this.gridBg.setAttribute('fill', this.gridConfig.show ? 'url(#gridPattern)' : 'none');
        }

        // Keep overlay visible to show background image through
        if (this.gridBgOverlay) {
            this.gridBgOverlay.style.display = 'block';
        }
    }

    // Setup event listeners
    setupEventListeners() {
        const listen = (id, event, handler) => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener(event, handler);
            }
            return el;
        };

        // Tool selection
        document.querySelectorAll('.tool-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tool = e.currentTarget.dataset.tool;
                this.setTool(tool);
            });
        });

        // Canvas events
        if (this.svg) {
            this.svg.addEventListener('mousedown', (e) => this.handleMouseDown(e));
            this.svg.addEventListener('mousemove', (e) => this.handleMouseMove(e));
            this.svg.addEventListener('mouseup', (e) => this.handleMouseUp(e));
            this.svg.addEventListener('click', (e) => this.handleCanvasClick(e));
        }

        // Hall properties
        listen('updateHall', 'click', () => this.updateHallFromProperties());

        // Booth properties
        listen('saveBooth', 'click', () => this.saveBoothFromProperties());
        listen('deleteBooth', 'click', () => this.deleteSelectedBooth());

        // Quick actions
        listen('snapToGrid', 'click', () => this.snapSelectedToGrid());
        listen('alignBooths', 'click', () => this.alignSelectedBooths());
        listen('duplicateBooth', 'click', () => this.duplicateSelectedBooth());
        listen('mergeBooths', 'click', () => this.mergeSelectedBooths());
        listen('generateBooths', 'click', () => this.showGenerateModal());
        listen('resetFloorplan', 'click', () => this.resetFloorplan());

        // Grid settings
        listen('showGrid', 'change', (e) => {
            this.gridConfig.show = e.target.checked;
            this.updateGrid();
        });

        listen('gridSize', 'change', (e) => {
            const newSize = parseInt(e.target.value);
            if (newSize !== this.gridConfig.size) {
                // Update grid size
                this.gridConfig.size = newSize;
                this.updateGrid();

                // Update hall to maintain grid units
                // If floor dimensions are set, recalculate from meters; otherwise maintain current grid units
                if (this.floorDimensions.widthMeters && this.floorDimensions.heightMeters) {
                    // Recalculate from floor meters with new grid size
                    const { widthPx, heightPx } = this.calculateDimensionsFromMeters(
                        this.floorDimensions.widthMeters,
                        this.floorDimensions.heightMeters
                    );
                    this.hallConfig.width = widthPx;
                    this.hallConfig.height = heightPx;
                } else {
                    // Fallback: maintain grid units
                    const widthGrid = Math.floor(this.hallConfig.width / this.gridConfig.size);
                    const heightGrid = Math.floor(this.hallConfig.height / this.gridConfig.size);
                    this.hallConfig.width = widthGrid * this.gridConfig.size;
                    this.hallConfig.height = heightGrid * this.gridConfig.size;
                }

                // Recalculate grid units
                this.hallGridUnits = {
                    width: Math.floor(this.hallConfig.width / this.gridConfig.size),
                    height: Math.floor(this.hallConfig.height / this.gridConfig.size)
                };

                this.updateCanvasDimensions();
                this.drawHall();

                // Snap all booths to new grid
                this.booths.forEach(booth => {
                    booth.x = this.snapToGridValue(booth.x);
                    booth.y = this.snapToGridValue(booth.y);
                    booth.width = this.snapToGridValue(booth.width);
                    booth.height = this.snapToGridValue(booth.height);
                    this.updateBoothElement(booth);
                });

                // Update grid unit displays if modal is open
                this.updateGridUnitDisplays();
            }
        });

        listen('snapEnabled', 'change', (e) => {
            this.gridConfig.snap = e.target.checked;
        });

        // Zoom controls
        listen('zoomIn', 'click', () => this.zoom(1.2));
        listen('zoomOut', 'click', () => this.zoom(0.8));
        listen('resetView', 'click', () => this.resetView());
        listen('fitToScreen', 'click', () => this.fitToScreen());

        // Save/Load
        listen('saveConfig', 'click', () => this.saveConfiguration());
        listen('loadConfig', 'click', () => this.loadConfiguration());
        listen('exportJson', 'click', () => this.exportToJSON());
        listen('clearAll', 'click', () => {
            if (confirm('Are you sure you want to clear all booths?')) {
                this.clearAllBooths();
            }
        });

        // Generate modal
        listen('closeGenerateModal', 'click', () => {
            const modal = document.getElementById('generateModal');
            if (modal) modal.classList.add('hidden');
        });

        listen('cancelGenerateBtn', 'click', () => {
            const modal = document.getElementById('generateModal');
            if (modal) modal.classList.add('hidden');
        });

        listen('generateGridBtn', 'click', () => this.generateBoothGrid());

        // Update grid unit displays when values change
        ['gridBoothWidth', 'gridBoothHeight', 'gridSpacing', 'gridStartX', 'gridStartY'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => {
                    this.updateGridUnitDisplays();
                });
            }
        });

        // Property inputs - update booth on change
        ['boothWidth', 'boothHeight', 'boothX', 'boothY', 'boothStatus', 'boothSize',
            'boothArea', 'boothSizeSqft', 'boothDiscount', 'boothDiscountUser'
        ].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('change', () => {
                    if (this.selectedBooth) {
                        this.updateBoothFromProperties();
                    }
                });
            }
        });
    }

    // Set current tool
    setTool(tool) {
        this.currentTool = tool;

        // Update UI
        document.querySelectorAll('.tool-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tool="${tool}"]`).classList.add('active');

        // Update cursor
        this.svg.className = `admin-svg ${tool}-mode`;

        // Update mode display
        document.getElementById('currentMode').textContent = tool.charAt(0).toUpperCase() + tool.slice(1);

        // Show/hide property panels
        document.getElementById('hallProperties').classList.add('hidden');
        document.getElementById('boothProperties').classList.add('hidden');

        if (tool === 'hall') {
            document.getElementById('hallProperties').classList.remove('hidden');
        } else if (tool === 'booth' && this.selectedBooth) {
            document.getElementById('boothProperties').classList.remove('hidden');
        }
    }

    // Handle mouse down
    handleMouseDown(e) {
        e.preventDefault();
        e.stopPropagation();

        const point = this.getSVGPoint(e);
        const booth = this.getBoothAtPoint(point.x, point.y);

        if (this.currentTool === 'select') {
            if (booth) {
                // Store the EXACT booth that was clicked
                const clickedBoothId = booth.id;
                const clickedBooth = this.booths.find(b => b.id === clickedBoothId);

                if (!clickedBooth) {
                    console.error('Clicked booth not found in booths array:', clickedBoothId);
                    return;
                }

                if (e.ctrlKey || e.metaKey) {
                    // Multi-select with Ctrl/Cmd
                    this.toggleBoothSelection(clickedBoothId);
                } else {
                    // Single select - ensure THIS booth is selected
                    if (!this.selectedBooths.has(clickedBoothId)) {
                        this.selectBooth(clickedBoothId);
                    }
                }

                // ALWAYS store start positions for ALL currently selected booths
                // Use the ACTUAL clicked booth's position as the drag reference
                this.boothStartPositions.clear();
                this.selectedBooths.forEach(id => {
                    const b = this.booths.find(bo => bo.id === id);
                    if (b) {
                        this.boothStartPositions.set(id, { x: b.x, y: b.y });
                    }
                });

                // Set up drag with the CLICKED booth as the primary reference
                this.isDragging = true;
                this.dragStart = { x: point.x, y: point.y };
                this.dragBoothStart = { x: clickedBooth.x, y: clickedBooth.y };
                this.selectedBooth = clickedBoothId; // Ensure clicked booth is primary

                // Update visuals to show correct selection
                this.updateBoothVisuals();
            } else {
                // Start selection box only if not holding Ctrl
                if (!e.ctrlKey && !e.metaKey) {
                    this.isSelecting = true;
                    this.selectStart = point;
                    this.selectionBox.classList.remove('hidden');
                    this.selectionBox.setAttribute('x', point.x);
                    this.selectionBox.setAttribute('y', point.y);
                    this.selectionBox.setAttribute('width', '0');
                    this.selectionBox.setAttribute('height', '0');
                }
            }
        } else if (this.currentTool === 'booth') {
            // Only start drawing if not clicking on existing booth
            if (!booth) {
                // Snap start point to grid
                const snappedPoint = this.snapToGrid(point);
                this.isDrawingBooth = true;
                this.drawingStart = snappedPoint;
            } else {
                // If clicking on booth while in booth tool, select it
                if (e.ctrlKey || e.metaKey) {
                    this.toggleBoothSelection(booth.id);
                } else {
                    this.selectBooth(booth.id);
                }
            }
        } else if (this.currentTool === 'delete') {
            if (booth) {
                this.deleteBooth(booth.id);
            }
        }
    }

    // Handle mouse move
    handleMouseMove(e) {
        const point = this.getSVGPoint(e);

        if (this.isDragging && this.dragBoothStart && this.dragStart && this.selectedBooth) {
            // Calculate drag offset from the initial click position
            const deltaX = point.x - this.dragStart.x;
            const deltaY = point.y - this.dragStart.y;

            // Get the primary booth (the one that was clicked)
            const primaryBooth = this.booths.find(b => b.id === this.selectedBooth);
            if (!primaryBooth) {
                console.error('Primary booth not found:', this.selectedBooth);
                return;
            }

            // Get the primary booth's start position
            const primaryStartPos = this.boothStartPositions.get(this.selectedBooth);
            if (!primaryStartPos) {
                console.error('Primary booth start position not found');
                return;
            }

            // Handle dragging for all selected booths
            const selectedBoothIds = Array.from(this.selectedBooths);
            selectedBoothIds.forEach(boothId => {
                const booth = this.booths.find(b => b.id === boothId);
                if (!booth) return;

                // Get this booth's start position
                const boothStartPos = this.boothStartPositions.get(boothId);
                if (!boothStartPos) {
                    console.error('Booth start position not found:', boothId);
                    return;
                }

                let newX, newY;

                if (this.selectedBooths.size > 1 && boothId !== this.selectedBooth) {
                    // Multi-drag: maintain relative positions to the primary booth
                    const relX = boothStartPos.x - primaryStartPos.x;
                    const relY = boothStartPos.y - primaryStartPos.y;

                    // Calculate new position: primary's new position + relative offset
                    newX = primaryStartPos.x + deltaX + relX;
                    newY = primaryStartPos.y + deltaY + relY;
                } else {
                    // Single drag: move directly by delta
                    newX = boothStartPos.x + deltaX;
                    newY = boothStartPos.y + deltaY;
                }

                // Snap to grid
                if (this.gridConfig.snap) {
                    newX = this.snapToGridValue(newX);
                    newY = this.snapToGridValue(newY);
                }

                // Constrain to hall bounds
                if (this.hallBounds) {
                    newX = Math.max(this.hallBounds.x, Math.min(newX, this.hallBounds.x + this.hallBounds.width - booth.width));
                    newY = Math.max(this.hallBounds.y, Math.min(newY, this.hallBounds.y + this.hallBounds.height - booth.height));
                } else {
                    newX = Math.max(0, newX);
                    newY = Math.max(0, newY);
                }

                // Update booth position
                booth.x = newX;
                booth.y = newY;
                this.updateBoothElement(booth);
            });

            // Update properties for the primary selected booth
            if (primaryBooth) {
                this.updateBoothProperties(primaryBooth);
            }
        } else if (this.isDrawingBooth && this.drawingStart) {
            // Snap end point to grid
            const snappedPoint = this.snapToGrid(point);
            const startX = Math.min(this.drawingStart.x, snappedPoint.x);
            const startY = Math.min(this.drawingStart.y, snappedPoint.y);
            const endX = Math.max(this.drawingStart.x, snappedPoint.x);
            const endY = Math.max(this.drawingStart.y, snappedPoint.y);

            // Calculate grid-aligned dimensions
            const width = this.snapToGridValue(endX - startX);
            const height = this.snapToGridValue(endY - startY);

            // Ensure minimum size (at least 1 grid unit)
            const minSize = this.gridConfig.size;
            const finalWidth = Math.max(minSize, width);
            const finalHeight = Math.max(minSize, height);

            // Snap start position to grid
            const finalX = this.snapToGridValue(startX);
            const finalY = this.snapToGridValue(startY);

            // Draw preview
            const preview = this.svg.querySelector('#boothPreview');
            if (preview) {
                preview.setAttribute('x', finalX);
                preview.setAttribute('y', finalY);
                preview.setAttribute('width', finalWidth);
                preview.setAttribute('height', finalHeight);
            } else {
                const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                rect.id = 'boothPreview';
                rect.setAttribute('x', finalX);
                rect.setAttribute('y', finalY);
                rect.setAttribute('width', finalWidth);
                rect.setAttribute('height', finalHeight);
                rect.setAttribute('fill', 'rgba(33, 150, 243, 0.3)');
                rect.setAttribute('stroke', '#2196f3');
                rect.setAttribute('stroke-width', '2');
                rect.setAttribute('stroke-dasharray', '5,5');
                this.boothsGroup.appendChild(rect);
            }
        } else if (this.isSelecting && this.selectStart) {
            const x = Math.min(this.selectStart.x, point.x);
            const y = Math.min(this.selectStart.y, point.y);
            const width = Math.abs(point.x - this.selectStart.x);
            const height = Math.abs(point.y - this.selectStart.y);

            this.selectionBox.setAttribute('x', x);
            this.selectionBox.setAttribute('y', y);
            this.selectionBox.setAttribute('width', width);
            this.selectionBox.setAttribute('height', height);
        }
    }

    // Handle mouse up
    handleMouseUp(e) {
        e.preventDefault();
        e.stopPropagation();

        if (this.isDragging) {
            // Finished dragging - ensure final snap to grid for all selected booths
            this.selectedBooths.forEach(boothId => {
                const booth = this.booths.find(b => b.id === boothId);
                if (booth && this.gridConfig.snap) {
                    booth.x = this.snapToGridValue(booth.x);
                    booth.y = this.snapToGridValue(booth.y);
                    this.updateBoothElement(booth);
                }
            });

            if (this.selectedBooth) {
                const booth = this.booths.find(b => b.id === this.selectedBooth);
                if (booth) {
                    this.updateBoothProperties(booth);
                }
            }

            this.isDragging = false;
            this.dragBoothStart = null;
            this.dragStart = null;
            this.boothStartPositions.clear();

            // Auto-save after dragging
            this.autoSave();
        } else if (this.isDrawingBooth && this.drawingStart) {
            const point = this.getSVGPoint(e);
            const snappedPoint = this.snapToGrid(point);

            const startX = Math.min(this.drawingStart.x, snappedPoint.x);
            const startY = Math.min(this.drawingStart.y, snappedPoint.y);
            const endX = Math.max(this.drawingStart.x, snappedPoint.x);
            const endY = Math.max(this.drawingStart.y, snappedPoint.y);

            // Calculate grid-aligned dimensions
            let width = this.snapToGridValue(endX - startX);
            let height = this.snapToGridValue(endY - startY);

            // Ensure minimum size (at least 1 grid unit)
            const minSize = this.gridConfig.size;
            width = Math.max(minSize, width);
            height = Math.max(minSize, height);

            // Snap start position to grid
            const x = this.snapToGridValue(startX);
            const y = this.snapToGridValue(startY);

            if (width >= minSize && height >= minSize) {
                // Check if within hall bounds
                if (!this.hallBounds ||
                    (x >= this.hallBounds.x && y >= this.hallBounds.y &&
                        x + width <= this.hallBounds.x + this.hallBounds.width &&
                        y + height <= this.hallBounds.y + this.hallBounds.height)) {

                    const booth = {
                        id: this.generateBoothId(),
                        x: x,
                        y: y,
                        width: width,
                        height: height,
                        status: 'available',
                        size: this.getSizeCategory(width, height),
                        area: Math.round((width * height) / (this.gridConfig.size * this.gridConfig.size) * 100), // Approximate sq ft
                        price: 10000,
                        openSides: 2,
                        category: 'Standard',
                        includedItems: ['Table', '2 Chairs', 'Power Outlet']
                    };

                    this.addBooth(booth);
                    this.selectBooth(booth.id);
                }
            }

            const preview = this.svg.querySelector('#boothPreview');
            if (preview) preview.remove();

            this.isDrawingBooth = false;
            this.drawingStart = null;
        } else if (this.isSelecting) {
            // Select booths in selection box
            const box = {
                x: parseFloat(this.selectionBox.getAttribute('x')),
                y: parseFloat(this.selectionBox.getAttribute('y')),
                width: parseFloat(this.selectionBox.getAttribute('width')),
                height: parseFloat(this.selectionBox.getAttribute('height'))
            };

            this.booths.forEach(booth => {
                const boothCenter = {
                    x: booth.x + booth.width / 2,
                    y: booth.y + booth.height / 2
                };

                if (boothCenter.x >= box.x && boothCenter.x <= box.x + box.width &&
                    boothCenter.y >= box.y && boothCenter.y <= box.y + box.height) {
                    this.selectedBooths.add(booth.id);
                }
            });

            this.updateBoothVisuals();
            this.selectionBox.classList.add('hidden');
            this.isSelecting = false;
            this.selectStart = null;
            this.updateCounts();
        }
    }

    // Handle canvas click
    handleCanvasClick(e) {
        // Only deselect if we're not dragging, not selecting, and not holding Ctrl
        if (this.currentTool === 'select' && !this.isDragging && !this.isSelecting && !e.ctrlKey && !e.metaKey) {
            const point = this.getSVGPoint(e);
            const booth = this.getBoothAtPoint(point.x, point.y);
            if (!booth) {
                this.deselectAll();
            }
        }
    }

    // Snap point to grid
    snapToGrid(point) {
        return {
            x: this.snapToGridValue(point.x),
            y: this.snapToGridValue(point.y)
        };
    }

    // Snap value to grid
    snapToGridValue(value) {
        return Math.round(value / this.gridConfig.size) * this.gridConfig.size;
    }

    // Get size category based on dimensions
    getSizeCategory(width, height) {
        const area = width * height;
        const gridArea = this.gridConfig.size * this.gridConfig.size;
        const gridUnits = area / gridArea;

        if (gridUnits <= 4) return 'small';
        if (gridUnits <= 8) return 'medium';
        return 'large';
    }

    // Get SVG point from mouse event - accurate coordinate transformation
    getSVGPoint(e) {
        const rect = this.svg.getBoundingClientRect();
        const point = this.svg.createSVGPoint();

        // Set screen coordinates
        point.x = e.clientX;
        point.y = e.clientY;

        // Transform to SVG coordinates using matrix transformation
        const svgPoint = point.matrixTransform(this.svg.getScreenCTM().inverse());

        return svgPoint;
    }

    // Get booth at point - check in reverse order (topmost first)
    getBoothAtPoint(x, y) {
        // Check booths in reverse order (last drawn = topmost)
        // This ensures we get the booth that's visually on top
        for (let i = this.booths.length - 1; i >= 0; i--) {
            const booth = this.booths[i];
            // Check if point is inside booth bounds (inclusive on left/top, exclusive on right/bottom for better accuracy)
            if (x >= booth.x && x < booth.x + booth.width &&
                y >= booth.y && y < booth.y + booth.height) {
                return booth;
            }
        }
        return null;
    }

    // Add booth
    addBooth(booth) {
        this.booths.push(booth);
        this.drawBooth(booth);
        this.updateBoothsList();
        this.updateCounts();
        // Auto-save after adding booth
        this.autoSave();
    }

    // Draw booth
    drawBooth(booth) {
        const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        group.setAttribute('data-booth-id', booth.id);
        group.setAttribute('class', 'booth-group');

        // Ensure status is set (default to 'available' if not set)
        const status = booth.status || 'available';
        booth.status = status; // Update the booth object to ensure consistency

        const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rect.setAttribute('x', booth.x);
        rect.setAttribute('y', booth.y);
        rect.setAttribute('width', booth.width);
        rect.setAttribute('height', booth.height);
        rect.setAttribute('class', `booth-admin ${status}`);
        rect.setAttribute('rx', '4');

        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        label.setAttribute('x', booth.x + booth.width / 2);
        label.setAttribute('y', booth.y + booth.height / 2);
        label.setAttribute('class', 'booth-label');
        label.textContent = booth.id;

        group.appendChild(rect);
        group.appendChild(label);
        this.boothsGroup.appendChild(group);
    }

    // Update booth element
    updateBoothElement(booth) {
        const group = this.getBoothElement(booth.id);
        if (group) {
            const rect = group.querySelector('rect');
            const label = group.querySelector('text');

            // Ensure status is set (default to 'available' if not set)
            const status = booth.status || 'available';
            booth.status = status; // Update the booth object to ensure consistency

            rect.setAttribute('x', booth.x);
            rect.setAttribute('y', booth.y);
            rect.setAttribute('width', booth.width);
            rect.setAttribute('height', booth.height);
            rect.setAttribute('class', `booth-admin ${status}`);

            label.setAttribute('x', booth.x + booth.width / 2);
            label.setAttribute('y', booth.y + booth.height / 2);
            label.textContent = booth.id;
        }
    }

    // Get booth element
    getBoothElement(boothId) {
        return this.svg.querySelector(`[data-booth-id="${boothId}"]`);
    }

    // Select booth
    selectBooth(boothId) {
        this.selectedBooths.clear();
        this.selectedBooths.add(boothId);
        this.selectedBooth = boothId;
        this.updateBoothVisuals();
        this.showBoothProperties();
        this.updateCounts();
    }

    // Toggle booth selection
    toggleBoothSelection(boothId) {
        if (this.selectedBooths.has(boothId)) {
            this.selectedBooths.delete(boothId);
            if (this.selectedBooth === boothId) {
                // Set another selected booth as primary, or null if none
                const remaining = Array.from(this.selectedBooths);
                this.selectedBooth = remaining.length > 0 ? remaining[0] : null;
            }
        } else {
            this.selectedBooths.add(boothId);
            this.selectedBooth = boothId;
        }
        this.updateBoothVisuals();
        this.updateCounts();

        // Show properties if we have a primary selection
        if (this.selectedBooth) {
            this.showBoothProperties();
        } else if (this.selectedBooths.size === 0) {
            document.getElementById('boothProperties').classList.add('hidden');
        }
    }

    // Deselect all
    deselectAll() {
        this.selectedBooths.clear();
        this.selectedBooth = null;
        this.updateBoothVisuals();
        document.getElementById('boothProperties').classList.add('hidden');
        this.updateCounts();
    }

    // Update booth visuals
    updateBoothVisuals() {
        this.booths.forEach(booth => {
            const group = this.getBoothElement(booth.id);
            if (group) {
                const rect = group.querySelector('rect');
                // Ensure status is set
                const status = booth.status || 'available';
                booth.status = status;

                // Update class to include both status and selected state
                const baseClass = 'booth-admin';
                const statusClass = status;
                const classes = [baseClass, statusClass];

                if (this.selectedBooths.has(booth.id)) {
                    classes.push('selected');
                }

                rect.setAttribute('class', classes.join(' '));
            }
        });
        this.updateBoothsList();
    }

    // Show booth properties
    showBoothProperties() {
        if (this.selectedBooth) {
            const booth = this.booths.find(b => b.id === this.selectedBooth);
            if (booth) {
                this.updateBoothProperties(booth);
                document.getElementById('boothProperties').classList.remove('hidden');
            }
        }
    }

    // Update booth properties panel
    updateBoothProperties(booth) {
        document.getElementById('boothId').value = booth.id;
        document.getElementById('boothWidth').value = booth.width;
        document.getElementById('boothHeight').value = booth.height;
        document.getElementById('boothX').value = Math.round(booth.x);
        document.getElementById('boothY').value = Math.round(booth.y);
        document.getElementById('boothStatus').value = booth.status;
        document.getElementById('boothSize').value = booth.size;
        document.getElementById('boothArea').value = booth.area;
        const sizeSqftSelect = document.getElementById('boothSizeSqft');
        if (sizeSqftSelect) {
            // Prefer existing explicit sizeId; also check exhibition_booth_size_id; otherwise default to first non-empty option
            let selectedValue = null;
            if (typeof booth.sizeId !== 'undefined' && booth.sizeId !== null) {
                selectedValue = booth.sizeId;
            } else if (typeof booth.exhibition_booth_size_id !== 'undefined' && booth.exhibition_booth_size_id !== null) {
                // Use exhibition_booth_size_id if sizeId is not set
                selectedValue = booth.exhibition_booth_size_id;
                // Sync sizeId for consistency
                booth.sizeId = parseInt(selectedValue) || null;
            } else {
                const firstRealOption = Array.from(sizeSqftSelect.options).find(opt => opt.value);
                if (firstRealOption) {
                    selectedValue = firstRealOption.value;
                }
            }

            if (selectedValue !== null) {
                sizeSqftSelect.value = selectedValue;
                // Keep booth object in sync so it gets persisted in JSON/DB
                booth.sizeId = parseInt(selectedValue) || null;
                // Also sync exhibition_booth_size_id if not already set
                if (typeof booth.exhibition_booth_size_id === 'undefined' || booth.exhibition_booth_size_id === null) {
                    booth.exhibition_booth_size_id = parseInt(selectedValue) || null;
                }
            }
        }
        // Category fixed to default; no UI control.

        // Sync discount dropdowns
        const discountSelect = document.getElementById('boothDiscount');
        if (discountSelect) {
            const value = booth.discount_id || booth.discountId || '';
            discountSelect.value = value || '';
        }
        const userSelect = document.getElementById('boothDiscountUser');
        if (userSelect) {
            const value = booth.discount_user_id || booth.discountUserId || '';
            userSelect.value = value || '';
        }
    }

    // Update booth from properties
    updateBoothFromProperties() {
        if (!this.selectedBooth) return;

        const booth = this.booths.find(b => b.id === this.selectedBooth);
        if (booth) {
            // Snap all values to grid
            booth.width = this.snapToGridValue(parseInt(document.getElementById('boothWidth').value));
            booth.height = this.snapToGridValue(parseInt(document.getElementById('boothHeight').value));
            booth.x = this.snapToGridValue(parseInt(document.getElementById('boothX').value));
            booth.y = this.snapToGridValue(parseInt(document.getElementById('boothY').value));
            booth.status = document.getElementById('boothStatus').value;
            booth.size = document.getElementById('boothSize').value;
            booth.area = parseInt(document.getElementById('boothArea').value);
            const sizeSqftSelect = document.getElementById('boothSizeSqft');
            if (sizeSqftSelect) {
                // If nothing selected, default to first non-empty option
                let rawValue = sizeSqftSelect.value;
                if (!rawValue) {
                    const firstRealOption = Array.from(sizeSqftSelect.options).find(opt => opt.value);
                    rawValue = firstRealOption ? firstRealOption.value : null;
                    if (rawValue) {
                        sizeSqftSelect.value = rawValue;
                    }
                }
                booth.sizeId = rawValue ? parseInt(rawValue) || null : null;
                booth.exhibition_booth_size_id = booth.sizeId; // Sync exhibition_booth_size_id

                // Update category based on selected size option
                if (rawValue) {
                    const selectedOption = sizeSqftSelect.options[sizeSqftSelect.selectedIndex];
                    if (selectedOption) {
                        const categoryValue = selectedOption.getAttribute('data-category');
                        if (categoryValue) {
                            // Normalize category value
                            if (categoryValue === '1') {
                                booth.category = 'Premium';
                            } else if (categoryValue === '2') {
                                booth.category = 'Standard';
                            } else if (categoryValue === '3') {
                                booth.category = 'Economy';
                            } else {
                                booth.category = categoryValue;
                            }
                        }
                    }
                }
            } else {
                booth.sizeId = null;
            }
            booth.category = booth.category || 'Standard';

            // Persist discount metadata (optional)
            const discountSelect = document.getElementById('boothDiscount');
            if (discountSelect) {
                const discountValue = discountSelect.value || '';
                booth.discount_id = discountValue ? parseInt(discountValue) : null;
                booth.discountId = booth.discount_id; // keep camelCase copy in payload for compatibility
            }

            const userSelect = document.getElementById('boothDiscountUser');
            if (userSelect) {
                const userValue = userSelect.value || '';
                booth.discount_user_id = userValue ? parseInt(userValue) : null;
                booth.discountUserId = booth.discount_user_id;
            }

            // Update size category based on dimensions
            booth.size = this.getSizeCategory(booth.width, booth.height);

            this.updateBoothElement(booth);
            // Auto-save after updating booth
            this.autoSave();
        }
    }

    // Save booth from properties
    saveBoothFromProperties() {
        const boothId = document.getElementById('boothId').value.trim();
        if (!boothId) {
            alert('Please enter a booth ID');
            return;
        }

        if (this.selectedBooth && this.selectedBooth !== boothId) {
            // Check if new ID already exists
            if (this.booths.find(b => b.id === boothId)) {
                alert('Booth ID already exists');
                return;
            }
        }

        if (this.selectedBooth) {
            // Update existing
            const booth = this.booths.find(b => b.id === this.selectedBooth);
            if (booth) {
                const oldBoothId = this.selectedBooth;
                const oldElement = this.getBoothElement(oldBoothId);

                // Update booth ID in the object
                booth.id = boothId;

                // Update the visual element's ID attribute and label immediately
                if (oldElement) {
                    oldElement.setAttribute('data-booth-id', boothId);
                    const label = oldElement.querySelector('text');
                    if (label) {
                        label.textContent = boothId;
                    }
                }

                // Update selection references
                if (this.selectedBooths.has(oldBoothId)) {
                    this.selectedBooths.delete(oldBoothId);
                    this.selectedBooths.add(boothId);
                }

                // Update selectedBooth BEFORE calling updateBoothFromProperties (it uses this.selectedBooth)
                this.selectedBooth = boothId;

                // Update other properties
                this.updateBoothFromProperties();
                this.updateBoothsList();
                this.updateBoothVisuals();
            }
        } else {
            // Create new
            const x = this.snapToGridValue(parseInt(document.getElementById('boothX').value) || 100);
            const y = this.snapToGridValue(parseInt(document.getElementById('boothY').value) || 100);
            const width = this.snapToGridValue(parseInt(document.getElementById('boothWidth').value) || 100);
            const height = this.snapToGridValue(parseInt(document.getElementById('boothHeight').value) || 80);

            const booth = {
                id: boothId,
                x: x,
                y: y,
                width: width,
                height: height,
                status: document.getElementById('boothStatus').value,
                size: this.getSizeCategory(width, height),
                area: Math.round((width * height) / (this.gridConfig.size * this.gridConfig.size) * 100),
                sizeId: (() => {
                    const sizeSelect = document.getElementById('boothSizeSqft');
                    return sizeSelect ? (parseInt(sizeSelect.value) || null) : null;
                })(),
                price: 0,
                openSides: 0,
                category: 'Standard',
                includedItems: []
            };

            this.addBooth(booth);
            this.selectBooth(boothId);
        }
    }

    // Delete booth (internal method - performs actual deletion)
    deleteBooth(boothId, skipConfirmation = false) {
        // Get booth info for better confirmation message
        const booth = this.booths.find(b => b.id === boothId);
        const boothName = booth ? booth.id : boothId;

        // Always show confirmation unless explicitly skipped
        if (!skipConfirmation) {
            const confirmed = confirm(`Are you sure you want to delete booth "${boothName}"?\n\nThis action cannot be undone.`);
            if (!confirmed) {
                return false;
            }
        }

        this.booths = this.booths.filter(b => b.id !== boothId);
        const element = this.getBoothElement(boothId);
        if (element) element.remove();
        this.selectedBooths.delete(boothId);
        if (this.selectedBooth === boothId) {
            this.selectedBooth = null;
            document.getElementById('boothProperties').classList.add('hidden');
        }
        this.updateBoothsList();
        this.updateCounts();
        // Auto-save after deleting booth
        this.autoSave();
        return true;
    }

    // Delete selected booth
    deleteSelectedBooth() {
        if (this.selectedBooth) {
            this.deleteBooth(this.selectedBooth);
        }
    }

    // Generate booth ID
    generateBoothId() {
        let num = 1;
        while (this.booths.find(b => b.id === `B${String(num).padStart(3, '0')}`)) {
            num++;
        }
        return `B${String(num).padStart(3, '0')}`;
    }

    // Update booths list
    updateBoothsList() {
        const list = document.getElementById('boothsList');
        if (this.booths.length === 0) {
            list.innerHTML = '<p class="empty-message">No booths added</p>';
            return;
        }

        list.innerHTML = '';
        this.booths.forEach(booth => {
            const item = document.createElement('div');
            item.className = 'booth-list-item';
            if (this.selectedBooths.has(booth.id)) {
                item.classList.add('selected');
            }

            item.innerHTML = `
                <div>
                    <div class="booth-id">${booth.id}</div>
                    <div class="booth-status">${booth.status}</div>
                </div>
            `;

            item.addEventListener('click', () => {
                if (this.currentTool === 'select') {
                    this.selectBooth(booth.id);
                }
            });

            list.appendChild(item);
        });
    }

    // Update counts
    updateCounts() {
        document.getElementById('boothCount').textContent = this.booths.length;
        document.getElementById('selectedCount').textContent = this.selectedBooths.size;

        // Update merge button text
        const mergeBtn = document.getElementById('mergeBooths');
        if (mergeBtn) {
            if (this.selectedBooths.size === 2) {
                mergeBtn.textContent = 'Merge Selected (2) ✓';
                mergeBtn.disabled = false;
            } else {
                mergeBtn.textContent = `Merge Selected (${this.selectedBooths.size}/2)`;
                mergeBtn.disabled = this.selectedBooths.size !== 2;
            }
        }
    }

    // Show hall properties
    showHallProperties() {
        const gridSize = this.gridConfig.size;
        const widthGrid = Math.floor(this.hallConfig.width / gridSize);
        const heightGrid = Math.floor(this.hallConfig.height / gridSize);

        document.getElementById('hallWidthGrid').value = widthGrid;
        document.getElementById('hallHeightGrid').value = heightGrid;
        document.getElementById('gridSizeHall').value = gridSize;
        document.getElementById('hallWidthPx').textContent = widthGrid * gridSize;
        document.getElementById('hallHeightPx').textContent = heightGrid * gridSize;
    }

    // Update hall from properties
    updateHallFromProperties() {
        const gridSize = parseInt(document.getElementById('gridSizeHall').value);
        const widthGrid = parseInt(document.getElementById('hallWidthGrid').value);
        const heightGrid = parseInt(document.getElementById('hallHeightGrid').value);

        this.gridConfig.size = gridSize;

        // If floor dimensions are set, recalculate from meters; otherwise use grid units
        if (this.floorDimensions.widthMeters && this.floorDimensions.heightMeters) {
            const { widthPx, heightPx } = this.calculateDimensionsFromMeters(
                this.floorDimensions.widthMeters,
                this.floorDimensions.heightMeters
            );
            this.hallConfig.width = widthPx;
            this.hallConfig.height = heightPx;
        } else {
            this.hallConfig.width = widthGrid * gridSize;
            this.hallConfig.height = heightGrid * gridSize;
        }

        // Recalculate grid units
        this.hallGridUnits = {
            width: Math.floor(this.hallConfig.width / this.gridConfig.size),
            height: Math.floor(this.hallConfig.height / this.gridConfig.size)
        };

        // Update grid
        this.updateGrid();

        // Update grid size input in grid settings
        document.getElementById('gridSize').value = gridSize;

        this.svg.setAttribute('viewBox', `0 0 ${this.hallConfig.width} ${this.hallConfig.height}`);
        this.updateCanvasDimensions();
        this.drawHall();

        // Snap all booths to new grid
        this.booths.forEach(booth => {
            booth.x = this.snapToGridValue(booth.x);
            booth.y = this.snapToGridValue(booth.y);
            booth.width = this.snapToGridValue(booth.width);
            booth.height = this.snapToGridValue(booth.height);
            this.updateBoothElement(booth);
        });
    }

    // Snap selected to grid
    snapSelectedToGrid() {
        this.selectedBooths.forEach(boothId => {
            const booth = this.booths.find(b => b.id === boothId);
            if (booth) {
                booth.x = this.snapToGridValue(booth.x);
                booth.y = this.snapToGridValue(booth.y);
                booth.width = this.snapToGridValue(booth.width);
                booth.height = this.snapToGridValue(booth.height);
                this.updateBoothElement(booth);
            }
        });
        if (this.selectedBooth) {
            this.updateBoothProperties(this.booths.find(b => b.id === this.selectedBooth));
        }
    }

    // Align selected booths
    alignSelectedBooths() {
        if (this.selectedBooths.size < 2) return;

        const booths = Array.from(this.selectedBooths).map(id => this.booths.find(b => b.id === id));
        const firstBooth = booths[0];

        booths.slice(1).forEach(booth => {
            booth.x = firstBooth.x;
            this.updateBoothElement(booth);
        });
    }

    // Duplicate selected booth
    duplicateSelectedBooth() {
        if (!this.selectedBooth) return;

        const original = this.booths.find(b => b.id === this.selectedBooth);
        if (original) {
            const offset = this.gridConfig.size;
            const newBooth = {
                ...original,
                id: this.generateBoothId(),
                x: this.snapToGridValue(original.x + offset),
                y: this.snapToGridValue(original.y + offset)
            };
            this.addBooth(newBooth);
            this.selectBooth(newBooth.id);
        }
    }

    // Merge selected booths (exactly 2 booths)
    mergeSelectedBooths() {
        if (this.selectedBooths.size !== 2) {
            alert('Please select exactly 2 booths to merge.');
            return;
        }

        const boothIds = Array.from(this.selectedBooths);
        const booth1 = this.booths.find(b => b.id === boothIds[0]);
        const booth2 = this.booths.find(b => b.id === boothIds[1]);

        if (!booth1 || !booth2) return;

        // Check if booths are adjacent (touching)
        const isAdjacent = this.areBoothsAdjacent(booth1, booth2);

        if (!isAdjacent) {
            if (!confirm('Selected booths are not adjacent. Merge anyway? They will be combined into a single booth.')) {
                return;
            }
        }

        // Calculate merged booth bounds
        const minX = Math.min(booth1.x, booth2.x);
        const minY = Math.min(booth1.y, booth2.y);
        const maxX = Math.max(booth1.x + booth1.width, booth2.x + booth2.width);
        const maxY = Math.max(booth1.y + booth1.height, booth2.y + booth2.height);

        // Snap to grid
        const mergedX = this.snapToGridValue(minX);
        const mergedY = this.snapToGridValue(minY);
        const mergedWidth = this.snapToGridValue(maxX - minX);
        const mergedHeight = this.snapToGridValue(maxY - minY);

        // Create merged booth
        const mergedBooth = {
            id: booth1.id, // Keep first booth's ID
            x: mergedX,
            y: mergedY,
            width: mergedWidth,
            height: mergedHeight,
            status: booth1.status, // Keep first booth's status
            size: this.getSizeCategory(mergedWidth, mergedHeight),
            area: Math.round((mergedWidth * mergedHeight) / (this.gridConfig.size * this.gridConfig.size) * 100),
            price: booth1.price + booth2.price, // Combine prices
            openSides: Math.max(booth1.openSides, booth2.openSides),
            category: booth1.category === booth2.category ? booth1.category : 'Premium',
            includedItems: [...new Set([...booth1.includedItems, ...booth2.includedItems])] // Combine unique items
        };

        // Delete original booths (skip confirmation as user already confirmed merge)
        this.deleteBooth(booth1.id, true);
        this.deleteBooth(booth2.id, true);

        // Add merged booth
        this.addBooth(mergedBooth);
        this.selectBooth(mergedBooth.id);

        alert(`Booths ${booth1.id} and ${booth2.id} merged into ${mergedBooth.id}`);
    }

    // Check if two booths are adjacent (touching) - works for both horizontal and vertical
    areBoothsAdjacent(booth1, booth2) {
        const tolerance = this.gridConfig.size * 0.5; // Tolerance for grid alignment (half a grid unit)

        // Check if booths overlap (shouldn't merge overlapping booths)
        const overlapX = !(booth1.x + booth1.width <= booth2.x || booth2.x + booth2.width <= booth1.x);
        const overlapY = !(booth1.y + booth1.height <= booth2.y || booth2.y + booth2.height <= booth1.y);
        if (overlapX && overlapY) {
            return false; // Booths overlap, not adjacent
        }

        // Check horizontal adjacency (booths side by side)
        // Booth1 is to the left of Booth2
        const booth1LeftOfBooth2 = Math.abs(booth1.x + booth1.width - booth2.x) < tolerance;
        // Booth2 is to the left of Booth1
        const booth2LeftOfBooth1 = Math.abs(booth2.x + booth2.width - booth1.x) < tolerance;

        // Check if they share vertical space (overlap or touch vertically)
        const shareVerticalSpace = !(booth1.y + booth1.height <= booth2.y - tolerance ||
            booth2.y + booth2.height <= booth1.y - tolerance);

        const horizontalAdjacent = (booth1LeftOfBooth2 || booth2LeftOfBooth1) && shareVerticalSpace;

        // Check vertical adjacency (booths stacked vertically)
        // Booth1 is above Booth2
        const booth1AboveBooth2 = Math.abs(booth1.y + booth1.height - booth2.y) < tolerance;
        // Booth2 is above Booth1
        const booth2AboveBooth1 = Math.abs(booth2.y + booth2.height - booth1.y) < tolerance;

        // Check if they share horizontal space (overlap or touch horizontally)
        const shareHorizontalSpace = !(booth1.x + booth1.width <= booth2.x - tolerance ||
            booth2.x + booth2.width <= booth1.x - tolerance);

        const verticalAdjacent = (booth1AboveBooth2 || booth2AboveBooth1) && shareHorizontalSpace;

        return horizontalAdjacent || verticalAdjacent;
    }

    // Show generate modal
    showGenerateModal() {
        const widthInput = document.getElementById('gridBoothWidth');
        const heightInput = document.getElementById('gridBoothHeight');
        const spacingInput = document.getElementById('gridSpacing');
        const startXInput = document.getElementById('gridStartX');
        const startYInput = document.getElementById('gridStartY');
        const modal = document.getElementById('generateModal');

        // If required modal fields are missing, just return quietly
        if (!widthInput || !heightInput || !spacingInput || !startXInput || !startYInput || !modal) {
            // Keep previous fallback of inline modal missing; ensure button doesn't break other flows
            return;
        }

        // Set default values in grid units
        const gridSize = this.gridConfig.size;
        widthInput.value = 2;
        heightInput.value = 2;
        spacingInput.value = 0;

        // Calculate start position in grid units (inside hall, with some margin)
        if (this.hallBounds) {
            const startXGrid = Math.ceil(this.hallBounds.x / gridSize) + 1;
            const startYGrid = Math.ceil(this.hallBounds.y / gridSize) + 1;
            startXInput.value = startXGrid;
            startYInput.value = startYGrid;
        } else {
            startXInput.value = 2;
            startYInput.value = 3;
        }

        this.updateGridUnitDisplays();
        modal.classList.remove('hidden');
    }

    // Update grid unit display values
    updateGridUnitDisplays() {
        const gridSize = this.gridConfig.size;
        const widthGrid = parseInt(document.getElementById('gridBoothWidth').value) || 2;
        const heightGrid = parseInt(document.getElementById('gridBoothHeight').value) || 2;
        const spacingGrid = parseInt(document.getElementById('gridSpacing').value) || 0;
        const startXGrid = parseInt(document.getElementById('gridStartX').value) || 2;
        const startYGrid = parseInt(document.getElementById('gridStartY').value) || 3;

        document.getElementById('gridBoothWidthPx').textContent = widthGrid * gridSize;
        document.getElementById('gridBoothHeightPx').textContent = heightGrid * gridSize;
        document.getElementById('gridSpacingPx').textContent = spacingGrid * gridSize;
        document.getElementById('gridStartXPx').textContent = startXGrid * gridSize;
        document.getElementById('gridStartYPx').textContent = startYGrid * gridSize;
    }

    // Generate booth grid - all values in grid units, converted to pixels
    generateBoothGrid() {
        const rows = parseInt(document.getElementById('gridRows').value);
        const cols = parseInt(document.getElementById('gridCols').value);
        const boothWidthGrid = parseInt(document.getElementById('gridBoothWidth').value);
        const boothHeightGrid = parseInt(document.getElementById('gridBoothHeight').value);
        const spacingGrid = parseInt(document.getElementById('gridSpacing').value);
        const startXGrid = parseInt(document.getElementById('gridStartX').value);
        const startYGrid = parseInt(document.getElementById('gridStartY').value);
        const prefix = document.getElementById('gridPrefix').value || 'B';

        // Get selected booth size category
        const boothSizeSelect = document.getElementById('gridBoothSizeCategory');
        let selectedBoothSizeId = null;
        let selectedCategory = null;
        let selectedSizeSqft = null;

        if (boothSizeSelect && boothSizeSelect.value) {
            selectedBoothSizeId = parseInt(boothSizeSelect.value);
            const selectedOption = boothSizeSelect.options[boothSizeSelect.selectedIndex];
            if (selectedOption) {
                selectedCategory = selectedOption.getAttribute('data-category');
                selectedSizeSqft = parseFloat(selectedOption.getAttribute('data-size-sqft'));

                // Normalize category value
                if (selectedCategory === '1') {
                    selectedCategory = 'Premium';
                } else if (selectedCategory === '2') {
                    selectedCategory = 'Standard';
                } else if (selectedCategory === '3') {
                    selectedCategory = 'Economy';
                }
            }
        }

        const gridSize = this.gridConfig.size;

        // Convert grid units to pixels
        const boothWidth = boothWidthGrid * gridSize;
        const boothHeight = boothHeightGrid * gridSize;
        const spacing = spacingGrid * gridSize;
        const startX = startXGrid * gridSize;
        const startY = startYGrid * gridSize;

        let boothNum = 1;
        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                // Calculate position in grid units, then convert to pixels
                const xGrid = startXGrid + col * (boothWidthGrid + spacingGrid);
                const yGrid = startYGrid + row * (boothHeightGrid + spacingGrid);

                // Convert to pixels and ensure grid alignment
                const x = this.snapToGridValue(xGrid * gridSize);
                const y = this.snapToGridValue(yGrid * gridSize);

                // Check if booth would be within hall bounds
                if (this.hallBounds) {
                    if (x < this.hallBounds.x || y < this.hallBounds.y ||
                        x + boothWidth > this.hallBounds.x + this.hallBounds.width ||
                        y + boothHeight > this.hallBounds.y + this.hallBounds.height) {
                        // Skip this booth if it's outside hall bounds
                        continue;
                    }
                }

                let boothId = `${prefix}${String(boothNum).padStart(3, '0')}`;
                while (this.booths.find(b => b.id === boothId)) {
                    boothNum++;
                    boothId = `${prefix}${String(boothNum).padStart(3, '0')}`;
                }

                // Calculate area in sq ft (approximate based on grid)
                const area = Math.round((boothWidth * boothHeight) / (gridSize * gridSize) * 100);

                const booth = {
                    id: boothId,
                    x: x,
                    y: y,
                    width: boothWidth,
                    height: boothHeight,
                    status: 'available',
                    size: this.getSizeCategory(boothWidth, boothHeight),
                    area: selectedSizeSqft || area, // Use selected size sqft if available, otherwise calculated
                    price: 5000 + Math.floor(Math.random() * 15000),
                    openSides: 2,
                    category: selectedCategory || 'Standard', // Use selected category if available
                    includedItems: ['Table', '2 Chairs', 'Power Outlet']
                };

                // Add exhibition_booth_size_id and sizeId if a category was selected
                if (selectedBoothSizeId) {
                    booth.exhibition_booth_size_id = selectedBoothSizeId;
                    booth.sizeId = selectedBoothSizeId; // Also set sizeId for compatibility with updateBoothProperties
                }

                this.addBooth(booth);
                boothNum++;
            }
        }

        document.getElementById('generateModal').classList.add('hidden');
    }

    // Clear all booths
    clearAllBooths() {
        this.booths = [];
        this.selectedBooths.clear();
        this.selectedBooth = null;
        this.boothsGroup.innerHTML = '';
        this.updateBoothsList();
        this.updateCounts();
        document.getElementById('boothProperties').classList.add('hidden');
    }

    // Reset full floorplan: hall/grid defaults + remove all booths
    resetFloorplan() {
        // defaults match initial config
        this.gridConfig = { size: 50, show: true, snap: true };

        // If floor dimensions are set, recalculate from meters; otherwise use defaults
        if (this.floorDimensions.widthMeters && this.floorDimensions.heightMeters) {
            const { widthPx, heightPx } = this.calculateDimensionsFromMeters(
                this.floorDimensions.widthMeters,
                this.floorDimensions.heightMeters
            );
            this.hallConfig = { width: widthPx, height: heightPx, margin: 0 };
        } else {
            this.hallConfig = { width: 2000, height: 800, margin: 0 };
        }

        // Recalculate grid units
        this.hallGridUnits = {
            width: Math.floor(this.hallConfig.width / this.gridConfig.size),
            height: Math.floor(this.hallConfig.height / this.gridConfig.size)
        };

        // update UI inputs
        const widthGrid = Math.floor(this.hallConfig.width / this.gridConfig.size);
        const heightGrid = Math.floor(this.hallConfig.height / this.gridConfig.size);
        const hallWidthGrid = document.getElementById('hallWidthGrid');
        const hallHeightGrid = document.getElementById('hallHeightGrid');
        const gridSizeHall = document.getElementById('gridSizeHall');
        const hallWidthPx = document.getElementById('hallWidthPx');
        const hallHeightPx = document.getElementById('hallHeightPx');
        if (hallWidthGrid) hallWidthGrid.value = widthGrid;
        if (hallHeightGrid) hallHeightGrid.value = heightGrid;
        if (gridSizeHall) gridSizeHall.value = this.gridConfig.size;
        if (hallWidthPx) hallWidthPx.textContent = widthGrid * this.gridConfig.size;
        if (hallHeightPx) hallHeightPx.textContent = heightGrid * this.gridConfig.size;

        const gridSizeInput = document.getElementById('gridSize');
        const showGrid = document.getElementById('showGrid');
        const snapEnabled = document.getElementById('snapEnabled');
        if (gridSizeInput) gridSizeInput.value = this.gridConfig.size;
        if (showGrid) showGrid.checked = this.gridConfig.show;
        if (snapEnabled) snapEnabled.checked = this.gridConfig.snap;

        // apply to canvas
        this.updateGrid();
        this.svg.setAttribute('viewBox', `0 0 ${this.hallConfig.width} ${this.hallConfig.height}`);
        this.updateCanvasDimensions();
        this.drawHall();

        // clear booths
        this.clearAllBooths();
    }

    // Zoom
    zoom(factor) {
        this.currentZoom *= factor;
        this.currentZoom = Math.max(0.1, Math.min(5, this.currentZoom)); // Allow zoom from 0.1x to 5x

        if (!this.svg || !this.canvasWrapper) return;

        // Apply zoom using CSS transform on the SVG
        this.svg.style.transform = `scale(${this.currentZoom})`;
        this.svg.style.transformOrigin = 'top left';

        // Adjust canvas wrapper size to accommodate zoomed content
        const scaledWidth = this.hallConfig.width * this.currentZoom;
        const scaledHeight = this.hallConfig.height * this.currentZoom;

        // Ensure wrapper is at least as large as the scaled content
        if (this.canvasWrapper) {
            this.canvasWrapper.style.width = `${Math.max(scaledWidth, this.hallConfig.width)}px`;
            this.canvasWrapper.style.height = `${Math.max(scaledHeight, this.hallConfig.height)}px`;
        }
    }

    // Reset view
    resetView() {
        this.currentZoom = 1;

        if (!this.svg || !this.canvasWrapper) return;

        // Reset transform
        this.svg.style.transform = 'scale(1)';
        this.svg.style.transformOrigin = 'top left';

        // Reset canvas wrapper to original dimensions
        if (this.canvasWrapper) {
            this.canvasWrapper.style.width = `${this.hallConfig.width}px`;
            this.canvasWrapper.style.height = `${this.hallConfig.height}px`;
        }

        // Scroll to top-left
        if (this.canvasWrapper) {
            this.canvasWrapper.scrollLeft = 0;
            this.canvasWrapper.scrollTop = 0;
        }
    }

    // Fit to screen
    fitToScreen() {
        if (!this.svg || !this.canvasWrapper) return;

        // Get viewport dimensions
        const viewportWidth = this.canvasWrapper.clientWidth;
        const viewportHeight = this.canvasWrapper.clientHeight;

        // Calculate scale to fit both width and height
        const scaleX = viewportWidth / this.hallConfig.width;
        const scaleY = viewportHeight / this.hallConfig.height;
        const scale = Math.min(scaleX, scaleY, 1); // Don't zoom in beyond 1x, only zoom out to fit

        // Apply zoom
        this.currentZoom = scale;
        this.svg.style.transform = `scale(${this.currentZoom})`;
        this.svg.style.transformOrigin = 'top left';

        // Reset canvas wrapper to original dimensions (scaling handles the visual size)
        if (this.canvasWrapper) {
            this.canvasWrapper.style.width = `${this.hallConfig.width}px`;
            this.canvasWrapper.style.height = `${this.hallConfig.height}px`;
        }

        // Center the content
        if (this.canvasWrapper) {
            this.canvasWrapper.scrollLeft = (this.hallConfig.width * this.currentZoom - viewportWidth) / 2;
            this.canvasWrapper.scrollTop = (this.hallConfig.height * this.currentZoom - viewportHeight) / 2;
        }
    }

    // Save configuration to JSON file
    async saveConfiguration() {
        const config = {
            hall: this.hallConfig,
            grid: this.gridConfig,
            booths: this.booths
        };

        // Include floor_id if available
        if (this.currentFloorId) {
            config.floor_id = this.currentFloorId;
        }

        try {
            if (!this.exhibitionId) return;

            const response = await fetch(`/admin/exhibitions/${this.exhibitionId}/floorplan/config`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                },
                body: JSON.stringify(config)
            });

            const result = await response.json();
            if (!result.success) {
                alert('Error saving: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error saving configuration:', error);
            alert('Failed to save configuration. Please try again.');
        }
    }

    // Load configuration from JSON file
    async loadConfiguration(floorId = null, showConfirm = true) {
        if (!this.exhibitionId) return;

        if (showConfirm && !confirm('Load floor plan from server? This will replace current setup.')) {
            return;
        }

        // Use provided floorId or get from currentFloorId
        const floorIdToUse = floorId || this.currentFloorId;

        // Build URL
        let url = `/admin/exhibitions/${this.exhibitionId}/floorplan/config`;
        if (floorIdToUse) {
            url = `/admin/exhibitions/${this.exhibitionId}/floorplan/config/${floorIdToUse}`;
        }

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const config = await response.json();

            if (config.hall) {
                this.hallConfig = config.hall;
            }
            if (config.grid) {
                this.gridConfig = config.grid;
            }
            this.booths = config.booths || [];

            // Update UI - preserve floor dimensions if they exist
            // If floor dimensions are set, recalculate from meters; otherwise keep loaded config
            if (this.floorDimensions.widthMeters && this.floorDimensions.heightMeters) {
                const { widthPx, heightPx } = this.calculateDimensionsFromMeters(
                    this.floorDimensions.widthMeters,
                    this.floorDimensions.heightMeters
                );
                this.hallConfig.width = widthPx;
                this.hallConfig.height = heightPx;
            }
            // If no floor dimensions, keep the loaded config values

            // Recalculate grid units
            this.hallGridUnits = {
                width: Math.floor(this.hallConfig.width / this.gridConfig.size),
                height: Math.floor(this.hallConfig.height / this.gridConfig.size)
            };

            this.updateCanvasDimensions();
            this.updateGrid();
            this.drawHall();

            // Clear and redraw booths
            this.boothsGroup.innerHTML = '';
            this.booths.forEach(booth => this.drawBooth(booth));

            // Update grid settings UI
            document.getElementById('gridSize').value = this.gridConfig.size;
            document.getElementById('showGrid').checked = this.gridConfig.show;
            document.getElementById('snapEnabled').checked = this.gridConfig.snap;

            this.updateBoothsList();
            this.updateCounts();
            alert('Floor plan loaded from server successfully!');
        } catch (error) {
            console.error('Error loading configuration:', error);
            alert('Failed to load configuration. Please try again.');
        }
    }

    // Export to JSON file (download)
    exportToJSON() {
        const config = {
            hall: this.hallConfig,
            grid: this.gridConfig,
            booths: this.booths
        };

        const json = JSON.stringify(config, null, 2);
        const blob = new Blob([json], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'floorplan-config.json';
        a.click();
        URL.revokeObjectURL(url);
    }

    // Auto-save on changes (optional - can be called after modifications)
    autoSave() {
        // Auto-save after a delay to avoid too many requests
        if (this.autoSaveTimeout) {
            clearTimeout(this.autoSaveTimeout);
        }
        this.autoSaveTimeout = setTimeout(() => {
            this.saveConfiguration();
        }, 2000); // Save 2 seconds after last change
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.floorplanEditor = new AdminFloorplanManager();

    // Load configuration for current floor if available
    const floorIdEl = document.getElementById('currentFloorId');
    if (floorIdEl && floorIdEl.value && window.floorplanEditor) {
        window.floorplanEditor.currentFloorId = floorIdEl.value;
        window.floorplanEditor.loadConfiguration(floorIdEl.value);
    }
});