# Eye Mag Form - Layout and View Options

## Overview

The Eye Mag form is a comprehensive ophthalmologic examination and charting interface in OpenEMR. It provides flexible viewing modes and zone management to accommodate different clinical workflows and preferences.

## View Modes

The form supports three primary view modes, selectable via buttons at the top of the exam section:

### 1. **TEXT Mode** (`#EXAM_TEXT`)
- Displays the clinical examination data in text/list format
- Right panels are hidden
- Shows only the left side clinical entry areas
- All zones display with `PREFS_*_RIGHT=0`
- Ideal for quick data entry and review

### 2. **DRAW Mode** (`#EXAM_DRAW`)
- Displays drawing/sketch panels on the right side for each zone
- Allows freehand annotation on anatomical diagrams
- Canvas-based drawing with color picker
- Stores drawings as images linked to the form
- Zones display with `PREFS_*_RIGHT='DRAW'`

### 3. **QP Mode (Quick Pick)** (`#EXAM_QP`)
- Displays Quick Pick panels with pre-configured finding options
- Right panels show templated selections for common findings
- Faster data entry for standard findings
- Zones display with `PREFS_*_RIGHT='QP'`

## Zone Structure

The form is organized into eight clinical zones:

1. **HPI** - History of Present Illness
2. **PMH** - Past Medical History (synced with HPI for height)
3. **EXT** - External examination
4. **ANTSEG** - Anterior Segment
5. **RETINA** - Posterior segment/Retina
6. **SDRETINA** - Special Retinal views (no left panel)
7. **NEURO** - Neuro-ophthalmic examination
8. **IMPPLAN** - Impression/Plan

## Collapse/Expand State Management

Each zone can be independently collapsed or expanded using the **tabs-left** panel on the left side:

- **Collapsed zones** (`setting_*='0'`): Hide both left and right panels, zone is minimized
- **Expanded zones** (`setting_*='1'`): Display the zone normally
- **Tabs panel visibility**: Automatically hidden if all zones are expanded, shown if any zone is collapsed (excluding SDRETINA)

### Expanding a Collapsed Zone

- Click the zone tab in the left tabs panel
- Click `BUTTON_QP_*` button for that zone
- Click `BUTTON_DRAW_*` button for that zone

### Collapsing an Expanded Zone

- Click the zone tab in the left tabs panel to toggle collapse

## Zone-Specific Controls

Each zone (except SDRETINA) has control buttons:

- **BUTTON_TEXT_*** - Switch zone to TEXT display mode
- **BUTTON_QP_*** - Switch zone to QP display mode  
- **BUTTON_DRAW_*** - Switch zone to DRAW display mode and open drawing panel
- **BUTTON_TEXTD_*** (X button) - Close the right panel (does not collapse zone)

## Preference Storage

Two separate preference systems manage the form state:

### Display Preferences (`PREFS_*_RIGHT`)
- Stored in `form_eye_mag_prefs` table with `PEZONE='PREFS'`
- Values: `'0'` (TEXT), `'QP'`, `'DRAW'`
- Controls which view is shown for each zone
- Independent from collapse state

### Collapse State Preferences (`setting_*`)
- Stored in `user_settings` table with `EyeFormSettings_` prefix
- Values: `'0'` (collapsed), `'1'` (expanded)
- Controls visibility of entire zone
- Independent from display mode preference

## Special Cases

### SDRETINA (Special Retinal Views)
- No left panel (peripheral retina visualization only)
- Right panel can still display DRAW or other modes
- Collapse state does NOT affect tabs-left visibility
- When collapsed, DRAW panel is hidden entirely

### HPI/PMH Sync
- These zones have synchronized height behavior
- When either zone is opened/closed, heights are recalculated
- Prevents layout breaks between clinical and history sections

## Visual Effects

### Panel Transitions
- Panels fade in/out smoothly over 0.3 seconds when toggled
- Uses CSS transitions on `.exam_section_right` class
- Visibility uses `opacity` and `visibility` properties

### Drawing Interface
- Color picker with pencil icon displayed in toolbar
- Tools palette centered in draw panel (width: 400px)
- Color selection via jscolor library
- Undo/Redo/Clear/Blank canvas controls

## Usage Flow

### Typical Workflow
1. Start with TEXT mode for structured data entry
2. Switch to QP mode for quick selections of common findings
3. Switch to DRAW mode for anatomical markups when needed
4. Use collapse/expand to focus on relevant zones
5. All preferences auto-save via AJAX

### Collapsing Unnecessary Zones
1. Click zone tab in left panel to collapse
2. Tabs panel appears automatically
3. Click zone tab again to expand when needed
4. Collapse state persists on page refresh

## Technical Notes

- All preference updates use AJAX POST to `save.php`
- Display preferences and collapse states saved independently
- JavaScript event handlers use delegated binding (`$("[id^='...']")`)
- Canvas images stored in documents table via `C_Document` wrapper
- Font Awesome icons used for UI buttons and controls

## Database Persistence

- Form opens via `view.php` which loads all settings
- Settings populated into hidden inputs: `setting_*`, `PREFS_*_RIGHT`
- AJAX calls to `save.php` persist changes immediately
- Page refresh reloads settings from database (both tables)
- Settings tied to user+encounter, not form record
