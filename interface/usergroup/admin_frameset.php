<?php
// Cloned from usergroup.php for new layout.

include_once("../globals.php");
include_once("../../library/acl.inc");
?>
<HTML>
<HEAD>
</HEAD>
<frameset rows="<?php echo $GLOBALS['navBarHeight'] ?>,*" frameborder="1"
 border="1" framespacing="1" bordercolor="#000000">
  <frame src="usergroup_navigation.php" name="Navigation" scrolling="no" frameborder="1" noresize>
  <frame
<?php if (acl_check('admin', 'users')) { ?>
   src="usergroup_admin.php"
<?php } else if (acl_check('admin', 'forms')) { ?>
   src="../forms_admin/forms_admin.php"
<?php } else if (acl_check('admin', 'practice')) { ?>
   src="<?php echo $GLOBALS['webroot'] ?>/controller.php?practice_settings"
<?php } else if (acl_check('admin', 'calendar')) { ?>
   src="../main/calendar/index.php?module=PostCalendar&type=admin&func=modifyconfig"
<?php } else if (acl_check('admin', 'database')) { ?>
   src="../main/myadmin/index.php"
<?php } else { ?>
   src="<?php echo $rootdir ?>/logview/logview.php"
<?php } ?>
   name="Main" scrolling="auto" frameborder="0" noresize>
</frameset>
</HTML>
