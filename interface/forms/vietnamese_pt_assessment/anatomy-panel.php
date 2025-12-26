<?php

/**
 * Vietnamese PT Assessment - Anatomy Panel Component
 *
 * Interactive SVG-based drill-down body diagram for anatomical selection
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2025 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;

// Get language preference
$languagePreference = $formData['language_preference'] ?? 'vi';

// Get existing anatomical selections if editing
$existingSelections = [];
if (!empty($formData['id'])) {
    $selectionsQuery = sqlStatement(
        "SELECT pas.*, ar.name_en, ar.name_vi, ar.structure_type
         FROM pt_anatomical_selections pas
         LEFT JOIN anatomy_regions ar ON pas.region_code = ar.code
         WHERE pas.assessment_id = ?
         ORDER BY pas.created_at",
        [$formData['id']]
    );
    while ($row = sqlFetchArray($selectionsQuery)) {
        $existingSelections[] = $row;
    }
}
?>

<!-- Anatomy Selector Styles -->
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/public/assets/anatomy/anatomy-selector.css">

<div class="card mt-3">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="fa fa-user-md mr-2"></i>
            <?php echo ($languagePreference === 'vi') ?
                'Chọn vùng giải phẫu' :
                'Anatomical Region Selection'; ?>
        </h5>
        <small>
            <?php echo ($languagePreference === 'vi') ?
                'Nhấp vào cơ thể để chọn vùng bị ảnh hưởng. Nhấp lần nữa để xem chi tiết.' :
                'Click on body to select affected regions. Click again to drill down for detail.'; ?>
        </small>
    </div>
    <div class="card-body p-0">
        <!-- Anatomy Selector Container -->
        <div id="anatomy-selector" style="min-height: 550px;"></div>

        <!-- Hidden field to store selections -->
        <input type="hidden" name="anatomical_selections" id="anatomical_selections"
               value="<?php echo attr(json_encode($existingSelections)); ?>">
    </div>
</div>

<!-- Anatomy Selector Script -->
<script src="<?php echo $GLOBALS['webroot']; ?>/library/js/anatomy-selector.js"></script>
<script>
(function() {
    'use strict';

    // Wait for DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize anatomy selector
        const anatomySelector = new AnatomySelector({
            containerId: 'anatomy-selector',
            assetsPath: '<?php echo $GLOBALS['webroot']; ?>/public/assets/anatomy/',
            language: '<?php echo attr($languagePreference); ?>',
            showBreadcrumb: true,
            showLegend: true,
            showLayerControls: true,
            enableTouch: true,

            // Callback when selections change
            onSelectionChange: function(selections) {
                // Update hidden field
                document.getElementById('anatomical_selections').value = JSON.stringify(selections);

                // Update summary count in form header if exists
                const countBadge = document.getElementById('anatomy-selection-count');
                if (countBadge) {
                    countBadge.textContent = selections.length;
                    countBadge.style.display = selections.length > 0 ? 'inline-block' : 'none';
                }
            },

            // Callback when region is clicked
            onRegionClick: function(regionCode, element, options) {
                console.log('Region clicked:', regionCode, options);
            },

            // Callback when drilling down
            onDrillDown: function(regionCode, navigationStack) {
                console.log('Drilled down to:', regionCode);
            },

            // Callback when drilling up
            onDrillUp: function(regionCode, navigationStack) {
                console.log('Drilled up to:', regionCode);
            }
        });

        // Load existing selections if editing
        const existingData = document.getElementById('anatomical_selections').value;
        if (existingData) {
            try {
                const selections = JSON.parse(existingData);
                if (Array.isArray(selections) && selections.length > 0) {
                    // Convert database format to component format
                    const formattedSelections = selections.map(s => ({
                        code: s.region_code,
                        name_en: s.name_en || s.region_code,
                        name_vi: s.name_vi || s.region_code,
                        structure_type: s.structure_type || 'region',
                        path: s.region_path || '',
                        severity: parseInt(s.severity_level) || 5,
                        pain_level: parseInt(s.pain_level) || 5,
                        notes: s.notes_en || s.notes_vi || '',
                        laterality: s.laterality || 'not_applicable'
                    }));
                    anatomySelector.setSelections(formattedSelections);
                }
            } catch (e) {
                console.warn('Could not load existing selections:', e);
            }
        }

        // Expose to global scope for form validation
        window.ptAnatomySelector = anatomySelector;
    });
})();
</script>

<style>
/* Additional form-specific styles */
#anatomy-selector .anatomy-selector-wrapper {
    border-radius: 0;
    border: none;
}

#anatomy-selector .anatomy-header {
    border-radius: 0;
}

/* Selection summary badge */
#anatomy-selection-count {
    background: #4CAF50;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 8px;
}

/* Make SVG container responsive */
@media (max-width: 992px) {
    #anatomy-selector .anatomy-content {
        flex-direction: column;
    }

    #anatomy-selector .anatomy-side-panel {
        width: 100%;
        max-height: 300px;
    }
}

/* Print styles */
@media print {
    #anatomy-selector .anatomy-header,
    #anatomy-selector .anatomy-zoom-controls,
    #anatomy-selector .anatomy-layers,
    #anatomy-selector .selections-actions {
        display: none !important;
    }

    #anatomy-selector .anatomy-svg-container {
        page-break-inside: avoid;
    }
}
</style>
