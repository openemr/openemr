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

$content = $_REQUEST['content'];
?>
<!DOCTYPE html>
<html>
    <head>
        <?php Header::setupHeader('opener'); ?>
        <script>
            function showWhereInTextarea() {
                opener.restoreSession();
                var textarea = document.getElementById('quest');
                start = textarea.value.indexOf("??");
                len = 2;
                if (textarea.setSelectionRange) {
                    textarea.setSelectionRange(parseInt(start), (parseInt(start) + parseInt(len)));
                } else {
                    var range = textarea.createTextRange();
                    range.collapse(true);

                    range.moveStart('character', parseInt(start));
                    range.moveEnd('character', parseInt(len));
                    range.select();
                }
                document.getElementById('quest').focus();
            }

            function replace_quest(val) {
                opener.restoreSession();
                var textarea = document.getElementById('quest').value;
                textarea = textarea.replace(/\?\?/i, val);
                document.getElementById('quest').value = textarea;
            }

            function save_this() {
                opener.restoreSession();
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
                    <?php
                    $res = sqlStatement("SELECT * FROM list_options WHERE list_id = 'nation_notes_replace_buttons' AND activity = 1 ORDER BY seq");
                    while ($row = sqlFetchArray($res)) {
                        ?>
                    <a href="#" onclick="replace_quest('<?php echo htmlspecialchars($row['option_id'], ENT_QUOTES);?>')" class="btn btn-primary"><span><?php echo htmlspecialchars($row['title'], ENT_QUOTES);?></span></a>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr class="text">
                <td>
                <textarea name="quest" id="quest" rows="12" cols="70"><?php echo htmlspecialchars($content, ENT_QUOTES);?></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="button" name="save" value="<?php echo htmlspecialchars(xl('Save'), ENT_QUOTES);?>" onclick="save_this()">
                    <input type="button" name="cancel" value="<?php echo htmlspecialchars(xl('Cancel'), ENT_QUOTES);?>" onclick="javascript:window.close()">
                </td>
            </tr>
        </table>

    </body>
</html>
