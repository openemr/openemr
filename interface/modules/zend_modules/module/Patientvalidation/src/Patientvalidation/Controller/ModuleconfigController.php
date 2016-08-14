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
*    @author  Dror Golan <drorgo@matrix.co.il>
* +------------------------------------------------------------------------------+
 *
 */
namespace Patientvalidation\Controller;

use Zend\View\Model\ViewModel;

class ModuleconfigController
{

    public function getHookConfig()
    {
        //NAME KEY SPECIFIES THE NAME OF THE HOOK FOR UNIQUELY IDENTIFYING IN A MODULE.
        //TITLE KEY SPECIFIES THE TITLE OF THE HOOK TO BE DISPLAYED IN THE CALLING PAGES.
        //PATH KEY SPECIFIES THE PATH OF THE RESOURCE, SHOULD SPECIFY THE CONTROLLER
        //AND IT’S ACTION IN THE PATH, (INCLUDING INDEX ACTION)
        $hooks = array (
            // hook for patient screen low security
            array (
                'name' => "Patientvalidation",
                'title' => "Patient Validation",
                'path' => "public/patientvalidation",
            ),

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
        $acl = array(
            array(
                'section_id' => 'configuration',
                'section_name' => 'Configuration screens',
                'parent_section' => '',
               ),
            );
            return $acl;

      }

}