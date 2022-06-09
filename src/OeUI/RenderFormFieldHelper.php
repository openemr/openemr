<?php

namespace OpenEMR\OeUi;

class RenderFormFieldHelper
{
    const SHOW_ON_NEW_ONLY = 'show_new';

    const SHOW_ON_EDIT_ONLY = 'show_edit';

    const SHOW_ALL = 'show_both';

    const HIDE_ALL = 'hide_both';

    const DEFAULT_FORM_RENDER_OPTIONS = [
        RenderFormFieldHelper::SHOW_ON_NEW_ONLY => 'Show on New Form Only',
        RenderFormFieldHelper::SHOW_ON_EDIT_ONLY => 'Show on Edit Form Only',
        RenderFormFieldHelper::SHOW_ALL => 'Show on New and Edit Form',
        RenderFormFieldHelper::HIDE_ALL => 'Hide on New and Edit Form',
    ];

    public static function shouldDisplayFormField($option, $mode)
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
