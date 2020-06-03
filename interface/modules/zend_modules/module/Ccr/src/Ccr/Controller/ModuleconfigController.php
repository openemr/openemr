<?php

/**
 * ModuleconfigController.php
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Ccr\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Filter\Compress\Zip;

/**
 * Handles the OpenEMR Module configuration for the Ccr module.
 */
class ModuleconfigController extends AbstractActionController
{
    public function __construct()
    {
    }

    public function getDependedModulesConfig()
    {
        // these modules need to be activated before this module can be installed
        $dependedModules = array(
            'Documents'       // Handles the saving and retrieving of embedded documents in this module.
        );
        return $dependedModules;
    }
}
