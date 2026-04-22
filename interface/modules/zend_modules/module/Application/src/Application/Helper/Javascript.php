<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Helper/Javascript.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Basil PT <basil@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Helper;

use Laminas\View\Helper\AbstractHelper;

class Javascript extends AbstractHelper
{
    public function __invoke()
    {
        $scheme = match (true) {
            isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true), isset($_SERVER['HTTP_SCHEME']) && ($_SERVER['HTTP_SCHEME'] == 'https'), 443 === $_SERVER['SERVER_PORT'] => 'https://',
            default => 'http://',
        };

        $basePath = str_replace("/index.php", "", $_SERVER['PHP_SELF']);
        echo '<script>';
        echo 'var basePath    = "' . $scheme . $_SERVER['SERVER_NAME'] . $basePath . '";';
        echo 'var dateFormat = "yy-mm-dd"';
        echo '</script>';
    }
}
