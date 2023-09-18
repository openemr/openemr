<?php

/**
 * LayoutsUtils class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2023 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Layouts;

class LayoutsUtils
{
    public static function getListItemTitle($list, $option)
    {
        $row = sqlQuery("SELECT `title` FROM `list_options` WHERE `list_id` = ? AND `option_id` = ? AND activity = 1", [$list, $option]);
        if (empty($row['title'])) {
            return $option;
        }
        return xl_list_label($row['title']);
    }
}
