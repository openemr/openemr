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
$content = $_REQUEST['content'];
?>
<html>
    <head>
        <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
        <script type="text/javascript">
    function showWhereInTextarea(){
    top.restoreSession();
    var textarea = document.getElementById('quest');
    start = textarea.value.indexOf("??");
    len =2;
    if(textarea.setSelectionRange){
        textarea.setSelectionRange(parseInt(start), (parseInt(start)+parseInt(len)));
    }
    else{
        var range = textarea.createTextRange();
        range.collapse(true);
        
        range.moveStart('character',parseInt(start) );
        range.moveEnd('character',parseInt(len));
        range.select();
        
    }
    document.getElementById('quest').focus();
    }
    function replace_quest(val){
        top.restoreSession();
        var textarea = document.getElementById('quest').value;
        textarea=textarea.replace(/\?\?/i,val);
        document.getElementById('quest').value=textarea;
    }
    function save_this(){
            top.restoreSession();
            var textFrom = document.getElementById('quest').value;
            window.opener.CKEDITOR.instances.textarea1.insertText(textFrom);
            window.close();
    }
        </script>
    </head>
    <body class="body_top" onload="showWhereInTextarea()">
        <table>
            <tr class="text">
                <td>
                    <a href="#" onclick="replace_quest('Yes')" class="css_button"><span><?php echo htmlspecialchars(xl('Yes'),ENT_QUOTES);?></span></a>
                    <a href="#" onclick="replace_quest('No')" class="css_button"><span><?php echo htmlspecialchars(xl('No'),ENT_QUOTES);?></span></a>
                    <a href="#" onclick="replace_quest('Normal')" class="css_button"><span><?php echo htmlspecialchars(xl('Normal'),ENT_QUOTES);?></span></a>
                    <a href="#" onclick="replace_quest('Abnormal')" class="css_button"><span><?php echo htmlspecialchars(xl('Abnormal'),ENT_QUOTES);?></span></a>
                </td>
            </tr>
            <tr class="text">
                <td>
                <textarea name="quest" id="quest" rows="5" cols="70"><?php echo htmlspecialchars($content,ENT_QUOTES);?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="button" name="save" value="<?php echo htmlspecialchars(xl('Save'),ENT_QUOTES);?>" onclick="save_this()">
                    <input type="button" name="cancel" value="<?php echo htmlspecialchars(xl('Cancel'),ENT_QUOTES);?>" onclick="javascript:window.close()">
                </td>
            </tr>
        </table>    
        
    </body>
</html>