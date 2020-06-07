<?php

// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Jacob T Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+



require_once("../../interface/globals.php");

use OpenEMR\Core\Header;

$list_id = $_REQUEST['list_id'];
?>
<html>
    <head>
        <?php Header::setupHeader(); ?>
        <script>
        function add_template(){
                top.restoreSession();
                len = document.getElementById('provider').options.length;
                sel_len=0;
                val="";
                for(i=0;i<len;i++){
                   if(document.getElementById('provider').options[i].selected==true){
                    sel_len++;
                    val+=document.getElementById('provider').options[i].value+"|";
                   }
                }
                if(sel_len>0){
                $.ajax({
                type: "POST",
                url: "ajax_code.php",
                dataType: "html",
                data: {
                     list_id: <?php echo htmlspecialchars($list_id, ENT_QUOTES);?>,
                     multi: val,
                     source: "save_provider"
                },
                async: false,
                success: function(thedata){

                            },
                error:function(){
                    alert("fail");
                }
                });
                dlgclose();
                return;
                }
                else{
                    alert("<?php echo addslashes(xl('You should select at least one Provider'));?>");
                }

        }
        </script>
    </head>
    <body class="body_top">
        <form >
            <table>
                <tr class="text">
                    <td>
                        <select multiple name="provider[]" id="provider" size="5">
                            <?php
                            $query = "SELECT id, lname, fname FROM users WHERE authorized = 1 AND username != '' " .
                                    "AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) ORDER BY lname, fname";
                            $res = sqlStatement($query);
                            $sel_query = "SELECT tu_user_id FROM template_users WHERE tu_template_id=?";
                            $row_sel = sqlQuery($sel_query, array($list_id));
                            while ($row = sqlFetchArray($res)) {
                                foreach ($row_sel as $key => $value) {
                                    if ($value == $row['id']) {
                                        $sel = "selected";
                                    } else {
                                        $sel = '';
                                    }
                                }
                                echo "<option value='" . htmlspecialchars($row['id'], ENT_QUOTES) . "' $sel>" . htmlspecialchars($row['lname'] . "," . $row['fname'], ENT_QUOTES) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                    <a href="#" onclick="add_template()" class="btn btn-primary"><span><?php echo htmlspecialchars(xl('Save'), ENT_QUOTES);?></span></a>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>
