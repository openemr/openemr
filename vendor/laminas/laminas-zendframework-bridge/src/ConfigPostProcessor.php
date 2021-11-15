<?php

/**
 * @see       https://github.com/laminas/laminas-zendframework-bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-zendframework-bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-zendframework-bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ZendFrameworkBridge;

use function array_intersect_key;
use function array_key_exists;
use function array_pop;
use function array_push;
use function count;
use function in_array;
use function is_array;
use function is_callable;
use function is_int;
use function is_string;

class ConfigPostProcessor
{
    /** @internal */
    const SERVICE_MANAGER_KEYS_OF_INTEREST = [
        'aliases'    => true,
        'factories'  => true,
        'invokables' => true,
        'services'   => true,
    ];

    /** @var array String keys => string values */
    private $exactReplacements = [
        'zend-expressive' => 'mezzio',
        'zf-apigility'    => 'api-tools',
    ];

    /** @var Replacements */
    private $replacements;

    /** @var callable[] */
    private $rulesets;

    public function __construct()
    {
        $this->replacements = new Replacements();

        /* Define the rulesets for replacements.
         *
         * Each ruleset has the following signature:
         *
         * @param mixed $value
         * @param string[] $keys Full nested key hierarchy leading to the value
         * @return null|callable
         *
         * If no match is made, a null is returned, allowing it to fallback to
         * the next ruleset in the list. If a match is made, a callback is returned,
         * and that will be used to perform the replacement on the value.
         *
         * The callback should have the following signature:
         *
         * @param mixed $value
         * @param string[] $keys
         * @return mixed The transformed value
         */
        $this->rulesets = [
            // Exact values
            function ($value) {
                return is_string($value) && isset($this->exactReplacements[$value])
                    ? [$this, 'replaceExactValue']
                    : null;
            },

            // Router (MVC applications)
            // We do not want to rewrite these.
            function ($value, array $keys) {
                $key = array_pop($keys);
                // Only worried about a top-level "router" key.
                return $key === 'router' && count($keys) === 0 && is_array($value)
                    ? [$this, 'noopReplacement']
                    : null;
            },

            // service- and pluginmanager handling
            function ($value) {
                return is_array($value) && array_intersect_key(self::SERVICE_MANAGER_KEYS_OF_INTEREST, $value) !== []
                    ? [$this, 'replaceDependencyConfiguration']
                    : null;
            },

            // Array values
            function ($value, array $keys) {
                return 0 !== count($keys) && is_array($value)
                    ? [$this, '__invoke']
                    : null;
            },
        ];
    }

    /**
     * @param string[] $keys Hierarchy of keys, for determining location in
     *     nested configuration.
     * @return array
     */
    public function __invoke(array $config, array $keys = [])
    {
        $rewritten = [];

        foreach ($config as $key => $value) {
            // Determine new key from replacements
            $newKey = is_string($key) ? $this->replace($key, $keys) : $key;

            // Keep original values with original key, if the key has changed, but only at the top-level.
            if (empty($keys) && $newKey !== $key) {
                $rewritten[$key] = $value;
            }

            // Perform value replacements, if any
            $newValue = $this->replace($value, $keys, $newKey);

            // Key does not already exist and/or is not an array value
            if (! array_key_exists($newKey, $rewritten) || ! is_array($rewritten[$newKey])) {
                // Do not overwrite existing values with null values
                $rewritten[$newKey] = array_key_exists($newKey, $rewritten) && null === $newValue
                    ? $rewritten[$newKey]
                    : $newValue;
                continue;
            }

            // New value is null; nothing to do.
            if (null === $newValue) {
                continue;
            }

            // Key already exists as an array value, but $value is not an array
            if (! is_array($newValue)) {
                $rewritten[$newKey][] = $newValue;
                continue;
            }

            // Key already exists as an array value, and $value is also an array
            $rewritten[$newKey] = static::merge($rewritten[$newKey], $newValue);
        }

        return $rewritten;
    }

    /**
     * Perform substitutions as needed on an individual value.
     *
     * The $key is provided to allow fine-grained selection of rewrite rules.
     *
     * @param mixed $value
     * @param string[] $keys Key hierarchy
     * @param null|int|string $key
     * @return mixed
     */
    private function replace($value, array $keys, $key = null)
    {
        // Add new key to the list of keys.
        // We do not need to remove it later, as we are working on a copy of the array.
        array_push($keys, $key);

        // Identify rewrite strategy and perform replacements
        $rewriteRule = $this->replacementRuleMatch($value, $keys);
        return $rewriteRule($value, $keys);
    }

    /**
     * Merge two arrays together.
     *
     * If an integer key exists in both arrays, the value from the second array
     * will be appended to the first array. If both values are arrays, they are
     * merged together, else the value of the second array overwrites the one
     * of the first array.
     *
     * Based on zend-stdlib Zend\Stdlib\ArrayUtils::merge
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @return array
     */
    public static function merge(array $a, array $b)
    {
        foreach ($b as $key => $value) {
            if (! isset($a[$key]) && ! array_key_exists($key, $a)) {
                $a[$key] = $value;
                continue;
            }

            if (null === $value && array_key_exists($key, $a)) {
                // Leave as-is if value from $b is null
                continue;
            }

            if (is_int($key)) {
                $a[] = $value;
                continue;
            }

            if (is_array($value) && is_array($a[$key])) {
                $a[$key] = static::merge($a[$key], $value);
                continue;
            }

            $a[$key] = $value;
        }

        return $a;
    }

    /**
     * @param mixed $value
     * @param null|int|string $key
     * @return callable Callable to invoke with value
     */
    private function replacementRuleMatch($value, $key = null)
    {
        foreach ($this->rulesets as $ruleset) {
            $result = $ruleset($value, $key);
            if (is_callable($result)) {
                return $result;
            }
        }
        return [$this, 'fallbackReplacement'];
    }

    /**
     * Replace a value using the translation table, if the value is a string.
     *
     * @param mixed $value
     * @return mixed
     */
    private function fallbackReplacement($value)
    {
        return is_string($value)
            ? $this->replacements->replace($value)
            : $value;
    }

    /**
     * Replace a value matched exactly.
     *
     * @param mixed $value
     * @return mixed
     */
    private function replaceExactValue($value)
    {
        return $this->exactReplacements[$value];
    }

    private function replaceDependencyConfiguration(array $config)
    {
        $aliases = isset($config['aliases']) && is_array($config['aliases'])
            ? $this->replaceDependencyAliases($config['aliases'])
            : [];

        if ($aliases) {
            $config['aliases'] = $aliases;
        }

        $config = $this->replaceDependencyInvokables($config);
        $config = $this->replaceDependencyFactories($config);
        $config = $this->replaceDependencyServices($config);

        $keys = self::SERVICE_MANAGER_KEYS_OF_INTEREST;
        foreach ($config as $key => $data) {
            if (isset($keys[$key])) {
                continue;
            }

            $config[$key] = is_array($data) ? $this->__invoke($data, [$key]) :  $data;
        }

        return $config;
    }

    /**
     * Rewrite dependency aliases array
     *
     * In this case, we want to keep the alias as-is, but rewrite the target.
     *
     * We need also provide an additional alias if the alias key is a legacy class.
     *
     * @return array
     */
    private function replaceDependencyAliases(array $aliases)
    {
        foreach ($aliases as $alias => $target) {
            if (! is_string($alias) || ! is_string($target)) {
                continue;
            }

            $newTarget = $this->replacements->replace($target);
            $newAlias  = $this->replacements->replace($alias);

            $notIn = [$newTarget];
            $name  = $newTarget;
            while (isset($aliases[$name])) {
                $notIn[] = $aliases[$name];
                $name    = $aliases[$name];
            }

            if ($newAlias === $alias && ! in_array($alias, $notIn, true)) {
                $aliases[$alias] = $newTarget;
                continue;
            }

            if (isset($aliases[$newAlias])) {
                continue;
            }

            if (! in_array($newAlias, $notIn, true)) {
                $aliases[$alias]    = $newAlias;
                $aliases[$newAlias] = $newTarget;
            }
        }

        return $aliases;
    }

    /**
     * Rewrite dependency invokables array
     *
     * In this case, we want to keep the alias as-is, but rewrite the target.
     *
     * We need also provide an additional alias if invokable is defined with
     * an alias which is a legacy class.
     *
     * @return array
     */
    private function replaceDependencyInvokables(array $config)
    {
        if (empty($config['invokables']) || ! is_array($config['invokables'])) {
            return $config;
        }

        foreach ($config['invokables'] as $alias => $target) {
            if (! is_string($alias)) {
                continue;
            }

            $newTarget = $this->replacements->replace($target);
            $newAlias  = $this->replacements->replace($alias);

            if ($alias === $target || isset($config['aliases'][$newAlias])) {
                $config['invokables'][$alias] = $newTarget;
                continue;
            }

            $config['invokables'][$newAlias] = $newTarget;

            if ($newAlias === $alias) {
                continue;
            }

            $config['aliases'][$alias] = $newAlias;

            unset($config['invokables'][$alias]);
        }

        return $config;
    }

    /**
     * @param mixed $value
     * @return mixed Returns $value verbatim.
     */
    private function noopReplacement($value)
    {
        return $value;
    }

    private function replaceDependencyFactories(array $config)
    {
        if (empty($config['factories']) || ! is_array($config['factories'])) {
            return $config;
        }

        foreach ($config['factories'] as $service => $factory) {
            if (! is_string($service)) {
                continue;
            }

            $replacedService = $this->replacements->replace($service);
            $factory         = is_string($factory) ? $this->replacements->replace($factory) : $factory;
            $config['factories'][$replacedService] = $factory;

            if ($replacedService === $service) {
                continue;
            }

            unset($config['factories'][$service]);
            if (isset($config['aliases'][$service])) {
                continue;
            }

            $config['aliases'][$service] = $replacedService;
        }

        return $config;
    }

    private function replaceDependencyServices(array $config)
    {
        if (empty($config['services']) || ! is_array($config['services'])) {
            return $config;
        }

        foreach ($config['services'] as $service => $serviceInstance) {
            if (! is_string($service)) {
                continue;
            }

            $replacedService = $this->replacements->replace($service);
            $serviceInstance = is_array($serviceInstance) ? $this->__invoke($serviceInstance) : $serviceInstance;

            $config['services'][$replacedService] = $serviceInstance;

            if ($service === $replacedService) {
                continue;
            }

            unset($config['services'][$service]);

            if (isset($config['aliases'][$service])) {
                continue;
            }

            $config['aliases'][$service] = $replacedService;
        }

        return $config;
    }
}
