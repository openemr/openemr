<?php

namespace OpenEMR\OeUi;

class RenderHelper
{
    const SHOW_ON_NEW_ONLY = 'show_new';

    const SHOW_ON_EDIT_ONLY = 'show_edit';

    const SHOW_ALL = 'show_both';

    const HIDE_ALL = 'hide_both';

    public static function displayField($option, $mode)
    {
        if ($option === self::HIDE_ALL) {
            return false;
        }

        if ($option === self::SHOW_ALL) {
            return true;
        }

        if ($mode === 'new' && $option === self::SHOW_ON_NEW_ONLY) {
            return true;
        }

        if ($mode === 'edit' && $option === self::SHOW_ON_EDIT_ONLY) {
            return true;
        }

        return false;
    }
}
