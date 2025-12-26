/**
 * Anatomy Selector - Interactive SVG-based drill-down body diagram
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2025 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * Features:
 * - Multi-level drill-down: Body → Region → Sub-region → Structure
 * - Bilingual labels (English/Vietnamese)
 * - Touch and mouse support
 * - Breadcrumb navigation
 * - Selection history
 * - Integration with PT Assessment forms
 */

(function(window, document) {
    'use strict';

    // Default configuration
    const DEFAULT_CONFIG = {
        containerId: 'anatomy-selector',
        assetsPath: '/public/assets/anatomy/',
        language: 'en', // 'en' or 'vi'
        showBreadcrumb: true,
        showLegend: true,
        showLayerControls: true,
        enableTouch: true,
        highlightColor: '#4CAF50',
        hoverColor: '#81C784',
        selectedColor: '#2196F3',
        onRegionClick: null,
        onRegionHover: null,
        onSelectionChange: null,
        onDrillDown: null,
        onDrillUp: null
    };

    // Structure type colors
    const STRUCTURE_COLORS = {
        region: '#E3F2FD',
        muscle: '#FFCDD2',
        bone: '#FFF9C4',
        joint: '#C8E6C9',
        nerve: '#E1BEE7',
        vessel: '#FFCCBC',
        ligament: '#B2DFDB',
        tendon: '#F0F4C3',
        organ: '#D1C4E9'
    };

    // Bilingual labels
    const LABELS = {
        en: {
            breadcrumbHome: 'Body',
            selectView: 'Select View',
            frontView: 'Front View',
            backView: 'Back View',
            layers: 'Layers',
            muscles: 'Muscles',
            bones: 'Bones',
            joints: 'Joints',
            nerves: 'Nerves',
            vessels: 'Vessels',
            ligaments: 'Ligaments',
            tendons: 'Tendons',
            selectedRegions: 'Selected Regions',
            noSelection: 'Click on body to select regions',
            addFinding: 'Add Finding',
            severity: 'Severity',
            painLevel: 'Pain Level',
            notes: 'Notes',
            clear: 'Clear',
            save: 'Save Selection',
            zoomIn: 'Zoom In',
            zoomOut: 'Zoom Out',
            reset: 'Reset View',
            left: 'Left',
            right: 'Right',
            bilateral: 'Bilateral'
        },
        vi: {
            breadcrumbHome: 'Cơ thể',
            selectView: 'Chọn góc nhìn',
            frontView: 'Mặt trước',
            backView: 'Mặt sau',
            layers: 'Lớp',
            muscles: 'Cơ',
            bones: 'Xương',
            joints: 'Khớp',
            nerves: 'Thần kinh',
            vessels: 'Mạch máu',
            ligaments: 'Dây chằng',
            tendons: 'Gân',
            selectedRegions: 'Vùng đã chọn',
            noSelection: 'Nhấp vào cơ thể để chọn vùng',
            addFinding: 'Thêm phát hiện',
            severity: 'Mức độ nghiêm trọng',
            painLevel: 'Mức độ đau',
            notes: 'Ghi chú',
            clear: 'Xóa',
            save: 'Lưu lựa chọn',
            zoomIn: 'Phóng to',
            zoomOut: 'Thu nhỏ',
            reset: 'Đặt lại',
            left: 'Trái',
            right: 'Phải',
            bilateral: 'Hai bên'
        }
    };

    /**
     * AnatomySelector Class
     */
    class AnatomySelector {
        constructor(config = {}) {
            this.config = { ...DEFAULT_CONFIG, ...config };
            this.container = null;
            this.svgContainer = null;
            this.currentSvg = null;
            this.currentLevel = 0;
            this.navigationStack = [];
            this.selections = [];
            this.regionsData = {};
            this.visibleLayers = ['muscle', 'bone', 'joint', 'ligament', 'tendon'];

            this.init();
        }

        /**
         * Initialize the anatomy selector
         */
        init() {
            this.container = document.getElementById(this.config.containerId);
            if (!this.container) {
                console.error('AnatomySelector: Container not found:', this.config.containerId);
                return;
            }

            this.createUI();
            this.loadRegionsData();
            this.loadInitialView();
            this.bindEvents();
        }

        /**
         * Create the UI structure
         */
        createUI() {
            const labels = LABELS[this.config.language] || LABELS.en;

            this.container.innerHTML = `
                <div class="anatomy-selector-wrapper">
                    <!-- Header with view selector and breadcrumb -->
                    <div class="anatomy-header">
                        <div class="anatomy-view-selector">
                            <button class="view-btn active" data-view="front">
                                <i class="fa fa-user"></i> ${labels.frontView}
                            </button>
                            <button class="view-btn" data-view="back">
                                <i class="fa fa-user fa-flip-horizontal"></i> ${labels.backView}
                            </button>
                        </div>
                        <div class="anatomy-breadcrumb">
                            <span class="breadcrumb-item home" data-level="0">${labels.breadcrumbHome}</span>
                        </div>
                    </div>

                    <!-- Main content area -->
                    <div class="anatomy-content">
                        <!-- SVG display area -->
                        <div class="anatomy-svg-container">
                            <div class="anatomy-svg-wrapper" id="anatomy-svg-wrapper">
                                <div class="anatomy-loading">
                                    <i class="fa fa-spinner fa-spin"></i> Loading...
                                </div>
                            </div>
                            <div class="anatomy-zoom-controls">
                                <button class="zoom-btn" data-action="zoom-in" title="${labels.zoomIn}">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button class="zoom-btn" data-action="zoom-out" title="${labels.zoomOut}">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <button class="zoom-btn" data-action="reset" title="${labels.reset}">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Side panel -->
                        <div class="anatomy-side-panel">
                            <!-- Layer controls -->
                            ${this.config.showLayerControls ? `
                            <div class="anatomy-layers">
                                <h4>${labels.layers}</h4>
                                <label class="layer-toggle">
                                    <input type="checkbox" data-layer="muscle" checked>
                                    <span class="layer-color" style="background:${STRUCTURE_COLORS.muscle}"></span>
                                    ${labels.muscles}
                                </label>
                                <label class="layer-toggle">
                                    <input type="checkbox" data-layer="bone" checked>
                                    <span class="layer-color" style="background:${STRUCTURE_COLORS.bone}"></span>
                                    ${labels.bones}
                                </label>
                                <label class="layer-toggle">
                                    <input type="checkbox" data-layer="joint" checked>
                                    <span class="layer-color" style="background:${STRUCTURE_COLORS.joint}"></span>
                                    ${labels.joints}
                                </label>
                                <label class="layer-toggle">
                                    <input type="checkbox" data-layer="ligament" checked>
                                    <span class="layer-color" style="background:${STRUCTURE_COLORS.ligament}"></span>
                                    ${labels.ligaments}
                                </label>
                                <label class="layer-toggle">
                                    <input type="checkbox" data-layer="tendon" checked>
                                    <span class="layer-color" style="background:${STRUCTURE_COLORS.tendon}"></span>
                                    ${labels.tendons}
                                </label>
                                <label class="layer-toggle">
                                    <input type="checkbox" data-layer="nerve">
                                    <span class="layer-color" style="background:${STRUCTURE_COLORS.nerve}"></span>
                                    ${labels.nerves}
                                </label>
                                <label class="layer-toggle">
                                    <input type="checkbox" data-layer="vessel">
                                    <span class="layer-color" style="background:${STRUCTURE_COLORS.vessel}"></span>
                                    ${labels.vessels}
                                </label>
                            </div>
                            ` : ''}

                            <!-- Selected regions list -->
                            <div class="anatomy-selections">
                                <h4>${labels.selectedRegions}</h4>
                                <div class="selections-list" id="anatomy-selections-list">
                                    <p class="no-selection">${labels.noSelection}</p>
                                </div>
                                <div class="selections-actions">
                                    <button class="btn btn-sm btn-secondary" id="anatomy-clear-btn">
                                        <i class="fa fa-times"></i> ${labels.clear}
                                    </button>
                                    <button class="btn btn-sm btn-primary" id="anatomy-save-btn">
                                        <i class="fa fa-save"></i> ${labels.save}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Finding dialog (hidden by default) -->
                    <div class="anatomy-finding-dialog" id="anatomy-finding-dialog" style="display:none;">
                        <div class="finding-dialog-content">
                            <h4 id="finding-region-name"></h4>
                            <div class="form-group">
                                <label>${labels.severity} (0-10)</label>
                                <input type="range" min="0" max="10" value="5" id="finding-severity" class="form-control">
                                <span id="finding-severity-value">5</span>
                            </div>
                            <div class="form-group">
                                <label>${labels.painLevel} (0-10)</label>
                                <input type="range" min="0" max="10" value="5" id="finding-pain" class="form-control">
                                <span id="finding-pain-value">5</span>
                            </div>
                            <div class="form-group">
                                <label>${labels.notes}</label>
                                <textarea id="finding-notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="dialog-actions">
                                <button class="btn btn-secondary" id="finding-cancel">${labels.clear}</button>
                                <button class="btn btn-primary" id="finding-add">${labels.addFinding}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            this.svgContainer = document.getElementById('anatomy-svg-wrapper');
        }

        /**
         * Load regions data from server
         */
        async loadRegionsData() {
            try {
                const response = await fetch('/apis/default/vietnamese-pt/anatomy/regions');
                if (response.ok) {
                    this.regionsData = await response.json();
                }
            } catch (error) {
                console.warn('Could not load regions data from server, using defaults');
            }
        }

        /**
         * Load the initial body view
         */
        loadInitialView() {
            this.loadSvg('body-full-front.svg', 'body_front');
        }

        /**
         * Load an SVG file
         */
        async loadSvg(filename, regionCode) {
            const svgPath = this.config.assetsPath + filename;

            try {
                this.svgContainer.innerHTML = '<div class="anatomy-loading"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';

                const response = await fetch(svgPath);
                if (!response.ok) {
                    throw new Error(`Failed to load SVG: ${response.status}`);
                }

                const svgContent = await response.text();
                this.svgContainer.innerHTML = svgContent;
                this.currentSvg = this.svgContainer.querySelector('svg');

                if (this.currentSvg) {
                    this.setupSvgInteraction();
                    this.applyLayerVisibility();

                    // Update navigation
                    if (regionCode) {
                        this.updateBreadcrumb(regionCode);
                    }

                    // Fire callback
                    if (typeof this.config.onDrillDown === 'function') {
                        this.config.onDrillDown(regionCode, this.navigationStack);
                    }
                }
            } catch (error) {
                console.error('Error loading SVG:', error);
                this.svgContainer.innerHTML = `
                    <div class="anatomy-error">
                        <i class="fa fa-exclamation-triangle"></i>
                        <p>Could not load anatomy diagram</p>
                        <small>${error.message}</small>
                    </div>
                `;
            }
        }

        /**
         * Setup SVG interaction (clicks, hovers)
         */
        setupSvgInteraction() {
            if (!this.currentSvg) return;

            // Find all clickable regions (elements with data-region attribute)
            const regions = this.currentSvg.querySelectorAll('[data-region]');

            regions.forEach(region => {
                // Add hover effect
                region.addEventListener('mouseenter', (e) => this.handleRegionHover(e, region));
                region.addEventListener('mouseleave', (e) => this.handleRegionLeave(e, region));

                // Add click handler
                region.addEventListener('click', (e) => this.handleRegionClick(e, region));

                // Add touch support
                if (this.config.enableTouch) {
                    region.addEventListener('touchstart', (e) => {
                        e.preventDefault();
                        this.handleRegionClick(e, region);
                    });
                }

                // Style as clickable
                region.style.cursor = 'pointer';
                region.style.transition = 'fill 0.2s ease, opacity 0.2s ease';
            });
        }

        /**
         * Handle region hover
         */
        handleRegionHover(event, region) {
            const regionCode = region.getAttribute('data-region');
            const originalFill = region.getAttribute('data-original-fill') || region.style.fill;

            // Store original fill if not already stored
            if (!region.getAttribute('data-original-fill')) {
                region.setAttribute('data-original-fill', region.style.fill || region.getAttribute('fill') || '#ccc');
            }

            // Apply hover color
            region.style.fill = this.config.hoverColor;
            region.style.opacity = '0.9';

            // Show tooltip with region name
            this.showTooltip(event, regionCode);

            // Fire callback
            if (typeof this.config.onRegionHover === 'function') {
                this.config.onRegionHover(regionCode, region);
            }
        }

        /**
         * Handle region leave
         */
        handleRegionLeave(event, region) {
            const originalFill = region.getAttribute('data-original-fill');
            const isSelected = region.classList.contains('selected');

            // Restore original fill or selected color
            region.style.fill = isSelected ? this.config.selectedColor : (originalFill || '');
            region.style.opacity = '1';

            this.hideTooltip();
        }

        /**
         * Handle region click
         */
        handleRegionClick(event, region) {
            event.stopPropagation();

            const regionCode = region.getAttribute('data-region');
            const canDrillDown = region.getAttribute('data-drill-down') === 'true';
            const svgFile = region.getAttribute('data-svg-file');

            // Fire callback
            if (typeof this.config.onRegionClick === 'function') {
                this.config.onRegionClick(regionCode, region, {
                    canDrillDown,
                    svgFile
                });
            }

            if (canDrillDown && svgFile) {
                // Drill down to sub-region
                this.drillDown(regionCode, svgFile);
            } else {
                // Select this region
                this.selectRegion(regionCode, region);
            }
        }

        /**
         * Drill down to a sub-region
         */
        drillDown(regionCode, svgFile) {
            // Save current state to navigation stack
            this.navigationStack.push({
                regionCode: this.getCurrentRegionCode(),
                svgFile: this.getCurrentSvgFile(),
                scrollPosition: this.svgContainer.scrollTop
            });

            this.currentLevel++;
            this.loadSvg(svgFile, regionCode);
        }

        /**
         * Drill up to parent region
         */
        drillUp() {
            if (this.navigationStack.length === 0) return;

            const previousState = this.navigationStack.pop();
            this.currentLevel--;

            this.loadSvg(previousState.svgFile, previousState.regionCode);

            // Fire callback
            if (typeof this.config.onDrillUp === 'function') {
                this.config.onDrillUp(previousState.regionCode, this.navigationStack);
            }
        }

        /**
         * Select a region
         */
        selectRegion(regionCode, element) {
            const regionData = this.getRegionData(regionCode);

            // Check if already selected
            const existingIndex = this.selections.findIndex(s => s.code === regionCode);

            if (existingIndex >= 0) {
                // Deselect
                this.selections.splice(existingIndex, 1);
                element.classList.remove('selected');
                element.style.fill = element.getAttribute('data-original-fill') || '';
            } else {
                // Show finding dialog
                this.showFindingDialog(regionCode, regionData, element);
            }

            this.updateSelectionsUI();

            // Fire callback
            if (typeof this.config.onSelectionChange === 'function') {
                this.config.onSelectionChange(this.selections);
            }
        }

        /**
         * Show finding dialog
         */
        showFindingDialog(regionCode, regionData, element) {
            const dialog = document.getElementById('anatomy-finding-dialog');
            const labels = LABELS[this.config.language] || LABELS.en;

            // Set region name
            const nameField = document.getElementById('finding-region-name');
            nameField.textContent = this.config.language === 'vi' ?
                (regionData?.name_vi || regionCode) :
                (regionData?.name_en || regionCode);

            // Reset form
            document.getElementById('finding-severity').value = 5;
            document.getElementById('finding-severity-value').textContent = '5';
            document.getElementById('finding-pain').value = 5;
            document.getElementById('finding-pain-value').textContent = '5';
            document.getElementById('finding-notes').value = '';

            // Show dialog
            dialog.style.display = 'flex';

            // Handle add button
            const addBtn = document.getElementById('finding-add');
            const cancelBtn = document.getElementById('finding-cancel');

            const addHandler = () => {
                const selection = {
                    code: regionCode,
                    name_en: regionData?.name_en || regionCode,
                    name_vi: regionData?.name_vi || regionCode,
                    structure_type: regionData?.structure_type || 'region',
                    path: this.getNavigationPath(),
                    severity: parseInt(document.getElementById('finding-severity').value),
                    pain_level: parseInt(document.getElementById('finding-pain').value),
                    notes: document.getElementById('finding-notes').value,
                    timestamp: new Date().toISOString()
                };

                this.selections.push(selection);
                element.classList.add('selected');
                element.style.fill = this.config.selectedColor;

                this.updateSelectionsUI();
                dialog.style.display = 'none';

                addBtn.removeEventListener('click', addHandler);
                cancelBtn.removeEventListener('click', cancelHandler);

                if (typeof this.config.onSelectionChange === 'function') {
                    this.config.onSelectionChange(this.selections);
                }
            };

            const cancelHandler = () => {
                dialog.style.display = 'none';
                addBtn.removeEventListener('click', addHandler);
                cancelBtn.removeEventListener('click', cancelHandler);
            };

            addBtn.addEventListener('click', addHandler);
            cancelBtn.addEventListener('click', cancelHandler);
        }

        /**
         * Update selections UI
         */
        updateSelectionsUI() {
            const list = document.getElementById('anatomy-selections-list');
            const labels = LABELS[this.config.language] || LABELS.en;

            if (this.selections.length === 0) {
                list.innerHTML = `<p class="no-selection">${labels.noSelection}</p>`;
                return;
            }

            list.innerHTML = this.selections.map((selection, index) => `
                <div class="selection-item" data-index="${index}">
                    <div class="selection-info">
                        <span class="selection-name">${this.config.language === 'vi' ? selection.name_vi : selection.name_en}</span>
                        <span class="selection-type" style="background:${STRUCTURE_COLORS[selection.structure_type] || STRUCTURE_COLORS.region}">
                            ${selection.structure_type}
                        </span>
                    </div>
                    <div class="selection-details">
                        <span class="severity">Severity: ${selection.severity}/10</span>
                        <span class="pain">Pain: ${selection.pain_level}/10</span>
                    </div>
                    <button class="remove-selection" data-index="${index}">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            `).join('');

            // Add remove handlers
            list.querySelectorAll('.remove-selection').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const index = parseInt(btn.getAttribute('data-index'));
                    this.removeSelection(index);
                });
            });
        }

        /**
         * Remove a selection
         */
        removeSelection(index) {
            if (index >= 0 && index < this.selections.length) {
                const removed = this.selections.splice(index, 1)[0];

                // Find and deselect in SVG
                const element = this.currentSvg?.querySelector(`[data-region="${removed.code}"]`);
                if (element) {
                    element.classList.remove('selected');
                    element.style.fill = element.getAttribute('data-original-fill') || '';
                }

                this.updateSelectionsUI();

                if (typeof this.config.onSelectionChange === 'function') {
                    this.config.onSelectionChange(this.selections);
                }
            }
        }

        /**
         * Update breadcrumb navigation
         */
        updateBreadcrumb(regionCode) {
            const breadcrumb = this.container.querySelector('.anatomy-breadcrumb');
            const labels = LABELS[this.config.language] || LABELS.en;

            let html = `<span class="breadcrumb-item home" data-level="0">${labels.breadcrumbHome}</span>`;

            this.navigationStack.forEach((item, index) => {
                const regionData = this.getRegionData(item.regionCode);
                const name = this.config.language === 'vi' ?
                    (regionData?.name_vi || item.regionCode) :
                    (regionData?.name_en || item.regionCode);
                html += ` <i class="fa fa-chevron-right"></i> `;
                html += `<span class="breadcrumb-item" data-level="${index + 1}" data-code="${item.regionCode}">${name}</span>`;
            });

            // Add current region
            if (regionCode && regionCode !== 'body_front' && regionCode !== 'body_back') {
                const regionData = this.getRegionData(regionCode);
                const name = this.config.language === 'vi' ?
                    (regionData?.name_vi || regionCode) :
                    (regionData?.name_en || regionCode);
                html += ` <i class="fa fa-chevron-right"></i> `;
                html += `<span class="breadcrumb-item current">${name}</span>`;
            }

            breadcrumb.innerHTML = html;

            // Add click handlers
            breadcrumb.querySelectorAll('.breadcrumb-item:not(.current)').forEach(item => {
                item.addEventListener('click', () => {
                    const level = parseInt(item.getAttribute('data-level'));
                    this.navigateToLevel(level);
                });
            });
        }

        /**
         * Navigate to a specific level
         */
        navigateToLevel(level) {
            while (this.navigationStack.length > level) {
                this.navigationStack.pop();
            }

            if (level === 0) {
                this.currentLevel = 0;
                this.loadInitialView();
            } else {
                const targetState = this.navigationStack[level - 1];
                if (targetState) {
                    this.currentLevel = level;
                    this.loadSvg(targetState.svgFile, targetState.regionCode);
                }
            }
        }

        /**
         * Get current region code
         */
        getCurrentRegionCode() {
            return this.navigationStack.length > 0 ?
                this.navigationStack[this.navigationStack.length - 1].regionCode :
                'body_front';
        }

        /**
         * Get current SVG file
         */
        getCurrentSvgFile() {
            if (this.navigationStack.length > 0) {
                return this.navigationStack[this.navigationStack.length - 1].svgFile;
            }
            return this.container.querySelector('.view-btn.active')?.getAttribute('data-view') === 'back' ?
                'body-full-back.svg' : 'body-full-front.svg';
        }

        /**
         * Get navigation path as string
         */
        getNavigationPath() {
            return this.navigationStack.map(s => s.regionCode).join('>');
        }

        /**
         * Get region data by code
         */
        getRegionData(code) {
            return this.regionsData[code] || null;
        }

        /**
         * Apply layer visibility
         */
        applyLayerVisibility() {
            if (!this.currentSvg) return;

            this.currentSvg.querySelectorAll('[data-structure-type]').forEach(element => {
                const type = element.getAttribute('data-structure-type');
                element.style.display = this.visibleLayers.includes(type) ? '' : 'none';
            });
        }

        /**
         * Show tooltip
         */
        showTooltip(event, regionCode) {
            let tooltip = document.getElementById('anatomy-tooltip');
            if (!tooltip) {
                tooltip = document.createElement('div');
                tooltip.id = 'anatomy-tooltip';
                tooltip.className = 'anatomy-tooltip';
                document.body.appendChild(tooltip);
            }

            const regionData = this.getRegionData(regionCode);
            const name = this.config.language === 'vi' ?
                (regionData?.name_vi || regionCode) :
                (regionData?.name_en || regionCode);

            tooltip.textContent = name;
            tooltip.style.display = 'block';
            tooltip.style.left = (event.pageX + 10) + 'px';
            tooltip.style.top = (event.pageY + 10) + 'px';
        }

        /**
         * Hide tooltip
         */
        hideTooltip() {
            const tooltip = document.getElementById('anatomy-tooltip');
            if (tooltip) {
                tooltip.style.display = 'none';
            }
        }

        /**
         * Bind global events
         */
        bindEvents() {
            // View selector buttons
            this.container.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.container.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const view = btn.getAttribute('data-view');
                    this.navigationStack = [];
                    this.currentLevel = 0;
                    this.loadSvg(view === 'back' ? 'body-full-back.svg' : 'body-full-front.svg',
                        view === 'back' ? 'body_back' : 'body_front');
                });
            });

            // Layer toggles
            this.container.querySelectorAll('.layer-toggle input').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const layer = checkbox.getAttribute('data-layer');
                    if (checkbox.checked) {
                        if (!this.visibleLayers.includes(layer)) {
                            this.visibleLayers.push(layer);
                        }
                    } else {
                        this.visibleLayers = this.visibleLayers.filter(l => l !== layer);
                    }
                    this.applyLayerVisibility();
                });
            });

            // Zoom controls
            this.container.querySelectorAll('.zoom-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const action = btn.getAttribute('data-action');
                    this.handleZoom(action);
                });
            });

            // Clear button
            const clearBtn = document.getElementById('anatomy-clear-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    this.clearSelections();
                });
            }

            // Save button
            const saveBtn = document.getElementById('anatomy-save-btn');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => {
                    this.saveSelections();
                });
            }

            // Severity/pain sliders
            const severitySlider = document.getElementById('finding-severity');
            if (severitySlider) {
                severitySlider.addEventListener('input', (e) => {
                    document.getElementById('finding-severity-value').textContent = e.target.value;
                });
            }

            const painSlider = document.getElementById('finding-pain');
            if (painSlider) {
                painSlider.addEventListener('input', (e) => {
                    document.getElementById('finding-pain-value').textContent = e.target.value;
                });
            }
        }

        /**
         * Handle zoom actions
         */
        handleZoom(action) {
            if (!this.currentSvg) return;

            const currentScale = parseFloat(this.currentSvg.style.transform?.replace(/[^0-9.]/g, '') || 1);

            switch (action) {
                case 'zoom-in':
                    this.currentSvg.style.transform = `scale(${Math.min(currentScale + 0.25, 3)})`;
                    break;
                case 'zoom-out':
                    this.currentSvg.style.transform = `scale(${Math.max(currentScale - 0.25, 0.5)})`;
                    break;
                case 'reset':
                    this.currentSvg.style.transform = 'scale(1)';
                    break;
            }
        }

        /**
         * Clear all selections
         */
        clearSelections() {
            this.selections = [];

            if (this.currentSvg) {
                this.currentSvg.querySelectorAll('.selected').forEach(el => {
                    el.classList.remove('selected');
                    el.style.fill = el.getAttribute('data-original-fill') || '';
                });
            }

            this.updateSelectionsUI();

            if (typeof this.config.onSelectionChange === 'function') {
                this.config.onSelectionChange(this.selections);
            }
        }

        /**
         * Save selections
         */
        saveSelections() {
            // This will be overridden by form integration
            console.log('Selections to save:', this.selections);
            return this.selections;
        }

        /**
         * Get current selections
         */
        getSelections() {
            return this.selections;
        }

        /**
         * Set selections (for loading saved data)
         */
        setSelections(selections) {
            this.selections = selections || [];
            this.updateSelectionsUI();
        }

        /**
         * Set language
         */
        setLanguage(lang) {
            this.config.language = lang;
            this.createUI();
            this.loadInitialView();
            this.bindEvents();
        }

        /**
         * Destroy the component
         */
        destroy() {
            this.container.innerHTML = '';
            this.selections = [];
            this.navigationStack = [];

            const tooltip = document.getElementById('anatomy-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        }
    }

    // Export to global scope
    window.AnatomySelector = AnatomySelector;

})(window, document);
