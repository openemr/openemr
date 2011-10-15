<?php
/* Copyright © 2010 by Andrew Moore */
/* Licensing information appears at the end of this file. */

class Installer
{

  public function __construct( $cgi_variables )
  {
    // Installation variables
    // For a good explanation of these variables, see documentation in
    //   the contrib/util/installScripts/InstallerAuto.php file.
    $this->iuser                = $cgi_variables['iuser'];
    $this->iuserpass            = $cgi_variables['iuserpass'];
    $this->iuname               = $cgi_variables['iuname'];
    $this->igroup               = $cgi_variables['igroup'];
    $this->server               = $cgi_variables['server']; // mysql server (usually localhost)
    $this->loginhost            = $cgi_variables['loginhost']; // php/apache server (usually localhost)
    $this->port                 = $cgi_variables['port'];
    $this->root                 = $cgi_variables['root'];
    $this->rootpass             = $cgi_variables['rootpass'];
    $this->login                = $cgi_variables['login'];
    $this->pass                 = $cgi_variables['pass'];
    $this->dbname               = $cgi_variables['dbname'];
    $this->collate              = $cgi_variables['collate'];
    $this->site                 = $cgi_variables['site'];
    $this->source_site_id       = $cgi_variables['source_site_id'];
    $this->clone_database       = $cgi_variables['clone_database'];
    $this->development_translations = $cgi_variables['development_translations'];

    // Make this true for IPPF.
    $this->ippf_specific = false;

    // Record name of sql access file
    $GLOBALS['OE_SITES_BASE'] = dirname(__FILE__) . '/../../sites';
    $GLOBALS['OE_SITE_DIR'] = $GLOBALS['OE_SITES_BASE'] . '/' . $this->site;
    $this->conffile  =  $GLOBALS['OE_SITE_DIR'] . '/sqlconf.php';

    // Record names of sql table files
    $this->main_sql = dirname(__FILE__) . '/../../sql/database.sql';
    $this->translation_sql = dirname(__FILE__) . '/../../contrib/util/language_translations/currentLanguage_utf8.sql';
    $this->devel_translation_sql = "http://github.com/openemr/translations_development_openemr/raw/master/languageTranslations_utf8.sql";
    $this->ippf_sql = dirname(__FILE__) . "/../../sql/ippf_layout.sql";
    $this->icd9 = dirname(__FILE__) . "/../../sql/icd9.sql";
    $this->cvx = dirname(__FILE__) . "/../../sql/cvx_codes.sql";

    // Record name of php-gacl installation files
    $this->gaclSetupScript1 = dirname(__FILE__) . "/../../gacl/setup.php";
    $this->gaclSetupScript2 = dirname(__FILE__) . "/../../acl_setup.php";

    // Prepare the dumpfile list
    $this->initialize_dumpfile_list();

    // Entities to hold error and debug messages
    $this->error_message = '';
    $this->debug_message = '';

    // Entity to hold sql connection
    $this->dbh = false;
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
      $this->error_message = "The password for the new database account is invalid: '$this->pass'";
      return FALSE;
    }
    return TRUE;
  }

  public function user_password_is_valid()
  {
    if ( $this->iuserpass == "" || !isset($this->iuserpass) ) {
      $this->error_message = "The password for the user is invalid: '$this->iuserpass'";
      return FALSE;
    }
    return TRUE;
  }

  public function root_database_connection()
  {
    $this->dbh = $this->connect_to_database( $this->server, $this->root, $this->rootpass, $this->port );
    if ( $this->dbh ) {
      return TRUE;
    } else {
      $this->error_message = 'unable to connect to database as root';
      return FALSE;
    }
  }

  public function user_database_connection()
  {
    $this->dbh = $this->connect_to_database( $this->server, $this->login, $this->pass, $this->port );
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
    return TRUE;
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

  /**
   * This method creates any dumpfiles necessary.
   * This is actually only done if we're cloning an existing site
   * and we need to dump their database into a file.
   * @return bool indicating success
   */
  public function create_dumpfiles() {
    return $this->dumpSourceDatabase();
  }
  
  public function load_dumpfiles() {
    $sql_results = ''; // information string which is returned
    foreach ($this->dumpfiles as $filename => $title) {
        $sql_results .= "Creating $title tables...\n";
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
                $query = $query.$line;          // Check for full query
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

  public function add_version_info() {
    include dirname(__FILE__) . "/../../version.php";
    if ($this->execute_sql("UPDATE version SET v_major = '$v_major', v_minor = '$v_minor', v_patch = '$v_patch', v_realpatch = '$v_realpatch', v_tag = '$v_tag', v_database = '$v_database'") == FALSE) {
      $this->error_message = "ERROR. Unable insert version information into database\n" .
        "<p>".mysql_error()." (#".mysql_errno().")\n";
      return FALSE;
    }
    return TRUE;
  }

  public function add_initial_user() {
    if ($this->execute_sql("INSERT INTO groups (id, name, user) VALUES (1,'$this->igroup','$this->iuser')") == FALSE) {
      $this->error_message = "ERROR. Unable to add initial user group\n" .
        "<p>".mysql_error()." (#".mysql_errno().")\n";
      return FALSE;
    }
    $password_hash = sha1( $this->iuserpass );
    if ($this->execute_sql("INSERT INTO users (id, username, password, authorized, lname, fname, facility_id, calendar, cal_ui) VALUES (1,'$this->iuser','$password_hash',1,'$this->iuname','',3,1,3)") == FALSE) {
      $this->error_message = "ERROR. Unable to add initial user\n" .
        "<p>".mysql_error()." (#".mysql_errno().")\n";
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Create site directory if it is missing.
   * @global string $GLOBALS['OE_SITE_DIR'] contains the name of the site directory to create
   * @return name of the site directory or False
   */
  public function create_site_directory() {
    if (!file_exists($GLOBALS['OE_SITE_DIR'])) {
      $source_directory      = $GLOBALS['OE_SITES_BASE'] . "/" . $this->source_site_id;
      $destination_directory = $GLOBALS['OE_SITE_DIR'];
      if ( ! $this->recurse_copy( $source_directory, $destination_directory ) ) {
        $this->error_message = "unable to copy directory: '$source_directory' to '$destination_directory'. " . $this->error_message;
        return False;
      }
    }
    return True;
  }
    
  public function write_configuration_file() {
    @touch($this->conffile); // php bug
    $fd = @fopen($this->conffile, 'w');
    if ( ! $fd ) {
      $this->error_message = 'unable to open configuration file for writing: ' . $this->conffile;
      return False;
    }
    $string = '<?php
//  OpenEMR
//  MySQL Config

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
    fwrite($fd,"global \$disable_utf8_flag;\n") or $it_died++;
    fwrite($fd,"\$disable_utf8_flag = false;\n") or $it_died++;

$string = '
$sqlconf = array();
global $sqlconf;
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
    fclose($fd) or $it_died++;

    //it's rather irresponsible to not report errors when writing this file.
    if ($it_died != 0) {
      $this->error_message = "ERROR. Couldn't write $it_died lines to config file '$this->conffile'.\n";
      return FALSE;
    }

    return TRUE;
  }

  public function insert_globals() {
    function xl($s) { return $s; }
    require(dirname(__FILE__) . '/../globals.inc.php');
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
    return TRUE;
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
    $this->debug_message .= $install_results_1 . $install_results_2;
    return TRUE;
  }

  public function quick_install() {
    // Validation of OpenEMR user settings
    //   (applicable if not cloning from another database)
    if (empty($this->clone_database)) {
      if ( ! $this->login_is_valid() ) {
        return False;
      }
      if ( ! $this->iuser_is_valid() ) {
        return False;
      }
      if ( ! $this->user_password_is_valid() ) {
        return False;
      }
    }
    // Validation of mysql database password
    if ( ! $this->password_is_valid() ) {
      return False;
    }
    // Connect to mysql via root user
    if (! $this->root_database_connection() ) {
      return False;
    }
    // Create the dumpfile
    //   (applicable if cloning from another database)
    if (! empty($this->clone_database)) {
      if ( ! $this->create_dumpfiles() ) {
        return False;
      }
    }
    // Create the site directory
    //   (applicable if mirroring another local site)
    if ( ! empty($this->source_site_id) ) {
      if ( ! $this->create_site_directory() ) {
        return False;
      }
    }
    // Create the mysql database
    if ( ! $this->create_database()) {
      return False;
    }
    // Grant user privileges to the mysql database
    if ( ! $this->grant_privileges() ) {
      return False;
    }
    // Connect to mysql via created user
    $this->disconnect();
    if ( ! $this->user_database_connection() ) {
      return False;
    }
    // Build the database
    if ( ! $this->load_dumpfiles() ) {
      return False;
    }
    // Write the sql configuration file
    if ( ! $this->write_configuration_file() ) {
      return False;
    }
    // Load the version information, globals settings,
    // initial user, and set up gacl access controls.
    //  (applicable if not cloning from another database)
    if (empty($this->clone_database)) {
      if ( ! $this->add_version_info() ) {
        return False;
      }
      if ( ! $this->insert_globals() ) {
        return False;
      }
      if ( ! $this->add_initial_user() ) {
        return False;
      }
      if ( ! $this->install_gacl()) {
        return False;
      }
    }

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

  private function connect_to_database( $server, $user, $password, $port )
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

  /**
   * innitialize $this->dumpfiles, an array of the dumpfiles that will
   * be loaded into the database, including the correct translation
   * dumpfile.
   * The keys are the paths of the dumpfiles, and the values are the titles
   * @return array
   */
  private function initialize_dumpfile_list() {
    if ( $this->clone_database ) {
      $this->dumpfiles = array( $this->get_backup_filename() => 'clone database' );
    } else {
      $dumpfiles = array( $this->main_sql => 'Main' );
      if (! empty($this->development_translations)) {
        // Use the online development translation set
        $dumpfiles[ $this->devel_translation_sql ] = "Online Development Language Translations (utf8)";
      }
      else {
        // Use the local translation set
        $dumpfiles[ $this->translation_sql ] = "Language Translation (utf8)";
      }
      if ($this->ippf_specific) {
        $dumpfiles[ $this->ippf_sql ] = "IPPF Layout";
      }
      // Load ICD-9 codes if present.
      if (file_exists( $this->icd9 )) {
        $dumpfiles[ $this->icd9 ] = "ICD-9";
      }
      // Load CVX codes if present
      if (file_exists( $this->cvx )) {
        $dumpfiles[ $this->cvx ] = "CVX Immunization Codes";
      }
      $this->dumpfiles = $dumpfiles;
    }
    return $this->dumpfiles;
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

  /**
   * 
   * Directory copy logic borrowed from a user comment at
   * http://www.php.net/manual/en/function.copy.php
   * @param string $src name of the directory to copy
   * @param string $dst name of the destination to copy to
   * @return bool indicating success
   */
  private function recurse_copy($src, $dst) {
    $dir = opendir($src);
    if ( ! @mkdir($dst) ) {
      $this->error_message = "unable to create directory: '$dst'";
      return False;
    }
    while(false !== ($file = readdir($dir))) {
      if ($file != '.' && $file != '..') {
        if (is_dir($src . '/' . $file)) {
          $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
        }
        else {
          copy($src . '/' . $file, $dst . '/' . $file);
        }
      }
    }
    closedir($dir);
    return True;
  }

  /**
   * 
   * dump a site's database to a temporary file.
   * @param string $source_site_id the site_id of the site to dump
   * @return filename of the backup
   */
  private function dumpSourceDatabase() {
    global $OE_SITES_BASE;
    $source_site_id = $this->source_site_id;
    
    include("$OE_SITES_BASE/$source_site_id/sqlconf.php");
    
    if (empty($config)) die("Source site $source_site_id has not been set up!");

    $backup_file = $this->get_backup_filename();
    $cmd = "mysqldump -u " . escapeshellarg($login) .
      " -p" . escapeshellarg($pass) .
      " --opt --skip-extended-insert --quote-names -r $backup_file " .
      escapeshellarg($dbase);
    
    $tmp0 = exec($cmd, $tmp1=array(), $tmp2);
    if ($tmp2) die("Error $tmp2 running \"$cmd\": $tmp0 " . implode(' ', $tmp1));
    
    return $backup_file;
  }

  /**
   * @return filename of the source backup database for cloning
   */
  private function get_backup_filename() {
    if (stristr(PHP_OS, 'WIN')) {
      $backup_file = 'C:/windows/temp/setup_dump.sql';
    }
    else {
      $backup_file = '/tmp/setup_dump.sql';
    }
    return $backup_file;
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
