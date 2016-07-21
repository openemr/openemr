<?php
/**
 * INI file modification task for Phing, the PHP build tool.
 *
 * Based on http://ant-contrib.sourceforge.net/tasks/tasks/inifile.html
 *
 * PHP version 5
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link     http://www.phing.info/
 */

/**
 * Class for collecting details for removing keys or sections from an ini file
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link     http://www.phing.info/
 */
class IniFileRemove
{
    /**
     * Property
     *
     * @var string
     */
    protected $property = null;

    /**
     * Section
     *
     * @var string
     */
    protected $section = null;

    /**
     * Set Section name
     *
     * @param string $section Name of section in ini file
     *
     * @return void
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * Set Property/Key name
     *
     * @param string $property ini key name
     *
     * @return void
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * Get Property
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Get Section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }
}
