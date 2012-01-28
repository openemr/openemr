<?php
// Copyright (C) 2012 by following Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Function to check and/or sanitize things for security such as
//   directories names, file names, etc.
//

// If the label contains any illegal characters, then the script will die.
function check_file_dir_name($label) {
  if (empty($label) || preg_match('/[^A-Za-z0-9_.-]/', $label))
    die(xlt("ERROR: The following variable contains invalid characters").": ".$label);
}

?>
