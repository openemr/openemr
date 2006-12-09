<?php
// Cloned from usergroup.php for new layout.

include_once("../globals.php");
include_once("../../library/acl.inc");
?>
<HTML>
<HEAD>
</HEAD>
<frameset rows="<?php echo $GLOBALS['navBarHeight'] ?>,*" frameborder="NO" border="0" framespacing="0">
  <frame src="usergroup_navigation.php" name="Navigation" scrolling="no" noresize frameborder="NO">
  <frame
<? if (acl_check('admin', 'users')) { ?>
   src="usergroup_admin.php"
<? } else if (acl_check('admin', 'forms')) { ?>
   src="../forms_admin/forms_admin.php"
<? } else if (acl_check('admin', 'practice')) { ?>
   src="<?php echo $GLOBALS['webroot'] ?>/controller.php?practice_settings"
<? } else if (acl_check('admin', 'calendar')) { ?>
   src="../main/calendar/index.php?module=PostCalendar&type=admin&func=modifyconfig"
<? } else if (acl_check('admin', 'database')) { ?>
   src="../main/myadmin/index.php"
<? } else { ?>
   src="<?php echo $rootdir ?>/logview/logview.php"
<? } ?>
   name="Main" scrolling="auto" noresize frameborder="NO">
</frameset>
</HTML>
