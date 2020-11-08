<?php

/**
 * Reusable data entries for new Box 14 and Box 15 date qualifiers that are part of
 * HCFA 1500 02/12 format
 *
 * For details on format refer to:n
 * <http://www.nucc.org/index.php?option=com_content&view=article&id=186&Itemid=138>
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @copyright Copyright (C) 2017 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function generateDateQualifierSelect($name, $options, $obj)
{
    echo     "<select name='" . attr($name) . "'>";
    for ($idx = 0; $idx < count($options); $idx++) {
        echo "<option value='" . attr($options[$idx][1]) . "'";
        if (!empty($obj[$name]) && ($obj[$name] == $options[$idx][1])) {
            echo " selected";
        }

        echo ">" . text($options[$idx][0]) . "</option>";
    }

    echo     "</select>";
}

function genReferringProviderSelect($selname, $toptext, $default = 0, $disabled = false)
{
    $query = "SELECT id, lname, fname FROM users WHERE npi != '' ORDER BY lname, fname";
    $res = sqlStatement($query);
    echo "<select name='" . attr($selname) . "'";
    if ($disabled) {
        echo " disabled";
    }

    echo ">";
    echo "<option value=''>" . text($toptext);
    while ($row = sqlFetchArray($res)) {
        $provid = $row['id'];
        echo "<option value='" . attr($provid) . "'";
        if ($provid == $default) {
            echo " selected";
        }

        echo ">" . text($row['lname'] . ", " . $row['fname']);
    }

    echo "</select>\n";
}

$box_14_qualifier_options = array(array(xl("Onset of Current Symptoms or Illness"),"431"),
                                            array(xl("Last Menstrual Period"),"484"));

$box_15_qualifier_options = array(array(xl("Initial Treatment"),"454"),
                                           array(xl("Latest Visit or Consultation"),"304"),
                                           array(xl("Acute Manifestation of a Chronic Condition"),"453"),
                                           array(xl("Accident"),"439"),
                                           array(xl("Last X-ray"),"455"),
                                           array(xl("Prescription"),"471"),
                                           array(xl("Report Start (Assumed Care Date)"),"090"),
                                           array(xl("Report End (Relinquished Care Date)"),"091"),
                                           array(xl("First Visit or Consultation"),"444")
                                            );
$hcfa_date_quals = array("box_14_date_qual" => $box_14_qualifier_options,"box_15_date_qual" => $box_15_qualifier_options);

function qual_id_to_description($qual_type, $value)
{
    $options = $GLOBALS['hcfa_date_quals'][$qual_type];
    for ($idx = 0; $idx < count($options); $idx++) {
        if ($options[$idx][1] == $value) {
            return $options[$idx][0];
        }
    }

    return null;
}
