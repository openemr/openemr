<?php
/* Copyright © 2010 by Andrew Moore */
/* Licensing information appears at the end of this file. */

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) );
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/..');
require_once 'acl.inc';

class Installer
{

  public function __construct( $cgi_variables, $manualPath )
  {
    $this->login     = $cgi_variables['login'];
    $this->iuser     = $cgi_variables['iuser'];
    $this->iuname    = $cgi_variables['iuname'];
    $this->igroup    = $cgi_variables['igroup'];
    $this->pass      = $cgi_variables['pass'];
    $this->server    = $cgi_variables['server'];
    $this->port      = $cgi_variables['port'];
    $this->root      = $cgi_variables['root'];
    $this->rootpass  = $cgi_variables['rootpass'];
    $this->dbname    = $cgi_variables['dbname'];
    $this->collate   = $cgi_variables['collate'];
    $this->loginhost = 'localhost';

    $this->manualPath = $manualPath;
    $this->conffile  = $this->manualPath . 'library/sqlconf.php';
    $this->openemrBasePath = $cgi_variables["openemrBasePath"];
    $this->openemrWebPath = $cgi_variables["openemrWebPath"];

    $this->gaclSetupScript1 = $this->manualPath . "gacl/setup.php";
    $this->gaclSetupScript2 = $this->manualPath . "acl_setup.php";

    // Make this true for IPPF.
    $this->ippf_specific = false;

    $this->error_message = '';
    $this->dbh = false;
    include_once($this->conffile);
  }

  public function login_is_valid()
  {
    if ( ($this->login == '') || (! isset( $this->login )) ) {
      $this->error_message = "login is invalid: '$this->login'";
      return FALSE;
    }
    return TRUE;
  }

  public function iuser_is_valid()
  {
    if ( strpos($this->iuser, " ") ) {
      $this->error_message = "Initial user is invalid: '$this->iuser'";
      return FALSE;
    }
    return TRUE;
  }

  public function password_is_valid()
  {
    if ( $this->pass == "" || !isset($this->pass) ) {
      $this->error_message = "Initial user password is invalid: '$this->pass'";
      return FALSE;
    }
    return TRUE;
  }

  public function root_database_connection()
  {
    $this->dbh = $this->connect_to_database( $this->server, $this->root, $this->rootpass );
    if ( $this->dbh ) {
      return $this->dbh;
    } else {
      $this->error_message = 'unable to connect to database as root';
      return False;
    }
  }

  public function user_database_connection()
  {
    $this->dbh = $this->connect_to_database( $this->server, $this->login, $this->pass );
    if ( ! $this->dbh ) {
      $this->error_message = "unable to connect to database as user: '$this->login'";
      return FALSE;
    }
    if ( ! $this->set_collation() ) {
      return FALSE;
    }
    if ( ! mysql_select_db($this->dbname, $this->dbh) ) {
      $this->error_message = "unable to select database: '$this->dbname'";
      return FALSE;
    }
    return $this->dbh;
  }

  public function create_database() {
    $sql = "create database $this->dbname";
    if ($this->collate) {
      $sql .= " character set utf8 collate $this->collate";
      $this->set_collation();
    }
    return $this->execute_sql($sql);
  }

  public function drop_database() {
   $sql = "drop database if exists $this->dbname";
   return $this->execute_sql($sql);
  }

  public function grant_privileges() {
    return $this->execute_sql( "GRANT ALL PRIVILEGES ON $this->dbname.* TO '$this->login'@'$this->loginhost' IDENTIFIED BY '$this->pass'" );
  }

  public function disconnect() {
    return mysql_close($this->dbh);
  }

  public function load_dumpfiles() {
    $sql_results = ''; // information string which is returned
    foreach ($this->dumpfiles() as $filename => $title) {
        $sql_results .= "Creating $title tables...\n";
	// mysql_query("USE $dbname",$dbh);
	$fd = fopen($filename, 'r');
	if ($fd == FALSE) {
          $this->error_message = "ERROR.  Could not open dumpfile '$filename'.\n";
          return FALSE;
	}
	$query = "";
	$line = "";
	while (!feof ($fd)){
		$line = fgets($fd,1024);
		$line = rtrim($line);
		if (substr($line,0,2) == "--") // Kill comments
			continue;
		if (substr($line,0,1) == "#") // Kill comments
			continue;
		if ($line == "")
			continue;
		$query = $query.$line;		// Check for full query
		$chr = substr($query,strlen($query)-1,1);
		if ($chr == ";") { // valid query, execute
			$query = rtrim($query,";");
			$this->execute_sql( $query );
			$query = "";
		}
	}
	$sql_results .= "OK<br>\n";
	fclose($fd);
    }
    return $sql_results;
  }

  public function add_initial_user() {
    //echo "INSERT INTO groups VALUES (1,'$igroup','$iuser')<br>\n";
    if ($this->execute_sql("INSERT INTO groups (id, name, user) VALUES (1,'$this->igroup','$this->iuser')") == FALSE) {
      $this->error_message = "ERROR. Unable to add initial user group\n" .
        "<p>".mysql_error()." (#".mysql_errno().")\n";
      return FALSE;
	}
    if ($this->execute_sql("INSERT INTO users (id, username, password, authorized, lname, fname, facility_id, calendar, cal_ui) VALUES (1,'$this->iuser','1a1dc91c907325c69271ddf0c944bc72',1,'$this->iuname','',3,1,3)") == FALSE) {
      $this->error_message = "ERROR. Unable to add initial user\n" .
        "<p>".mysql_error()." (#".mysql_errno().")\n";
      return FALSE;
    }
    return TRUE;
  }

  public function write_configuration_file() {
    @touch($this->conffile); // php bug
    $fd = @fopen($this->conffile, 'w');
    $string = '<?php
//  OpenEMR
//  MySQL Config
//  Referenced from sql.inc

';

    $it_died = 0;   //fmg: variable keeps running track of any errors

    fwrite($fd,$string) or $it_died++;
    fwrite($fd,"\$host\t= '$this->server';\n") or $it_died++;
    fwrite($fd,"\$port\t= '$this->port';\n") or $it_died++;
    fwrite($fd,"\$login\t= '$this->login';\n") or $it_died++;
    fwrite($fd,"\$pass\t= '$this->pass';\n") or $it_died++;
    fwrite($fd,"\$dbase\t= '$this->dbname';\n\n") or $it_died++;
    fwrite($fd,"//Added ability to disable\n") or $it_died++;
    fwrite($fd,"//utf8 encoding - bm 05-2009\n") or $it_died++;
    fwrite($fd,"\$disable_utf8_flag = false;\n") or $it_died++;

$string = '
$sqlconf = array();
$sqlconf["host"]= $host;
$sqlconf["port"] = $port;
$sqlconf["login"] = $login;
$sqlconf["pass"] = $pass;
$sqlconf["dbase"] = $dbase;
//////////////////////////
//////////////////////////
//////////////////////////
//////DO NOT TOUCH THIS///
$config = 1; /////////////
//////////////////////////
//////////////////////////
//////////////////////////
?>
';
?><?php // done just for coloring

      fwrite($fd,$string) or $it_died++;

//it's rather irresponsible to not report errors when writing this file.
 if ($it_died != 0) {
   $this->error_message = "ERROR. Couldn't write $it_died lines to config file '$this->conffile'.\n";
   return FALSE;
 }
 fclose($fd);

 return TRUE;
  }

  public function insert_globals() {
    require_once("library/globals.inc.php");
    foreach ($GLOBALS_METADATA as $grpname => $grparr) {
      foreach ($grparr as $fldid => $fldarr) {
        list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
        if (substr($fldtype, 0, 2) !== 'm_') {
          $res = $this->execute_sql("SELECT count(*) AS count FROM globals WHERE gl_name = '$fldid'");
          $row = @mysql_fetch_array($res, MYSQL_ASSOC);
          if (empty($row['count'])) {
            $this->execute_sql("INSERT INTO globals ( gl_name, gl_index, gl_value ) " .
                           "VALUES ( '$fldid', '0', '$flddef' )");
          }
        }
      }
    }
  }

  public function install_gacl()
  {
    $install_results_1 = $this->get_require_contents($this->gaclSetupScript1);
    if (! $install_results_1 ) {
      $this->error_message = "install_gacl failed: unable to require gacl script 1";
      return FALSE;
    }

    $install_results_2 = $this->get_require_contents($this->gaclSetupScript2);
    if (! $install_results_2 ) {
      $this->error_message = "install_gacl failed: unable to require gacl script 2";
      return FALSE;
    }
    // return TRUE;
    return $install_results_1 . $install_results_2;;
  }

  public function configure_gacl()
  {
    //give the administrator user admin priviledges
    $groupArray = array("Administrators");
    return set_user_aro($groupArray,$this->iuser,$this->iuname,"","");
  }

  public function quick_install() {
    if ( ! $this->login_is_valid() ) {
      return False;
    }
    if ( ! $this->iuser_is_valid() ) {
      return False;
    }
    if ( ! $this->password_is_valid() ) {
      return False;
    }
    $dbh = $this->root_database_connection();
    if ($dbh == FALSE) {
      return False;
    }
    if ( ! $this->create_database()) {
      return False;
    }
    if ( ! $this->grant_privileges() ) {
      return False;
    }
    $this->disconnect();
    if ( ! $this->user_database_connection() ) {
      return False;
    }
    $dump_results = $this->load_dumpfiles();
    if (! $dump_results ) {
      return False;
    }
    if ( ! $this->add_initial_user() ) {
      return False;
    }
    if ( ! $this->write_configuration_file() ) {
      return False;
    }
    require 'sqlconf.php';
    require_once 'translation.inc.php';
    $this->insert_globals();
    return True;
  }

  private function execute_sql( $sql ) {
    $this->error_message = '';
    if ( ! $this->dbh ) {
      $this->user_database_connection();
    }
    $results = mysql_query($sql, $this->dbh);
    if ( $results ) {
      return $results;
    } else {
      $this->error_message = "unable to execute SQL: '$sql' due to: " . mysql_error();
      return False;
    }
  }

  private function connect_to_database( $server, $user, $password, $port='3306' )
  {
    if ($server == "localhost")
      $dbh = mysql_connect($server, $user, $password);
    else
      $dbh = mysql_connect("$server:$port", $user, $password);
    return $dbh;
  }

  private function set_collation()
  {
   if ($this->collate) {
     return $this->execute_sql("SET NAMES 'utf8'");
   }
   return TRUE;
  }

  // These are the dumpfiles that are loaded into database
  // including the correct translation dumpfile
  // The keys are the paths of the dumpfiles, and the values are the titles
  private function dumpfiles() {
    $dumpfiles = array( $this->manualPath."sql/database.sql" => 'Main',
                        $this->manualPath."contrib/util/language_translations/currentLanguage_utf8.sql" => "Language Translation (utf8)" );
    if ($this->ippf_specific) {
      $dumpfiles[ $this->manualPath."sql/ippf_layout.sql" ] = "IPPF Layout";
    }
    return $dumpfiles;
  }

  // http://www.php.net/manual/en/function.include.php
  private function get_require_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        require $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
  }
}

/*
This file is free software: you can redistribute it and/or modify it under the
terms of the GNU General Public License as publish by the Free Software
Foundation.

This file is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU Gneral Public License for more details.

You should have received a copy of the GNU General Public Licence along with
this file.  If not see <http://www.gnu.org/licenses/>.
*/
?>
