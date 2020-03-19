<?php
/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

namespace OpenEMR\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="addresses")
 * @ORM\Entity(repositoryClass="OpenEMR\Repositories\AddressesRepository")
 */

class Addresses
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $line1;

    /**
     * @ORM\Column(type="string")
     */
    private $line2;

    /**
     * @ORM\Column(type="string")
     */
    private $city;

    /**
     * @ORM\Column(type="string")
     */
    private $state;

    /**
     * @ORM\Column(type="string")
     */
    private $zip;

    /**
     * @ORM\Column(type="string")
     */
    private $plus_four;

    /**
     * @ORM\Column(type="string")
     */
    private $country;

    /**
     * @ORM\Column(type="int")
     */
    private $foreign_id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLine1()
    {
        return $this->line1;
    }

    /**
     * @param mixed $line1
     */
    public function setLine1($line1): void
    {
        $this->line1 = $line1;
    }

    /**
     * @return mixed
     */
    public function getLine2()
    {
        return $this->line2;
    }

    /**
     * @param mixed $line2
     */
    public function setLine2($line2): void
    {
        $this->line2 = $line2;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip): void
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getPlusFour()
    {
        return $this->plus_four;
    }

    /**
     * @param mixed $plus_four
     */
    public function setPlusFour($plus_four): void
    {
        $this->plus_four = $plus_four;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getForeignId()
    {
        return $this->foreign_id;
    }

    /**
     * @param mixed $foreign_id
     */
    public function setForeignId($foreign_id): void
    {
        $this->foreign_id = $foreign_id;
    }




}
