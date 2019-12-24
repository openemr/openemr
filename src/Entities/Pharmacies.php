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
 * @ORM\Entity
 * @ORM\Table(name="pharmacies")
 * @ORM\Entity(repositoryClass="OpenEMR\Repositories\PharmaciesRepository")
 */
class Pharmacies
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $transmit_method;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="integer")
     */
    private $ncpdp;

    /**
     * @ORM\Column(type="integer")
     */
    private $npi;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTransmitMethod()
    {
        return $this->transmit_method;
    }

    /**
     * @param mixed $transmit_method
     */
    public function setTransmitMethod($transmit_method): void
    {
        $this->transmit_method = $transmit_method;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getNcpdp()
    {
        return $this->ncpdp;
    }

    /**
     * @param mixed $ncpdp
     */
    public function setNcpdp($ncpdp): void
    {
        $this->ncpdp = $ncpdp;
    }

    /**
     * @return mixed
     */
    public function getNpi()
    {
        return $this->npi;
    }

    /**
     * @param mixed $npi
     */
    public function setNpi($npi): void
    {
        $this->npi = $npi;
    }


}
