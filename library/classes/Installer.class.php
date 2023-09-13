<?php

/**
 *
 * Installer class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Moore <amoore@cpan.org>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Andrew Moore <amoore@cpan.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Gacl\GaclApi;

class Installer
{
    public $iuser;
    public $iuserpass;
    public $iuname;
    public $iufname;
    public $igroup;
    public $i2faEnable;
    public $i2faSecret;
    public $server;
    public $loginhost;
    public $port;
    public $root;
    public $rootpass;
    public $login;
    public $pass;
    public $dbname;
    public $collate;
    public $site;
    public $source_site_id;
    public $clone_database;
    public $no_root_db_access;
    public $development_translations;
    public $new_theme;
    public $ippf_specific;
    public $conffile;
    public $main_sql;
    public $translation_sql;
    public $devel_translation_sql;
    public $ippf_sql;
    public $icd9;
    public $cvx;
    public $additional_users;
    public $dumpfiles;
    public $error_message;
    public $debug_message;
    public $dbh;

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
        $this->i2faEnable               = isset($cgi_variables['i2faenable']) ? ($cgi_variables['i2faenable']) : '';
        $this->i2faSecret               = isset($cgi_variables['i2fasecret']) ? ($cgi_variables['i2fasecret']) : '';
        $this->server                   = isset($cgi_variables['server']) ? ($cgi_variables['server']) : ''; // mysql server (usually localhost)
        $this->loginhost                = isset($cgi_variables['loginhost']) ? ($cgi_variables['loginhost']) : ''; // php/apache server (usually localhost)
        $this->port                     = isset($cgi_variables['port']) ? ($cgi_variables['port']) : '';
        $this->root                     = isset($cgi_variables['root']) ? ($cgi_variables['root']) : '';
        $this->rootpass                 = isset($cgi_variables['rootpass']) ? ($cgi_variables['rootpass']) : '';
        $this->login                    = isset($cgi_variables['login']) ? ($cgi_variables['login']) : '';
        $this->pass                     = isset($cgi_variables['pass']) ? ($cgi_variables['pass']) : '';
        $this->dbname                   = isset($cgi_variables['dbname']) ? ($cgi_variables['dbname']) : '';
        $this->collate                  = isset($cgi_variables['collate']) ? ($cgi_variables['collate']) : '';
        $this->site                     = isset($cgi_variables['site']) ? ($cgi_variables['site']) : 'default'; // set to default if not set in order for install script to work correctly
        $this->source_site_id           = isset($cgi_variables['source_site_id']) ? ($cgi_variables['source_site_id']) : '';
        $this->clone_database           = isset($cgi_variables['clone_database']) ? ($cgi_variables['clone_database']) : '';
        $this->no_root_db_access        = isset($cgi_variables['no_root_db_access']) ? ($cgi_variables['no_root_db_access']) : ''; // no root access to database. user/privileges pre-configured
        $this->development_translations = isset($cgi_variables['development_translations']) ? ($cgi_variables['development_translations']) : '';
        $this->new_theme                = isset($cgi_variables['new_theme']) ? ($cgi_variables['new_theme']) : '';
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

    public function iuname_is_valid()
    {
        if ($this->iuname == "" || !isset($this->iuname)) {
            $this->error_message = "Initial user last name is invalid: '$this->iuname'";
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
            if (!$this->set_sql_strict()) {
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
        if (empty($this->collate) || ($this->collate == 'utf8_general_ci')) {
            $this->collate = 'utf8mb4_general_ci';
        }
        $sql .= " character set utf8mb4 collate " . $this->escapeCollateName($this->collate);
        $this->set_collation();

        return $this->execute_sql($sql);
    }

    public function drop_database()
    {
        $sql = "drop database if exists " . $this->escapeDatabaseName($this->dbname);
        return $this->execute_sql($sql);
    }

    public function create_database_user()
    {
        // First, check for database user in the mysql.user table (this works for all except mariadb 10.4+)
        $checkUser = $this->execute_sql("SELECT user FROM mysql.user WHERE user = '" . $this->escapeSql($this->login) . "' AND host = '" . $this->escapeSql($this->loginhost) . "'", false);
        if ($checkUser === false) {
            // Above caused error, so is MariaDB 10.4+, and need to do below query instead in the mysql.global_priv table
            $checkUser = $this->execute_sql("SELECT user FROM mysql.global_priv WHERE user = '" . $this->escapeSql($this->login) . "' AND host = '" . $this->escapeSql($this->loginhost) . "'");
        }

        if ($checkUser === false) {
            // there was an error in the check database user query, so return false
            return false;
        } elseif ($checkUser->num_rows > 0) {
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
            if (getenv('FORCE_DATABASE_X509_CONNECT', true) == 1) {
                // this use case is to allow enforcement of x509 database connection use in applicable docker and kubernetes auto installations
                return $this->execute_sql("CREATE USER '" . $this->escapeSql($this->login) . "'@'" . $this->escapeSql($this->loginhost) . "' IDENTIFIED BY '" . $this->escapeSql($this->pass) . "' REQUIRE X509");
            } elseif (getenv('FORCE_DATABASE_SSL_CONNECT', true) == 1) {
                // this use case is to allow enforcement of ssl database connection use in applicable docker and kubernetes auto installations
                return $this->execute_sql("CREATE USER '" . $this->escapeSql($this->login) . "'@'" . $this->escapeSql($this->loginhost) . "' IDENTIFIED BY '" . $this->escapeSql($this->pass) . "' REQUIRE SSL");
            } else {
                return $this->execute_sql("CREATE USER '" . $this->escapeSql($this->login) . "'@'" . $this->escapeSql($this->loginhost) . "' IDENTIFIED BY '" . $this->escapeSql($this->pass) . "'");
            }
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

            $query = $query . $line;          // Check for full query
            $chr = substr($query, strlen($query) - 1, 1);
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

        $sql_results .= "<span class='text-success'><b>OK</b></span>.<br>\n";
        fclose($fd);
        return $sql_results;
    }

    public function add_version_info()
    {
        include dirname(__FILE__) . "/../../version.php";
        if ($this->execute_sql("UPDATE version SET v_major = '" . $this->escapeSql($v_major) . "', v_minor = '" . $this->escapeSql($v_minor) . "', v_patch = '" . $this->escapeSql($v_patch) . "', v_realpatch = '" . $this->escapeSql($v_realpatch) . "', v_tag = '" . $this->escapeSql($v_tag) . "', v_database = '" . $this->escapeSql($v_database) . "', v_acl = '" . $this->escapeSql($v_acl) . "'") == false) {
            $this->error_message = "ERROR. Unable insert version information into database\n" .
            "<p>" . mysqli_error($this->dbh) . " (#" . mysqli_errno($this->dbh) . ")\n";
            return false;
        }

        return true;
    }

    public function add_initial_user()
    {
        if ($this->execute_sql("INSERT INTO `groups` (id, name, user) VALUES (1,'" . $this->escapeSql($this->igroup) . "','" . $this->escapeSql($this->iuser) . "')") == false) {
            $this->error_message = "ERROR. Unable to add initial user group\n" .
            "<p>" . mysqli_error($this->dbh) . " (#" . mysqli_errno($this->dbh) . ")\n";
            return false;
        }

        if ($this->execute_sql("INSERT INTO users (id, username, password, authorized, lname, fname, facility_id, calendar, cal_ui) VALUES (1,'" . $this->escapeSql($this->iuser) . "','NoLongerUsed',1,'" . $this->escapeSql($this->iuname) . "','" . $this->escapeSql($this->iufname) . "',3,1,3)") == false) {
            $this->error_message = "ERROR. Unable to add initial user\n" .
            "<p>" . mysqli_error($this->dbh) . " (#" . mysqli_errno($this->dbh) . ")\n";
            return false;
        }

        $hash = password_hash($this->iuserpass, PASSWORD_DEFAULT);
        if (empty($hash)) {
            // Something is seriously wrong
            error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
            die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
        }
        if ($this->execute_sql("INSERT INTO users_secure (id, username, password, last_update_password) VALUES (1,'" . $this->escapeSql($this->iuser) . "','" . $this->escapeSql($hash) . "',NOW())") == false) {
            $this->error_message = "ERROR. Unable to add initial user login credentials\n" .
            "<p>" . mysqli_error($this->dbh) . " (#" . mysqli_errno($this->dbh) . ")\n";
            return false;
        }

        // Create new 2fa if enabled
        if (($this->i2faEnable) && (!empty($this->i2faSecret)) && (class_exists('Totp')) && (class_exists('OpenEMR\Common\Crypto\CryptoGen'))) {
            // Encrypt the new secret with the hashed password
            $cryptoGen = new OpenEMR\Common\Crypto\CryptoGen();
            $secret = $cryptoGen->encryptStandard($this->i2faSecret, $hash);
            if ($this->execute_sql("INSERT INTO login_mfa_registrations (user_id, name, method, var1, var2) VALUES (1, 'App Based 2FA', 'TOTP', '" . $this->escapeSql($secret) . "', '')") == false) {
                $this->error_message = "ERROR. Unable to add initial user's 2FA credentials\n" .
                    "<p>" . mysqli_error($this->dbh) . " (#" . mysqli_errno($this->dbh) . ")\n";
                return false;
            }
        }

        return true;
    }

    /**
     * Handle the additional users now that our gacl's have finished installing.
     * @return bool
     */
    public function install_additional_users()
    {
        // Add the official openemr users (services)
        if ($this->load_file($this->additional_users, "Additional Official Users") == false) {
            return false;
        }
        return true;
    }

    public function on_care_coordination()
    {
        $resource = $this->execute_sql("SELECT `mod_id` FROM `modules` WHERE `mod_name` = 'Carecoordination' LIMIT 1");
        $resource_array = mysqli_fetch_array($resource, MYSQLI_ASSOC);
        $modId = $resource_array['mod_id'];
        if (empty($modId)) {
            $this->error_message = "ERROR configuring Care Coordination module. Unable to get mod_id for Carecoordination module\n";
            return false;
        }

        $resource = $this->execute_sql("SELECT `section_id` FROM `module_acl_sections` WHERE `section_identifier` = 'carecoordination' LIMIT 1");
        $resource_array = mysqli_fetch_array($resource, MYSQLI_ASSOC);
        $sectionId = $resource_array['section_id'];
        if (empty($sectionId)) {
            $this->error_message = "ERROR configuring Care Coordination module. Unable to get section_id for carecoordination module section\n";
            return false;
        }

        $resource = $this->execute_sql("SELECT `id` FROM `gacl_aro_groups` WHERE `value` = 'admin' LIMIT 1");
        $resource_array = mysqli_fetch_array($resource, MYSQLI_ASSOC);
        $groupId = $resource_array['id'];
        if (empty($groupId)) {
            $this->error_message = "ERROR configuring Care Coordination module. Unable to get id for gacl_aro_groups admin section\n";
            return false;
        }

        if ($this->execute_sql("INSERT INTO `module_acl_group_settings` (`module_id`, `group_id`, `section_id`, `allowed`) VALUES ('" . $this->escapeSql($modId) . "', '" . $this->escapeSql($groupId) . "', '" . $this->escapeSql($sectionId) . "', 1)") == false) {
            $this->error_message = "ERROR configuring Care Coordination module. Unable to add the module_acl_group_settings acl entry\n";
            return false;
        }

        return true;
    }

    /**
     * Generates the initial user's 2FA QR Code
     * @deprecated Recommended to use get_initial_user_mfa_totp() instead
     * @return bool|string|void
     */
    public function get_initial_user_2fa_qr()
    {
        if (($this->i2faEnable) && (!empty($this->i2faSecret)) && (class_exists('Totp'))) {
            $adminTotp = new Totp($this->i2faSecret, $this->iuser);
            $qr = $adminTotp->generateQrCode();
            return $qr;
        }
        return false;
    }

    /**
     * Generates the initial user's 2FA QR Code
     * @return bool|string|void
     */
    public function get_initial_user_mfa_totp()
    {
        if (($this->i2faEnable) && (!empty($this->i2faSecret)) && (class_exists('Totp'))) {
            $adminTotp = new Totp($this->i2faSecret, $this->iuser);
            return $adminTotp;
        }
        return false;
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
            // the new site will create it's own keys so okay to delete these copied from the source site
            if (!$this->clone_database) {
                array_map('unlink', glob($destination_directory . "/documents/logs_and_misc/methods/*"));
            }
        }

        return true;
    }

    public function write_configuration_file()
    {
        if (!file_exists($GLOBALS['OE_SITE_DIR'])) {
            $this->create_site_directory();
        }
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
        fwrite($fd, "global \$disable_utf8_flag;\n") or $it_died++;
        fwrite($fd, "\$disable_utf8_flag = false;\n\n") or $it_died++;
        fwrite($fd, "\$host\t= '$this->server';\n") or $it_died++;
        fwrite($fd, "\$port\t= '$this->port';\n") or $it_died++;
        fwrite($fd, "\$login\t= '$this->login';\n") or $it_died++;
        fwrite($fd, "\$pass\t= '$this->pass';\n") or $it_died++;
        fwrite($fd, "\$dbase\t= '$this->dbname';\n") or $it_died++;
        fwrite($fd, "\$db_encoding\t= 'utf8mb4';\n") or $it_died++;

        $string = '
$sqlconf = array();
global $sqlconf;
$sqlconf["host"]= $host;
$sqlconf["port"] = $port;
$sqlconf["login"] = $login;
$sqlconf["pass"] = $pass;
$sqlconf["dbase"] = $dbase;
$sqlconf["db_encoding"] = $db_encoding;

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

        fwrite($fd, $string) or $it_died++;
        fclose($fd) or $it_died++;

        //it's rather irresponsible to not report errors when writing this file.
        if ($it_died != 0) {
            $this->error_message = "ERROR. Couldn't write $it_died lines to config file '$this->conffile'.\n";
            return false;
        }

        // Tell PHP that its cached bytecode version of sqlconf.php is no longer usable.
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->conffile, true);
        }

        return true;
    }

    public function insert_globals()
    {
        if (!(function_exists('xl'))) {
            function xl($s)
            {
                return $s;
            }
        } else {
            $GLOBALS['temp_skip_translations'] = true;
        }
        $skipGlobalEvent = true; //use in globals.inc.php script to skip event stuff
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

        $gacl = new GaclApi();

        // Create the ACO sections.  Every ACO must have a section.
        //
        if ($gacl->add_object_section('Accounting', 'acct', 10, 0, 'ACO') === false) {
            $this->error_message = "ERROR, Unable to create the access controls for OpenEMR.";
            return false;
        }
        // xl('Accounting')
        $gacl->add_object_section('Administration', 'admin', 10, 0, 'ACO');
        // xl('Administration')
        $gacl->add_object_section('Encounters', 'encounters', 10, 0, 'ACO');
        // xl('Encounters')
        $gacl->add_object_section('Lists', 'lists', 10, 0, 'ACO');
        // xl('Lists')
        $gacl->add_object_section('Patients', 'patients', 10, 0, 'ACO');
        // xl('Patients')
        $gacl->add_object_section('Squads', 'squads', 10, 0, 'ACO');
        // xl('Squads')
        $gacl->add_object_section('Sensitivities', 'sensitivities', 10, 0, 'ACO');
        // xl('Sensitivities')
        $gacl->add_object_section('Placeholder', 'placeholder', 10, 0, 'ACO');
        // xl('Placeholder')
        $gacl->add_object_section('Nation Notes', 'nationnotes', 10, 0, 'ACO');
        // xl('Nation Notes')
        $gacl->add_object_section('Patient Portal', 'patientportal', 10, 0, 'ACO');
        // xl('Patient Portal')
        $gacl->add_object_section('Menus', 'menus', 10, 0, 'ACO');
        // xl('Menus')
        $gacl->add_object_section('Groups', 'groups', 10, 0, 'ACO');
        // xl('Groups')
        $gacl->add_object_section('Inventory', 'inventory', 10, 0, 'ACO');
        // xl('Inventory')

        // Create Accounting ACOs.
        //
        $gacl->add_object('acct', 'Billing (write optional)', 'bill', 10, 0, 'ACO');
        // xl('Billing (write optional)')
        $gacl->add_object('acct', 'Price Discounting', 'disc', 10, 0, 'ACO');
        // xl('Price Discounting')
        $gacl->add_object('acct', 'EOB Data Entry', 'eob', 10, 0, 'ACO');
        // xl('EOB Data Entry')
        $gacl->add_object('acct', 'Financial Reporting - my encounters', 'rep', 10, 0, 'ACO');
        // xl('Financial Reporting - my encounters')
        $gacl->add_object('acct', 'Financial Reporting - anything', 'rep_a', 10, 0, 'ACO');
        // xl('Financial Reporting - anything')

        // Create Administration ACOs.
        //
        $gacl->add_object('admin', 'Superuser', 'super', 10, 0, 'ACO');
        // xl('Superuser')
        $gacl->add_object('admin', 'Calendar Settings', 'calendar', 10, 0, 'ACO');
        // xl('Calendar Settings')
        $gacl->add_object('admin', 'Database Reporting', 'database', 10, 0, 'ACO');
        // xl('Database Reporting')
        $gacl->add_object('admin', 'Forms Administration', 'forms', 10, 0, 'ACO');
        // xl('Forms Administration')
        $gacl->add_object('admin', 'Practice Settings', 'practice', 10, 0, 'ACO');
        // xl('Practice Settings')
        $gacl->add_object('admin', 'Superbill Codes Administration', 'superbill', 10, 0, 'ACO');
        // xl('Superbill Codes Administration')
        $gacl->add_object('admin', 'Users/Groups/Logs Administration', 'users', 10, 0, 'ACO');
        // xl('Users/Groups/Logs Administration')
        $gacl->add_object('admin', 'Batch Communication Tool', 'batchcom', 10, 0, 'ACO');
        // xl('Batch Communication Tool')
        $gacl->add_object('admin', 'Language Interface Tool', 'language', 10, 0, 'ACO');
        // xl('Language Interface Tool')
        $gacl->add_object('admin', 'Inventory Administration', 'drugs', 10, 0, 'ACO');
        // xl('Inventory Administration')
        $gacl->add_object('admin', 'ACL Administration', 'acl', 10, 0, 'ACO');
        // xl('ACL Administration')
        $gacl->add_object('admin', 'Multipledb', 'multipledb', 10, 0, 'ACO');
        // xl('Multipledb')
        $gacl->add_object('admin', 'Menu', 'menu', 10, 0, 'ACO');
        // xl('Menu')
        $gacl->add_object('admin', 'Manage modules', 'manage_modules', 10, 0, 'ACO');
        // xl('Manage modules')


        // Create ACOs for encounters.
        //
        $gacl->add_object('encounters', 'Authorize - my encounters', 'auth', 10, 0, 'ACO');
        // xl('Authorize - my encounters')
        $gacl->add_object('encounters', 'Authorize - any encounters', 'auth_a', 10, 0, 'ACO');
        // xl('Authorize - any encounters')
        $gacl->add_object('encounters', 'Coding - my encounters (write,wsome optional)', 'coding', 10, 0, 'ACO');
        // xl('Coding - my encounters (write,wsome optional)')
        $gacl->add_object('encounters', 'Coding - any encounters (write,wsome optional)', 'coding_a', 10, 0, 'ACO');
        // xl('Coding - any encounters (write,wsome optional)')
        $gacl->add_object('encounters', 'Notes - my encounters (write,addonly optional)', 'notes', 10, 0, 'ACO');
        // xl('Notes - my encounters (write,addonly optional)')
        $gacl->add_object('encounters', 'Notes - any encounters (write,addonly optional)', 'notes_a', 10, 0, 'ACO');
        // xl('Notes - any encounters (write,addonly optional)')
        $gacl->add_object('encounters', 'Fix encounter dates - any encounters', 'date_a', 10, 0, 'ACO');
        // xl('Fix encounter dates - any encounters')
        $gacl->add_object('encounters', 'Less-private information (write,addonly optional)', 'relaxed', 10, 0, 'ACO');
        // xl('Less-private information (write,addonly optional)')

        // Create ACOs for lists.
        //
        $gacl->add_object('lists', 'Default List (write,addonly optional)', 'default', 10, 0, 'ACO');
        // xl('Default List (write,addonly optional)')
        $gacl->add_object('lists', 'State List (write,addonly optional)', 'state', 10, 0, 'ACO');
        // xl('State List (write,addonly optional)')
        $gacl->add_object('lists', 'Country List (write,addonly optional)', 'country', 10, 0, 'ACO');
        // xl('Country List (write,addonly optional)')
        $gacl->add_object('lists', 'Language List (write,addonly optional)', 'language', 10, 0, 'ACO');
        // xl('Language List (write,addonly optional)')
        $gacl->add_object('lists', 'Ethnicity-Race List (write,addonly optional)', 'ethrace', 10, 0, 'ACO');
        // xl('Ethnicity-Race List (write,addonly optional)')

        // Create ACOs for patientportal.
        //
        $gacl->add_object('patientportal', 'Patient Portal', 'portal', 10, 0, 'ACO');
        // xl('Patient Portal')

        // Create ACOs for modules.
        //
        $gacl->add_object('menus', 'Modules', 'modle', 10, 0, 'ACO');
        // xl('Modules')

        // Create ACOs for patients.
        //
        $gacl->add_object('patients', 'Appointments (write,wsome optional)', 'appt', 10, 0, 'ACO');
        // xl('Appointments (write,wsome optional)')
        $gacl->add_object('patients', 'Demographics (write,addonly optional)', 'demo', 10, 0, 'ACO');
        // xl('Demographics (write,addonly optional)')
        $gacl->add_object('patients', 'Medical/History (write,addonly optional)', 'med', 10, 0, 'ACO');
        // xl('Medical/History (write,addonly optional)')
        $gacl->add_object('patients', 'Transactions (write optional)', 'trans', 10, 0, 'ACO');
        // xl('Transactions (write optional)')
        $gacl->add_object('patients', 'Documents (write,addonly optional)', 'docs', 10, 0, 'ACO');
        // xl('Documents (write,addonly optional)')
        $gacl->add_object('patients', 'Documents Delete', 'docs_rm', 10, 0, 'ACO');
        // xl('Documents Delete')
        $gacl->add_object('patients', 'Patient Notes (write,addonly optional)', 'notes', 10, 0, 'ACO');
        // xl('Patient Notes (write,addonly optional)')
        $gacl->add_object('patients', 'Sign Lab Results (write,addonly optional)', 'sign', 10, 0, 'ACO');
        // xl('Sign Lab Results (write,addonly optional)')
        $gacl->add_object('patients', 'Patient Reminders (write,addonly optional)', 'reminder', 10, 0, 'ACO');
        // xl('Patient Reminders (write,addonly optional)')
        $gacl->add_object('patients', 'Clinical Reminders/Alerts (write,addonly optional)', 'alert', 10, 0, 'ACO');
        // xl('Clinical Reminders/Alerts (write,addonly optional)')
        $gacl->add_object('patients', 'Disclosures (write,addonly optional)', 'disclosure', 10, 0, 'ACO');
        // xl('Disclosures (write,addonly optional)')
        $gacl->add_object('patients', 'Prescriptions (write,addonly optional)', 'rx', 10, 0, 'ACO');
        // xl('Prescriptions (write,addonly optional)')
        $gacl->add_object('patients', 'Amendments (write,addonly optional)', 'amendment', 10, 0, 'ACO');
        // xl('Amendments (write,addonly optional)')
        $gacl->add_object('patients', 'Lab Results (write,addonly optional)', 'lab', 10, 0, 'ACO');
        // xl('Lab Results (write,addonly optional)')
        $gacl->add_object('patients', 'Patient Report', 'pat_rep', 10, 0, 'ACO');
        // xl('Patient Report')


        $gacl->add_object('groups', 'View/Add/Update groups', 'gadd', 10, 0, 'ACO');
        // xl('View/Add/Update groups')
        $gacl->add_object('groups', 'View/Create/Update groups appointment in calendar', 'gcalendar', 10, 0, 'ACO');
        // xl('View/Create/Update groups appointment in calendar')
        $gacl->add_object('groups', 'Group encounter log', 'glog', 10, 0, 'ACO');
        // xl('Group encounter log')
        $gacl->add_object('groups', 'Group detailed log of appointment in patient record', 'gdlog', 10, 0, 'ACO');
        // xl('Group detailed log of appointment in patient record')
        $gacl->add_object('groups', 'Send message from the permanent group therapist to the personal therapist', 'gm', 10, 0, 'ACO');
        // xl('Send message from the permanent group therapist to the personal therapist')

        // Create ACOs for sensitivities.
        //
        $gacl->add_object('sensitivities', 'Normal', 'normal', 10, 0, 'ACO');
        // xl('Normal')
        $gacl->add_object('sensitivities', 'High', 'high', 20, 0, 'ACO');
        // xl('High')

        // Create ACO for placeholder.
        //
        $gacl->add_object('placeholder', 'Placeholder (Maintains empty ACLs)', 'filler', 10, 0, 'ACO');
        // xl('Placeholder (Maintains empty ACLs)')

        // Create ACO for nationnotes.
        //
        $gacl->add_object('nationnotes', 'Nation Notes Configure', 'nn_configure', 10, 0, 'ACO');
        // xl('Nation Notes Configure')

        // Create ACOs for Inventory.
        //
        $gacl->add_object('inventory', 'Lots', 'lots', 10, 0, 'ACO');
        // xl('Lots')
        $gacl->add_object('inventory', 'Sales', 'sales', 20, 0, 'ACO');
        // xl('Sales')
        $gacl->add_object('inventory', 'Purchases', 'purchases', 30, 0, 'ACO');
        // xl('Purchases')
        $gacl->add_object('inventory', 'Transfers', 'transfers', 40, 0, 'ACO');
        // xl('Transfers')
        $gacl->add_object('inventory', 'Adjustments', 'adjustments', 50, 0, 'ACO');
        // xl('Adjustments')
        $gacl->add_object('inventory', 'Consumption', 'consumption', 60, 0, 'ACO');
        // xl('Consumption')
        $gacl->add_object('inventory', 'Destruction', 'destruction', 70, 0, 'ACO');
        // xl('Destruction')
        $gacl->add_object('inventory', 'Reporting', 'reporting', 80, 0, 'ACO');
        // xl('Reporting')

        // Create ARO groups.
        //
        $users = $gacl->add_group('users', 'OpenEMR Users', 0, 'ARO');
        // xl('OpenEMR Users')
        $admin = $gacl->add_group('admin', 'Administrators', $users, 'ARO');
        // xl('Administrators')
        $clin  = $gacl->add_group('clin', 'Clinicians', $users, 'ARO');
        // xl('Clinicians')
        $doc   = $gacl->add_group('doc', 'Physicians', $users, 'ARO');
        // xl('Physicians')
        $front = $gacl->add_group('front', 'Front Office', $users, 'ARO');
        // xl('Front Office')
        $back  = $gacl->add_group('back', 'Accounting', $users, 'ARO');
        // xl('Accounting')
        $breakglass  = $gacl->add_group('breakglass', 'Emergency Login', $users, 'ARO');
        // xl('Emergency Login')


        // Create a Users section for the AROs (humans).
        //
        $gacl->add_object_section('Users', 'users', 10, 0, 'ARO');
        // xl('Users')

        // Create the Administrator in the above-created "users" section
        // and add him/her to the above-created "admin" group.
        // If this script is being used by OpenEMR's setup, then will
        //   incorporate the installation values. Otherwise will
        //    hardcode the 'admin' user.
        if (isset($this) && isset($this->iuser)) {
            $gacl->add_object('users', $this->iuname, $this->iuser, 10, 0, 'ARO');
            $gacl->add_group_object($admin, 'users', $this->iuser, 'ARO');
        } else {
            $gacl->add_object('users', 'Administrator', 'admin', 10, 0, 'ARO');
            $gacl->add_group_object($admin, 'users', 'admin', 'ARO');
        }

        // Declare return terms for language translations
        //  xl('write') xl('wsome') xl('addonly') xl('view')

        // Set permissions for administrators.
        //
        $gacl->add_acl(
            array(
                'acct' => array('bill', 'disc', 'eob', 'rep', 'rep_a'),
                'admin' => array('calendar', 'database', 'forms', 'practice', 'superbill', 'users', 'batchcom', 'language', 'super', 'drugs', 'acl','multipledb','menu','manage_modules'),
                'encounters' => array('auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'),
                'inventory' => array('lots', 'sales', 'purchases', 'transfers', 'adjustments', 'consumption', 'destruction', 'reporting'),
                'lists' => array('default','state','country','language','ethrace'),
                'patients' => array('appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab', 'docs_rm','pat_rep'),
                'sensitivities' => array('normal', 'high'),
                'nationnotes' => array('nn_configure'),
                'patientportal' => array('portal'),
                'menus' => array('modle'),
                'groups' => array('gadd','gcalendar','glog','gdlog','gm')
            ),
            null,
            array($admin),
            null,
            null,
            1,
            1,
            'write',
            'Administrators can do anything'
        );
        // xl('Administrators can do anything')

        // Set permissions for physicians.
        //
        $gacl->add_acl(
            array(
                'patients' => array('pat_rep')
            ),
            null,
            array($doc),
            null,
            null,
            1,
            1,
            'view',
            'Things that physicians can only read'
        );
        // xl('Things that physicians can only read')
        $gacl->add_acl(
            array(
                'placeholder' => array('filler')
            ),
            null,
            array($doc),
            null,
            null,
            1,
            1,
            'addonly',
            'Things that physicians can read and enter but not modify'
        );
        // xl('Things that physicians can read and enter but not modify')
        $gacl->add_acl(
            array(
                'placeholder' => array('filler')
            ),
            null,
            array($doc),
            null,
            null,
            1,
            1,
            'wsome',
            'Things that physicians can read and partly modify'
        );
        // xl('Things that physicians can read and partly modify')
        $gacl->add_acl(
            array(
                'acct' => array('disc', 'rep'),
                'admin' => array('drugs'),
                'encounters' => array('auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'),
                'patients' => array('appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert',
                    'disclosure', 'rx', 'amendment', 'lab'),
                'sensitivities' => array('normal', 'high'),
                'groups' => array('gcalendar','glog')
            ),
            null,
            array($doc),
            null,
            null,
            1,
            1,
            'write',
            'Things that physicians can read and modify'
        );
        // xl('Things that physicians can read and modify')

        // Set permissions for clinicians.
        //
        $gacl->add_acl(
            array(
                'patients' => array('pat_rep')
            ),
            null,
            array($clin),
            null,
            null,
            1,
            1,
            'view',
            'Things that clinicians can only read'
        );
        // xl('Things that clinicians can only read')
        $gacl->add_acl(
            array(
                'encounters' => array('notes', 'relaxed'),
                'patients' => array('demo', 'med', 'docs', 'notes','trans', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab'),
                'sensitivities' => array('normal')
            ),
            null,
            array($clin),
            null,
            null,
            1,
            1,
            'addonly',
            'Things that clinicians can read and enter but not modify'
        );
        // xl('Things that clinicians can read and enter but not modify')
        $gacl->add_acl(
            array(
                'placeholder' => array('filler')
            ),
            null,
            array($clin),
            null,
            null,
            1,
            1,
            'wsome',
            'Things that clinicians can read and partly modify'
        );
        // xl('Things that clinicians can read and partly modify')
        $gacl->add_acl(
            array(
                'admin' => array('drugs'),
                'encounters' => array('auth', 'coding', 'notes'),
                'patients' => array('appt'),
                'groups' => array('gcalendar', 'glog')
            ),
            null,
            array($clin),
            null,
            null,
            1,
            1,
            'write',
            'Things that clinicians can read and modify'
        );
        // xl('Things that clinicians can read and modify')

        // Set permissions for front office staff.
        //
        $gacl->add_acl(
            array(
                'patients' => array('alert')
            ),
            null,
            array($front),
            null,
            null,
            1,
            1,
            'view',
            'Things that front office can only read'
        );
        // xl('Things that front office can only read')
        $gacl->add_acl(
            array(
                'placeholder' => array('filler')
            ),
            null,
            array($front),
            null,
            null,
            1,
            1,
            'addonly',
            'Things that front office can read and enter but not modify'
        );
        // xl('Things that front office can read and enter but not modify')
        $gacl->add_acl(
            array(
                'placeholder' => array('filler')
            ),
            null,
            array($front),
            null,
            null,
            1,
            1,
            'wsome',
            'Things that front office can read and partly modify'
        );
        // xl('Things that front office can read and partly modify')
        $gacl->add_acl(
            array(
                'patients' => array('appt', 'demo'),
                'groups' => array('gcalendar')
            ),
            null,
            array($front),
            null,
            null,
            1,
            1,
            'write',
            'Things that front office can read and modify'
        );
        // xl('Things that front office can read and modify')

        // Set permissions for back office staff.
        //
        $gacl->add_acl(
            array(
                'patients' => array('alert')
            ),
            null,
            array($back),
            null,
            null,
            1,
            1,
            'view',
            'Things that back office can only read'
        );
        // xl('Things that back office can only read')
        $gacl->add_acl(
            array(
                'placeholder' => array('filler')
            ),
            null,
            array($back),
            null,
            null,
            1,
            1,
            'addonly',
            'Things that back office can read and enter but not modify'
        );
        // xl('Things that back office can read and enter but not modify')
        $gacl->add_acl(
            array(
                'placeholder' => array('filler')
            ),
            null,
            array($back),
            null,
            null,
            1,
            1,
            'wsome',
            'Things that back office can read and partly modify'
        );
        // xl('Things that back office can read and partly modify')
        $gacl->add_acl(
            array(
                'acct' => array('bill', 'disc', 'eob', 'rep', 'rep_a'),
                'admin' => array('practice', 'superbill'),
                'encounters' => array('auth_a', 'coding_a', 'date_a'),
                'patients' => array('appt', 'demo')
            ),
            null,
            array($back),
            null,
            null,
            1,
            1,
            'write',
            'Things that back office can read and modify'
        );
        // xl('Things that back office can read and modify')

        // Set permissions for Emergency Login.
        //
        $gacl->add_acl(
            array(
                'acct' => array('bill', 'disc', 'eob', 'rep', 'rep_a'),
                'admin' => array('calendar', 'database', 'forms', 'practice', 'superbill', 'users', 'batchcom', 'language', 'super', 'drugs', 'acl','multipledb','menu','manage_modules'),
                'encounters' => array('auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'),
                'inventory' => array('lots', 'sales', 'purchases', 'transfers', 'adjustments', 'consumption', 'destruction', 'reporting'),
                'lists' => array('default','state','country','language','ethrace'),
                'patients' => array('appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab', 'docs_rm','pat_rep'),
                'sensitivities' => array('normal', 'high'),
                'nationnotes' => array('nn_configure'),
                'patientportal' => array('portal'),
                'menus' => array('modle'),
                'groups' => array('gadd','gcalendar','glog','gdlog','gm')
            ),
            null,
            array($breakglass),
            null,
            null,
            1,
            1,
            'write',
            'Emergency Login user can do anything'
        );
        // xl('Emergency Login user can do anything')

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
            // Using @ in below call to hide the php warning in cases where the
            //  below connection does not work, which is expected behavior.
            // Using try in below call to catch the mysqli exception when the
            //  below connection does not work, which is expected behavior (needed to
            //  add this try/catch clause for PHP 8.1).
            try {
                $checkUserDatabaseConnection = @$this->user_database_connection();
            } catch (Exception $e) {
                $checkUserDatabaseConnection = false;
            }
            if (! $checkUserDatabaseConnection) {
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

            if (! $this->install_additional_users()) {
                return false;
            }

            if (! $this->on_care_coordination()) {
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
                error_log("ERROR IN OPENEMR INSTALL: Unable to execute SQL: " . htmlspecialchars($sql, ENT_QUOTES) . " due to: " . htmlspecialchars($error_mes, ENT_QUOTES));
            }
            return false;
        }
    }

    private function connect_to_database($server, $user, $password, $port, $dbname = '')
    {
        $pathToCerts = __DIR__ . "/../../sites/" . $this->site . "/documents/certificates/";
        $mysqlSsl = false;
        $mysqli = mysqli_init();
        if (defined('MYSQLI_CLIENT_SSL') && file_exists($pathToCerts . "mysql-ca")) {
            $mysqlSsl = true;
            if (
                file_exists($pathToCerts . "mysql-key") &&
                file_exists($pathToCerts . "mysql-cert")
            ) {
                // with client side certificate/key
                mysqli_ssl_set(
                    $mysqli,
                    $pathToCerts . "mysql-key",
                    $pathToCerts . "mysql-cert",
                    $pathToCerts . "mysql-ca",
                    null,
                    null
                );
            } else {
                // without client side certificate/key
                mysqli_ssl_set(
                    $mysqli,
                    null,
                    null,
                    $pathToCerts . "mysql-ca",
                    null,
                    null
                );
            }
        }
        try {
            if ($mysqlSsl) {
                $ok = mysqli_real_connect($mysqli, $server, $user, $password, $dbname, (int)$port != 0 ? (int)$port : 3306, '', MYSQLI_CLIENT_SSL);
            } else {
                $ok = mysqli_real_connect($mysqli, $server, $user, $password, $dbname, (int)$port != 0 ? (int)$port : 3306);
            }
        } catch (mysqli_sql_exception $e) {
            $this->error_message = "unable to connect to sql server because of mysql error: " . $e->getMessage();
            return false;
        }
        if (!$ok) {
            $this->error_message = 'unable to connect to sql server because of: (' . mysqli_connect_errno() . ') ' . mysqli_connect_error();
            return false;
        }
        return $mysqli;
    }

    private function set_sql_strict()
    {
        // Turn off STRICT SQL
        return $this->execute_sql("SET sql_mode = ''");
    }

    private function set_collation()
    {
        return $this->execute_sql("SET NAMES 'utf8mb4'");
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
        " -h " . $host .
        " -p" . escapeshellarg($pass) .
        " --ignore-table=" . escapeshellarg($dbase . ".onsite_activity_view") . " --hex-blob --opt --skip-extended-insert --quote-names -r $backup_file " .
        escapeshellarg($dbase);

        $tmp1 = [];
        $tmp0 = exec($cmd, $tmp1, $tmp2);
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
    //RP_ADDED
    public function getCurrentTheme()
    {
        $current_theme =  $this->execute_sql("SELECT gl_value FROM globals WHERE gl_name LIKE '%css_header%'");
        $current_theme = mysqli_fetch_array($current_theme);
        return $current_theme[0];
    }

    public function setCurrentTheme()
    {
        $current_theme = $this->getCurrentTheme();
        // for cloned sites since they're not asked about a new theme
        if (!$this->new_theme) {
            $this->new_theme = $current_theme;
        }
        return $this->execute_sql("UPDATE globals SET gl_value='" . $this->escapeSql($this->new_theme) . "' WHERE gl_name LIKE '%css_header%'");
    }

    public function listThemes()
    {
        $themes_img_dir = "public/images/stylesheets/";
        $arr_themes_img = array_values(array_filter(scandir($themes_img_dir), function ($item) {
            return $item[0] !== '.';
        }));
        return $arr_themes_img;
    }

    private function extractFileName($theme_file_name = '')
    {
        $this->theme_file_name = $theme_file_name;
        $under_score = strpos($theme_file_name, '_') + 1;
        $dot = strpos($theme_file_name, '.');
        $theme_value = substr($theme_file_name, $under_score, ($dot - $under_score));
        $theme_title = ucwords(str_replace("_", " ", $theme_value));
        return array('theme_value' => $theme_value, 'theme_title' => $theme_title);
    }

    public function displayThemesDivs()
    {
        $themes_number = count($this->listThemes());
        for ($i = 0; $i < $themes_number; $i++) {
            $id = $i + 1;
            $arr_theme_name = $this->listThemes();
            $theme_file_name = $arr_theme_name[$i];
            $arr_extracted_file_name = $this->extractFileName($theme_file_name);
            $theme_value = $arr_extracted_file_name['theme_value'];
            $theme_title = $arr_extracted_file_name['theme_title'];
            $img_path = "public/images/stylesheets/";
            $theme_file_path = $img_path . $theme_file_name;
            $div_start = "                      <div class='row'>";
            $div_end = "                      </div>";
            $img_div = "                <div class='col-sm-2 checkboxgroup'>
                                            <label for='my_radio_button_id" . attr($id) . "'><img height='160px' src='" . attr($theme_file_path) . "' width='100%'></label>
                                            <p class='m-0'>" . text($theme_title) . "</p><input id='my_radio_button_id" . attr($id) . "' name='stylesheet' type='radio' value='" . attr($theme_value) . "'>
                                        </div>";
            $theme_img_number = $i % 6; //to ensure that last file in array will always generate 5 and will end the row
            switch ($theme_img_number) {
                case 0: //start row
                    echo $div_start . "\r\n";
                    echo $img_div . "\r\n";
                    break;

                case 1:
                case 2:
                case 3:
                case 4:
                    echo $img_div . "\r\n";
                    break;

                case 5://end row
                    echo $img_div . "\r\n";
                    echo $div_end . "\r\n";
                    echo "<br />" . "\r\n";
                    break;

                default:
                    echo $div_start . "\r\n";
                    echo "<h5>Sorry no stylesheet images in directory</h5>";
                    echo $div_end . "\r\n";
                    break;
            }
        }
        return;
    }

    public function displaySelectedThemeDiv()
    {
        $theme_file_name = $this->getCurrentTheme();
        $arr_extracted_file_name = $this->extractFileName($theme_file_name);
        $theme_value = $arr_extracted_file_name['theme_value'];
        $theme_title = $arr_extracted_file_name['theme_title'];
        $img_path = "public/images/stylesheets/";
        $theme_file_path = $img_path . "style_" . $theme_value . ".png";

        $display_selected_theme_div = <<<DSTD
                        <div class="row">
                            <div class="col-sm-12">
                                <h4>Current Theme:</h4>
                                <div class="col-sm-4 offset-sm-4 checkboxgroup">
                                    <label for="nothing"><img  id="current_theme" src="{$theme_file_path}" width="100%"></label>
                                    <p id="current_theme_title"style="margin:0">{$theme_title}</p>
                                </div>
                            </div>
                        </div>
                        <br />
DSTD;
        echo $display_selected_theme_div . "\r\n";
        return;
    }

    public function displayNewThemeDiv()
    {
        // cloned sites don't get a chance to set a new theme
        if (!$this->new_theme) {
            $this->new_theme = $this->getCurrentTheme();
        }
        $theme_file_name = $this->new_theme;
        $arr_extracted_file_name = $this->extractFileName($theme_file_name);
        $theme_value = $arr_extracted_file_name['theme_value'];
        $theme_title = $arr_extracted_file_name['theme_title'];
        $img_path = "public/images/stylesheets/";
        $theme_file_path = $img_path . "style_" . $theme_value . ".png";

        $display_selected_theme_div = <<<DSTD
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="col-sm-4 offset-sm-4 checkboxgroup">
                                    <label for="nothing"><img  id="current_theme" src="{$theme_file_path}" width="75%"></label>
                                    <p id="current_theme_title"style="margin:0">{$theme_title}</p>
                                </div>
                            </div>
                        </div>
                        <br />
DSTD;
        echo $display_selected_theme_div . "\r\n";
        return;
    }

    public function setupHelpModal()
    {
        $setup_help_modal = <<<SETHLP
    <div class="row">
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content  oe-modal-content" style="height:700px">
                        <div class="modal-header clearfix">
                            <button type="button" class="close" data-dismiss="modal" aria-label=Close>
                            <span aria-hidden="true" style="color:var(--black); font-size:1.5em;">×</span></button>
                        </div>
                        <div class="modal-body" style="height:80%;">
                            <iframe src="" id="targetiframe" style="height:100%; width:100%; overflow-x: hidden; border:none"
                            allowtransparency="true"></iframe>
                        </div>
                        <div class="modal-footer" style="margin-top:0px;">
                           <button class="btn btn-link btn-cancel oe-pull-away" data-dismiss="modal" type="button">Close</button>
                           <!--<button class="btn btn-secondary btn-print oe-pull-away" data-dismiss="modal" id="print-help-href" type="button">Print</button>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(function () {
                $('#help-href').click (function(){
                    document.getElementById('targetiframe').src = "Documentation/help_files/openemr_installation_help.php";
                })
            });
            $(function () {
                $('#print-help-href').click (function(){
                    $("#targetiframe").get(0).contentWindow.print();
                })
            });
            // Jquery draggable
            $(".modal-dialog").addClass('drag-action');
            $(".modal-content").addClass('resize-action');
        </script>
SETHLP;
        echo $setup_help_modal  . "\r\n";
        return;
    }
}
