<?php

/* +-----------------------------------------------------------------------------+
* Copyright 2016 matrix israel
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see
* http://www.gnu.org/licenses/licenses.html#GPL
*    @author  Oshri Rozmarin <oshri.rozmarin@gmail.com>
* +------------------------------------------------------------------------------+
 *
 */
namespace Multipledb\Controller;

use Laminas\View\Model\ViewModel;

/**
 * This is the configuration for the OpenEMR module installer.
 * Here we add the OpenEMR hooks and the ACL (permission).
 * This also specifies the paths to CSS and JS files (currently in the Zend public folder, but this may change).
 */
class ModuleconfigController
{
    public function getHookConfig()
    {
        //NAME KEY SPECIFIES THE NAME OF THE HOOK FOR UNIQUELY IDENTIFYING IN A MODULE.
        //TITLE KEY SPECIFIES THE TITLE OF THE HOOK TO BE DISPLAYED IN THE CALLING PAGES.
        //PATH KEY SPECIFIES THE PATH OF THE RESOURCE, SHOULD SPECIFY THE CONTROLLER
        //AND IT’S ACTION IN THE PATH, (INCLUDING INDEX ACTION)

        //EXAMPLES!!
        $hooks =  [
            // hook for patient screen low security
             [
                'name' => "multipledb",
                'title' => "Multipledb",
                'path' => "public/multipledb",
             ]
        ];
        return $hooks;
    }

    public function getDependedModulesConfig()
    {
        return [];
    }

    public function getAclConfig()
    {
        //new acl rule for disallow using in the General setting screen
        //EXAMPLES!!
        $acl = [
            [
                'section_id' => "multipledb",
                'section_name' => "Multipledb",
                'parent_section' => ""
            ]
        ];
        return $acl;
    }
}
