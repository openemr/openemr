<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Listener/Listener.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * This class is supposed to listen for events in the module like the aclcheckEvent and trigger actions
 * based on those events.  However, it doesn't appear to be used for that at all.  Instead it acts as
 * an adapter for the OpenEMR language conversion within the module system.
 * TODO: We should look at deleting this class or renaming it to be a TranslatorAdapter since that appears
 * to be its functionality here...
 */
class Listener extends AbstractActionController implements ListenerAggregateInterface
{
  /**
   * @var \Laminas\Stdlib\CallbackHandler[]
   */
    protected $listeners = array();
    protected $applicationTable;
  /**
   * {@inheritDoc}
   */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // TODO: This aclcheckEvent doesn't appear to be in the system or used... especially since the callable onAclcheckEvent doesn't exist
        // in this class.  We should look at removing this.
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $events->attach('aclcheckEvent', array($this, 'onAclcheckEvent'));
    }


    public function detach(EventManagerInterface $events, $priority = 1)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

  /**
   * Language converter
   * @param string $str
   * @return string
   */
    public static function z_xl($str)
    {
        return xl($str);
    }

  /**
   * Language converter
   * @param string $str
   * @return string
   */
    public static function z_xlt($str)
    {
        return xlt($str);
    }

  /**
   * Language converter
   * @param string $str
   * @return string
   */
    public static function z_xla($str)
    {
        return xla($str);
    }

    /**
   * Language converter
   * @param string $str
   * @return string
   */
    public static function z_xls($str)
    {
        return xls($str);
    }
}
