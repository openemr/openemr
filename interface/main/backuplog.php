<?php
/********************************************************************************\
 * Copyright (C) ViCarePlus, Visolve (vicareplus_engg@visolve.com)              *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 \********************************************************************************/
 
/* 
*  Here /interface/globals.php is not referred, because it includes auth.inc. 
*   auth.inc verifies for user authentication information & login session variables. 
*  Pass these variables $webserver_root & $_GLOBALS[backup_log_dir] as parameters for CRON.
*/
include_once ("$argv[1]/library/sqlconf.php");
$backuptime=date("Ymd_His");
$BACKUP_EVENTLOG_DIR = $argv[2] . "/emr_eventlog_backup";
 if (!file_exists($BACKUP_EVENTLOG_DIR))
  {
  mkdir($BACKUP_EVENTLOG_DIR);
  chmod($BACKUP_EVENTLOG_DIR,0777);
}
$BACKUP_EVENTLOG_DIR=$BACKUP_EVENTLOG_DIR.'/eventlog_'.$backuptime.'.sql';
$cmd=$argv[1].'/interface/main/backuplog.sh '.$sqlconf["login"].' '.$sqlconf["pass"].' '.$sqlconf["dbase"].' '.$BACKUP_EVENTLOG_DIR.' '.$sqlconf["host"];
system($cmd);
?>
