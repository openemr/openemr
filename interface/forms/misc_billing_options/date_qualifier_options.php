<?php
/**
 * Reusable data entries for new Box 14 and Box 15 date qualifiers that are part of 
 * HCFA 1500 02/12 format
 * 
 * For details on format refer to: 
 * <http://www.nucc.org/index.php?option=com_content&view=article&id=186&Itemid=138>
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */

$box_14_qualifier_options=array([xl("Onset of Current Symptoms or Illness"),"431"],
                                            [xl("Last Menstrual Period"),"484"]);

$box_15_qualifier_options=array([xl("Initial Treatment"),"454"],
                                           [xl("Latest Visit or Consultation"),"304"],
                                           [xl("Acute Manifestation of a Chronic Condition"),"453"],
                                           [xl("Accident"),"439"], 
                                           [xl("Last X-ray"),"455"], 
                                           [xl("Prescription"),"471"], 
                                           [xl("Report Start (Assumed Care Date)"),"090"], 
                                           [xl("Report End (Relinquished Care Date)"),"091"], 
                                           [xl("First Visit or Consultation"),"444"] 
                                            );
$hcfa_date_quals=array("box_14_date_qual"=>$box_14_qualifier_options,"box_15_date_qual"=>$box_15_qualifier_options);
function qual_id_to_description($qual_type,$value)
{
    $options=$GLOBALS['hcfa_date_quals'][$qual_type];
    for($idx=0;$idx<count($options);$idx++)
    {
        if($options[$idx][1]==$value)
        {
            return $options[$idx][0];
        }
    }
    return null;
}
?>