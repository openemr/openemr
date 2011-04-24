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
        <td class="title"><?php echo out( xl( 'Add Rule' ) ); ?></td>
        <td>
            <a href="index.php?action=add!add" class="iframe_medium css_button" onclick="top.restoreSession()"><span><?php echo out( xl( 'Save' ) ); ?></span></a>
            <a href="index.php?action=browse!list" class="iframe_medium css_button" onclick="top.restoreSession()"><span><?php echo out( xl( 'Cancel') ); ?></span></a>
        </td>
  </tr>
</table>
