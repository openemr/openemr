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

if (trim($_POST['contextname'] ?? '') != '' && $_POST['action'] == 'add') {
    $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0 AND cl_list_item_long=?", array($_POST['contextname']));
    if (!sqlNumRows($res)) {
        $id = sqlInsert("INSERT INTO customlists (cl_list_type,cl_list_item_long) VALUES(?,?)", array(2,$_POST['contextname']));
        sqlStatement("UPDATE customlists SET cl_list_id=? WHERE cl_list_slno=?", array($id,$id));
    }
} elseif ($_POST['action'] ?? '' == 'delete' && $_POST['item'] != '') {
    sqlStatement("UPDATE customlists SET cl_deleted=1 WHERE cl_list_type=2 AND cl_list_slno=?", array($_POST['item']));
} elseif ($_POST['action'] ?? '' == 'update' && $_POST['item'] != '') {
    sqlStatement("UPDATE customlists SET cl_list_item_long=? WHERE cl_deleted=0 AND cl_list_type=2 AND cl_list_slno=?", array($_POST['updatecontextname'],$_POST['item']));
}
?>
<html>
    <head>
        <?php Header::setupHeader('opener'); ?>
        <style>
            .bottom {
                border-bottom: 1px solid var(--black);
            }
            .top {
                border-top: 1px solid var(--black);
            }
            .left {
                border-left: 1px solid var(--black);
            }
            .right {
                border-right:1px solid var(--black);
            }
            .class1 {
                background-color: #add9e9;
            }
            .class2 {
                background-color: #b1c0a5;
            }
        </style>
        <script>
            $(function () {
            $('#contextadd').hide();
            $('#contextupdate').hide();
            $('#addcontextbtn').click(function() {
               $('#contextadd').show(600);
               $('#contextlist').hide(600);
               $('#addcontextbtn').hide(600);
               return false;
             });
            $('#cancel').click(function() {
               $('#contextadd').hide(600);
               $('#contextlist').show(600);
               $('#addcontextbtn').show(600);
               return false;
             });
            $('#ucancel').click(function() {
               $('#contextupdate').hide(600);
               $('#contextlist').show(600);
               $('#addcontextbtn').show(600);
               return false;
             });
            });
            function checkSubmit(){
                top.restoreSession();
                if(document.getElementById('contextname').value){
                    document.getElementById('action').value='add';
                    document.designation_managment.submit();
                }
                else{
                    alert("<?php echo addslashes(xl('Context name can\'t be empty'));?>");
                }
            }
            function deleteme(id){
                top.restoreSession();
                msg = '';
                CheckContextLive(id);
                stat = document.getElementById('stat').value;
                if(stat==1){
                    msg = "<?php echo addslashes(xl('This context contains categories, which will be deleted. Do you still want to continue?'));?>";
                }
                else{
                    msg = "<?php echo addslashes(xl('Do you want to delete this?'));?>";
                }
                if(confirm(msg)){
                document.getElementById('action').value='delete';
                document.getElementById('item').value=id;
                document.designation_managment.submit();
                }
            }
            function editme(id,val){
                top.restoreSession();
                $('#contextupdate').show(600);
                $('#contextlist').hide(600);
                $('#addcontextbtn').hide(600);
                document.getElementById('item').value=id;
                document.getElementById('updatecontextname').value=val;
            }
            function checkUpdate(){
                top.restoreSession();
                if(document.getElementById('updatecontextname').value){
                document.getElementById('action').value='update';
                document.designation_managment.submit();
                }
                else{
                   alert("<?php echo addslashes(xl('Context name can\'t be empty'));?>");
                }
            }
            function CheckContextLive(id){
                top.restoreSession();
                $.ajax({
                type: "POST",
                url: "ajax_code.php",
                dataType: "html",
                data: {
                     list_id: id,
                     source: "checkcontext"
                },
                async: false,
                success: function(thedata){
                    document.getElementById('stat').value=thedata;
                },
                error:function(){
                    alert("fail");
                }
                });
                return;
            }
     </script>

    </head>
    <body class="body_top">
     <form name="designation_managment" action="" method="post">
        <table cellpadding='2' cellspacing='0' border="0" align="center">

            <tr height="30">
              <td class='title_bar' colspan="4" align="center"><u><?php echo htmlspecialchars(xl('Add Context'), ENT_QUOTES);?></u></td>
              <td class='title_bar' align="center"><a href="#" id="addcontextbtn" class="btn btn-primary"><span><?php echo htmlspecialchars(xl('Add'), ENT_QUOTES);?></span></a></td>
            </tr>
            <tr id="contextlist">
                <td colspan="4">
                    <table>
                        <tr>
                         <td align="center" class="title_bar_top top right bottom left">#</td>
                         <td align="center" class="title_bar_top top right bottom"><?php echo htmlspecialchars(xl('Context'), ENT_QUOTES);?></td>
                         <td align="center" class="title_bar_top top right bottom"><?php echo htmlspecialchars(xl('Edit'), ENT_QUOTES);?></td>
                         <td align="center" class="title_bar_top top right bottom"><?php echo htmlspecialchars(xl('Delete'), ENT_QUOTES);?></td>
                        </tr>
                        <?php
                        $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
                        $i = 0;
                        while ($row = sqlFetchArray($res)) {
                            $i++;
                            $class = ($class ?? '' == 'class1') ? 'class2' : 'class1';
                            ?>
                            <tr class="text <?php echo $class;?>">
                                <td class="right bottom left"><?php echo htmlspecialchars($i, ENT_QUOTES);?></td>
                                <td class="right bottom"><?php echo htmlspecialchars(xl($row['cl_list_item_long']), ENT_QUOTES);?></td>
                                <td class="right bottom"><a href="#" onclick='editme("<?php echo htmlspecialchars($row['cl_list_slno'], ENT_QUOTES);?>","<?php echo htmlspecialchars($row['cl_list_item_long'], ENT_QUOTES);?>")'><img src='<?php echo $GLOBALS['images_static_relative']; ?>/b_edit.png' border=0></a></td>
                                <td class="right bottom"><a href="#" onclick="deleteme(<?php echo htmlspecialchars($row['cl_list_slno'], ENT_QUOTES);?>)"><img src='<?php echo $GLOBALS['images_static_relative']; ?>/deleteBtn.png' border=0></a></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </td>
            </tr>
            <tr id="contextadd">
                <td colspan="3"><input type="text" name="contextname" id="contextname"></td>
                <td colspan="1"><a href="#" onclick="checkSubmit()" class="btn btn-primary"><span><?php echo htmlspecialchars(xl('Save'), ENT_QUOTES);?><a href="#" id="cancel" class="btn btn-secondary"><span><?php echo htmlspecialchars(xl('Cancel'), ENT_QUOTES);?></span></a></span></a></td>
            </tr>
            <tr id="contextupdate">
                <td colspan="3"><input type="text" name="updatecontextname" id="updatecontextname"></td>
                <td colspan="1"><a href="#" onclick="checkUpdate()" class="btn btn-primary"><span><?php echo htmlspecialchars(xl('Update'), ENT_QUOTES);?><a href="#" id="ucancel" class="btn btn-secondary"><span><?php echo htmlspecialchars(xl('Cancel'), ENT_QUOTES);?></span></a></span></a></td>
            </tr>
            <input type="hidden" name="action" id="action">
            <input type="hidden" name="item" id="item">
            <input type="hidden" name="stat" id="stat">
        </table>
     </form>
    </body>
</html>
