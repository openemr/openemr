<?php

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Services\Globals\GlobalSetting;

$fldid ??= '';
$fldarr ??= [];
echo "<div class='row form-group'><div class='col-12'>";
if (
    isset($fldoptions[GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK])
    && is_callable($fldoptions[GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK])
) {
    try {
        $displaySection = ($fldoptions[GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK])(
            $fldid,
            $fldarr
        );
        if (!empty($displaySection)) {
            echo $displaySection;
        }
    } catch (\Throwable $e) {
        ob_end_clean();
        ServiceContainer::getLogger()->error($e->getMessage(), ['exception' => $e]);
        echo xlt("Error in rendering html display section.")
            . xlt("Field name") . " '" . text($fldname) . "'";
    }
}
echo "</div></div>";
