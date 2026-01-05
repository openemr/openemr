<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Helper/TranslatorViewHelper.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Helper;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Decorates the OpenEMR functions making it so a module can avoid hard coding global functions
 */
class TranslatorViewHelper extends \Laminas\View\Helper\AbstractHelper
{
    /**
     * Translates a string.
     */
    public function xl($str)
    {
        return xl($str);
    }
}
