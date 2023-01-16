<?php

/**
 * RenderFormFieldHelper.
 *
 * @package   OpenEMR
 * @subpackage OeUI
 * @link      https://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022-2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\OeUI;

/**
 * RenderFormFieldHelper
 *
 * Helper class to decide if a form field should be rendered based on a variety
 * of possible settings.
 */
class RenderFormFieldHelper
{
    const SHOW_ON_NEW_ONLY = 'show_new';

    const SHOW_ON_EDIT_ONLY = 'show_edit';

    const SHOW_ALL = 'show_both';

    const HIDE_ALL = 'hide_both';

    const FORM_STATE_NEW = 'new';

    const FORM_STATE_EDIT = 'edit';

    const DEFAULT_FORM_RENDER_OPTIONS = [
        RenderFormFieldHelper::SHOW_ON_NEW_ONLY => 'Show on New Form Only',
        RenderFormFieldHelper::SHOW_ON_EDIT_ONLY => 'Show on Edit Form Only',
        RenderFormFieldHelper::SHOW_ALL => 'Show on New and Edit Form',
        RenderFormFieldHelper::HIDE_ALL => 'Hide on New and Edit Form',
    ];

    /**
     * shouldDisplayFormField
     *
     * Static function returning a boolean if the given field should be displayed
     * based on the current mode of the form.
     *
     * @param string $option The condition to test
     * @param string $mode The current state of the form field
     * @return boolean
     */
    public static function shouldDisplayFormField($option, $mode)
    {
        if ($option === self::SHOW_ALL) {
            return true;
        }

        if ($mode === self::FORM_STATE_NEW && $option === self::SHOW_ON_NEW_ONLY) {
            return true;
        }

        if ($mode === self::FORM_STATE_EDIT && $option === self::SHOW_ON_EDIT_ONLY) {
            return true;
        }

        // We don't check for $option === HIDE_ALL as the default return value of this form is false
        return false;
    }
}
