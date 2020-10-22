<?php

/**
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see
 * http://www.gnu.org/licenses/licenses.html#GPL .
 *
 * @package OpenEMR
 * @license http://www.gnu.org/licenses/licenses.html#GPL GNU GPL V3+
 * @author  Sharon Cohen <sharonco@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */

class LBF_Validation
{

    /*If another library is used the key names can be modified here*/
    const VJS_KEY_REQUIRED = 'presence';
    /*
 * Function to generate the constraints used in validation.js library
 * Using the data save in layout options validation
 */
    public static function generate_validate_constraints($form_id)
    {
        //to prevent an empty form id error do :
        if (!$form_id || $form_id == '') {
            return json_encode(array());
        }

        $fres = sqlStatement(
            "SELECT layout_options.*,list_options.notes as validation_json
              FROM layout_options
              LEFT JOIN list_options ON layout_options.validation = list_options.option_id AND list_options.list_id = 'LBF_Validations' AND list_options.activity = 1
              WHERE layout_options.form_id = ? AND layout_options.uor > 0 AND layout_options.field_id != ''
              ORDER BY layout_options.group_id, layout_options.seq ",
            array($form_id)
        );
        $constraints = array();
        $validation_arr = array();
        $required = array();
        while ($frow = sqlFetchArray($fres)) {
            $id = 'form_' . $frow['field_id'];
            $validation_arr = array();
            $required = array();
            //Keep "required" option from the LBF form
            if ($frow['uor'] == 2) {
                $required = array(self::VJS_KEY_REQUIRED => true);
            }

            if ($frow['validation_json']) {
                if (json_decode($frow['validation_json'])) {
                    $validation_arr = json_decode($frow['validation_json'], true);
                } else {
                    trigger_error($frow['validation_json'] . " is not a valid json ", E_USER_WARNING);
                }
            }

            if (!empty($required) || !empty($validation_arr)) {
                $constraints[$id] = array_merge($required, $validation_arr);
            }
        }

        return json_encode($constraints);
    }
}
