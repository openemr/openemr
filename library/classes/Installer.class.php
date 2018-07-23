<?php
/* Copyright © 2010 by Andrew Moore */
/* Licensing information appears at the end of this file. */

class Installer
{

    public function __construct($cgi_variables)
    {
        // Installation variables
        // For a good explanation of these variables, see documentation in
        //   the contrib/util/installScripts/InstallerAuto.php file.
        $this->iuser                    = isset($cgi_variables['iuser']) ? ($cgi_variables['iuser']) : '';
        $this->iuserpass                = isset($cgi_variables['iuserpass']) ? ($cgi_variables['iuserpass']) : '';
        $this->iuname                   = isset($cgi_variables['iuname']) ? ($cgi_variables['iuname']) : '';
        $this->iufname                  = isset($cgi_variables['iufname']) ? ($cgi_variables['iufname']) : '';
        $this->igroup                   = isset($cgi_variables['igroup']) ? ($cgi_variables['igroup']) : '';
        $this->server                   = isset($cgi_variables['server']) ? ($cgi_variables['server']) : ''; // mysql server (usually localhost)
        $this->loginhost                = isset($cgi_variables['loginhost']) ? ($cgi_variables['loginhost']) : ''; // php/apache server (usually localhost)
        $this->port                     = isset($cgi_variables['port']) ? ($cgi_variables['port']): '';
        $this->root                     = isset($cgi_variables['root']) ? ($cgi_variables['root']) : '';
        $this->rootpass                 = isset($cgi_variables['rootpass']) ? ($cgi_variables['rootpass']) : '';
        $this->login                    = isset($cgi_variables['login']) ? ($cgi_variables['login']) : '';
        $this->pass                     = isset($cgi_variables['pass']) ? ($cgi_variables['pass']) : '';
        $this->dbname                   = isset($cgi_variables['dbname']) ? ($cgi_variables['dbname']) : '';
        $this->collate                  = isset($cgi_variables['collate']) ? ($cgi_variables['collate']) : '';
        $this->site                     = isset($cgi_variables['site']) ? ($cgi_variables['site']) : '';
        $this->source_site_id           = isset($cgi_variables['source_site_id']) ? ($cgi_variables['source_site_id']) : '';
        $this->clone_database           = isset($cgi_variables['clone_database']) ? ($cgi_variables['clone_database']) : '';
        $this->no_root_db_access        = isset($cgi_variables['no_root_db_access']) ? ($cgi_variables['no_root_db_access']) : ''; // no root access to database. user/privileges pre-configured
        $this->development_translations = isset($cgi_variables['development_translations']) ? ($cgi_variables['development_translations']) : '';
        // Make this true for IPPF.
        $this->ippf_specific = false;

        // Record name of sql access file
        $GLOBALS['OE_SITES_BASE'] = dirname(__FILE__) . '/../../sites';
        $GLOBALS['OE_SITE_DIR'] = $GLOBALS['OE_SITES_BASE'] . '/' . $this->site;
        $this->conffile  =  $GLOBALS['OE_SITE_DIR'] . '/sqlconf.php';

        // Record names of sql table files
        $this->main_sql = dirname(__FILE__) . '/../../sql/database.sql';
        $this->translation_sql = dirname(__FILE__) . '/../../contrib/util/language_translations/currentLanguage_utf8.sql';
        $this->devel_translation_sql = "http://translations.openemr.io/languageTranslations_utf8.sql";
        $this->ippf_sql = dirname(__FILE__) . "/../../sql/ippf_layout.sql";
        $this->icd9 = dirname(__FILE__) . "/../../sql/icd9.sql";
        $this->cvx = dirname(__FILE__) . "/../../sql/cvx_codes.sql";
        $this->additional_users = dirname(__FILE__) . "/../../sql/official_additional_users.sql";

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
        if (($this->login == '') || (! isset($this->login))) {
            $this->error_message = "login is invalid: '$this->login'";
            return false;
        }

        return true;
    }

    public function char_is_valid($input_text)
    {
        // to prevent php injection
        trim($input_text);
        if ($input_text == '') {
            return false;
        }

        if (preg_match('@[\\\\;()<>/\'"]@', $input_text)) {
            return false;
        }

        return true;
    }

    public function databaseNameIsValid($name)
    {
        if (preg_match('/[^A-Za-z0-9_-]/', $name)) {
            return false;
        }
        return true;
    }

    public function collateNameIsValid($name)
    {
        if (preg_match('/[^A-Za-z0-9_-]/', $name)) {
            return false;
        }
        return true;
    }

    public function iuser_is_valid()
    {
        if (strpos($this->iuser, " ")) {
            $this->error_message = "Initial user is invalid: '$this->iuser'";
            return false;
        }

        return true;
    }

    public function password_is_valid()
    {
        if ($this->pass == "" || !isset($this->pass)) {
            $this->error_message = "The password for the new database account is invalid: '$this->pass'";
            return false;
        }

        return true;
    }

    public function user_password_is_valid()
    {
        if ($this->iuserpass == "" || !isset($this->iuserpass)) {
            $this->error_message = "The password for the user is invalid: '$this->iuserpass'";
            return false;
        }

        return true;
    }



    public function root_database_connection()
    {
        $this->dbh = $this->connect_to_database($this->server, $this->root, $this->rootpass, $this->port);
        if ($this->dbh) {
            if (! $this->set_sql_strict()) {
                $this->error_message = 'unable to set strict sql setting';
                return false;
            }

            return true;
        } else {
            $this->error_message = 'unable to connect to database as root';
            return false;
        }
    }

    public function user_database_connection()
    {
        $this->dbh = $this->connect_to_database($this->server, $this->login, $this->pass, $this->port, $this->dbname);
        if (! $this->dbh) {
            $this->error_message = "unable to connect to database as user: '$this->login'";
            return false;
        }

        if (! $this->set_sql_strict()) {
            $this->error_message = 'unable to set strict sql setting';
            return false;
        }

        if (! $this->set_collation()) {
            $this->error_message = 'unable to set sql collation';
            return false;
        }

        if (! mysqli_select_db($this->dbh, $this->dbname)) {
            $this->error_message = "unable to select database: '$this->dbname'";
            return false;
        }

        return true;
    }

    public function create_database()
    {
        $sql = "create database " . $this->escapeDatabaseName($this->dbname);
        if ($this->collate) {
            $sql .= " character set utf8 collate " . $this->escapeCollateName($this->collate);
            $this->set_collation();
        }

        return $this->execute_sql($sql);
    }

    public function drop_database()
    {
        $sql = "drop database if exists " . $this->escapeDatabaseName($this->dbname);
        return $this->execute_sql($sql);
    }

    public function check_database_user()
    {
        return $this->execute_sql("SELECT user FROM mysql.user WHERE user = '" . $this->escapeSql($this->login) . "' AND host = '" . $this->escapeSql($this->loginhost) . "'");
    }

    public function create_database_user()
    {
        $checkUser = $this->check_database_user();

        if ($checkUser === false) {
            // there was an error in the check database user query, so return false
            return false;
        } else if ($checkUser->num_rows > 0) {
            // the mysql user already exists, so do not need to create the user, but need to set the password
            // Note need to try two different methods, first is for newer mysql versions and second is for older mysql versions (if the first method fails)
            $returnSql = $this->execute_sql("ALTER USER '" . $this->escapeSql($this->login) . "'@'" . $this->escapeSql($this->loginhost) . "' IDENTIFIED BY '" . $this->escapeSql($this->pass) . "'", false);
            if ($returnSql === false) {
                error_log("Using older mysql version method to set password for the mysql user");
                $returnSql = $this->execute_sql("SET PASSWORD FOR '" . $this->escapeSql($this->login) . "'@'" . $this->escapeSql($this->loginhost) . "' = PASSWORD('" . $this->escapeSql($this->pass) . "')");
            }
            return $returnSql;
        } else {
            // the mysql user does not yet exist, so create the user
            return $this->execute_sql("CREATE USER '" . $this->escapeSql($this->login) . "'@'" . $this->escapeSql($this->loginhost) . "' IDENTIFIED BY '" . $this->escapeSql($this->pass) . "'");
        }
    }

    public function grant_privileges()
    {
        return $this->execute_sql("GRANT ALL PRIVILEGES ON " . $this->escapeDatabaseName($this->dbname) . ".* TO '" . $this->escapeSql($this->login) . "'@'" . $this->escapeSql($this->loginhost) . "'");
    }

    public function disconnect()
    {
        return mysqli_close($this->dbh);
    }

  /**
   * This method creates any dumpfiles necessary.
   * This is actually only done if we're cloning an existing site
   * and we need to dump their database into a file.
   * @return bool indicating success
   */
    public function create_dumpfiles()
    {
        return $this->dumpSourceDatabase();
    }

    public function load_dumpfiles()
    {
        $sql_results = ''; // information string which is returned
        foreach ($this->dumpfiles as $filename => $title) {
            $sql_results_temp = '';
            $sql_results_temp = $this->load_file($filename, $title);
            if ($sql_results_temp == false) {
                return false;
            }

            $sql_results .= $sql_results_temp;
        }

        return $sql_results;
    }

    public function load_file($filename, $title)
    {
        $sql_results = ''; // information string which is returned
        $sql_results .= "Creating $title tables...\n";
        $fd = fopen($filename, 'r');
        if ($fd == false) {
            $this->error_message = "ERROR.  Could not open dumpfile '$filename'.\n";
            return false;
        }

        $query = "";
        $line = "";

        // Settings to drastically speed up installation with InnoDB
        if (! $this->execute_sql("SET autocommit=0;")) {
            return false;
        }

        if (! $this->execute_sql("START TRANSACTION;")) {
            return false;
        }

        while (!feof($fd)) {
            $line = fgets($fd, 1024);
            $line = rtrim($line);
            if (substr($line, 0, 2) == "--") { // Kill comments
                    continue;
            }

            if (substr($line, 0, 1) == "#") { // Kill comments
                    continue;
            }

            if ($line == "") {
                    continue;
            }

            $query = $query.$line;          // Check for full query
            $chr = substr($query, strlen($query)-1, 1);
            if ($chr == ";") { // valid query, execute
                    $query = rtrim($query, ";");
                if (! $this->execute_sql($query)) {
                    return false;
                }

                    $query = "";
            }
        }

        // Settings to drastically speed up installation with InnoDB
        if (! $this->execute_sql("COMMIT;")) {
            return false;
        }

        if (! $this->execute_sql("SET autocommit=1;")) {
            return false;
        }

        $sql_results .= "OK<br>\n";
        fclose($fd);
        return $sql_results;
    }

  // Please note that the plain sql is used over the Doctrine ORM for
  // `version` table interactions because it cannot connect due to a
  // lack of context (this code is ran outside of the OpenEMR context).
    public function add_version_info()
    {
        include dirname(__FILE__) . "/../../version.php";
        if ($this->execute_sql("UPDATE version SET v_major = '" . $this->escapeSql($v_major) . "', v_minor = '" . $this->escapeSql($v_minor) . "', v_patch = '" . $this->escapeSql($v_patch) . "', v_realpatch = '" . $this->escapeSql($v_realpatch) . "', v_tag = '" . $this->escapeSql($v_tag) . "', v_database = '" . $this->escapeSql($v_database) . "', v_acl = '" . $this->escapeSql($v_acl) . "'") == false) {
            $this->error_message = "ERROR. Unable insert version information into database\n" .
            "<p>".mysqli_error($this->dbh)." (#".mysqli_errno($this->dbh).")\n";
            return false;
        }

        return true;
    }

    public function add_initial_user()
    {
        if ($this->execute_sql("INSERT INTO `groups` (id, name, user) VALUES (1,'" . $this->escapeSql($this->igroup) . "','" . $this->escapeSql($this->iuser) . "')") == false) {
            $this->error_message = "ERROR. Unable to add initial user group\n" .
            "<p>".mysqli_error($this->dbh)." (#".mysqli_errno($this->dbh).")\n";
            return false;
        }

        $password_hash = "NoLongerUsed";  // This is the value to insert into the password column in the "users" table. password details are now being stored in users_secure instead.
        $salt=oemr_password_salt();     // Uses the functions defined in library/authentication/password_hashing.php
        $hash=oemr_password_hash($this->iuserpass, $salt);
        if ($this->execute_sql("INSERT INTO users (id, username, password, authorized, lname, fname, facility_id, calendar, cal_ui) VALUES (1,'" . $this->escapeSql($this->iuser) . "','" . $this->escapeSql($password_hash) . "',1,'" . $this->escapeSql($this->iuname) . "','" . $this->escapeSql($this->iufname) . "',3,1,3)") == false) {
            $this->error_message = "ERROR. Unable to add initial user\n" .
            "<p>".mysqli_error($this->dbh)." (#".mysqli_errno($this->dbh).")\n";
            return false;
        }

        // Create the new style login credentials with blowfish and salt
        if ($this->execute_sql("INSERT INTO users_secure (id, username, password, salt) VALUES (1,'" . $this->escapeSql($this->iuser) . "','" . $this->escapeSql($hash) . "','" . $this->escapeSql($salt) . "')") == false) {
            $this->error_message = "ERROR. Unable to add initial user login credentials\n" .
            "<p>".mysqli_error($this->dbh)." (#".mysqli_errno($this->dbh).")\n";
            return false;
        }

        // Add the official openemr users (services)
        if ($this->load_file($this->additional_users, "Additional Official Users") == false) {
            return false;
        }

        return true;
    }

  /**
   * Create site directory if it is missing.
   * @global string $GLOBALS['OE_SITE_DIR'] contains the name of the site directory to create
   * @return name of the site directory or False
   */
    public function create_site_directory()
    {
        if (!file_exists($GLOBALS['OE_SITE_DIR'])) {
            $source_directory      = $GLOBALS['OE_SITES_BASE'] . "/" . $this->source_site_id;
            $destination_directory = $GLOBALS['OE_SITE_DIR'];
            if (! $this->recurse_copy($source_directory, $destination_directory)) {
                $this->error_message = "unable to copy directory: '$source_directory' to '$destination_directory'. " . $this->error_message;
                return false;
            }
        }

        return true;
    }

    public function write_configuration_file()
    {
        @touch($this->conffile); // php bug
        $fd = @fopen($this->conffile, 'w');
        if (! $fd) {
            $this->error_message = 'unable to open configuration file for writing: ' . $this->conffile;
            return false;
        }

        $string = '<?php
//  OpenEMR
//  MySQL Config

';

        $it_died = 0;   //fmg: variable keeps running track of any errors

        fwrite($fd, $string) or $it_died++;
        fwrite($fd, "\$host\t= '$this->server';\n") or $it_died++;
        fwrite($fd, "\$port\t= '$this->port';\n") or $it_died++;
        fwrite($fd, "\$login\t= '$this->login';\n") or $it_died++;
        fwrite($fd, "\$pass\t= '$this->pass';\n") or $it_died++;
        fwrite($fd, "\$dbase\t= '$this->dbname';\n\n") or $it_died++;
        fwrite($fd, "//Added ability to disable\n") or $it_died++;
        fwrite($fd, "//utf8 encoding - bm 05-2009\n") or $it_died++;
        fwrite($fd, "global \$disable_utf8_flag;\n") or $it_died++;
        fwrite($fd, "\$disable_utf8_flag = false;\n") or $it_died++;

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

    fwrite($fd, $string) or $it_died++;
    fclose($fd) or $it_died++;

    //it's rather irresponsible to not report errors when writing this file.
if ($it_died != 0) {
    $this->error_message = "ERROR. Couldn't write $it_died lines to config file '$this->conffile'.\n";
    return false;
}

    return true;
    }

    public function insert_globals()
    {
        function xl($s)
        {
            return $s;
        }
        require(dirname(__FILE__) . '/../globals.inc.php');
        foreach ($GLOBALS_METADATA as $grpname => $grparr) {
            foreach ($grparr as $fldid => $fldarr) {
                list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                if (is_array($fldtype) || substr($fldtype, 0, 2) !== 'm_') {
                    $res = $this->execute_sql("SELECT count(*) AS count FROM globals WHERE gl_name = '" . $this->escapeSql($fldid) . "'");
                    $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
                    if (empty($row['count'])) {
                        $this->execute_sql("INSERT INTO globals ( gl_name, gl_index, gl_value ) " .
                           "VALUES ( '" . $this->escapeSql($fldid) . "', '0', '" . $this->escapeSql($flddef) . "' )");
                    }
                }
            }
        }

        return true;
    }

    public function install_gacl()
    {
        $install_results_1 = $this->get_require_contents($this->gaclSetupScript1);
        if (! $install_results_1) {
            $this->error_message = "install_gacl failed: unable to require gacl script 1";
            return false;
        }

        $install_results_2 = $this->get_require_contents($this->gaclSetupScript2);
        if (! $install_results_2) {
            $this->error_message = "install_gacl failed: unable to require gacl script 2";
            return false;
        }

        $this->debug_message .= $install_results_1 . $install_results_2;
        return true;
    }

    public function quick_install()
    {
        // Validation of OpenEMR user settings
        //   (applicable if not cloning from another database)
        if (empty($this->clone_database)) {
            if (! $this->login_is_valid()) {
                return false;
            }

            if (! $this->iuser_is_valid()) {
                return false;
            }

            if (! $this->user_password_is_valid()) {
                return false;
            }
        }

        // Validation of mysql database password
        if (! $this->password_is_valid()) {
            return false;
        }

        if (! $this->no_root_db_access) {
            // Connect to mysql via root user
            if (! $this->root_database_connection()) {
                return false;
            }

            // Create the dumpfile
            //   (applicable if cloning from another database)
            if (! empty($this->clone_database)) {
                if (! $this->create_dumpfiles()) {
                    return false;
                }
            }

            // Create the site directory
            //   (applicable if mirroring another local site)
            if (! empty($this->source_site_id)) {
                if (! $this->create_site_directory()) {
                    return false;
                }
            }

            $this->disconnect();
            if (! $this->user_database_connection()) {
                // Re-connect to mysql via root user
                if (! $this->root_database_connection()) {
                    return false;
                }

                // Create the mysql database
                if (! $this->create_database()) {
                    return false;
                }

                // Create the mysql user
                if (! $this->create_database_user()) {
                    return false;
                }

                // Grant user privileges to the mysql database
                if (! $this->grant_privileges()) {
                    return false;
                }
            }

            $this->disconnect();
        }

        // Connect to mysql via created user
        if (! $this->user_database_connection()) {
            return false;
        }

        // Build the database
        if (! $this->load_dumpfiles()) {
            return false;
        }

        // Write the sql configuration file
        if (! $this->write_configuration_file()) {
            return false;
        }

        // Load the version information, globals settings,
        // initial user, and set up gacl access controls.
        //  (applicable if not cloning from another database)
        if (empty($this->clone_database)) {
            if (! $this->add_version_info()) {
                return false;
            }

            if (! $this->insert_globals()) {
                return false;
            }

            if (! $this->add_initial_user()) {
                return false;
            }

            if (! $this->install_gacl()) {
                return false;
            }
        }

        return true;
    }

    private function escapeSql($sql)
    {
        return mysqli_real_escape_string($this->dbh, $sql);
    }

    private function escapeDatabaseName($name)
    {
        if (preg_match('/[^A-Za-z0-9_-]/', $name)) {
            error_log("Illegal character(s) in database name");
            die("Illegal character(s) in database name");
        }
        return $name;
    }

    private function escapeCollateName($name)
    {
        if (preg_match('/[^A-Za-z0-9_-]/', $name)) {
            error_log("Illegal character(s) in collation name");
            die("Illegal character(s) in collation name");
        }
        return $name;
    }

    private function execute_sql($sql, $showError = true)
    {
        $this->error_message = '';
        if (! $this->dbh) {
            $this->user_database_connection();
        }

        $results = mysqli_query($this->dbh, $sql);
        if ($results) {
            return $results;
        } else {
            if ($showError) {
                $error_mes = mysqli_error($this->dbh);
                $this->error_message = "unable to execute SQL: '$sql' due to: " . $error_mes;
                error_log("ERROR IN OPENEMR INSTALL: Unable to execute SQL: " . $sql . " due to: " . $error_mes);
            }
            return false;
        }
    }

    private function connect_to_database($server, $user, $password, $port, $dbname = '')
    {
        if ($server == "localhost") {
            $dbh = mysqli_connect($server, $user, $password, $dbname);
        } else {
            $dbh = mysqli_connect($server, $user, $password, $dbname, $port);
        }

        return $dbh;
    }

    private function set_sql_strict()
    {
        // Turn off STRICT SQL
        return $this->execute_sql("SET sql_mode = ''");
    }

    private function set_collation()
    {
        if ($this->collate) {
            return $this->execute_sql("SET NAMES 'utf8'");
        }

        return true;
    }

  /**
   * innitialize $this->dumpfiles, an array of the dumpfiles that will
   * be loaded into the database, including the correct translation
   * dumpfile.
   * The keys are the paths of the dumpfiles, and the values are the titles
   * @return array
   */
    private function initialize_dumpfile_list()
    {
        if ($this->clone_database) {
            $this->dumpfiles = array( $this->get_backup_filename() => 'clone database' );
        } else {
            $dumpfiles = array( $this->main_sql => 'Main' );
            if (! empty($this->development_translations)) {
                // Use the online development translation set
                $dumpfiles[ $this->devel_translation_sql ] = "Online Development Language Translations (utf8)";
            } else {
                // Use the local translation set
                $dumpfiles[ $this->translation_sql ] = "Language Translation (utf8)";
            }

            if ($this->ippf_specific) {
                $dumpfiles[ $this->ippf_sql ] = "IPPF Layout";
            }

            // Load ICD-9 codes if present.
            if (file_exists($this->icd9)) {
                $dumpfiles[ $this->icd9 ] = "ICD-9";
            }

            // Load CVX codes if present
            if (file_exists($this->cvx)) {
                $dumpfiles[ $this->cvx ] = "CVX Immunization Codes";
            }

            $this->dumpfiles = $dumpfiles;
        }

        return $this->dumpfiles;
    }

  // http://www.php.net/manual/en/function.include.php
    private function get_require_contents($filename)
    {
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
    private function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        if (! @mkdir($dst)) {
            $this->error_message = "unable to create directory: '$dst'";
            return false;
        }

        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
        return true;
    }

  /**
   *
   * dump a site's database to a temporary file.
   * @param string $source_site_id the site_id of the site to dump
   * @return filename of the backup
   */
    private function dumpSourceDatabase()
    {
        global $OE_SITES_BASE;
        $source_site_id = $this->source_site_id;

        include("$OE_SITES_BASE/$source_site_id/sqlconf.php");

        if (empty($config)) {
            die("Source site $source_site_id has not been set up!");
        }

        $backup_file = $this->get_backup_filename();
        $cmd = "mysqldump -u " . escapeshellarg($login) .
        " -p" . escapeshellarg($pass) .
        " --opt --skip-extended-insert --quote-names -r $backup_file " .
        escapeshellarg($dbase);

        $tmp0 = exec($cmd, $tmp1 = array(), $tmp2);
        if ($tmp2) {
            die("Error $tmp2 running \"$cmd\": $tmp0 " . implode(' ', $tmp1));
        }

        return $backup_file;
    }

  /**
   * @return filename of the source backup database for cloning
   */
    private function get_backup_filename()
    {
        if (stristr(PHP_OS, 'WIN')) {
            $backup_file = 'C:/windows/temp/setup_dump.sql';
        } else {
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
