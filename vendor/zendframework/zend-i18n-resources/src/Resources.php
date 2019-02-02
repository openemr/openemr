<?php
/**
 * @see       http://github.com/zendframework/zend-i18n-resources for the canonical source repository
 * @copyright Copyright (c) 2015-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-i18n-resources/blob/master/LICENSE.md New BSD License
 */

namespace Zend\I18n\Translator;

/**
 * Provide paths and patterns for locating zend-i18n translation files.
 */
final class Resources
{
    /**
     * Non-instantiable.
     */
    private function __construct()
    {
    }

    /**
     * Return the base path to the language resources.
     *
     * @return string
     */
    public static function getBasePath()
    {
        return __DIR__ . '/../languages/';
    }

    /**
     * Retrieve the translation file pattern for zend-captcha translations.
     *
     * @return string
     */
    public static function getPatternForCaptcha()
    {
        return '%s/Zend_Captcha.php';
    }

    /**
     * Retrieve the translation file pattern for zend-validator translations.
     *
     * @return string
     */
    public static function getPatternForValidator()
    {
        return '%s/Zend_Validate.php';
    }
}
