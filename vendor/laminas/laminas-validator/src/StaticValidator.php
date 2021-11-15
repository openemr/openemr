<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

use Laminas\ServiceManager\ServiceManager;

class StaticValidator
{
    /**
     * @var ValidatorPluginManager
     */
    protected static $plugins;

    /**
     * Set plugin manager to use for locating validators
     *
     * @param  ValidatorPluginManager|null $plugins
     * @return void
     */
    public static function setPluginManager(ValidatorPluginManager $plugins = null)
    {
        // Don't share by default to allow different arguments on subsequent calls
        if ($plugins instanceof ValidatorPluginManager) {
            // Vary how the share by default flag is set based on laminas-servicemanager version
            if (method_exists($plugins, 'configure')) {
                $plugins->configure(['shared_by_default' => false]);
            } else {
                $plugins->setShareByDefault(false);
            }
        }
        static::$plugins = $plugins;
    }

    /**
     * Get plugin manager for locating validators
     *
     * @return ValidatorPluginManager
     */
    public static function getPluginManager()
    {
        if (null === static::$plugins) {
            static::setPluginManager(new ValidatorPluginManager(new ServiceManager));
        }
        return static::$plugins;
    }

    /**
     * @param  mixed    $value
     * @param  string   $classBaseName
     * @param  array    $options OPTIONAL associative array of options to pass as
     *     the sole argument to the validator constructor.
     * @return bool
     * @throws Exception\InvalidArgumentException for an invalid $options argument.
     */
    public static function execute($value, $classBaseName, array $options = [])
    {
        if ($options && array_values($options) === $options) {
            throw new Exception\InvalidArgumentException(
                'Invalid options provided via $options argument; must be an associative array'
            );
        }

        $plugins = static::getPluginManager();

        $validator = $plugins->get($classBaseName, $options);
        return $validator->isValid($value);
    }
}
