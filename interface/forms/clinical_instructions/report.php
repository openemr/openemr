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
// Author:   Jacob Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+

include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");
function clinical_instructions_report($pid, $encounter, $cols, $id) {
    $count = 0;
    $data = formFetch("form_clinical_instructions", $id);
    if ($data) {
        ?>
        <table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Instructions'); ?></span></td>
            </tr>            
            <tr>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo nl2br(text($data['instruction'])); ?></span></td>
            </tr>
        </table>
        <?php
    }
}
?> 
