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

$res = sqlStatement("SELECT * FROM customlists as cl left outer join users as u on cl_creator=u.id WHERE cl_list_type=3 AND cl_deleted=0");
?>
<html>
    <head>
        <title><!-- Insert your title here --></title>
        <?php Header::setupHeader('opener'); ?>
        <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajax_functions_writer.js"></script>

        <script>
        function delete_full_category(id){
                top.restoreSession();
                $.ajax({
                type: "POST",
                url: "ajax_code.php",
                dataType: "html",
                data: {
                     templateid: id,
                     source: "delete_full_category"
                },
                success: function(thedata){
                            alert("<?php echo addslashes(xl('Deleted Successfully.'));?>");
                            document.location.reload();
                            },
                error:function(){
                }
               });
               return;
        }
        function delete_category(id){
            top.restoreSession();
            if(confirm("<?php echo addslashes(xl('Do you want to delete?'));?>")){
                $.ajax({
                type: "POST",
                url: "ajax_code.php",
                dataType: "html",
                data: {
                     templateid: id,
                     source: "delete_category"
                },
                success: function(thedata){
                            if(thedata){
                                alert("<?php echo addslashes('There are currently other users of the category you are trying to delete. Please contact them and ask them to delete it. Categories may not be deleted while in use. This Categories are currently used by \n');?>"+thedata);
                            }
                            else{
                                delete_full_category(id);
                            }
                            },
                error:function(){
                }
               });

               return;
            }
        }
        </script>
    </head>
    <body class="body_top">
    <form name="myform">
        <table align="center">
            <tr class="text reportTableHeadRow">
                <th><?php echo htmlspecialchars('Sl.No', ENT_QUOTES);?></th>
                <th><?php echo htmlspecialchars(xl('Category'), ENT_QUOTES);?></th>
                <th><?php echo htmlspecialchars(xl('Context'), ENT_QUOTES);?></th>
                <th><?php echo htmlspecialchars(xl('Creator'), ENT_QUOTES);?></th>
                <th><?php echo htmlspecialchars(xl('Delete'), ENT_QUOTES);?></th>
            </tr>
    <?php
    $i = 0;
    while ($row = sqlFetchArray($res)) {
        $context = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=?", array($row['cl_list_id']));
        $i++;
        $class = (($class ?? '') == 'reportTableOddRow') ? 'reportTableEvenRow' : 'reportTableOddRow';
        echo "<tr class='text " . htmlspecialchars($class, ENT_QUOTES) . "'>";
        echo "<td>" . $i . "</td>";
        echo "<td>" . htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES) . "</td>";
        echo "<td>" . htmlspecialchars($context['cl_list_item_long'], ENT_QUOTES) . "</td>";
        echo "<td>" . htmlspecialchars($row['fname'] . " " . $row['mname'] . " " . $row['lname'], ENT_QUOTES) . "</td>";
        echo "<td><a href=#>";
        echo "<img src='../../interface/pic/Delete.gif' border=0 title='Delete This Row' onclick=delete_category('" . htmlspecialchars($row['cl_list_slno'], ENT_QUOTES) . "')>";
        echo "</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    ?>
