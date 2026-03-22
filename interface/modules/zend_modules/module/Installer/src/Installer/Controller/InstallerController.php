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
 * @copyright Copyright (c) 2020-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Installer\Controller;

use Application\Listener\Listener;
use Exception;
use Installer\Model\InstModule;
use Installer\Model\InstModuleTable;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Utils\SQLUpgradeService;
use RuntimeException;

class InstallerController extends AbstractActionController
{
    protected $listenerObject;

    public function __construct(protected readonly InstModuleTable $InstallerTable)
    {
        $this->listenerObject = new Listener();
    }

    /**
     * @return ViewModel
     */
    public function nolayout()
    {
        // Turn off the layout, i.e. only render the view script.
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->scanAndRegisterCustomModules();
        //get the list of installed and new modules
        $result = $this->InstallerTable->allModules();
        $allModules = [];
        foreach ($result as $dataArray) {
            $mod = new InstModule();
            $mod->exchangeArray($dataArray);
            $mod = $this->makeButtonForSqlAction($mod);
            $mod = $this->makeButtonForAClAction($mod);
            $allModules[] = $mod;
        }

        return new ViewModel([
            'InstallersExisting' => $allModules,
            'InstallersAll' => $allModules,
            'listenerObject' => $this->listenerObject,
            'dependencyObject' => $this->InstallerTable,
            // TODO: @adunsulag there should be a way to pull this from application.config.php but so far the answer eludes me.
            'coreModules' => ['Application', 'Acl', 'Installer', 'FHIR', 'PatientFlowBoard']
        ]);
    }

    /**
     * @return void
     */
    private function scanAndRegisterCustomModules(): void
    {
        $baseModuleDir = $GLOBALS['baseModDir'];
        $customDir = $GLOBALS['customModDir'];
        $zendModDir = $GLOBALS['zendModDir'];
        $coreModules = ['Application', 'Acl', 'Installer', 'FHIR', 'PatientFlowBoard'];
        $allModules = [];

        $result = $this->InstallerTable->allModules();
        foreach ($result as $dataArray) {
            $mod = new InstModule();
            $mod->exchangeArray($dataArray);
            $mod = $this->makeButtonForSqlAction($mod);
            $mod = $this->makeButtonForAClAction($mod);
            $allModules[] = $mod;
        }

        $dir_path = $GLOBALS['srcdir'] . "/../$baseModuleDir$customDir/";
        $dp = opendir($dir_path);
        $inDirCustom = [];
        for ($i = 0; false != ($file_name = readdir($dp)); $i++) {
            if (!in_array($file_name, [".", "..", "Application"]) && is_dir($dir_path . $file_name)) {
                $inDirCustom[$i] = $file_name;
            }
        }
        /* Laminas directory Unregistered scan */
        $dir_path = $GLOBALS['srcdir'] . "/../$baseModuleDir$zendModDir/module";
        $dp = opendir($dir_path);
        $inDirLaminas = [];
        for ($i = 0; false != ($file_name = readdir($dp)); $i++) {
            if ($file_name != "." && $file_name != ".." && (!in_array($file_name, $coreModules)) && is_dir($dir_path . "/" . $file_name)) {
                $inDirLaminas[$i] = $file_name;
            }
        }
        // do not show registered modules in the unregistered list
        if (count($allModules) > 0) {
            foreach ($allModules as $modules) {
                $key = array_search($modules->modDirectory, $inDirLaminas);
                if ($key !== false) {
                    unset($inDirLaminas[$key]);
                    continue;
                }
                $key = array_search($modules->modDirectory, $inDirCustom);
                if ($key !== false) {
                    unset($inDirCustom[$key]);
                }
            }
        }
        foreach ($inDirLaminas as $file_name) {
            $rel_path = $file_name . "/index.php";
            $status = $this->InstallerTable->register($file_name, $rel_path, 0, $zendModDir);
        }
        foreach ($inDirCustom as $file_name) {
            $rel_path = $file_name . "/index.php";
            $status = $this->InstallerTable->register($file_name, $rel_path);
        }
    }

    /**
     * @return void
     */
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
                if ($this->InstallerTable->register($request->getPost('mod_name'), $rel_path, 0, $GLOBALS['zendModDir'])) {
                    $status = true;
                }
            } else {
                // TODO: there doesn't appear to be any methodology in how to load these custom registered modules... which seems pretty odd.
                // there aren't any in the system... but why have this then?
                $rel_path = $request->getPost('mod_name') . "/index.php";
                if ($this->InstallerTable->register($request->getPost('mod_name'), $rel_path)) {
                    $status = true;
                }
            }
            die($status ? $this->listenerObject->z_xlt("Success") : $this->listenerObject->z_xlt("Failure"));
        } else {
            die("Something went very wrong, so exiting");
        }
    }

    /**
     * @return void
     */
    public function manageAction()
    {
        if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
            echo json_encode(["status" => xlt('Not Authorized')]);
            exit;
        }

        $request = $this->getRequest();
        $status = $this->listenerObject->z_xlt("Failure");
        if ($request->isPost()) {
            $modId = $request->getPost('modId') ?? null;
            $registryEntry = $this->InstallerTable->getRegistryEntry($modId, "mod_directory");
            $dirModule = $registryEntry->modDirectory;
            $modType = $registryEntry->type;
            $action = $request->getPost('modAction');
            // send pre action event to module.
            if ($modType == InstModuleTable::MODULE_TYPE_CUSTOM) {
                $status = $this->notifyModuleListener("pre" . $action, $modId, $dirModule, $status);
                if ($status == 'Failure' && $action == 'help_requested') {
                    $status = $this->listenerObject->z_xlt("Help doesn't exist for this module!");
                }
                if ($status == 'bypass_event') {
                    $output = "";
                    echo json_encode(["status" => 'Success', "output" => $output]);
                    exit(0);
                }
            }
            if ($action == "enable") {
                $status = $this->EnableModule($request->getPost('modId'));
            } elseif ($action == "disable") {
                $status = $this->DisableModule($request->getPost('modId'));
            } elseif ($action == "install") {
                $modId = $request->getPost('modId');
                $mod_enc_menu = $request->getPost('mod_enc_menu');
                $mod_nick_name = $request->getPost('mod_nick_name');
                $status = $this->InstallModule($modId, $mod_enc_menu, $mod_nick_name);
            } elseif ($action == 'install_sql') {
                if ($this->InstallModuleSQL($request->getPost('modId'))) {
                    $status = $this->listenerObject->z_xlt("Success");
                } else {
                    $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not open table") . '.' . $this->listenerObject->z_xlt("sql") . ', ' . $this->listenerObject->z_xlt("broken form") . "?";
                }
            } elseif ($action == 'upgrade_sql') {
                $div = $this->UpgradeModuleSQL($request->getPost('modId'));
                $status = $this->listenerObject->z_xlt("Success");
            } elseif ($action == 'install_acl') {
                if ($div = $this->InstallModuleACL($request->getPost('modId'))) {
                    $status = $this->listenerObject->z_xlt("Success");
                } else {
                    $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not install ACL");
                }
            } elseif ($action == 'upgrade_acl') {
                if ($div = $this->UpgradeModuleACL($request->getPost('modId'))) {
                    $status = $this->listenerObject->z_xlt("Success");
                } else {
                    $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not install ACL");
                }
            } elseif ($action == "unregister") {
                $status = $this->UnregisterModule($request->getPost('modId'));
            } elseif ($action === 'remove_files') {
                if ($modType !== InstModuleTable::MODULE_TYPE_CUSTOM) {
                    $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("Can only remove custom (uploaded) modules from disk.");
                } else {
                    $srcDir     = OEGlobalsBag::getInstance()->get('srcdir');
                    $baseModDir = OEGlobalsBag::getInstance()->get('baseModDir');
                    if (!is_string($srcDir) || !is_string($baseModDir)) {
                        $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("Module configuration paths not available.");
                    } else {
                        $customModulesBase = realpath($srcDir . '/../' . $baseModDir . 'custom_modules');
                        if ($customModulesBase === false) {
                            $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("Custom modules directory not found.");
                        } else {
                            $targetDir = $customModulesBase . DIRECTORY_SEPARATOR . $dirModule;
                            if (dirname($targetDir) === $customModulesBase && is_dir($targetDir)) {
                                $this->deleteDirectoryRecursively($targetDir);
                                $modIdValue = $request->getPost('modId');
                                $status = $this->UnregisterModule(is_string($modIdValue) ? $modIdValue : '');
                            } else {
                                $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("Module directory not found or outside sandbox.");
                            }
                        }
                    }
                }
            } elseif ($action == "reset_module") {
                // call listener to reset module to initial state perhaps!
                $status =  "Success";
            }
            // send post same action event to module.
            if ($modType == InstModuleTable::MODULE_TYPE_CUSTOM) {
                $status = $this->notifyModuleListener($action, $modId, $dirModule, $status);
                if ($status == 'Failure' && $action == 'help_requested') {
                    $status = $this->listenerObject->z_xlt("Help doesn't exist for this module!");
                }
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
     * @param $action
     * @param $modId
     * @param $dirModule
     * @param $currentStatus
     * @return mixed
     */
    private function notifyModuleListener($action, $modId, $dirModule, $currentStatus): mixed
    {
        $modPath = $GLOBALS['fileroot'] . "/" . $GLOBALS['baseModDir'] . "custom_modules/" . $dirModule;
        $moduleClassPath = $modPath . '/ModuleManagerListener.php';
        $className = 'ModuleManagerListener';
        $action = trim((string) $action);

        // Check if the module class file exists
        if (!file_exists($moduleClassPath)) {
            return $currentStatus;
        }

        // Load the module class file
        require_once($moduleClassPath);
        // Get the namespace of the module listener class if one exists.
        // This is very useful to avoid namespacing contention between modules
        // as well as to avoid the use of the Laminas MM namespace.
        // Useful when the config script is called so config doesn't have to register its namespace.
        $namespace = $className::getModuleNamespace();
        if (!empty($namespace)) {
            try {
                $classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
                $classLoader->registerNamespaceIfNotExists($namespace, $modPath . DIRECTORY_SEPARATOR . 'src');
            } catch (Exception $e) {
                error_log('Error loading namespace: ' . $e->getMessage());
            }
        }
        // Get the method name and initialize the listener instance
        $methodName = trim($action);
        $instance = $className::initListenerSelf();
        // Check if the listener class exists and has the required method
        if (class_exists($instance::class) && method_exists($instance, 'moduleManagerAction')) {
            try {
                // Call the module manager action method and return the result
                // This method is expected to return the current status of the module unless module wishes to override it.
                // In that case, new text of result will display as alert in UI.
                return ($instance->moduleManagerAction(...))($methodName, $modId, $currentStatus);
            } catch (Exception $e) {
                error_log('Error calling module manager action: ' . $e->getMessage());
                return $currentStatus;
            }
        }

        return $currentStatus;
    }

    /**
     * @param $version
     * @return int|string
     */
    private function upgradeAclFromVersion($ACL_UPGRADE, $version)
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
    private function installACL($dir): bool
    {
        $aclfile = $dir . "/moduleACL.php";
        if (file_exists($aclfile)) {
            include_once($aclfile);
            return true;
        }
        return false;
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

    /**
     * @return JsonModel
     */
    public function SaveHooksAction()
    {
        $request = $this->getRequest();
        $postArr = $request->getPost();
        //DELETE OLD HOOKS OF A MODULE
        $this->InstallerTable->deleteModuleHooks($postArr['mod_id']);
        if (!empty($postArr['hook_hanger']) && count($postArr['hook_hanger']) > 0) {
            foreach ($postArr['hook_hanger'] as $hookId => $hooks) {
                foreach ($hooks as $hangerId => $hookHanger) {
                    $this->InstallerTable->saveHooks($postArr['mod_id'], $hookId, $hangerId);
                }
            }

            $return[0] = ['return' => 1, 'msg' => $this->listenerObject->z_xlt("Saved Successfully")];
        } else {
            $return[0] = ['return' => 1, 'msg' => $this->listenerObject->z_xlt("No Hooks enabled for this Module")];
        }

        $arr = new JsonModel($return);
        return $arr;
    }

    /**
     * @return ViewModel
     */
    public function configureAction()
    {
        $request = $this->getRequest();
        $modId = $request->getPost('mod_id');

        /** Configuration Details */
        $result = $this->InstallerTable->getConfigSettings($modId);
        $configuration = [];
        foreach ($result as $tmp) {
            $configuration[$tmp['field_name']] = $tmp;
        }

        //INSERT MODULE HOOKS IF NOT EXISTS
        $moduleDirectory = $this->InstallerTable->getModuleDirectory($modId);
        //GET MODULE HOOKS FROM A FUNCTION IN CONFIGURATION MODEL CLASS
        $hooksArr = $this->InstallerTable->getModuleHooks($moduleDirectory) ?: [];

        if (count($hooksArr) > 0) {
            foreach ($hooksArr as $hook) {
                if (count($hook ?? []) > 0) {
                    if ($this->InstallerTable->checkModuleHookExists($modId, $hook['name']) == "0") {
                        $this->InstallerTable->saveModuleHooks($modId, $hook['name'], $hook['title'], $hook['path']);
                    }
                }
            }
        } else {
            //DELETE ADDED HOOKS TO HANGERS OF THIS MODULE, IF NO HOOKS EXIST IN THIS MODULE
            $this->InstallerTable->deleteModuleHooks($modId);
            //DELETE MODULE HOOKS
            $this->InstallerTable->deleteModuleHookSettings($modId);
        }

        //GET MODULE ACL SECTION FROM A FUNCTION IN CONFIGURATION MODEL CLASS
        $aclArray = $this->InstallerTable->getModuleAclSections($moduleDirectory);
        if (count($aclArray) > 0) {
            $this->InstallerTable->insertAclSections($aclArray, $moduleDirectory, $modId);
        } else {
            $this->InstallerTable->deleteACLSections($modId);
        }

        $obj = $this->InstallerTable->getObject($moduleDirectory, 'Controller');
        $aclArray = [];
        if ($obj) {
            $aclArray = $obj->getAclConfig();
        }

        /** Configuration Form and Configuration Form Class */
        $configForm = $this->InstallerTable->getFormObject($moduleDirectory);

        /** Setup Config Details */
        $setup = $this->InstallerTable->getSetupObject($moduleDirectory);

        return new ViewModel([
            'mod_id' => $modId,
            'TabSettings' => $this->InstallerTable->getTabSettings($modId),
            'ACL' => $this->InstallerTable->getSettings('ACL', $modId),
            'OemrUserGroup' => $this->InstallerTable->getOemrUserGroup(),
            'OemrUserGroupAroMap' => $this->InstallerTable->getOemrUserGroupAroMap(),
            'ListActiveUsers' => $this->InstallerTable->getActiveUsers(),
            'ListActiveACL' => $this->InstallerTable->getActiveACL($modId),
            'ListActiveHooks' => $this->InstallerTable->getActiveHooks($modId),
            'helperObject' => $this->helperObject ?? null,
            'configuration' => $configuration,
            'hangers' => $this->InstallerTable->getHangers(),
            'Hooks' => $hooksArr,
            'hookObject' => $this->InstallerTable,
            'settings' => $configForm,
            'listenerObject' => $this->listenerObject,
            'setup' => $setup,
        ]);
    }

    /**
     * @return JsonModel
     */
    public function saveConfigAction()
    {
        $request = $this->getRequest();
        $moduleId = $request->getPost()->module_id;

        foreach ($request->getPost() as $key => $value) {
            $fieldName = $key;
            $fieldValue = $value;
            if ($fieldName != 'module_id') {
                $result = $this->InstallerTable->saveSettings($fieldName, $fieldValue, $moduleId);
            }
        }

        $data = [];
        $returnArr = ['modeId' => $moduleId];
        $return = new JsonModel($returnArr);
        return $return;
    }

    /**
     * @return JsonModel
     */
    public function DeleteAclAction()
    {
        $request = $this->getRequest();
        $this->InstallerTable->DeleteAcl($request->getPost());
        $return[0] = ['return' => 1, 'msg' => $this->listenerObject->z_xlt("Deleted Successfully")];
        $arr = new JsonModel($return);
        return $arr;
    }

    /**
     * @return JsonModel
     */
    public function DeleteHooksAction()
    {
        $request = $this->getRequest();
        $this->InstallerTable->DeleteHooks($request->getPost());
        $return[0] = ['return' => 1, 'msg' => $this->listenerObject->z_xlt("Deleted Successfully")];
        $arr = new JsonModel($return);
        return $arr;
    }

    /**
     * @return void
     */
    public function nickNameAction(): never
    {
        $request = $this->getRequest();
        $nickname = $request->getPost()->nickname;
        echo $this->InstallerTable->validateNickName(trim((string) $nickname));
        exit(0);
    }

    /**
     * @param $modId
     * @return false|string
     */
    function getModuleVersionFromFile($modId)
    {
        //SQL version of Module
        $dirModule = $this->InstallerTable->getRegistryEntry($modId, "mod_directory");
        $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $dirModule->modDirectory;
        if (!is_dir($ModulePath)) {
            $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "custom_modules/" . $dirModule->modDirectory;
        }
        $version_of_module = $ModulePath . "/version.php";
        $table_sql = $ModulePath . "/table.sql";
        $install_sql = $ModulePath . "/sql/install.sql";
        $upgrade_sql = $ModulePath . "/sql/upgrade.sql";
        $install_acl = $ModulePath . "/acl/acl_setup.php";
        if (file_exists($version_of_module) && (file_exists($table_sql) || file_exists($install_sql) || file_exists($install_acl))) {
            include_once($version_of_module);
            $version = $v_major . "." . $v_minor . "." . $v_patch;
            return $version;
        }
        return false;
    }

    /**
     * @param $modDirectory
     * @param $sqldir
     * @return array|false
     */
    public function getFilesForUpgrade($modDirectory, $sqldir): false|array
    {
        $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $modDirectory;
        $versions = [];
        $dh = opendir($sqldir);
        if (!$dh) {
            return false;
        }

        while (false !== ($sfname = readdir($dh))) {
            if (str_starts_with($sfname, '.')) {
                continue;
            }

            if (preg_match('/^(\d+)_(\d+)_(\d+)-to-\d+_\d+_\d+_upgrade.sql$/', $sfname, $matches)) {
                $version = $matches[1] . '.' . $matches[2] . '.' . $matches[3];
                $versions[$version] = $sfname;
            }
        }
        $arrayKeys = array_keys($versions);
        usort($arrayKeys, version_compare(...));
        $sortVersions = [];
        foreach ($arrayKeys as $key) {
            $sortVersions[$key] = $versions[$key];
        }
        return $sortVersions;
    }

    /**
     * @param InstModule $mod
     * @return InstModule
     */
    public function makeButtonForSqlAction(InstModule $mod)
    {
        $dirModule = $this->InstallerTable->getRegistryEntry($mod->modId, "mod_directory");
        $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $dirModule->modDirectory;
        $sqldir = $ModulePath . "/sql";
        if (!is_dir($sqldir)) {
            $sqldir = $ModulePath;
        }
        if (!is_dir($sqldir)) {
            $ModulePath = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "custom_modules/" . $dirModule->modDirectory;
            $sqldir = $ModulePath . "/sql";
            if (!is_dir($sqldir)) {
                $sqldir = $ModulePath;
            }
        }
        $mod->sql_action = "";

        if (file_exists($sqldir . "/install.sql") && file_exists($ModulePath . "/version.php") && empty($mod->sql_version)) {
            $mod->sql_action = "install";
        }

        if (!empty($mod->sql_version) && $mod->sqlRun == 1) {
            $versions = $this->getFilesForUpgrade($mod->modDirectory, $sqldir) ?: [];

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

    /**
     * @param InstModule $mod
     * @return InstModule
     */
    public function makeButtonForACLAction(InstModule $mod)
    {
        $dirModule = $this->InstallerTable->getRegistryEntry($mod->modId, "mod_directory");
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
    public function getModuleId($moduleName): bool
    {
        if (empty($moduleName)) {
            return false;
        }
        $allModules = $this->InstallerTable->allModules();
        foreach ($allModules as $module) {
            if ($module["mod_directory"] === $moduleName) {
                return $module["mod_id"];
            }
        }
        return false;
    }

    /**
     * @param string $modId
     * @return bool
     */
    public function InstallModuleSQL($modId = '')
    {
        $registryEntry = $this->InstallerTable->getRegistryEntry($modId, "mod_directory");
        $dirModule = $registryEntry->modDirectory;
        $modType = $registryEntry->type;
        $modUri = "zend_modules/module/";
        if ($modType == InstModuleTable::MODULE_TYPE_CUSTOM) {
            $modUri = "custom_modules/";
        }
        if ($this->InstallerTable->installSQL($modId, $modType, $GLOBALS['fileroot'] . "/" . $GLOBALS['baseModDir'] . $modUri . $dirModule)) {
            $values = [$registryEntry->mod_nick_name, $registryEntry->mod_enc_menu];
            $values[2] = $this->getModuleVersionFromFile($modId);
            $values[3] = $registryEntry->acl_version;
            $this->InstallerTable->updateRegistered($modId, '', $values);
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
        $Module = $this->InstallerTable->getRegistryEntry($modId, "mod_directory");
        $modType = $Module->type;
        $modUri = "zend_modules/module/";
        if ($modType == InstModuleTable::MODULE_TYPE_CUSTOM) {
            $modUri = "custom_modules/";
        }
        $modDir = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . $modUri . $Module->modDirectory;
        $sqlInstallLocation = $modDir . '/sql';
        // if this is a custom module that for some reason doesn't have the SQL in a sql folder...
        if (!file_exists($sqlInstallLocation)) {
            $sqlInstallLocation = $modDir;
        }

        $versions = $this->getFilesForUpgrade($Module->modDirectory, $sqlInstallLocation);

        $values = [$Module->mod_nick_name, $Module->mod_enc_menu];
        $div = [];
        $outputToBrowser = '';
        foreach ($versions as $version => $filename) {
            if (version_compare($version, $Module->sql_version) < 0) {
                continue;
            }
            ob_start();
            $sqlUpgradeService = new SQLUpgradeService();
            $sqlUpgradeService->setRenderOutputToScreen(false);
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
                if (count($matches[1]) == (count($div ?? []) - $add_ended_divs) && (!$prev_html_tag && !$curr_html_tag)) {
                    $div[] = "</div>";
                }
                $k++;
            }
        }
        $values[2] = $this->getModuleVersionFromFile($modId);
        $values[3] = $Module->acl_version;
        $this->InstallerTable->updateRegistered($modId, '', $values);
        return $div;
    }

    /**
     * @param string $modId
     * @return bool
     */
    public function InstallModuleACL($modId = '')
    {
        $Module = $this->InstallerTable->getRegistryEntry($modId, "mod_directory");
        $modDir = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $Module->modDirectory;
        $div = [];
        if (file_exists($modDir . "/acl/acl_setup.php") && empty($modDir->acl_version)) {
            // Pass a variable, so below scripts can not be run on their own
            $aclSetupFlag = true;
            ob_start();
            include_once($modDir . "/acl/acl_setup.php");
            $div[] = ob_get_contents();
            ob_end_clean();
            $values = [$Module->mod_nick_name, $Module->mod_enc_menu];
            $values[2] = $Module->sql_version;
            $values[3] = $this->getModuleVersionFromFile($modId);
            $this->InstallerTable->updateRegistered($modId, '', $values);
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
        $resp = $this->InstallerTable->updateRegistered($modId, "mod_active=1");
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
        $resp = $this->InstallerTable->updateRegistered($modId, "mod_active=0");
        if ($resp['status'] == 'failure' && $resp['code'] == '200') {
            $plural = "Module";
            if (count($resp['value'] ?? []) > 1) {
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
        $registryEntry = $this->InstallerTable->getRegistryEntry($modId, "mod_directory");
        $modType = $registryEntry->type;
        $dirModule = $registryEntry->modDirectory;
        $sqlInstalled = false;
        if ($modType == InstModuleTable::MODULE_TYPE_CUSTOM) {
            $fullDirectory = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . $GLOBALS['customModDir'] . "/" . $dirModule;
            if ($this->InstallerTable->installSQL($modId, $modType, $fullDirectory)) {
                $sqlInstalled = true;
            } else {
                // TODO: This is a weird error... why is it written like this?
                $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not open table") . '.' . $this->listenerObject->z_xlt("sql") . ', ' . $this->listenerObject->z_xlt("broken form") . "?";
            }
        } elseif ($modType == InstModuleTable::MODULE_TYPE_ZEND) {
            $fullDirectory = $GLOBALS['srcdir'] . "/../" . $GLOBALS['baseModDir'] . "zend_modules/module/" . $dirModule;
            if ($this->InstallerTable->installSQL($modId, $modType, $fullDirectory)) {
                $sqlInstalled = true;
            } else {
                $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not run sql query");
            }
        }

        if ($sqlInstalled) {
            $values = [$mod_nick_name, $mod_enc_menu];
            $values[2] = $this->getModuleVersionFromFile($modId);
            $this->InstallerTable->updateRegistered($modId, '', $values);
            $status = $this->listenerObject->z_xlt("Success");
        }

        return $status;
    }

    /**
     * Function to Unregister Module
     *
     * @param string $modId
     * @return boolean
     */
    /**
     * Recursively delete a directory and all its contents.
     */
    private function deleteDirectoryRecursively(string $dir): void
    {
        $items = glob($dir . DIRECTORY_SEPARATOR . '*') ?: [];
        foreach ($items as $item) {
            if (is_dir($item)) {
                $this->deleteDirectoryRecursively($item);
            } else {
                unlink($item);
            }
        }
        rmdir($dir);
    }

    /**
     * @param string $modId
     */
    public function UnregisterModule($modId = ''): bool|string
    {
        $resp = $this->InstallerTable->unRegister($modId);
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
        $Module = $this->InstallerTable->getRegistryEntry($modId, "mod_directory");
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

            if (strlen((string) $version) > 0) {
                $values = [$Module->mod_nick_name, $Module->mod_enc_menu];
                $values[2] = $Module->sql_version;
                $values[3] = $this->getModuleVersionFromFile($modId);
                $this->InstallerTable->updateRegistered($modId, '', $values);
            }
            return $div;
        }
        return false;
    }

    /**
     * Upload and install a third-party custom module from a ZIP file.
     *
     * Security model:
     *  - ZIP must contain exactly one top-level directory (the module directory name).
     *  - Every file inside the ZIP must reside under that single top-level directory.
     *  - No path traversal sequences (..) are permitted in any ZIP entry name.
     *  - The resolved extraction target must be inside custom_modules/ only.
     *  - Extraction is done entry-by-entry so no ZipArchive::extractTo() path bypass.
     *  - The action requires admin/manage_modules ACL.
     *
     * @return ViewModel
     */
    public function uploadAction(): ViewModel
    {
        if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
            echo xlt('Not Authorized');
            exit;
        }

        $viewModel = new ViewModel();
        $error   = null;
        $success = null;

        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $session = SessionWrapperFactory::getInstance()->getActiveSession();
                if (!CsrfUtils::verifyCsrfToken($request->getPost('csrf_token_form'), $session)) {
                    CsrfUtils::csrfNotVerified();
                }

                $confirmOverwrite = ($request->getPost('confirm_overwrite') === '1');
                $uploadMethod = $request->getPost('upload_method') ?? 'file';

                // --- Acquire the ZIP path ---
                /** @var array{token: string, path: string, modName: string}|null $pending */
                $pending = null;
                if ($confirmOverwrite) {
                    // Confirmation pass: retrieve and validate the stashed file
                    $pendingToken = $request->getPost('pending_token');
                    if (!is_string($pendingToken) || $pendingToken === '') {
                        throw new \RuntimeException(xlt('Invalid confirmation request.'));
                    }
                    $sessionPending = $_SESSION['oemr_mod_pending'] ?? null;
                    if (
                        !is_array($sessionPending) ||
                        !isset($sessionPending['token'], $sessionPending['path'], $sessionPending['modName']) ||
                        !is_string($sessionPending['token']) ||
                        !is_string($sessionPending['path']) ||
                        !is_string($sessionPending['modName']) ||
                        !hash_equals($sessionPending['token'], $pendingToken) ||
                        !is_file($sessionPending['path'])
                    ) {
                        throw new \RuntimeException(xlt('Upload confirmation token is invalid or has expired. Please upload the file again.'));
                    }
                    $pending = [
                        'token'   => $sessionPending['token'],
                        'path'    => $sessionPending['path'],
                        'modName' => $sessionPending['modName'],
                    ];
                    $tmpPath = $pending['path'];
                } else {
                    // Normal upload pass — require disclaimer
                    if (!$request->getPost('disclaimer_accepted')) {
                        throw new \RuntimeException(xlt('You must accept the disclaimer before uploading a module.'));
                    }

                    if ($uploadMethod === 'url') {
                        // URL download method
                        $moduleUrl = $request->getPost('module_url');
                        if (!is_string($moduleUrl) || $moduleUrl === '') {
                            throw new \RuntimeException(xlt('No module URL provided.'));
                        }
                        $tmpPath = $this->downloadModuleFromUrl($moduleUrl);
                    } else {
                        // File upload method
                        /** @var array{name: string, type: string, tmp_name: string, error: int, size: int}|null $file */
                        $file = $_FILES['module_zip'] ?? null;
                        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
                            throw new \RuntimeException(xlt('No file uploaded or upload error occurred.'));
                        }
                        $tmpPath = $file['tmp_name'];
                    }
                }

                // --- Validate ZIP integrity ---
                $zip = new \ZipArchive();
                if ($zip->open($tmpPath) !== true) {
                    throw new \RuntimeException(xlt('The uploaded file is not a valid ZIP archive.'));
                }

                // Phase 1: discover the single top-level directory
                $topDirs = [];
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if ($entryName === false) {
                        continue;
                    }
                    if (str_contains($entryName, '..')) {
                        $zip->close();
                        throw new \RuntimeException(xlt('ZIP contains path traversal sequences and was rejected.'));
                    }
                    if (str_starts_with($entryName, '/') || str_starts_with($entryName, '\\')) {
                        $zip->close();
                        throw new \RuntimeException(xlt('ZIP contains absolute paths and was rejected.'));
                    }
                    $parts = explode('/', ltrim($entryName, '/'), 2);
                    if ($parts[0] !== '') {
                        $topDirs[$parts[0]] = true;
                    }
                }
                if (count($topDirs) !== 1) {
                    $zip->close();
                    throw new \RuntimeException(xlt('ZIP must contain exactly one top-level directory (the module directory).'));
                }
                $moduleDirName = array_key_first($topDirs);

                // On confirm pass: verify the ZIP still matches what the session recorded
                if ($confirmOverwrite && is_array($pending) && $moduleDirName !== $pending['modName']) {
                    $zip->close();
                    throw new \RuntimeException(xlt('Upload confirmation mismatch. Please upload the file again.'));
                }

                // Validate directory name: only alphanumeric, dash, underscore
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $moduleDirName)) {
                    $zip->close();
                    throw new \RuntimeException(xlt('Module directory name contains invalid characters.'));
                }

                // Block names that conflict with OpenEMR core directories or Laminas core modules
                // THIS MUST BE CHECKED BEFORE DIRECTORY EXISTENCE to prevent wrong error messages
                $reservedNames = [
                    'interface', 'library', 'src', 'sites', 'portal', 'modules',
                    'apis', 'oauth2', 'ccdaservice', 'ccr', 'config', 'contrib',
                    'controllers', 'custom', 'gacl', 'templates', 'tests',
                    'application', 'acl', 'installer', 'fhir', 'patientflowboard',
                    'custom_modules', 'zend_modules',
                ];
                if (in_array(strtolower($moduleDirName), $reservedNames)) {
                    $zip->close();
                    throw new \RuntimeException(xlt('Module directory name conflicts with a reserved OpenEMR name.'));
                }

                // --- Module Structure Validation ---
                // Validate that this is a properly packaged OpenEMR custom module
                $requiredFiles = ['moduleConfig.php']; // Core required file
                $invalidStructures = [
                    'interface/modules/' => 'ZIP appears to be created from OpenEMR root instead of module directory',
                    'library/' => 'ZIP appears to be created from OpenEMR root instead of module directory', 
                    'sites/' => 'ZIP appears to be created from OpenEMR root instead of module directory',
                    'vendor/' => 'ZIP appears to be created from OpenEMR root instead of module directory'
                ];
                
                $foundFiles = [];
                $hasInvalidStructure = false;
                $invalidReason = '';
                
                // Scan ZIP contents for validation
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if ($entryName === false) {
                        continue;
                    }
                    
                    // Check for invalid OpenEMR root structures
                    foreach ($invalidStructures as $badPath => $reason) {
                        if (str_starts_with($entryName, $moduleDirName . '/' . $badPath)) {
                            $hasInvalidStructure = true;
                            $invalidReason = $reason;
                            break 2;
                        }
                    }
                    
                    // Track required files (relative to module root)
                    $relativePath = substr($entryName, strlen($moduleDirName) + 1);
                    if (!str_ends_with($entryName, '/')) { // Not a directory
                        $foundFiles[] = $relativePath;
                    }
                }
                
                // Check for invalid structure (developer zipped from wrong directory)
                if ($hasInvalidStructure) {
                    $zip->close();
                    throw new \RuntimeException(xlt('Invalid module ZIP structure') . ': ' . xlt($invalidReason) . '. ' . 
                        xlt('The ZIP should contain only the module files, not OpenEMR core directories.'));
                }
                
                // Check for required module files
                $missingFiles = [];
                foreach ($requiredFiles as $requiredFile) {
                    if (!in_array($requiredFile, $foundFiles, true)) {
                        $missingFiles[] = $requiredFile;
                    }
                }
                
                if (count($missingFiles) > 0) {
                    $zip->close();
                    throw new \RuntimeException(xlt('Not a valid OpenEMR module ZIP') . ': ' . 
                        xlt('Missing required files') . ': ' . implode(', ', $missingFiles) . '. ' .
                        xlt('This does not appear to be a properly packaged OpenEMR custom module.'));
                }

                // Resolve the target path and confirm it sits inside custom_modules/
                $srcDir     = OEGlobalsBag::getInstance()->get('srcdir');
                $baseModDir = OEGlobalsBag::getInstance()->get('baseModDir');
                if (!is_string($srcDir) || !is_string($baseModDir)) {
                    $zip->close();
                    throw new \RuntimeException(xlt('Module configuration paths not available.'));
                }
                $customModulesBase = realpath($srcDir . '/../' . $baseModDir . 'custom_modules');
                if ($customModulesBase === false) {
                    $zip->close();
                    throw new \RuntimeException(xlt('Custom modules directory not found.'));
                }
                $targetModuleDir = $customModulesBase . DIRECTORY_SEPARATOR . $moduleDirName;
                if (dirname($targetModuleDir) !== $customModulesBase) {
                    $zip->close();
                    throw new \RuntimeException(xlt('Resolved module path is outside the allowed custom_modules directory.'));
                }

                // --- Overwrite guard ---
                if (is_dir($targetModuleDir) && !$confirmOverwrite) {
                    $zip->close();

                    // If the directory is already registered in the database the admin must
                    // explicitly unregister it through the Module Manager before uploading a
                    // replacement.  This prevents a malicious (or careless) upload from
                    // silently substituting the files of a trusted installed module.
                    $registrationStatus = $this->InstallerTable->getModuleStatusByDirectoryName($moduleDirName);
                    if ($registrationStatus !== 'Missing') {
                        throw new \RuntimeException(sprintf(
                            xlt('Module "%s" is already registered (%s). Unregister it from the Module Manager before uploading a replacement.'),
                            $moduleDirName,
                            is_string($registrationStatus) ? $registrationStatus : 'registered'
                        ));
                    }

                    // Directory exists but is not registered (orphaned) — prompt for confirmation.
                    $token    = bin2hex(random_bytes(16));
                    $tmpStash = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'oemr_mod_' . $token . '.zip';
                    move_uploaded_file($tmpPath, $tmpStash);
                    $_SESSION['oemr_mod_pending'] = [
                        'token'   => $token,
                        'path'    => $tmpStash,
                        'modName' => $moduleDirName,
                    ];
                    $viewModel->setVariables([
                        'listenerObject' => $this->listenerObject,
                        'needsConfirm'   => true,
                        'moduleDirName'  => $moduleDirName,
                        'pendingToken'   => $token,
                        'error'          => null,
                        'success'        => null,
                    ]);
                    return $viewModel;
                }

                // Remove existing directory before re-extraction
                if ($confirmOverwrite && is_dir($targetModuleDir)) {
                    $this->deleteDirectoryRecursively($targetModuleDir);
                }

                // Phase 2: extract entry-by-entry into the target directory
                if (!is_dir($targetModuleDir)) {
                    mkdir($targetModuleDir, 0755, true);
                }
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if ($entryName === false) {
                        continue;
                    }
                    if (rtrim($entryName, '/') === $moduleDirName) {
                        continue;
                    }
                    $relPath = substr($entryName, strlen($moduleDirName) + 1);
                    if ($relPath === '') {
                        continue;
                    }
                    $destPath = $targetModuleDir . DIRECTORY_SEPARATOR . $relPath;
                    $normalizedDest = str_replace('\\', '/', $destPath);
                    $normalizedBase = str_replace('\\', '/', $targetModuleDir) . '/';
                    if (!str_starts_with($normalizedDest, $normalizedBase) && $destPath !== $targetModuleDir) {
                        $zip->close();
                        throw new \RuntimeException(xlt('ZIP entry would extract outside module directory. Aborting.'));
                    }
                    if (str_ends_with($entryName, '/')) {
                        if (!is_dir($destPath)) {
                            mkdir($destPath, 0755, true);
                        }
                    } else {
                        $parentDir = dirname($destPath);
                        if (!is_dir($parentDir)) {
                            mkdir($parentDir, 0755, true);
                        }
                        $stream = $zip->getStream($entryName);
                        if ($stream === false) {
                            $zip->close();
                            throw new \RuntimeException(sprintf(xlt('Could not read ZIP entry: %s'), $entryName));
                        }
                        file_put_contents($destPath, stream_get_contents($stream));
                        fclose($stream);
                    }
                }
                $zip->close();

                // Clean up session stash after successful confirm-overwrite
                if ($confirmOverwrite && is_array($pending) && is_file($pending['path'])) {
                    @unlink($pending['path']);
                    unset($_SESSION['oemr_mod_pending']);
                }

                // Registration is handled automatically by scanAndRegisterCustomModules()
                // on the next Module Manager index load. Nothing more needed here.
                $success = sprintf(
                    xlt('Module "%s" uploaded successfully. It will appear in the Module Manager below. Click Install to complete setup.'),
                    $moduleDirName
                );
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $viewModel->setVariables([
            'listenerObject' => $this->listenerObject,
            'error'          => $error,
            'success'        => $success,
            'needsConfirm'   => false,
            'moduleDirName'  => '',
            'pendingToken'   => '',
        ]);
        return $viewModel;
    }

    /**
     *
     */
    public function commandInstallModuleAction($moduleName, $moduleAction)
    {
        if (php_sapi_name() !== 'cli') {
            throw new RuntimeException('You can only use this action from a console!');
        }

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
                $div = $this->EnableModule($moduleId);
            } elseif ($moduleAction === "disable") {
                $div = $this->DisableModule($moduleId);
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

    /**
     * Download a module ZIP file from a URL with security validation
     * 
     * @param string $url The URL to download the ZIP from
     * @return string Path to the downloaded temporary file
     * @throws \RuntimeException If download fails or URL is invalid
     */
    private function downloadModuleFromUrl(string $url): string
    {
        // Validate URL format and security
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException(xlt('Invalid URL format provided.'));
        }

        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['scheme'], $parsedUrl['host'])) {
            throw new \RuntimeException(xlt('Invalid URL structure.'));
        }

        // Require HTTPS for security
        if ($parsedUrl['scheme'] !== 'https') {
            throw new \RuntimeException(xlt('Only HTTPS URLs are allowed for security reasons.'));
        }

        // Validate trusted hosts (GitHub, GitLab, etc.)
        $trustedHosts = [
            'github.com',
            'gitlab.com',
            'bitbucket.org',
            'raw.githubusercontent.com',
            'gitlab.io'
        ];
        
        $isValidHost = false;
        foreach ($trustedHosts as $trustedHost) {
            if ($parsedUrl['host'] === $trustedHost || str_ends_with($parsedUrl['host'], '.' . $trustedHost)) {
                $isValidHost = true;
                break;
            }
        }
        
        if (!$isValidHost) {
            throw new \RuntimeException(xlt('URL host not in allowed list. Only GitHub, GitLab, and other trusted repositories are supported.'));
        }

        // Create temporary file for download
        $tmpFile = tempnam(sys_get_temp_dir(), 'oemr_module_url_');
        if ($tmpFile === false) {
            throw new \RuntimeException(xlt('Could not create temporary file for download.'));
        }

        // Initialize cURL with security settings
        $ch = curl_init();
        if ($ch === false) {
            @unlink($tmpFile);
            throw new \RuntimeException(xlt('Could not initialize HTTP client.'));
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_FILE => fopen($tmpFile, 'w'),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'OpenEMR Module Manager/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_MAXFILESIZE => 100 * 1024 * 1024, // 100MB limit
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            @unlink($tmpFile);
            throw new \RuntimeException(xlt('Download failed') . ': ' . ($error ?: xlt('Unknown error')));
        }

        if ($httpCode !== 200) {
            @unlink($tmpFile);
            throw new \RuntimeException(xlt('Download failed with HTTP status') . ': ' . $httpCode);
        }

        // Verify downloaded file exists and has content
        if (!is_file($tmpFile) || filesize($tmpFile) === 0) {
            @unlink($tmpFile);
            throw new \RuntimeException(xlt('Downloaded file is empty or invalid.'));
        }

        return $tmpFile;
    }
}
