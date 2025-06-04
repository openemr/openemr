<?php

namespace OpenEMR\Common\Forms;

class FormActionBarSettings
{
    const ACTION_BAR_DISPLAY_FORM_TOP = '0';
    const ACTION_BAR_DISPLAY_FORM_BOTTOM = '1';

    // TODO: @adunsulag demographics_full.php does NOT like the TOP_AND_BOTTOM option so going to skip this for now as a valid setting.
    const ACTION_BAR_DISPLAY_FORM_TOP_AND_BOTTOM = '2';
    public static function getGlobalSettingsList()
    {
        return array(
            self::ACTION_BAR_DISPLAY_FORM_TOP => xl('Top of Form (default)')
            ,self::ACTION_BAR_DISPLAY_FORM_BOTTOM => xl('Bottom of Form')
//            ,self::ACTION_BAR_DISPLAY_FORM_TOP_AND_BOTTOM => xl('Top and Bottom of Form')
        );
    }

    public static function getDefaultSetting()
    {
        return self::ACTION_BAR_DISPLAY_FORM_TOP;
    }

    public static function shouldDisplayTopActionBar()
    {
        // probably could make this more efficient by doing integer position comparisons, but the global values are stored as strings...
        return $GLOBALS['form_actionbar_position'] == self::ACTION_BAR_DISPLAY_FORM_TOP
            || $GLOBALS['form_actionbar_position'] == self::ACTION_BAR_DISPLAY_FORM_TOP_AND_BOTTOM;
    }
    public static function shouldDisplayBottomActionBar()
    {
        return $GLOBALS['form_actionbar_position'] == self::ACTION_BAR_DISPLAY_FORM_BOTTOM
            || $GLOBALS['form_actionbar_position'] == self::ACTION_BAR_DISPLAY_FORM_TOP_AND_BOTTOM;
    }
}
