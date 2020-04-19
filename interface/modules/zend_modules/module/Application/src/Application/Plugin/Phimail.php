<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Plugin/Phimail.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Riju KP <rijukp@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Application\Model\ApplicationTable;
use Application\Listener\Listener;
use Interop\Container\ContainerInterface;

require_once($GLOBALS['srcdir'] . '/direct_message_check.inc');

class Phimail extends AbstractPlugin
{
    protected $application;
  /**
  *
  * Application Table Object
  * Listener Object
  * @param type $container ContainerInterface
  */
    public function __construct(ContainerInterface $container)
    {
        // TODO: again why grab the service... construct the tables and do nothing with them.  Can this code be removed?
        $container->get('Laminas\Db\Adapter\Adapter');
        $this->application    = new ApplicationTable();
        $this->listenerObject = new Listener();
    }

    public function phimail_connect($err)
    {
        return phimail_connect($err);
    }

    public function phimail_write($fp, $text)
    {
        phimail_write($fp, $text);
    }

    public function phimail_write_expect_OK($fp, $text)
    {
        return phimail_write_expect_OK($fp, $text);
    }

    public function phimail_close($fp)
    {
        phimail_close($fp);
    }
}
