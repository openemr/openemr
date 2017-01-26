<?php

namespace Multipledb\Controller;

use Zend\View\Model\ViewModel;

/**
 * This is is the configuration for the openemr module installer.
 * here we adding the openemr hooks and the Acl (permission).
 * alse we put here the path to css and js file (now it's in zend public folder but in could change).
 * */
class ModuleconfigController
{

    public function getHookConfig()
    {
        //NAME KEY SPECIFIES THE NAME OF THE HOOK FOR UNIQUELY IDENTIFYING IN A MODULE.
        //TITLE KEY SPECIFIES THE TITLE OF THE HOOK TO BE DISPLAYED IN THE CALLING PAGES.
        //PATH KEY SPECIFIES THE PATH OF THE RESOURCE, SHOULD SPECIFY THE CONTROLLER
        //AND IT’S ACTION IN THE PATH, (INCLUDING INDEX ACTION)

        //EXAMPLES!!
        $hooks = array (
            // hook for patient screen low security
            array (
                'name' => "multipledb",
                'title' => "Multipledb",
                'path' => "public/multipledb",
            )
        );
        return $hooks;
    }

    public function getDependedModulesConfig()
    {
        return array();
    }

    public function getAclConfig()
    {
        //new acl rule for disallow using in the General setting screen
        //EXAMPLES!!
        $acl = array(

            );
            return $acl;

      }

}