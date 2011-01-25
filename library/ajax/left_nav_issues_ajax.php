<?php
// Copyright (C) 2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

$sanitize_all_escapes  = true;
$fake_register_globals = false;

require_once("../../interface/globals.php");

$type = $_GET['type'];

echo "// pid = $pid, type = $type\n";

$res = sqlStatement("SELECT * FROM lists WHERE " .
  "pid = ? AND type = ? AND activity = 1 AND enddate IS NULL " .
  "ORDER BY begdate DESC", array($pid, $type));

while ($row = sqlFetchArray($res)) {
  // Note the new sliding menu style requires exactly one <a> tag per list
  // item, so we use embedded <span> tags to serve as the links.
?>
  $('#icontainer_<?php echo $type ?>').append("<li>" +
   "<a href='' id='xxx1' onclick='return false'>" +
   "<span onclick='return repPopup(" +
   "\"../patient_file/summary/add_edit_issue.php?issue=" +
   "<?php echo $row['id']; ?>\")' " +
   "title='<?php echo htmlspecialchars(xl('View/edit issue')); ?>'>" +
   "<?php echo $row['begdate']; ?> </span>" +
   "<span onclick=\"return addEncNotes(<?php echo $row['id']; ?>);\" " +
   "title='<?php echo htmlspecialchars(xl('Add encounter/notes')); ?>'>" +
   "[<?php echo htmlspecialchars(xl('Add')); ?>] </span>" +
   "<span onclick=\"return loadFrame2('ens1','RBot'," +
   "'patient_file/history/encounters.php?issue=<?php echo $row['id']; ?>')\" " +
   "title='<?php echo htmlspecialchars(xl('List encounters')); ?>'>" +
   "<?php echo htmlspecialchars($row['title']); ?></span></a></li>");
<?php
}
?>
