<?php

/**
 * Installer class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Andrew Moore <amoore@cpan.org>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2010 Andrew Moore <amoore@cpan.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Gacl\GaclApi;

class Installer
{
    public array $custom_globals;
    public array $dumpfiles;
    public mysqli|false $dbh;
    public string $additional_users;
    public string $clone_database;
    public string $collate;
    public string $conffile;
    public string $cvx;
    public string $dbname;
    public string $debug_message;
    public string $devel_translation_sql;
    public string $development_translations;
    public string $error_message;
    public string $i2faEnable;
    public string $i2faSecret;
    public string $igroup;
    public string $ippf_specific;
    public string $ippf_sql;
    public string $iufname;
    public string $iuname;
    public string $iuser;
    public string $iuserpass;
    public string $login;
    public string $loginhost;
    public string $main_sql;
    public string $new_theme;
    public string $no_root_db_access;
    public string $pass;
    public string $port;
    public string $root;
    public string $rootpass;
    public string $server;
    public string $site;
    public string $source_site_id;
    public string $translation_sql;

    /**
     * Initialize the Installer with configuration variables.
     *
     * @param array $cgi_variables Configuration array containing installation parameters
     */
    public function __construct(array $cgi_variables)
    {
        // Installation variables
        // For a good explanation of these variables, see documentation in
        //   the contrib/util/installScripts/InstallerAuto.php file.
        $this->iuser                    = $cgi_variables['iuser'] ?? '';
        $this->iuserpass                = $cgi_variables['iuserpass'] ?? '';
        $this->iuname                   = $cgi_variables['iuname'] ?? '';
        $this->iufname                  = $cgi_variables['iufname'] ?? '';
        $this->igroup                   = $cgi_variables['igroup'] ?? '';
        $this->i2faEnable               = $cgi_variables['i2faenable'] ?? '';
        $this->i2faSecret               = $cgi_variables['i2fasecret'] ?? '';
        $this->server                   = $cgi_variables['server'] ?? ''; // mysql server (usually localhost)
        $this->loginhost                = $cgi_variables['loginhost'] ?? ''; // php/apache server (usually localhost)
        $this->port                     = $cgi_variables['port'] ?? '';
        $this->root                     = $cgi_variables['root'] ?? '';
        $this->rootpass                 = $cgi_variables['rootpass'] ?? '';
        $this->login                    = $cgi_variables['login'] ?? '';
        $this->pass                     = $cgi_variables['pass'] ?? '';
        $this->dbname                   = $cgi_variables['dbname'] ?? '';
        $this->collate                  = $cgi_variables['collate'] ?? '';
        $this->site                     = $cgi_variables['site'] ?? 'default'; // set to default if not set in order for install script to work correctly
        $this->source_site_id           = $cgi_variables['source_site_id'] ?? '';
        $this->clone_database           = $cgi_variables['clone_database'] ?? '';
        $this->no_root_db_access        = $cgi_variables['no_root_db_access'] ?? ''; // no root access to database. user/privileges pre-configured
        $this->development_translations = $cgi_variables['development_translations'] ?? '';
        $this->new_theme                = $cgi_variables['new_theme'] ?? '';
        $this->custom_globals           = isset($cgi_variables['custom_globals']) ? json_decode($cgi_variables['custom_globals'], true) : [];
        // Make this true for IPPF.
        $this->ippf_specific = false;

        // Record name of sql access file
        $GLOBALS['OE_SITES_BASE'] = __DIR__ . '/../../sites';
        $GLOBALS['OE_SITE_DIR'] = $GLOBALS['OE_SITES_BASE'] . '/' . $this->site;
        $this->conffile  =  $GLOBALS['OE_SITE_DIR'] . '/sqlconf.php';

        // Record names of sql table files
        $this->main_sql = __DIR__ . '/../../sql/database.sql';
        $this->translation_sql = __DIR__ . '/../../contrib/util/language_translations/currentLanguage_utf8.sql';
        $this->devel_translation_sql = "http://translations.openemr.io/languageTranslations_utf8.sql";
        $this->ippf_sql = __DIR__ . "/../../sql/ippf_layout.sql";
        $this->cvx = __DIR__ . "/../../sql/cvx_codes.sql";
        $this->additional_users = __DIR__ . "/../../sql/official_additional_users.sql";

        // Prepare the dumpfile list
        $this->initialize_dumpfile_list();

        // Entities to hold error and debug messages
        $this->error_message = '';
        $this->debug_message = '';

        // Entity to hold sql connection
        $this->dbh = false;
    }

    /**
     * Validate if the database login is valid.
     *
     * @return bool True if login is valid, false otherwise
     */
    public function login_is_valid(): bool
    {
        if ($this->login === '') {
            $this->error_message = "login is invalid: '$this->login'";
            return false;
        }

        return true;
    }

    /**
     * Validate if input text contains only safe characters.
     *
     * Prevents PHP injection by checking for dangerous characters.
     *
     * @param string $input_text Text to validate
     * @return bool True if text is safe, false otherwise
     */
    public function char_is_valid(?string $input_text): bool
    {
        $input_text ??= '';
        // to prevent php injection
        $input_text = trim($input_text);
        if ($input_text == '') {
            return false;
        }

        if (preg_match('@[\\\\;()<>/\'"]@', $input_text)) {
            return false;
        }

        return true;
    }

    /**
     * Validate if database name contains only allowed characters.
     *
     * @param string $name Database name to validate
     * @return bool True if name is valid, false otherwise
     */
    public function databaseNameIsValid(?string $name): bool
    {
        if (empty($name)) {
            return false;
        }
        if (preg_match('/[^A-Za-z0-9_-]/', $name)) {
            return false;
        }
        return true;
    }

    /**
     * Validate if collation name contains only allowed characters.
     *
     * @param string $name Collation name to validate
     * @return bool True if name is valid, false otherwise
     */
    public function collateNameIsValid(?string $name): bool
    {

        if (empty($name)) {
            return false;
        }
        if (preg_match('/[^A-Za-z0-9_-]/', $name)) {
            return false;
        }
        return true;
    }

    /**
     * Validate if the initial user name is valid.
     *
     * @return bool True if initial user is valid, false otherwise
     */
    public function iuser_is_valid(): bool
    {
        if (strpos($this->iuser, " ")) {
            $this->error_message = "Initial user is invalid: '$this->iuser'";
            return false;
        }

        return true;
    }

    /**
     * Validate if the initial user last name is valid.
     *
     * @return bool True if initial user last name is valid, false otherwise
     */
    public function iuname_is_valid(): bool
    {
        if ($this->iuname === '') {
            $this->error_message = "Initial user last name is invalid: '$this->iuname'";
            return false;
        }

        return true;
    }

    /**
     * Validate if the database password is valid.
     *
     * @return bool True if password is valid, false otherwise
     */
    public function password_is_valid(): bool
    {
        if ($this->pass === '') {
            $this->error_message = "The password for the new database account is invalid: '$this->pass'";
            return false;
        }

        return true;
    }

    /**
     * Validate if the initial user password is valid.
     *
     * @return bool True if user password is valid, false otherwise
     */
    public function user_password_is_valid(): bool
    {
        if ($this->iuserpass === '') {
            $this->error_message = "The password for the user is invalid: '$this->iuserpass'";
            return false;
        }

        return true;
    }

    /**
     * Establish a database connection using root credentials.
     *
     * Connects to the database server using root privileges and sets strict SQL mode.
     *
     * @return bool True if connection successful, false otherwise
     */
    public function root_database_connection(): bool
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

    /**
     * Establish a database connection using user credentials.
     *
     * Connects to the database server using the configured user account,
     * sets strict SQL mode, collation, and selects the target database.
     *
     * @return bool True if connection successful, false otherwise
     */
    public function user_database_connection(): bool
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

        if (! $this->mysqliSelectDb($this->dbh, $this->dbname)) {
            $this->error_message = "unable to select database: '$this->dbname'";
            return false;
        }

        return true;
    }

    /**
     * Create the target database with UTF8MB4 character set.
     *
     * Creates the database using the configured name and collation,
     * defaulting to utf8mb4_general_ci if not specified.
     *
     * @return bool True if database creation successful, false otherwise
     */
    public function create_database(): bool
    {
        $sql = "create database " . $this->escapeDatabaseName($this->dbname);
        if (empty($this->collate) || ($this->collate == 'utf8_general_ci')) {
            $this->collate = 'utf8mb4_general_ci';
        }
        $sql .= " character set utf8mb4 collate " . $this->escapeCollateName($this->collate);
        $this->set_collation();

        return $this->execute_sql($sql);
    }

    /**
     * Drop the target database if it exists.
     *
     * @return bool True if database drop successful, false otherwise
     */
    public function drop_database(): bool
    {
        $sql = "drop database if exists " . $this->escapeDatabaseName($this->dbname);
        return $this->execute_sql($sql);
    }

    /**
     * Create or update the database user account.
     *
     * Checks if the user exists in mysql.user (or mysql.global_priv for MariaDB 10.4+),
     * creates the user if it doesn't exist, or updates the password if it does.
     * Supports X509 and SSL connection requirements based on environment variables.
     *
     * @return mysqli_result|bool Query result or false on error
     */
    public function create_database_user(): mysqli_result|bool
    {
        $escapedLogin = $this->escapeSql($this->login);
        $escapedHost = $this->escapeSql($this->loginhost);
        $escapedPass = $this->escapeSql($this->pass);

        // First, check for database user in the mysql.user table (this works for all except mariadb 10.4+)
        $checkUser = $this->execute_sql("SELECT user FROM mysql.user WHERE user = '{$escapedLogin}' AND host = '{$escapedHost}'", false);
        if ($checkUser === false) {
            // Above caused error, so is MariaDB 10.4+, and need to do below query instead in the mysql.global_priv table
            $checkUser = $this->execute_sql("SELECT user FROM mysql.global_priv WHERE user = '{$escapedLogin}' AND host = '{$escapedHost}'");
        }

        if ($checkUser === false) {
            // there was an error in the check database user query, so return false
            return false;
        } elseif ($this->mysqliNumRows($checkUser) > 0) {
            // the mysql user already exists, so do not need to create the user, but need to set the password
            // Note need to try two different methods, first is for newer mysql versions and second is for older mysql versions (if the first method fails)
            $returnSql = $this->execute_sql("ALTER USER '{$escapedLogin}'@'{$escapedHost}' IDENTIFIED BY '{$escapedPass}'", false);
            if ($returnSql === false) {
                error_log("Using older mysql version method to set password for the mysql user");
                $returnSql = $this->execute_sql("SET PASSWORD FOR '{$escapedLogin}'@'{$escapedHost}' = PASSWORD('{$escapedPass}')");
            }
            return $returnSql;
        } else {
            // the mysql user does not yet exist, so create the user
            if (getenv('FORCE_DATABASE_X509_CONNECT', true) == 1) {
                // this use case is to allow enforcement of x509 database connection use in applicable docker and kubernetes auto installations
                return $this->execute_sql("CREATE USER '{$escapedLogin}'@'{$escapedHost}' IDENTIFIED BY '{$escapedPass}' REQUIRE X509");
            } elseif (getenv('FORCE_DATABASE_SSL_CONNECT', true) == 1) {
                // this use case is to allow enforcement of ssl database connection use in applicable docker and kubernetes auto installations
                return $this->execute_sql("CREATE USER '{$escapedLogin}'@'{$escapedHost}' IDENTIFIED BY '{$escapedPass}' REQUIRE SSL");
            } else {
                return $this->execute_sql("CREATE USER '{$escapedLogin}'@'{$escapedHost}' IDENTIFIED BY '{$escapedPass}'");
            }
        }
    }

    /**
     * Grant all privileges on the database to the user account.
     *
     * @return bool True if privileges granted successfully, false otherwise
     */
    public function grant_privileges(): bool
    {
        return $this->execute_sql("GRANT ALL PRIVILEGES ON " . $this->escapeDatabaseName($this->dbname) . ".* TO '" . $this->escapeSql($this->login) . "'@'" . $this->escapeSql($this->loginhost) . "'");
    }

  /**
   * This method creates any dumpfiles necessary.
   * This is actually only done if we're cloning an existing site
   * and we need to dump their database into a file.
   *
   * @return string name of the backup file
   */
    public function create_dumpfiles(): string
    {
        return $this->dumpSourceDatabase();
    }

    /**
     * Load all configured database dump files.
     *
     * Iterates through the list of dump files and loads each one,
     * accumulating results and returning combined output.
     *
     * @return string|false Combined results from all loaded files, or false on error
     */
    public function load_dumpfiles(): string|false
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

    /**
     * Load and execute SQL commands from a database dump file.
     *
     * Opens the specified file, reads it line by line, and executes
     * SQL statements. Uses transactions for improved performance with InnoDB.
     * Ignores comment lines starting with -- or #.
     *
     * @param string $filename Path to the SQL dump file
     * @param string $title Descriptive title for the operation
     * @return string|false Success message or false on error
     */
    public function load_file(string $filename, string $title): string|false
    {
        $sql_results = ''; // information string which is returned
        $sql_results .= "Creating $title tables...\n";
        $fd = $this->openFile($filename, 'r');
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

        while (!$this->atEndOfFile($fd)) {
            $line = $this->getLine($fd, 1024);
            $line = rtrim($line);
            if ($line === "" || str_starts_with($line, "--") || str_starts_with($line, "#")) {
                continue;
            }

            $query .= $line;          // Check for full query
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
        $this->closeFile($fd);
        return $sql_results;
    }

    /**
     * Add version information to the database.
     *
     * Loads version constants from version.php and updates the version table
     * with current OpenEMR version information.
     *
     * @return bool True if version info added successfully, false otherwise
     */
    public function add_version_info(): bool
    {
        include __DIR__ . "/../../version.php";
        /**
         * This annotation declares variables from the legacy include
         * so PHPStan recognizes them.
         *
         * @var string $v_major
         * @var string $v_minor
         * @var string $v_patch
         * @var string $v_realpatch
         * @var string $v_tag
         * @var string $v_database
         * @var string $v_acl
         */
        $version_fields = array_map($this->escapeSql(...), [
            'v_major' => $v_major,
            'v_minor' => $v_minor,
            'v_patch' => $v_patch,
            'v_realpatch' => $v_realpatch,
            'v_tag' => $v_tag,
            'v_database' => $v_database,
            'v_acl' => $v_acl
        ]);
        $update_parts = array_map(fn($field): string => sprintf("%s = '%s'", $field, $version_fields[$field]), array_keys($version_fields));

        // Join the parts with commas
        $update_sql = "UPDATE version SET " . implode(", ", $update_parts);

        if ($this->execute_sql($update_sql) !== false) {
            return true;
        }
        $this->error_message = "ERROR. Unable insert version information into database\n" .
        "<p>" . $this->mysqliError($this->dbh) . " (#" . $this->mysqliErrno($this->dbh) . ")\n";
        return false;
    }

    /**
     * Add the initial administrator user to the database.
     *
     * Creates the initial user group, user account, secure password hash,
     * and optionally sets up 2FA if enabled during installation.
     *
     * @return bool True if initial user added successfully, false otherwise
     */
    public function add_initial_user(): bool
    {
        $escapedGroup = $this->escapeSql($this->igroup);
        $escapedUser = $this->escapeSql($this->iuser);
        $escapedFirstName = $this->escapeSql($this->iufname);
        $escapedLastName = $this->escapeSql($this->iuname);
        if ($this->execute_sql("INSERT INTO `groups` (id, name, user) VALUES (1,'{$escapedGroup}', '{$escapedUser}')") == false) {
            $this->error_message = "ERROR. Unable to add initial user group\n" .
            "<p>" . $this->mysqliError($this->dbh) . " (#" . $this->mysqliErrno($this->dbh) . ")\n";
            return false;
        }

        if ($this->execute_sql("INSERT INTO users (id, username, password, authorized, lname, fname, facility_id, calendar, cal_ui) VALUES (1,'{$escapedUser}','NoLongerUsed',1,'{$escapedLastName}','{$escapedFirstName}',3,1,3)") == false) {
            $this->error_message = "ERROR. Unable to add initial user\n" .
            "<p>" . $this->mysqliError($this->dbh) . " (#" . $this->mysqliErrno($this->dbh) . ")\n";
            return false;
        }

        $hash = password_hash($this->iuserpass, PASSWORD_DEFAULT);
        $escapedHash = $this->escapeSql($hash);
        if (empty($hash)) {
            // Something is seriously wrong
            error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
            $this->die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
        }
        if ($this->execute_sql("INSERT INTO users_secure (id, username, password, last_update_password) VALUES (1,'{$escapedUser}','{$escapedHash}',NOW())") == false) {
            $this->error_message = "ERROR. Unable to add initial user login credentials\n" .
            "<p>" . $this->mysqliError($this->dbh) . " (#" . $this->mysqliErrno($this->dbh) . ")\n";
            return false;
        }

        // Create new 2fa if enabled
        if (($this->i2faEnable) && (!empty($this->i2faSecret)) && $this->totpClassExists() && $this->cryptoGenClassExists()) {
            // Encrypt the new secret with the hashed password
            $secret = $this->encryptTotpSecret($this->i2faSecret, $hash);
            $escapedSecret = $this->escapeSql($secret);
            if ($this->execute_sql("INSERT INTO login_mfa_registrations (user_id, name, method, var1, var2) VALUES (1, 'App Based 2FA', 'TOTP', '{$escapedSecret}', '')") == false) {
                $this->error_message = "ERROR. Unable to add initial user's 2FA credentials\n" .
                    "<p>" . $this->mysqliError($this->dbh) . " (#" . $this->mysqliErrno($this->dbh) . ")\n";
                return false;
            }
        }

        return true;
    }

    /**
     * Handle the additional users now that our gacl's have finished installing.
     *
     * @return bool
     */
    public function install_additional_users(): bool
    {
        // Add the official openemr users (services)
        if ($this->load_file($this->additional_users, "Additional Official Users") == false) {
            return false;
        }
        return true;
    }

    /**
     * Configure Care Coordination module ACL permissions.
     *
     * Sets up module access control by linking the Carecoordination module
     * to the admin group with appropriate permissions.
     *
     * @return bool True if configuration successful, false otherwise
     */
    public function on_care_coordination(): bool
    {
        $resource = $this->execute_sql("SELECT `mod_id` FROM `modules` WHERE `mod_name` = 'Carecoordination' LIMIT 1");
        $resource_array = $this->mysqliFetchArray($resource, MYSQLI_ASSOC);
        $modId = $resource_array['mod_id'];
        if (empty($modId)) {
            $this->error_message = "ERROR configuring Care Coordination module. Unable to get mod_id for Carecoordination module\n";
            return false;
        }

        $resource = $this->execute_sql("SELECT `section_id` FROM `module_acl_sections` WHERE `section_identifier` = 'carecoordination' LIMIT 1");
        $resource_array = $this->mysqliFetchArray($resource, MYSQLI_ASSOC);
        $sectionId = $resource_array['section_id'];
        if (empty($sectionId)) {
            $this->error_message = "ERROR configuring Care Coordination module. Unable to get section_id for carecoordination module section\n";
            return false;
        }

        $resource = $this->execute_sql("SELECT `id` FROM `gacl_aro_groups` WHERE `value` = 'admin' LIMIT 1");
        $resource_array = $this->mysqliFetchArray($resource, MYSQLI_ASSOC);
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
     *
     * @return Totp|false
     */
    public function get_initial_user_mfa_totp(): Totp|false
    {
        if (($this->i2faEnable) && (!empty($this->i2faSecret)) && $this->totpClassExists()) {
            return $this->createTotpInstance($this->i2faSecret, $this->iuser);
        }
        return false;
    }

  /**
   * Create site directory if it is missing.
   *
   * @global string $GLOBALS['OE_SITE_DIR'] contains the name of the site directory to create
   * @return bool true if the site directory was created or false if it already exists
   */
    public function create_site_directory(): bool
    {
        if (!$this->fileExists($GLOBALS['OE_SITE_DIR'])) {
            $source_directory      = $GLOBALS['OE_SITES_BASE'] . "/" . $this->source_site_id;
            $destination_directory = $GLOBALS['OE_SITE_DIR'];
            if (! $this->recurse_copy($source_directory, $destination_directory)) {
                $this->error_message = "unable to copy directory: '$source_directory' to '$destination_directory'. " . $this->error_message;
                return false;
            }
            // the new site will create it's own keys so okay to delete these copied from the source site
            if (!$this->clone_database) {
                $files = $this->globPattern($destination_directory . "/documents/logs_and_misc/methods/*");
                if ($files !== false) {
                    array_map($this->unlinkFile(...), $files);
                }
            }
        }

        return true;
    }

    /**
     * Write the database configuration file (sqlconf.php).
     *
     * Creates the site directory if needed and writes the database
     * connection configuration to the sqlconf.php file.
     *
     * @return bool True if configuration written successfully, false otherwise
     */
    public function write_configuration_file(): bool
    {
        if (!$this->fileExists($GLOBALS['OE_SITE_DIR'])) {
            $this->create_site_directory();
        }
        @$this->touchFile($this->conffile); // php bug
        $fd = @$this->openFile($this->conffile, 'w');
        if (! $fd) {
            $this->error_message = 'unable to open configuration file for writing: ' . $this->conffile;
            return false;
        }

        $string = '<?php
//  OpenEMR
//  MySQL Config

';

        $it_died = 0;   //fmg: variable keeps running track of any errors

        $this->writeToFile($fd, $string) or $it_died++;
        $this->writeToFile($fd, "global \$disable_utf8_flag;\n") or $it_died++;
        $this->writeToFile($fd, "\$disable_utf8_flag = false;\n\n") or $it_died++;
        $this->writeToFile($fd, "\$host\t= '$this->server';\n") or $it_died++;
        $this->writeToFile($fd, "\$port\t= '$this->port';\n") or $it_died++;
        $this->writeToFile($fd, "\$login\t= '$this->login';\n") or $it_died++;
        $this->writeToFile($fd, "\$pass\t= '$this->pass';\n") or $it_died++;
        $this->writeToFile($fd, "\$dbase\t= '$this->dbname';\n") or $it_died++;
        $this->writeToFile($fd, "\$db_encoding\t= 'utf8mb4';\n") or $it_died++;

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

        $this->writeToFile($fd, $string) or $it_died++;
        $this->closeFile($fd) or $it_died++;

        //it's rather irresponsible to not report errors when writing this file.
        if ($it_died != 0) {
            $this->error_message = "ERROR. Couldn't write $it_died lines to config file '$this->conffile'.\n";
            return false;
        }

        // Tell PHP that its cached bytecode version of sqlconf.php is no longer usable.
        // @codeCoverageIgnoreStart
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->conffile, true);
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    /**
     * Insert global configuration settings into the database.
     *
     * Loads the global settings metadata and inserts default values
     * into the globals table for system configuration.
     *
     * @return true Always returns true
     */
    public function insert_globals(): true
    {
        $GLOBALS['temp_skip_translations'] = true;
        $skipGlobalEvent = true; // use in globals.inc.php script to skip event stuff
        require(__DIR__ . '/../globals.inc.php');
        /** @phpstan-ignore variable.undefined */
        foreach ($GLOBALS_METADATA as $grparr) {
            foreach ($grparr as $fldid => $fldarr) {
                [$fldname, $fldtype, $flddef, $flddesc] = $fldarr;
                if (is_array($fldtype) || !str_starts_with((string) $fldtype, 'm_')) {
                    $this->writeGlobal($fldid, $flddef, 0, true);
                }
            }
        }

        return true;
    }

    /**
     * Write a global setting to the database
     *
     * @param string $name Global name
     * @param string $value Global value
     * @param int $index Global index (default: 0)
     * @param bool $insert_only If true, only insert if not exists; if false, use REPLACE INTO (default: false)
     * @return bool True on success, false on failure
     */
    protected function writeGlobal(string $name, string $value, int $index = 0, bool $insert_only = false): bool
    {
        $expression = "%s INTO globals ( gl_name, gl_index, gl_value ) VALUES ( '%s', '%d', '%s' )";
        if ($insert_only) {
            $check_sql = "SELECT count(*) AS count FROM globals WHERE gl_name = '%s'";
            $sql = sprintf($check_sql, $this->escapeSql($name));
            $res = $this->execute_sql($sql);
            $row = $this->mysqliFetchArray($res, MYSQLI_ASSOC);

            if (!empty($row['count'])) {
                return true; // Already exists, skip
            }

            $sql = sprintf($expression, 'INSERT', $this->escapeSql($name), $index, $this->escapeSql($value));
        } else {
            $sql = sprintf($expression, 'REPLACE', $this->escapeSql($name), $index, $this->escapeSql($value));
        }

        return $this->execute_sql($sql) !== false;
    }

    /**
     * Add arbitrary custom globals to the database after insert_globals
     *
     * @param array $new_globals Associative array where keys are global names and values are configuration arrays.
     *                           Each configuration array can contain:
     *                           - 'value' (string): The global value (default: '')
     *                           - 'index' (int): The global index (default: 0)
     * @return bool True on success, false on failure
     */
    public function upsertCustomGlobals(array $new_globals): bool
    {
        foreach ($new_globals as $global_name => $global_config) {
            if (!is_array($global_config) || empty($global_name)) {
                continue;
            }

            $global_value = $global_config['value'] ?? '';
            $global_index = isset($global_config['index']) ? intval($global_config['index']) : 0;

            if (!$this->writeGlobal($global_name, $global_value, $global_index, false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install the Generic Access Control List (GACL) system.
     *
     * Creates all access control objects (ACOs), sections, and groups
     * needed for OpenEMR's role-based access control system.
     *
     * @return bool True if GACL installation successful, false otherwise
     */
    public function install_gacl(): bool
    {
        $gacl = $this->newGaclApi();

        // Create the ACO sections.  Every ACO must have a section.
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
        $gacl->add_object('encounters', 'Less-protected information (write,addonly optional)', 'relaxed', 10, 0, 'ACO');
        // xl('Less-protected information (write,addonly optional)')

        // Create ACOs for lists.
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
        $gacl->add_object('patientportal', 'Patient Portal', 'portal', 10, 0, 'ACO');
        // xl('Patient Portal')

        // Create ACOs for modules.
        $gacl->add_object('menus', 'Modules', 'modle', 10, 0, 'ACO');
        // xl('Modules')

        // Create ACOs for patients.
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
        $gacl->add_object('sensitivities', 'Normal', 'normal', 10, 0, 'ACO');
        // xl('Normal')
        $gacl->add_object('sensitivities', 'High', 'high', 20, 0, 'ACO');
        // xl('High')

        // Create ACO for placeholder.
        $gacl->add_object('placeholder', 'Placeholder (Maintains empty ACLs)', 'filler', 10, 0, 'ACO');
        // xl('Placeholder (Maintains empty ACLs)')

        // Create ACO for nationnotes.
        $gacl->add_object('nationnotes', 'Nation Notes Configure', 'nn_configure', 10, 0, 'ACO');
        // xl('Nation Notes Configure')

        // Create ACOs for Inventory.
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
        $gacl->add_object_section('Users', 'users', 10, 0, 'ARO');
        // xl('Users')

        // Create the Administrator in the above-created "users" section
        // and add him/her to the above-created "admin" group.
        // If this script is being used by OpenEMR's setup, then will
        //   incorporate the installation values. Otherwise will
        //    hardcode the 'admin' user.
        $gacl->add_object('users', $this->iuname, $this->iuser, 10, 0, 'ARO');
        $gacl->add_group_object($admin, 'users', $this->iuser, 'ARO');

        // Declare return terms for language translations
        //  xl('write') xl('wsome') xl('addonly') xl('view')

        // Set permissions for administrators.
        $gacl->add_acl(
            [
                'acct' => ['bill', 'disc', 'eob', 'rep', 'rep_a'],
                'admin' => ['calendar', 'database', 'forms', 'practice', 'superbill', 'users', 'batchcom', 'language', 'super', 'drugs', 'acl','multipledb','menu','manage_modules'],
                'encounters' => ['auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'],
                'inventory' => ['lots', 'sales', 'purchases', 'transfers', 'adjustments', 'consumption', 'destruction', 'reporting'],
                'lists' => ['default','state','country','language','ethrace'],
                'patients' => ['appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab', 'docs_rm','pat_rep'],
                'sensitivities' => ['normal', 'high'],
                'nationnotes' => ['nn_configure'],
                'patientportal' => ['portal'],
                'menus' => ['modle'],
                'groups' => ['gadd','gcalendar','glog','gdlog','gm']
            ],
            null,
            [$admin],
            null,
            null,
            1,
            1,
            'write',
            'Administrators can do anything'
        );
        // xl('Administrators can do anything')

        // Set permissions for physicians.
        $gacl->add_acl(
            [
                'patients' => ['pat_rep']
            ],
            null,
            [$doc],
            null,
            null,
            1,
            1,
            'view',
            'Things that physicians can only read'
        );
        // xl('Things that physicians can only read')
        $gacl->add_acl(
            [
                'placeholder' => ['filler']
            ],
            null,
            [$doc],
            null,
            null,
            1,
            1,
            'addonly',
            'Things that physicians can read and enter but not modify'
        );
        // xl('Things that physicians can read and enter but not modify')
        $gacl->add_acl(
            [
                'placeholder' => ['filler']
            ],
            null,
            [$doc],
            null,
            null,
            1,
            1,
            'wsome',
            'Things that physicians can read and partly modify'
        );
        // xl('Things that physicians can read and partly modify')
        $gacl->add_acl(
            [
                'acct' => ['disc', 'rep'],
                'admin' => ['drugs'],
                'encounters' => ['auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'],
                'patients' => ['appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert',
                    'disclosure', 'rx', 'amendment', 'lab'],
                'sensitivities' => ['normal', 'high'],
                'groups' => ['gcalendar','glog']
            ],
            null,
            [$doc],
            null,
            null,
            1,
            1,
            'write',
            'Things that physicians can read and modify'
        );
        // xl('Things that physicians can read and modify')

        // Set permissions for clinicians.
        $gacl->add_acl(
            [
                'patients' => ['pat_rep']
            ],
            null,
            [$clin],
            null,
            null,
            1,
            1,
            'view',
            'Things that clinicians can only read'
        );
        // xl('Things that clinicians can only read')
        $gacl->add_acl(
            [
                'encounters' => ['notes', 'relaxed'],
                'patients' => ['demo', 'med', 'docs', 'notes','trans', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab'],
                'sensitivities' => ['normal']
            ],
            null,
            [$clin],
            null,
            null,
            1,
            1,
            'addonly',
            'Things that clinicians can read and enter but not modify'
        );
        // xl('Things that clinicians can read and enter but not modify')
        $gacl->add_acl(
            [
                'placeholder' => ['filler']
            ],
            null,
            [$clin],
            null,
            null,
            1,
            1,
            'wsome',
            'Things that clinicians can read and partly modify'
        );
        // xl('Things that clinicians can read and partly modify')
        $gacl->add_acl(
            [
                'admin' => ['drugs'],
                'encounters' => ['auth', 'coding', 'notes'],
                'patients' => ['appt'],
                'groups' => ['gcalendar', 'glog']
            ],
            null,
            [$clin],
            null,
            null,
            1,
            1,
            'write',
            'Things that clinicians can read and modify'
        );
        // xl('Things that clinicians can read and modify')

        // Set permissions for front office staff.
        $gacl->add_acl(
            [
                'patients' => ['alert']
            ],
            null,
            [$front],
            null,
            null,
            1,
            1,
            'view',
            'Things that front office can only read'
        );
        // xl('Things that front office can only read')
        $gacl->add_acl(
            [
                'placeholder' => ['filler']
            ],
            null,
            [$front],
            null,
            null,
            1,
            1,
            'addonly',
            'Things that front office can read and enter but not modify'
        );
        // xl('Things that front office can read and enter but not modify')
        $gacl->add_acl(
            [
                'placeholder' => ['filler']
            ],
            null,
            [$front],
            null,
            null,
            1,
            1,
            'wsome',
            'Things that front office can read and partly modify'
        );
        // xl('Things that front office can read and partly modify')
        $gacl->add_acl(
            [
                'patients' => ['appt', 'demo'],
                'groups' => ['gcalendar']
            ],
            null,
            [$front],
            null,
            null,
            1,
            1,
            'write',
            'Things that front office can read and modify'
        );
        // xl('Things that front office can read and modify')

        // Set permissions for back office staff.
        $gacl->add_acl(
            [
                'patients' => ['alert']
            ],
            null,
            [$back],
            null,
            null,
            1,
            1,
            'view',
            'Things that back office can only read'
        );
        // xl('Things that back office can only read')
        $gacl->add_acl(
            [
                'placeholder' => ['filler']
            ],
            null,
            [$back],
            null,
            null,
            1,
            1,
            'addonly',
            'Things that back office can read and enter but not modify'
        );
        // xl('Things that back office can read and enter but not modify')
        $gacl->add_acl(
            [
                'placeholder' => ['filler']
            ],
            null,
            [$back],
            null,
            null,
            1,
            1,
            'wsome',
            'Things that back office can read and partly modify'
        );
        // xl('Things that back office can read and partly modify')
        $gacl->add_acl(
            [
                'acct' => ['bill', 'disc', 'eob', 'rep', 'rep_a'],
                'admin' => ['practice', 'superbill'],
                'encounters' => ['auth_a', 'coding_a', 'date_a'],
                'patients' => ['appt', 'demo']
            ],
            null,
            [$back],
            null,
            null,
            1,
            1,
            'write',
            'Things that back office can read and modify'
        );
        // xl('Things that back office can read and modify')

        // Set permissions for Emergency Login.
        $gacl->add_acl(
            [
                'acct' => ['bill', 'disc', 'eob', 'rep', 'rep_a'],
                'admin' => ['calendar', 'database', 'forms', 'practice', 'superbill', 'users', 'batchcom', 'language', 'super', 'drugs', 'acl','multipledb','menu','manage_modules'],
                'encounters' => ['auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'],
                'inventory' => ['lots', 'sales', 'purchases', 'transfers', 'adjustments', 'consumption', 'destruction', 'reporting'],
                'lists' => ['default','state','country','language','ethrace'],
                'patients' => ['appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab', 'docs_rm','pat_rep'],
                'sensitivities' => ['normal', 'high'],
                'nationnotes' => ['nn_configure'],
                'patientportal' => ['portal'],
                'menus' => ['modle'],
                'groups' => ['gadd','gcalendar','glog','gdlog','gm']
            ],
            null,
            [$breakglass],
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

    /**
     * Perform a complete OpenEMR installation process.
     *
     * Orchestrates the entire installation by validating settings,
     * creating databases and users, loading SQL files, configuring
     * access controls, and setting up the initial system state.
     *
     * @return bool True if installation completed successfully, false otherwise
     */
    public function quick_install(): bool
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
            } catch (Exception) {
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
        // (applicable if not cloning from another database)
        if (empty($this->clone_database)) {
            if (! $this->add_version_info()) {
                return false;
            }

            if (! $this->insert_globals()) {
                return false;
            }

            if (is_array($this->custom_globals) && ! $this->upsertCustomGlobals($this->custom_globals)) {
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

    /**
     * Validate and escape database name.
     *
     * Ensures database name contains only safe characters.
     *
     * @param string $name Database name to validate
     * @return string Validated database name
     * @throws void Dies if invalid characters found
     */
    protected function escapeDatabaseName(string $name): string
    {
        if (preg_match('/[^A-Za-z0-9_-]/', $name)) {
            $this->die("Illegal character(s) in database name");
        }
        return $name;
    }

    /**
     * Validate and escape collation name.
     *
     * Ensures collation name contains only safe characters.
     *
     * @param string $name Collation name to validate
     * @return string Validated collation name
     * @throws void Dies if invalid characters found
     */
    protected function escapeCollateName(string $name): string
    {
        if (preg_match('/[^A-Za-z0-9_-]/', $name)) {
            $this->die("Illegal character(s) in collation name");
        }
        return $name;
    }

    /**
     * Execute SQL query with error handling.
     *
     * Executes a SQL query against the database connection,
     * with optional error reporting and logging.
     *
     * @param string $sql SQL query to execute
     * @param bool $showError Whether to log/display errors
     * @return mysqli_result|bool Query result or false on error
     */
    protected function execute_sql(string $sql, bool $showError = true): mysqli_result|bool
    {
        $this->error_message = '';
        if (! $this->dbh) {
            $this->user_database_connection();
        }

        try {
            $results = $this->mysqliQuery($this->dbh, $sql);
            if ($results) {
                return $results;
            } else {
                if ($showError) {
                    $error_mes = $this->mysqliError($this->dbh);
                    $this->error_message = "unable to execute SQL: '$sql' due to: " . $error_mes;
                    error_log("ERROR IN OPENEMR INSTALL: Unable to execute SQL: " . htmlspecialchars($sql, ENT_QUOTES) . " due to: " . htmlspecialchars($error_mes, ENT_QUOTES));
                }
                return false;
            }
        // this exception only occurs if MYSQLI_REPORT_STRICT is enabled (see https://www.php.net/manual/en/mysqli.query.php)
        } catch (\mysqli_sql_exception $exception) {
            if ($showError) {
                $this->error_message = "unable to execute SQL: '$sql' due to: " . $exception->getMessage();
                error_log("ERROR IN OPENEMR INSTALL: Unable to execute SQL: " . htmlspecialchars($sql, ENT_QUOTES) . " due to: " . htmlspecialchars($exception->getMessage(), ENT_QUOTES));
            }
            return false;
        }
    }

    protected function connect_to_database(string $server, string $user, string $password, int|string $port, string $dbname = ''): mysqli|false
    {
        $pathToCerts = __DIR__ . "/../../sites/" . $this->site . "/documents/certificates/";
        $mysqlSsl = false;
        $mysqli = $this->mysqliInit();
        if (defined('MYSQLI_CLIENT_SSL') && $this->fileExists($pathToCerts . "mysql-ca")) {
            $mysqlSsl = true;
            if (
                $this->fileExists($pathToCerts . "mysql-key") &&
                $this->fileExists($pathToCerts . "mysql-cert")
            ) {
                // with client side certificate/key
                $this->mysqliSslSet(
                    $mysqli,
                    $pathToCerts . "mysql-key",
                    $pathToCerts . "mysql-cert",
                    $pathToCerts . "mysql-ca",
                    null,
                    null
                );
            } else {
                // without client side certificate/key
                $this->mysqliSslSet(
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
            $ok = $this->mysqliRealConnect(
                $mysqli,
                $server,
                $user,
                $password,
                $dbname,
                (int)$port != 0 ? (int)$port : 3306,
                '',
                $mysqlSsl ? MYSQLI_CLIENT_SSL : 0
            );
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

    /**
     * Disable strict SQL mode for installation.
     *
     * Turns off MySQL strict mode to allow legacy SQL patterns
     * during installation.
     *
     * @return mysqli_result|bool Result of SQL execution
     */
    protected function set_sql_strict()
    {
        // Turn off STRICT SQL
        return $this->execute_sql("SET sql_mode = ''");
    }

    /**
     * Set database character encoding to UTF8MB4.
     *
     * Configures the connection to use UTF8MB4 character set
     * for proper Unicode support.
     *
     * @return mysqli_result|bool Result of SQL execution
     */
    protected function set_collation()
    {
        return $this->execute_sql("SET NAMES 'utf8mb4'");
    }

   /**
    * Initialize $this->dumpfiles, an array of the dumpfiles that will
    * be loaded into the database, including the correct translation
    * dumpfile.
    * The keys are the paths of the dumpfiles, and the values are the titles
    *
    * @return array
    */
    protected function initialize_dumpfile_list(): array
    {
        if ($this->clone_database) {
            $this->dumpfiles = [ $this->get_backup_filename() => 'clone database' ];
        } else {
            $dumpfiles = [ $this->main_sql => 'Main' ];
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

            // Load CVX codes if present
            if ($this->fileExists($this->cvx)) {
                $dumpfiles[ $this->cvx ] = "CVX Immunization Codes";
            }

            $this->dumpfiles = $dumpfiles;
        }

        return $this->dumpfiles;
    }

    /**
     * Directory copy logic borrowed from a user comment at
     * http://www.php.net/manual/en/function.copy.php
     *
     * @param string $src name of the directory to copy
     * @param string $dst name of the destination to copy to
     * @return bool indicating success
     */
    protected function recurse_copy(string $src, string $dst): bool
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
     * Dump a site's database to a temporary file.
     *
     * @param string $source_site_id the site_id of the site to dump
     * @return string filename of the backup
     */
    protected function dumpSourceDatabase(): string
    {
        global $OE_SITES_BASE;
        $source_site_id = $this->source_site_id;

        include("$OE_SITES_BASE/$source_site_id/sqlconf.php");

        if (empty($config)) {
            $this->die("Source site $source_site_id has not been set up!");
        }

        /**
         * @var string $login
         * @var string $host
         * @var string $pass
         * @var string $dbase
         */
        $backup_file = $this->get_backup_filename();
        $cmd = "mysqldump -u " . escapeshellarg($login) .
        " -h " . $host .
        " -p" . escapeshellarg($pass) .
        " --ignore-table=" . escapeshellarg($dbase . ".onsite_activity_view") . " --hex-blob --opt --skip-extended-insert --quote-names -r $backup_file " .
        escapeshellarg($dbase);

        $tmp1 = [];
        $tmp0 = exec($cmd, $tmp1, $tmp2);
        if ($tmp2) {
            $this->die("Error $tmp2 running \"$cmd\": $tmp0 " . implode(' ', $tmp1));
        }

        return $backup_file;
    }

    /**
     * @return string filename of the source backup database for cloning
     */
    protected function get_backup_filename(): string
    {
        $backup_file = stristr(PHP_OS, 'WIN') ? 'C:/windows/temp/setup_dump.sql' : '/tmp/setup_dump.sql';

        return $backup_file;
    }

    /**
     * Get the currently selected theme.
     *
     * @return string Current theme name from globals table
     */
    public function getCurrentTheme()
    {
        $current_theme = $this->execute_sql("SELECT gl_value FROM globals WHERE gl_name LIKE '%css_header%'");
        $current_theme = $this->mysqliFetchArray($current_theme);
        return $current_theme[0];
    }

    /**
     * Set the current theme in the database.
     *
     * Updates the globals table with the selected theme.
     * For cloned sites, uses current theme if no new theme specified.
     *
     * @return mysqli_result|bool Result of the update operation
     */
    public function setCurrentTheme()
    {
        $current_theme = $this->getCurrentTheme();
        // for cloned sites since they're not asked about a new theme
        if (!$this->new_theme) {
            $this->new_theme = $current_theme;
        }
        return $this->execute_sql("UPDATE globals SET gl_value='" . $this->escapeSql($this->new_theme) . "' WHERE gl_name LIKE '%css_header%'");
    }

    /**
     * Get list of available themes.
     *
     * Scans the themes directory and returns available theme files.
     *
     * @return array List of theme file names
     */
    public function listThemes(): array
    {
        $themes_img_dir = "public/images/stylesheets/";
        $arr_themes_img = array_values(array_filter($this->scanDir($themes_img_dir), fn($item): bool => $item[0] !== '.'));
        return $arr_themes_img;
    }

    protected function extractFileName(string $theme_file_name = ''): array
    {
        $under_score = strpos($theme_file_name, '_') + 1;
        $dot = strpos($theme_file_name, '.');
        $theme_value = substr($theme_file_name, $under_score, ($dot - $under_score));
        $theme_title = ucwords(str_replace("_", " ", $theme_value));
        return ['theme_value' => $theme_value, 'theme_title' => $theme_title];
    }

    /**
     * Display HTML divs for theme selection interface.
     *
     * Generates radio button interface with theme preview images
     * for the installation theme selection step.
     *
     * @return void
     */
    public function displayThemesDivs(): void
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
    }

    /**
     * Display the currently selected theme information.
     *
     * Shows theme preview and details for the currently active theme.
     *
     * @return void
     */
    public function displaySelectedThemeDiv(): void
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
    }

    /**
     * Display the newly selected theme information.
     *
     * Shows preview of the theme that will be applied after installation.
     * For cloned sites, defaults to current theme if no new theme selected.
     *
     * @return void
     */
    public function displayNewThemeDiv(): void
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
    }

    /**
     * Display the installation help modal dialog.
     *
     * Generates HTML and JavaScript for a modal popup that shows
     * installation help documentation in an iframe.
     *
     * @return void
     */
    public function setupHelpModal(): void
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
    }

    /**
     * Wrapper for feof to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param resource $stream
     * @return bool
     */
    protected function atEndOfFile($stream): bool
    {
        return feof($stream);
    }

    /**
     * Wrapper for fclose to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param resource $stream
     * @return bool
     */
    protected function closeFile($stream): bool
    {
        return fclose($stream);
    }

    /**
     * Create a new Totp instance.
     *
     * @codeCoverageIgnore
     *
     * @param string $secret The TOTP secret
     * @param string $user The username
     * @return Totp
     */
    protected function createTotpInstance(string $secret, string $user): Totp
    {
        return new Totp($secret, $user);
    }

    /**
     * Check if OpenEMR CryptoGen class exists.
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    protected function cryptoGenClassExists(): bool
    {
        return class_exists(\OpenEMR\Common\Crypto\CryptoGen::class);
    }

    /**
     * Wrapper for die() to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @return never
     */
    protected function die(string $message): never
    {
        error_log($message);
        die($message);
    }

    /**
     * Close the mysqli connection.
     *
     * @codeCoverageIgnore
     *
     * @return true
     */
    public function disconnect(): true
    {
        return mysqli_close($this->dbh);
    }

    /**
     * Encrypt TOTP secret using CryptoGen.
     *
     * @codeCoverageIgnore
     *
     * @param string $secret The TOTP secret to encrypt
     * @param string $hash The password hash to use for encryption
     * @return string Encrypted secret
     */
    protected function encryptTotpSecret(string $secret, string $hash): string
    {
        $cryptoGen = new \OpenEMR\Common\Crypto\CryptoGen();
        return $cryptoGen->encryptStandard($secret, $hash);
    }

    /**
     * Escape SQL strings to prevent injection attacks.
     *
     * @codeCoverageIgnore
     *
     * @param string $sql SQL string to escape
     * @return string Escaped SQL string
     */
    protected function escapeSql(string $sql): string
    {
        return mysqli_real_escape_string($this->dbh, $sql);
    }

    /**
     * Wrapper for file_exists to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param string $fileName
     * @return bool
     */
    protected function fileExists(string $fileName): bool
    {
        return file_exists($fileName);
    }

    /**
     * Wrapper for fgets to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param resource $stream
     * @param int $length
     * @return string|false
     */
    protected function getLine($stream, int $length): string|false
    {
        return fgets($stream, $length);
    }

    /**
     * Wrapper for glob to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param string $pattern
     * @param int $flags
     * @return array|false
     */
    protected function globPattern(string $pattern, int $flags = 0): array|false
    {
        return glob($pattern, $flags);
    }

    /**
     * Wrapper for mysqli_errno to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param mysqli $mysql
     * @return int
     */
    protected function mysqliErrno(mysqli $mysql): int
    {
        return mysqli_errno($mysql);
    }

    /**
     * Wrapper for mysqli_error to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param mysqli $mysql
     * @return string
     */
    protected function mysqliError(mysqli $mysql): string
    {
        return mysqli_error($mysql);
    }

    /**
     * Wrapper for mysqli_fetch_array to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param mysqli_result $result
     * @param int $mode
     * @return array|null|false
     */
    protected function mysqliFetchArray(mysqli_result $result, int $mode = MYSQLI_BOTH): array|null|false
    {
        return mysqli_fetch_array($result, $mode);
    }

    /**
     * Wrapper for mysqli_init to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @return mysqli|false
     */
    protected function mysqliInit(): mysqli|false
    {
        return mysqli_init();
    }

    /**
     * Wrapper for mysqli_connect to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param mysqli_result $result
     * @return int
     */
    protected function mysqliNumRows(mysqli_result $result): int
    {
        return mysqli_num_rows($result);
    }

    /**
     * Wrapper for mysqli_query to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param mysqli $mysql
     * @param string $query
     * @return mysqli_result|bool
     */
    protected function mysqliQuery(mysqli $mysql, string $query): mysqli_result|bool
    {
        return mysqli_query($mysql, $query);
    }

    /**
     * Wrapper for mysqli_real_connect to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param mysqli $link
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param int $port
     * @param string $socket
     * @param int $flags
     * @return bool
     */
    protected function mysqliRealConnect(mysqli $link, string $host, string $user, string $password, string $database = '', int $port = 0, string $socket = '', int $flags = 0): bool
    {
        return mysqli_real_connect($link, $host, $user, $password, $database, $port, $socket, $flags);
    }

    /**
     * Wrapper for mysqli_connect to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param mysqli $mysql
     * @param string $dbname
     * @return bool
     */
    protected function mysqliSelectDb(mysqli $mysql, string $dbname): bool
    {
        return mysqli_select_db($mysql, $dbname);
    }

    /**
     * Wrapper for mysqli_ssl_set to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param mysqli $link
     * @param ?string $key
     * @param ?string $cert
     * @param ?string $ca
     * @param ?string $capath
     * @param ?string $cipher
     * @return bool
     */
    protected function mysqliSslSet(mysqli $link, ?string $key, ?string $cert, ?string $ca, ?string $capath, ?string $cipher): bool
    {
        return mysqli_ssl_set($link, $key, $cert, $ca, $capath, $cipher);
    }

    /**
     * Create a new instance of the GaclApi class.
     *
     * @codeCoverageIgnore
     *
     * @return GaclApi New instance of GaclApi
     */
    protected function newGaclApi(): GaclApi
    {
        return new GaclApi();
    }

    /**
     * Wrapper for fopen to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param string $filename
     * @param string $mode
     * @return resource|false
     */
    protected function openFile(string $filename, string $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * Wrapper for scandir to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param string $directory
     * @return array|false
     */
    protected function scanDir(string $directory)
    {
        return scandir($directory);
    }

    /**
     * Check if Totp class exists.
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    protected function totpClassExists(): bool
    {
        return class_exists('Totp');
    }

    /**
     * Wrapper for touch to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param string $filename
     * @param ?int $mtime
     * @param ?int $atime
     * @return bool
     */
    protected function touchFile(string $filename, ?int $mtime = null, ?int $atime = null): bool
    {
        return touch($filename, $mtime, $atime);
    }

    /**
     * Wrapper for unlink to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param string $filename
     * @return bool
     */
    protected function unlinkFile(string $filename): bool
    {
        return unlink($filename);
    }

    /**
     * Wrapper for fwrite to facilitate unit testing.
     *
     * @codeCoverageIgnore
     *
     * @param resource $stream
     * @param string $data
     * @param ?int $length
     * @return int|false
     */
    protected function writeToFile($stream, string $data, ?int $length = null): int|false
    {
        return $length !== null ? fwrite($stream, $data, $length) : fwrite($stream, $data);
    }
}
