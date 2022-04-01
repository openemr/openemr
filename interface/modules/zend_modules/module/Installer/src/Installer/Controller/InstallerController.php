<?php

/**
 * interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Vipin Kumar <vipink@zhservices.com>
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Installer\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Json\Json;
use Installer\Model\InstModule;
use Application\Listener\Listener;
use Installer\Model\InstModuleTable;
use Laminas\Db\Adapter\Adapter;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Utils\RandomGenUtils;
use Laminas\Console\Request as ConsoleRequest;
use OpenEMR\Services\Utils\SQLUpgradeService;

class InstallerController extends AbstractActionController
{
    /**
     * @var InstModuleTable
     */
    protected $InstallerTable;
    protected $listenerObject;

    /**
     * @var Laminas\Db\Adapter\Adapter
     */
    private $dbAdapter;

    public function __construct(InstModuleTable $installerTable)
    {
        $this->listenerObject = new Listener();
        $this->InstallerTable = $installerTable;
        $this->dbAdapter = $adapter ?? null;
    }

    public function nolayout()
    {
        // Turn off the layout, i.e. only render the view script.
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function indexAction()
    {
        //get the list of installed and new modules
        $result = $this->getInstallerTable()->allModules();

        $allModules = array();
        foreach ($result as $dataArray) {
            $mod = new InstModule();
            $mod->exchangeArray($dataArray);
            $mod = $this->makeButtonForSqlAction($mod);
            $mod = $this->makeButtonForAClAction($mod);
            array_push($allModules, $mod);
        }

        return new ViewModel(array(
            'InstallersExisting' => $allModules,
            'InstallersAll' => $allModules,
            'listenerObject' => $this->listenerObject,
            'dependencyObject' => $this->getInstallerTable(),
            // TODO: @adunsulag there should be a way to pull this from application.config.php but so far the answer eludes me.
            'coreModules' => ['Application', 'Acl', 'Installer', 'FHIR', 'PatientFlowBoard']
        ));
    }

    /**
     * @return Installer\Model\InstModuleTable
     */
    public function getInstallerTable(): InstModuleTable
    {
        return $this->InstallerTable;
    }

    public function registerAction()
    {
        if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
            echo xlt('Not Authorized');
            exit;
        }

        $status = false;
        $request = $this->getRequest();
        if (method_exists($request, 'isPost')) {
            if ($request->getPost('mtype') == 'zend') {
                // TODO: We want to be able to load the modules
                // from the database.. however, this can be fairly slow so we might want to do some kind of APC caching of the module
                // list that is loaded using the OpenEMR db connector and not the zend db connector, cache the modules, and then
                // we can filter / update that list.  We'll have to inject the unloaded module list into the installer but that is fine.
                $rel_path = "public/" . $request->getPost('mod_name') . "/";

                // registering the table inserts the module record into the database.
                // it's always loaded regardless, but it inserts it in the database as not activated
                if ($this->getInstallerTable()->register($request->getPost('mod_name'), $rel_path, 0, $GLOBALS['zendModDir'])) {
                    $status = true;
                }
            } else {
                // TODO: there doesn't appear to be any methodology in how to load these custom registered modules... which seems pretty odd.
                // there aren't any in the system... but why have this then?
                $rel_path = $request->getPost('mod_name') . "/index.php";
                if ($this->getInstallerTable()->register($request->getPost('mod_name'), $rel_path)) {
                    $status = true;
                }
            }
            die($status ? $this->listenerObject->z_xlt("Success") : $this->listenerObject->z_xlt("Failure"));
        } else {
            $moduleType = $request->getParam('mtype');
            $moduleName = $request->getParam('modname');
            if ($moduleType == 'zend') {
                $rel_path = "public/" . $moduleName . "/";
                // registering the table inserts the module record into the database.
                // it's always loaded regardless, but it inserts it in the database as not activated
                if ($this->getInstallerTable()->register($moduleName, $rel_path, 0, $GLOBALS['zendModDir'])) {
                    $status = true;
                }
                die($status ? $this->listenerObject->z_xlt("Success") : $this->listenerObject->z_xlt("Failure"));
            } else {
                die("not supported");
            }
        }
    }

    public function manageAction()
    {
        if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
            echo json_encode(["status" => xlt('Not Authorized')]);
            exit;
        }

        $outputToBrowser = '';
        $request = $this->getRequest();
        $status = $this->listenerObject->z_xlt("Failure");
        if ($request->isPost()) {
            if ($request->getPost('modAction') == "enable") {
                $status = $this->EnableModule($request->getPost('modId'));
            } elseif ($request->getPost('modAction') == "disable") {
                $status = $this->DisableModule($request->getPost('modId'));
            } elseif ($request->getPost('modAction') == "install") {
                $modId = $request->getPost('modId');
                $mod_enc_menu = $request->getPost('mod_enc_menu');
                $mod_nick_name = $request->getPost('mod_nick_name');
                $status = $this->InstallModule($modId, $mod_enc_menu, $mod_nick_name);
            } elseif ($request->getPost('modAction') == 'install_sql') {
                if ($this->InstallModuleSQL($request->getPost('modId'))) {
                    $status = $this->listenerObject->z_xlt("Success");
                } else {
                    $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not open table") . '.' . $this->listenerObject->z_xlt("sql") . ', ' . $this->listenerObject->z_xlt("broken form") . "?";
                }
            } elseif ($request->getPost('modAction') == 'upgrade_sql') {
                $div = $this->UpgradeModuleSQL($request->getPost('modId'));
                $status = $this->listenerObject->z_xlt("Success");
            } elseif ($request->getPost('modAction') == 'install_acl') {
                if ($div = $this->InstallModuleACL($request->getPost('modId'))) {
                    $status = $this->listenerObject->z_xlt("Success");
                } else {
                    $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not install ACL");
                }
            } elseif ($request->getPost('modAction') == 'upgrade_acl') {
                if ($div = $this->UpgradeModuleACL($request->getPost('modId'))) {
                    $status = $this->listenerObject->z_xlt("Success");
                } else {
                    $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not install ACL");
                }
            } elseif ($request->getPost('modAction') == "unregister") {
                $status = $this->UnregisterModule($request->getPost('modId'));
            }
        }
        $output = "";
        if (!empty($div) && is_array($div)) {
            $output = implode("<br />\n", $div);
        }
        echo json_encode(["status" => $status, "output" => $output]);
        exit(0);
    }

    /**
     * @param $version
     * @return int|string
     */
    function upgradeAclFromVersion($ACL_UPGRADE, $version)
    {
        $toVersion = '';
        foreach ($ACL_UPGRADE as $toVersion => $function) {
            if (version_compare($version, $toVersion) < 0) {
                $function();
            }
        }
        return $toVersion;
    }

    /**
     * Function to install ACL for the installed modules
     *
     * @param string $dir Location of the php file which calling functions to add sections,aco etc.
     * @return boolean
     */
    private function installACL($dir)
    {
        $aclfile = $dir . "/moduleACL.php";
        if (file_exists($aclfile)) {
            include_once($aclfile);
        }
    }

    /**
     * Used to recreate the application config file
     *
     * @param unknown_type $data
     * @return string
     */
    private function getContent($data)
    {
        $string = "";
        foreach ($data as $key => $value) {
            $string .= " '$key' => ";
            if (is_array($value)) {
                $string .= " array(";
                $string .= $this->getContent($value);
                $string .= " )";
            } else {
                $string .= "'$value'";
            }

            $string .= ",";
        }

        return $string;
    }

    public function SaveHooksAction()
    {
        $request = $this->getRequest();
        $postArr = $request->getPost();
        //DELETE OLD HOOKS OF A MODULE
        $this->getInstallerTable()->deleteModuleHooks($postArr['mod_id']);
        if (!empty($postArr['hook_hanger']) && count($postArr['hook_hanger']) > 0) {
            foreach ($postArr['hook_hanger'] as $hookId => $hooks) {
                foreach ($hooks as $hangerId => $hookHanger) {
                    $this->getInstallerTable()->saveHooks($postArr['mod_id'], $hookId, $hangerId);
                }
            }

            $return[0] = array('return' => 1, 'msg' => $this->listenerObject->z_xlt("Saved Successfully"));
        } else {
            $return[0] = array('return' => 1, 'msg' => $this->listenerObject->z_xlt("No Hooks enabled for this Module"));
        }

        $arr = new JsonModel($return);
        return $arr;
    }

    public function configureAction()
    {
        $request = $this->getRequest();
        $modId = $request->getPost('mod_id');

        /** Configuration Details */
        $result = $this->getInstallerTable()->getConfigSettings($modId);
        $configuration = array();
        foreach ($result as $tmp) {
            $configuration[$tmp['field_name']] = $tmp;
        }

        //INSERT MODULE HOOKS IF NOT EXISTS
        $moduleDirectory = $this->getInstallerTable()->getModuleDirectory($modId);
        //GET MODULE HOOKS FROM A FUNCTION IN CONFIGURATION MODEL CLASS
        $hooksArr = $this->getInstallerTable()->getModuleHooks($moduleDirectory);

        if (count($hooksArr) > 0) {
            foreach ($hooksArr as $hook) {
                if (count($hook) > 0) {
                    if ($this->getInstallerTable()->checkModuleHookExists($modId, $hook['name']) == "0") {
                        $this->getInstallerTable()->saveModuleHooks($modId, $hook['name'], $hook['title'], $hook['path']);
                    }
                }
            }
        } else {
            //DELETE ADDED HOOKS TO HANGERS OF THIS MODULE, IF NO HOOKS EXIST IN THIS MODULE
            $this->getInstallerTable()->deleteModuleHooks($modId);
            //DELETE MODULE HOOKS
            $this->getInstallerTable()->deleteModuleHookSettings($modId);
        }

        //GET MODULE ACL SECTION FROM A FUNCTION IN CONFIGURATION MODEL CLASS
        $aclArray = $this->getInstallerTable()->getModuleAclSections($moduleDirectory);
        if (sizeof($aclArray) > 0) {
            $this->getInstallerTable()->insertAclSections($aclArray, $moduleDirectory, $modId);
        } else {
            $this->getInstallerTable()->deleteACLSections($modId);
        }

        $obj = $this->getInstallerTable()->getObject($moduleDirectory, 'Controller');
        $aclArray = array();
        if ($obj) {
            $aclArray = $obj->getAclConfig();
        }

        /** Configuration Form and Configuration Form Class */
        $configForm = $this->getInstallerTable()->getFormObject($moduleDirectory);

        /** Setup Config Details */
        $setup = $this->getInstallerTable()->getSetupObject($moduleDirectory);

        return new ViewModel(array(
            'mod_id' => $modId,
            'TabSettings' => $this->getInstallerTable()->getTabSettings($modId),
            'ACL' => $this->getInstallerTable()->getSettings('ACL', $modId),
            'OemrUserGroup' => $this->getInstallerTable()->getOemrUserGroup(),
            'OemrUserGroupAroMap' => $this->getInstallerTable()->getOemrUserGroupAroMap(),
            'ListActiveUsers' => $this->getInstallerTable()->getActiveUsers(),
            'ListActiveACL' => $this->getInstallerTable()->getActiveACL($modId),
            'ListActiveHooks' => $this->getInstallerTable()->getActiveHooks($modId),
            'helperObject' => $this->helperObject,
            'configuration' => $configuration,
            'hangers' => $this->getInstallerTable()->getHangers(),
            'Hooks' => $hooksArr,
            'hookObject' => $this->getInstallerTable(),
            'settings' => $configForm,
            'listenerObject' => $this->listenerObject,
            'setup' => $setup,
        ));
    }

    public function saveConfigAction()
    {
        $request = $this->getRequest();
        $moduleId = $request->getPost()->module_id;

        foreach ($request->getPost() as $key => $value) {
            $fieldName = $key;
            $fieldValue = $value;
            if ($fieldName != 'module_id') {
                $result = $this->getInstallerTable()->saveSettings($fieldName, $fieldValue, $moduleId);
            }
        }

        $data = array();
        $returnArr = array('modeId' => $moduleId);
        $return = new JsonModel($returnArr);
        return $return;
    }

    public function DeleteAclAction()
    {
        $request = $this->getRequest();
        $this->getInstallerTable()->DeleteAcl($request->getPost());
        $return[0] = array('return' => 1, 'msg' => $this->listenerObject->z_xlt("Deleted Successfully"));
        $arr = new JsonModel($return);
        return $arr;
    }

    public function DeleteHooksAction()
    {
        $request = $this->getRequest();
        $this->getInstallerTable()->DeleteHooks($request->getPost());
        $return[0] = array('return' => 1, 'msg' => $this->listenerObject->z_xlt("Deleted Successfully"));
        $arr = new JsonModel($return);
        return $arr;
    }

    public function nickNameAction()
    {
        $request = $this->getRequest();
        $nickname = $request->getPost()->nickname;
        echo $this->getInstallerTable()->validateNickName(trim($nickname));
        exit(0);
    }

    function getModuleVersionFromFile($modId)
    {
        //SQL version of Module
        $dirModule = $this->getInstallerTable()->getRegistryEntry($modId, "mod_directory");
        $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $dirModule->modDirectory;

        $version_of_module = $ModulePath . "/version.php";
        $table_sql = $ModulePath . "/table.sql";
        $install_sql = $ModulePath . "/sql/install.sql";
        $install_acl = $ModulePath . "/acl/acl_setup.php";
        if (file_exists($version_of_module) && (file_exists($table_sql) || file_exists($install_sql) || file_exists($install_acl))) {
            include_once($version_of_module);
            $version = $v_major . "." . $v_minor . "." . $v_patch;
            return $version;
        }
        return false;
    }

    public function getFilesForUpgrade($modDirectory, $sqldir)
    {
        $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $modDirectory;
        $versions = [];
        $dh = opendir($sqldir);
        if (!$dh) {
            return false;
        }

        while (false !== ($sfname = readdir($dh))) {
            if (substr($sfname, 0, 1) == '.') {
                continue;
            }

            if (preg_match('/^(\d+)_(\d+)_(\d+)-to-\d+_\d+_\d+_upgrade.sql$/', $sfname, $matches)) {
                $version = $matches[1] . '.' . $matches[2] . '.' . $matches[3];
                $versions[$version] = $sfname;
            }
        }
        $arrayKeys = array_keys($versions);
        usort($arrayKeys, 'version_compare');
        $sortVersions = array();
        foreach ($arrayKeys as $key) {
            $sortVersions[$key] = $versions[$key];
        }
        return $sortVersions;
    }

    public function makeButtonForSqlAction(InstModule $mod)
    {
        $dirModule = $this->getInstallerTable()->getRegistryEntry($mod->modId, "mod_directory");
        $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $dirModule->modDirectory;
        $sqldir = $ModulePath . "/sql";
        if (!is_dir($sqldir)) {
            $sqldir = $ModulePath;
        }
        $mod->sql_action = "";

        if (file_exists($sqldir . "/install.sql") && file_exists($ModulePath . "/version.php") && empty($mod->sql_version)) {
            $mod->sql_action = "install";
        }

        if (!empty($mod->sql_version) && $mod->sqlRun == 1) {
            $versions = $this->getFilesForUpgrade($mod->modDirectory, $sqldir);

            if (count($versions) > 0) {
                foreach ($versions as $version => $sfname) {
                    if (version_compare($version, $mod->sql_version) < 0) {
                        continue;
                    }
                    $mod->sql_action = "upgrade";
                }
            }
        }
        return $mod;
    }

    public function makeButtonForACLAction(InstModule $mod)
    {
        $dirModule = $this->getInstallerTable()->getRegistryEntry($mod->modId, "mod_directory");
        $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $dirModule->modDirectory;
        $sqldir = $ModulePath . "/acl";
        $mod->acl_action = "";

        if (file_exists($sqldir . "/acl_setup.php") && file_exists($ModulePath . "/version.php") && empty($mod->acl_version)) {
            $mod->acl_action = "install";
        }
        if (file_exists($sqldir . "/acl_upgrade.php") && file_exists($ModulePath . "/version.php") && !empty($mod->acl_version)) {
            global $ACL_UPGRADE;
            // Pass a variable, so below scripts can not be run on their own
            $aclSetupFlag = true;
            include_once($sqldir . "/acl_upgrade.php");

            foreach ($ACL_UPGRADE as $toVersion => $function) {
                if (version_compare($mod->acl_version, $toVersion) > 0) {
                    continue;
                }
                $mod->acl_action = "upgrade";
            }
        }
        return $mod;
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function getModuleId($moduleName)
    {
        if (empty($moduleName)) {
            return false;
        }
        $allModules = $this->getInstallerTable()->allModules();
        foreach ($allModules as $module) {
            if ($module["mod_directory"] === $moduleName) {
                return $module["mod_id"];
            }
        }
    }

    /**
     * @param string $modId
     * @return bool
     */
    public function InstallModuleSQL($modId = '')
    {
        $registryEntry = $this->getInstallerTable()->getRegistryEntry($modId, "mod_directory");
        $dirModule = $registryEntry->modDirectory;
        $modType = $registryEntry->type;
        if ($this->getInstallerTable()->installSQL($modId, $modType, $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $dirModule)) {
            $values = array($registryEntry->mod_nick_name,$registryEntry->mod_enc_menu);
            $values[2] = $this->getModuleVersionFromFile($modId);
            $values[3] = $registryEntry->acl_version;
            $this->getInstallerTable()->updateRegistered($modId, '', $values);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $modId
     * @return array
     */
    public function UpgradeModuleSQL($modId = '')
    {
        $Module = $this->getInstallerTable()->getRegistryEntry($modId, "mod_directory");
        $modDir = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $Module->modDirectory;
        $sqlInstallLocation = $modDir . '/sql';
        // if this is a custom module that for some reason doesn't have the SQL in a sql folder...
        if (!file_exists($sqlInstallLocation)) {
            $sqlInstallLocation = $modDir;
        }

        $versions = $this->getFilesForUpgrade($Module->modDirectory, $sqlInstallLocation);

        $values = array($Module->mod_nick_name,$Module->mod_enc_menu);
        $div = [];
        $outputToBrowser = '';
        foreach ($versions as $version => $filename) {
            if (version_compare($version, $Module->sql_version) < 0) {
                continue;
            }
            ob_start();
            $sqlUpgradeService = new SQLUpgradeService();
            $sqlUpgradeService->setRenderOutputToScreen(true);
            $sqlUpgradeService->upgradeFromSqlFile($filename, $sqlInstallLocation);
            $outputToBrowser .= ob_get_contents();
            ob_end_clean();
        }

        if (preg_match_all("/(.*)\<br \/\>\n/i", $outputToBrowser, $matches)) {
            $add_query_string = 0;
            $add_ended_divs = 0;
            $k = 0;
            foreach ($matches[1] as $string) {
                $prev_html_tag = false;
                if (preg_match("/<([a-z]+).*?>([^<]+)<\/([a-z]+)>/i", $string, $mm)) {
                    if ($add_query_string > 0) {
                        $div[] = "</div>";
                        $add_ended_divs++;
                    }
                    $div[] = $string;
                    $prev_html_tag = true;
                    $curr_html_tag = true;
                }
                if (!$prev_html_tag && $curr_html_tag) {
                    $div[] = "<div class='show_hide_log'>" . xlt("show/hide executed query log") . "</div><div class='spoiler' style='margin-left: 10px' >" . $string;
                    $curr_html_tag = false;
                } elseif (!$prev_html_tag && !$curr_html_tag) {
                    $div[] = $string;
                    $add_query_string++;
                }
                if (count($matches[1]) == (count($div) - $add_ended_divs) && (!$prev_html_tag && !$curr_html_tag)) {
                    $div[] = "</div>";
                }
                $k++;
            }
        }
        $values[2] = $this->getModuleVersionFromFile($modId);
        $values[3] = $Module->acl_version;
        $this->getInstallerTable()->updateRegistered($modId, '', $values);
        return $div;
    }

    /**
     * @param string $modId
     * @return bool
     */
    public function InstallModuleACL($modId = '')
    {
        $Module = $this->getInstallerTable()->getRegistryEntry($modId, "mod_directory");
        $modDir = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $Module->modDirectory;
        $div = [];
        if (file_exists($modDir . "/acl/acl_setup.php") && empty($modDir->acl_version)) {
            // Pass a variable, so below scripts can not be run on their own
            $aclSetupFlag = true;
            ob_start();
            include_once($modDir . "/acl/acl_setup.php");
            $div[] = ob_get_contents();
            ob_end_clean();
            $values = array($Module->mod_nick_name,$Module->mod_enc_menu);
            $values[2] = $Module->sql_version;
            $values[3] = $this->getModuleVersionFromFile($modId);
            $this->getInstallerTable()->updateRegistered($modId, '', $values);
            return $div;
        }
        return false;
    }

    /**
     * Function to Enable Module
     *
     * @param string $dir Location of the php file which calling functions to add sections,aco etc.
     * @return boolean
     */
    public function EnableModule($modId = '')
    {
        $resp = $this->getInstallerTable()->updateRegistered($modId, "mod_active=0");
        if ($resp['status'] == 'failure' && $resp['code'] == '200') {
            $status = $resp['value'];
        } else {
            $status = $this->listenerObject->z_xlt("Success");
        }
        return $status;
    }

    /**
     * Function to Disable Module
     *
     * @param string $dir Location of the php file which calling functions to add sections,aco etc.
     * @return boolean
     */
    public function DisableModule($modId = '')
    {
        $resp = $this->getInstallerTable()->updateRegistered($modId, "mod_active=1");
        if ($resp['status'] == 'failure' && $resp['code'] == '200') {
            $plural = "Module";
            if (count($resp['value']) > 1) {
                $plural = "Modules";
            }

            $status = $this->listenerObject->z_xlt("Dependency Problem") . ':' . implode(", ", $resp['value']) . " " . $this->listenerObject->z_xlt($plural) . " " . $this->listenerObject->z_xlt("Should be Enabled");
        } elseif ($resp['status'] == 'failure' && ($resp['code'] == '300' || $resp['code'] == '400')) {
            $status = $resp['value'];
        } else {
            $status = $this->listenerObject->z_xlt("Success");
        }
        return $status;
    }

    /**
     * Function to Install Module
     *
     * @param string $dir Location of the php file which calling functions to add sections,aco etc.
     * @return boolean
     */
    public function InstallModule($modId = '', $mod_enc_menu = '', $mod_nick_name = '')
    {
        $registryEntry = $this->getInstallerTable()->getRegistryEntry($modId, "mod_directory");
        $modType = $registryEntry->type;
        $dirModule = $registryEntry->modDirectory;
        $sqlInstalled = false;
        if ($modType == InstModuleTable::MODULE_TYPE_CUSTOM) {
            $fullDirectory = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . $GLOBALS['customModDir'] . "/" . $dirModule;
            if ($this->getInstallerTable()->installSQL($modId, $modType, $fullDirectory)) {
                $sqlInstalled = true;
            } else {
                // TODO: This is a wierd error... why is it written like this?
                $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not open table") . '.' . $this->listenerObject->z_xlt("sql") . ', ' . $this->listenerObject->z_xlt("broken form") . "?";
            }
        } else if ($modType == InstModuleTable::MODULE_TYPE_ZEND) {
            $fullDirectory = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $dirModule;
            if ($this->getInstallerTable()->installSQL($modId, $modType, $fullDirectory)) {
                $sqlInstalled = true;
            } else {
                $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not run sql query");
            }
        }

        if ($sqlInstalled) {
            $values = array($mod_nick_name, $mod_enc_menu);
            $values[2] = $this->getModuleVersionFromFile($modId);
            $this->getInstallerTable()->updateRegistered($modId, '', $values);
            $status = $this->listenerObject->z_xlt("Success");
        }

        return $status;
    }

    /**
     * Function to Unregister Module
     *
     * @param string $dir Location of the php file which calling functions to add sections,aco etc.
     * @return boolean
     */
    public function UnregisterModule($modId = '')
    {
        $resp = $this->getInstallerTable()->unRegister($modId);
        if ($resp == 'failure') {
            $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("Failed to unregister module.");
        } else {
            $status = $this->listenerObject->z_xlt("Success");
        }

        return $status;
    }






    /**
     * @param string $modId
     * @return array|bool
     */
    public function UpgradeModuleACL($modId = '')
    {
        $Module = $this->getInstallerTable()->getRegistryEntry($modId, "mod_directory");
        $modDir = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $Module->modDirectory;
        $div = [];
        if (file_exists($modDir . "/acl/acl_upgrade.php") && !empty($Module->acl_version)) {
            // Pass a variable, so below scripts can not be run on their own
            $aclSetupFlag = true;
            ob_start();
            $ACL_UPGRADE = include_once($modDir . "/acl/acl_upgrade.php");
            $version = $this->upgradeAclFromVersion($ACL_UPGRADE, $Module->acl_version);
            $div[] = ob_get_contents();
            ob_end_clean();

            if (strlen($version) > 0) {
                $values = array($Module->mod_nick_name,$Module->mod_enc_menu);
                $values[2] = $Module->sql_version;
                $values[3] = $this->getModuleVersionFromFile($modId);
                $this->getInstallerTable()->updateRegistered($modId, '', $values);
            }
            return $div;
        }
        return false;
    }

    /**
     *
     */
    public function commandInstallModuleAction()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console!');
        }

        $moduleAction = $request->getParam('modaction');
        $moduleName = $request->getParam('modname');

        $moduleId = null;
        $div = [];

        echo PHP_EOL . '--- Run command [' . $moduleAction . '] in module:  ' . $moduleName . '---' . PHP_EOL;
        echo 'start process - ' . date('Y-m-d H:i:s') . PHP_EOL;

        if (!empty($moduleAction) && !empty($moduleName) && $moduleName != "all") {
            $moduleId = $this->getModuleId($moduleName);
        }

        if ($moduleId !== null) {
            echo 'module [' . $moduleName . '] was found' . PHP_EOL;

            $msg = "command completed successfully";

            if ($moduleAction === "install_sql") {
                $this->InstallModuleSQL($moduleId);
            } elseif ($moduleAction === "upgrade_sql") {
                $div = $this->UpgradeModuleSQL($moduleId);
            } elseif ($moduleAction === "install_acl") {
                $div = $this->InstallModuleACL($moduleId);
            } elseif ($moduleAction === "upgrade_acl") {
                $div = $this->UpgradeModuleACL($moduleId);
            } elseif ($moduleAction === "enable") {
                $div = $this->DisableModule($moduleId);
            } elseif ($moduleAction === "disable") {
                $div = $this->EnableModule($moduleId);
            } elseif ($moduleAction === "install") {
                $div = $this->InstallModule($moduleId);
            } elseif ($moduleAction === "unregister") {
                $div = $this->UnregisterModule($moduleId);
            } else {
                $msg = 'Unsupported command';
            }
        } else {
            $msg = "module Id is null";
        }


        $output = "";

        if (is_array($div)) {
            $output = implode("<br />\n", $div) . PHP_EOL;
        }
        echo $output;

        exit($msg . PHP_EOL);
    }
}
