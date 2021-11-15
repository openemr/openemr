<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Writer;

use Laminas\Config\Exception;

use function array_merge;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function strpos;

class Ini extends AbstractWriter
{
    /**
     * Separator for nesting levels of configuration data identifiers.
     *
     * @var string
     */
    protected $nestSeparator = '.';

    /**
     * If true the INI string is rendered in the global namespace without
     * sections.
     *
     * @var bool
     */
    protected $renderWithoutSections = false;

    /**
     * Set nest separator.
     *
     * @param  string $separator
     * @return self
     */
    public function setNestSeparator($separator)
    {
        $this->nestSeparator = $separator;
        return $this;
    }

    /**
     * Get nest separator.
     *
     * @return string
     */
    public function getNestSeparator()
    {
        return $this->nestSeparator;
    }

    /**
     * Set if rendering should occur without sections or not.
     *
     * If set to true, the INI file is rendered without sections completely
     * into the global namespace of the INI file.
     *
     * @param bool $withoutSections
     * @return self
     */
    public function setRenderWithoutSectionsFlags($withoutSections)
    {
        $this->renderWithoutSections = (bool) $withoutSections;
        return $this;
    }

    /**
     * Return whether the writer should render without sections.
     *
     * @return bool
     */
    public function shouldRenderWithoutSections()
    {
        return $this->renderWithoutSections;
    }

    /**
     * processConfig(): defined by AbstractWriter.
     *
     * @param  array $config
     * @return string
     */
    public function processConfig(array $config)
    {
        $iniString = '';

        if ($this->shouldRenderWithoutSections()) {
            $iniString .= $this->addBranch($config);
        } else {
            $config = $this->sortRootElements($config);

            foreach ($config as $sectionName => $data) {
                if (! is_array($data)) {
                    $iniString .= $sectionName
                               .  ' = '
                               .  $this->prepareValue($data)
                               .  "\n";
                } else {
                    $iniString .= '[' . $sectionName . ']' . "\n"
                               .  $this->addBranch($data)
                               .  "\n";
                }
            }
        }

        return $iniString;
    }

    /**
     * Add a branch to an INI string recursively.
     *
     * @param  array $config
     * @param  array $parents
     * @return string
     */
    protected function addBranch(array $config, $parents = [])
    {
        $iniString = '';

        foreach ($config as $key => $value) {
            $group = array_merge($parents, [$key]);

            if (is_array($value)) {
                $iniString .= $this->addBranch($value, $group);
            } else {
                $iniString .= implode($this->nestSeparator, $group)
                           .  ' = '
                           .  $this->prepareValue($value)
                           .  "\n";
            }
        }

        return $iniString;
    }

    /**
     * Prepare a value for INI.
     *
     * @param  mixed $value
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function prepareValue($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return ($value ? 'true' : 'false');
        }

        if (false === strpos($value, '"')) {
            return '"' . $value .  '"';
        }

        throw new Exception\RuntimeException('Value can not contain double quotes');
    }

    /**
     * Root elements that are not assigned to any section needs to be on the
     * top of config.
     *
     * @param  array $config
     * @return array
     */
    protected function sortRootElements(array $config)
    {
        $sections = [];

        // Remove sections from config array.
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $sections[$key] = $value;
                unset($config[$key]);
            }
        }

        // Read sections to the end.
        foreach ($sections as $key => $value) {
            $config[$key] = $value;
        }

        return $config;
    }
}
