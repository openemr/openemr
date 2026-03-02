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

use Application\Listener\Listener;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

require_once($GLOBALS['srcdir'] . '/direct_message_check.inc.php');

class Phimail extends AbstractPlugin
{
    private readonly Listener $listenerObject;

    public function __construct()
    {
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
