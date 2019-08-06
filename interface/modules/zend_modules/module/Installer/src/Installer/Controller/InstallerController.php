<?php
/**
 * interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Vipin Kumar <vipink@zhservices.com>
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Installer\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Installer\Model\InstModule;
use Application\Listener\Listener;
use Installer\Model\InstModuleTable;
use Zend\Db\Adapter\Adapter;

class InstallerController extends AbstractActionController
{
    /**
     * @var Installer\Model\InstModuleTable
     */
    protected $InstallerTable;
    protected $listenerObject;

    /**
     * @var Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    public function __construct(InstModuleTable $installerTable)
    {
        $this->listenerObject = new Listener;
        $this->InstallerTable = $installerTable;
        $this->dbAdapter = $adapter;
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
            $mod -> exchangeArray($dataArray);
            array_push($allModules, $mod);
        }

        return new ViewModel(array(
        'InstallersExisting'    => $allModules,
        'InstallersAll'         => $allModules,
        'listenerObject'        => $this->listenerObject,
        'dependencyObject'      => $this->getInstallerTable(),
        ));
    }

    /**
     * @return Installer\Model\InstModuleTable
     */
    public function getInstallerTable()
    {
        return $this->InstallerTable;
    }

    public function registerAction()
    {
        $status   = false;
        $request  = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('mtype') == 'zend') {
                // TODO: We want to be able to load the modules
                // from the database.. however, this can be fairly slow so we might want to do some kind of APC caching of the module
                // list that is loaded using the OpenEMR db connector and not the zend db connector, cache the modules, and then
                // we can filter / update that list.  We'll have to inject the unloaded module list into the installer but that is fine.
                $rel_path = "public/".$request->getPost('mod_name')."/";

                // registering the table inserts the module record into the database.
                // it's always loaded regardless, but it inserts it in the database as not activated
                if ($this -> getInstallerTable() -> register($request->getPost('mod_name'), $rel_path, 0, $GLOBALS['zendModDir'])) {
                    $status = true;
                }
            } else {
                // TODO: there doesn't appear to be any methodology in how to load these custom registered modules... which seems pretty odd.
                // there aren't any in the system... but why have this then?
                $rel_path = $request->getPost('mod_name')."/index.php";
                if ($this -> getInstallerTable() -> register($request->getPost('mod_name'), $rel_path)) {
                    $status = true;
                }
            }

            die($status ? $this->listenerObject->z_xlt("Success") : $this->listenerObject->z_xlt("Failure"));
        }
    }

    public function manageAction()
    {
        $request = $this->getRequest();
        $status  = $this->listenerObject->z_xlt("Failure");
        if ($request->isPost()) {
            if ($request->getPost('modAction') == "enable") {
                $resp = $this -> getInstallerTable() -> updateRegistered($request->getPost('modId'), "mod_active=0");
                if ($resp['status'] == 'failure' && $resp['code'] == '200') {
                    $status = $resp['value'];
                } else {
                    $status = $this->listenerObject->z_xlt("Success");
                }
            } elseif ($request->getPost('modAction') == "disable") {
                $resp = $this -> getInstallerTable() -> updateRegistered($request->getPost('modId'), "mod_active=1");
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
            } elseif ($request->getPost('modAction') == "install") {
                $dirModule = $this->getInstallerTable()->getRegistryEntry($request->getPost('modId'), "mod_directory");
                $mod_enc_menu = $request->getPost('mod_enc_menu');
                $mod_nick_name = $request->getPost('mod_nick_name');
                if ($this->getInstallerTable()->installSQL($GLOBALS['srcdir']."/../".$GLOBALS['baseModDir'].$GLOBALS['customModDir']."/".$dirModule -> modDirectory)) {
                    $values = array($mod_nick_name, $mod_enc_menu);
                    $this -> getInstallerTable() -> updateRegistered($request->getPost('modId'), '', $values);
                    $status = $this->listenerObject->z_xlt("Success");
                } else {
                    $status = $this->listenerObject->z_xlt("ERROR") . ':' . $this->listenerObject->z_xlt("could not open table") . '.' . $this->listenerObject->z_xlt("sql").', ' . $this->listenerObject->z_xlt("broken form") . "?";
                }
            }
        }

        echo $status;
        exit(0);
    }

  /**
   * Function to install ACL for the installed modules
   * @param     string  $dir Location of the php file which calling functions to add sections,aco etc.
   * @return boolean
   */
    private function installACL($dir)
    {
        $aclfile = $dir."/moduleACL.php";
        if (file_exists($aclfile)) {
            include_once($aclfile);
        }
    }

  /**
   * Used to recreate the application config file
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
                $string .=        $this ->getContent($value);
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
        $postArr  = $request->getPost();
        //DELETE OLD HOOKS OF A MODULE
        $this->getInstallerTable()->deleteModuleHooks($postArr['mod_id']);
        if (!empty($postArr['hook_hanger']) && count($postArr['hook_hanger']) > 0) {
            foreach ($postArr['hook_hanger'] as $hookId => $hooks) {
                foreach ($hooks as $hangerId => $hookHanger) {
                    $this->getInstallerTable()->saveHooks($postArr['mod_id'], $hookId, $hangerId);
                }
            }

            $return[0]  = array('return' => 1,'msg' => $this->listenerObject->z_xlt("Saved Successfully"));
        } else {
            $return[0]  = array('return' => 1,'msg' => $this->listenerObject->z_xlt("No Hooks enabled for this Module"));
        }

        $arr = new JsonModel($return);
        return $arr;
    }

    public function configureAction()
    {
        $request  = $this->getRequest();
        $modId        = $request->getPost('mod_id');

        /** Configuration Details */
        $result = $this->getInstallerTable()->getConfigSettings($modId);
        $configuration    = array();
        foreach ($result as $tmp) {
            $configuration[$tmp['field_name']] = $tmp;
        }

        //INSERT MODULE HOOKS IF NOT EXISTS
        $moduleDirectory  = $this->getInstallerTable()->getModuleDirectory($modId);
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
        if (sizeof($aclArray)>0) {
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
          'mod_id'                  => $modId,
          'TabSettings'             => $this->getInstallerTable()->getTabSettings($modId),
          'ACL'                     => $this->getInstallerTable()->getSettings('ACL', $modId),
          'OemrUserGroup'           => $this->getInstallerTable()->getOemrUserGroup(),
          'OemrUserGroupAroMap'     => $this->getInstallerTable()->getOemrUserGroupAroMap(),
          'ListActiveUsers'         => $this->getInstallerTable()->getActiveUsers(),
          'ListActiveACL'           => $this->getInstallerTable()->getActiveACL($modId),
          'ListActiveHooks'         => $this->getInstallerTable()->getActiveHooks($modId),
          'helperObject'            => $this->helperObject,
          'configuration'           => $configuration,
          'hangers'                 => $this->getInstallerTable()->getHangers(),
          'Hooks'                   => $hooksArr,
          'hookObject'              => $this->getInstallerTable(),
          'settings'                => $configForm,
          'listenerObject'          => $this->listenerObject,
          'setup'                   => $setup,
        ));
    }

    public function saveConfigAction()
    {
        $request    = $this->getRequest();
        $moduleId   = $request->getPost()->module_id;

        foreach ($request->getPost() as $key => $value) {
            $fieldName  = $key;
            $fieldValue = $value;
            if ($fieldName != 'module_id') {
                $result = $this->getInstallerTable()->saveSettings($fieldName, $fieldValue, $moduleId);
            }
        }

        $data         = array();
        $returnArr    = array('modeId' => $moduleId);
        $return   = new JsonModel($returnArr);
        return $return;
    }

    public function DeleteAclAction()
    {
        $request = $this->getRequest();
        $this->getInstallerTable()->DeleteAcl($request->getPost());
        $return[0]  = array('return' => 1,'msg' => $this->listenerObject->z_xlt("Deleted Successfully"));
        $arr        = new JsonModel($return);
        return $arr;
    }

    public function DeleteHooksAction()
    {
        $request = $this->getRequest();
        $this->getInstallerTable()->DeleteHooks($request->getPost());
        $return[0]  = array('return' => 1,'msg' => $this->listenerObject->z_xlt("Deleted Successfully"));
        $arr        = new JsonModel($return);
        return $arr;
    }

    public function nickNameAction()
    {
        $request    = $this->getRequest();
        $nickname   = $request->getPost()->nickname;
        echo $this->getInstallerTable()->validateNickName(trim($nickname));
        exit(0);
    }
}
