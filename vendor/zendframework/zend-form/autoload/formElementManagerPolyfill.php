<?php
/**
 * @link      http://github.com/zendframework/zend-form for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

use Zend\Form\FormElementManager;
use Zend\ServiceManager\ServiceManager;

call_user_func(function () {
    $target = method_exists(ServiceManager::class, 'configure')
        ? FormElementManager\FormElementManagerV3Polyfill::class
        : FormElementManager\FormElementManagerV2Polyfill::class;

    class_alias($target, FormElementManager::class);
});
