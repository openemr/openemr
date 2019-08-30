<?php
/**
 * This file is part of OpenEMR.
 *
 * @package     OpenEMR
 * @subpackage
 * @link        https://www.open-emr.org
 * @author      Robert Down <robertdown@live.com>
 * @copyright   Copyright (c) 2019 Robert Down <robertdown@live.com
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Setting
 *
 * @package OpenEMR\Admin\Entity
 * @ORM\Entity(repositoryClass="OpenEMR\Admin\Repository\SettingRepository")
 * @ORM\Table(name="globals")
 */
class Setting
{

    /**
     * The name of the setting.
     *
     * @ORM\Column(type="string", name="gl_name")
     * @ORM\Id()
     * @var String
     */
    private $name;

    /**
     * The value of the setting.
     *
     * @ORM\Column(type="string", name="gl_value")
     * @ORM\Id()
     * @var String
     */
    private $value;

    /**
     * Index of the setting.
     *
     * @ORM\Column(type="integer", name="gl_index")
     * @var int
     */
    private $index;

    /**
     * @return String
     */
    public function getName(): String
    {
        return $this->name;
    }

    /**
     * @param String $name
     */
    public function setName(String $name): void
    {
        $this->name = $name;
    }

    /**
     * @return String
     */
    public function getValue(): String
    {
        return $this->value;
    }

    /**
     * @param String $value
     */
    public function setValue(String $value): void
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @param int $index
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }
}
