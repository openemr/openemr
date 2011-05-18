<?php
 // Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

    //log out by killing the session
	session_start();
	session_destroy();

    //redirect to pretty login/logout page
	header('Location: index.php?logout');
    // 
?>
