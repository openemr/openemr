<?php

use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Common\Logging\SystemLogger;

$fldid = $fldid ?? '';
$fldarr = $fldarr ?? [];
echo "<div class='row form-group'><div class='col-12'>";
if (
    isset($fldoptions[GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK])
    && is_callable($fldoptions[GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK])
) {
    try {
        $displaySection = call_user_func(
            $fldoptions[GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK],
            $fldid,
            $fldarr
        );
        if (!empty($displaySection)) {
            echo $displaySection;
        }
    } catch (\Exception $e) {
        ob_end_clean();
        (new SystemLogger())->errorLogCaller($e->getMessage(), ['trace' => $e->getMessage()]);
        echo xlt("Error in rendering html display section.")
            . xlt("Field name") . " '" . text($fldname) . "'";
    }
}
echo "</div></div>";
