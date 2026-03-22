<?php
/**
 * MedEx Recall Board — interface/modules/custom_modules/ loader
 *
 * The actual nav tabs, show_this() override, and AJAX status tracking
 * are all handled by modules/oe-module-medex/recall_buttons_loader.php
 * (loaded first by DisplayService). This file only provides supplemental
 * CSS fixes that the other loader does not cover.
 */
?>
<style>
    /* Ensure the caret toggle for search criteria stays clickable above injected tabs */
    #rcb_caret {
        position: relative !important;
        z-index: 9999 !important;
        pointer-events: auto !important;
    }
</style>