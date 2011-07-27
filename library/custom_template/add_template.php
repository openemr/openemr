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

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../interface/globals.php");
$list_id = $_REQUEST['list_id'];
?>
<html>
    <head>
        <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
        <script type="text/javascript">
        function add_template(){
            top.restoreSession();
            mainform=window.parent.parent.document;
            if(document.getElementById('template_name').value){
                len = document.getElementById('multi_context').options.length;
                sel_len=0;
                val="";
                for(i=0;i<len;i++){
                   if(document.getElementById('multi_context').options[i].selected==true){
                    sel_len++;
                    val+=document.getElementById('multi_context').options[i].value+"|";
                   }
                }
                if(sel_len>0){
                $.ajax({
                type: "POST",
                url: "ajax_code.php",
                dataType: "html",
                data: {
                     templateid: document.getElementById('template_name').value,
                     list_id: <?php echo $list_id;?>,
                     multi: val,
                     source: "add_template"
                },
                async: false,
                success: function(thedata){
                        if(thedata=="Fail"){
                            alert(document.getElementById('template_name').value+" <?php echo addslashes(xl('already exists'));?>");
                            return false;
                        }
                        else{
                            mainform.getElementById('templateDD').innerHTML = thedata;
                            alert("<?php echo addslashes(xl('Successfully added category'));?> "+document.getElementById('template_name').value);
                            window.parent.parent.location.reload();
                        }
                            },
                error:function(){
                    
                }	
                });
                }
                else{
                    alert("<?php echo addslashes(xl('You should select at least one context'));?>");
                }
            }
            else{
                alert("<?php echo addslashes(xl('Category name is empty'));?>");
                return false;
            }
        }
        </script>
    </head>
    <body class="body_top">
        <form >
            <table>
                <tr class="text">
                    <td>
                        <select multiple name="multi_context[]" id="multi_context" size="5">
                            <?php
                            $res = sqlStatement("SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0");
                            while($row=sqlFetchArray($res)){
                            echo "<option value='".htmlspecialchars($row['cl_list_id'],ENT_QUOTES)."'>".htmlspecialchars($row['cl_list_item_long'],ENT_QUOTES)."</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                    <input type="text" name="template_name" id="template_name">
                    </td>
                    <td>
                    <a href="#" onclick="add_template()" class="css_button"><span><?php echo htmlspecialchars(xl('ADD'),ENT_QUOTES);?></span></a>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>