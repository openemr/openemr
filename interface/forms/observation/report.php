<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Jacob T Paul <jacob@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

function observation_report($pid, $encounter, $cols, $id) {
    $count = 0;
    $sql = "SELECT * FROM `form_observation` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($id,$_SESSION["pid"], $_SESSION["encounter"]));
    


    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
        $data[$iter] = $row;
    if ($data) {
        print "<table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>".xlt('Code')."</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>".xlt('Description')."</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>".xlt('Code Type')."</span></td> 
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>".xlt('Table Code')."</span></td> 
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>".xlt('Value')."</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>".xlt('Unit')."</span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold>".xlt('Date')."</span></td>
            </tr>";
        foreach ($data as $key => $value) {
            if($value['code'] == 'SS003') {
              if($value['ob_value'] == '261QE0002X') 
                $value['ob_value'] ='Emergency Care';
              else if($value['ob_value'] == '261QM2500X')
                $value['ob_value'] ='Medical Specialty';
              else if($value['ob_value'] == '261QP2300X')
                $value['ob_value'] ='Primary Care';
              else if($value['ob_value'] == '261QU0200X')
                $value['ob_value'] ='Urgent Care';
            }
            if($value['code'] == '21612-7') {
              if($value['ob_unit'] == 'd') 
                $value['ob_unit'] ='Day';
              else if($value['ob_unit'] == 'mo')
                $value['ob_unit'] ='Month';
              else if($value['ob_unit'] == 'UNK')
                $value['ob_unit'] ='Unknown';
              else if($value['ob_unit'] == 'wk')
                $value['ob_unit'] ='Week';
              else if($value['ob_unit'] == 'a')
                $value['ob_unit'] ='Year';
            }
            print "<tr>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>".text($value['code'])."</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>".text($value['description'])."</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>".text($value['code_type'])."</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>".text($value['table_code'])."</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>".text($value['ob_value'])."</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>".text($value['ob_unit'])."</span></td>
                        <td style='border:1px solid #ccc;padding:4px;'><span class=text>".text($value['date'])."</span></td>
                    </tr>";
            print "\n";
        }
        print "</table>";
    }
}
?> 
