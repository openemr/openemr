<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
?>
<table class="header">
  <tr >
        <td class="title"><?php echo out(xl('Add Rule')); ?></td>
        <td>
            <div class="btn-group">
                <a href="index.php?action=add!add" class="iframe_medium btn btn-default btn-save" onclick="top.restoreSession()"><?php echo out(xl('Save')); ?></a>
                <a href="index.php?action=browse!list" class="iframe_medium btn btn-link btn-cancel" onclick="top.restoreSession()"><?php echo out(xl('Cancel')); ?></a>
            </div>
        </td>
  </tr>
</table>
