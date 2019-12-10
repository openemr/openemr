<?php
/**
 * ArSession Entity
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Table(name="ar_session")
 * @ORM\Entity(repositoryClass="OpenEMR\Repositories\ArSessionRepository")
 */
class ArSession
{
     /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\OneToMany(targetEntity="OpenEMR\Entities\ArActivity", mappedBy="ArSession")
      * @ORM\OrderBy({"post_to_date"="DESC"})
     */
    private $session_id;

    public function __construct()
    {
        $this->session_id = new ArrayCollection();
    }

    /**
     * @ORM\Column(name="payer_id", type="integer")
     */
    private $payer_id;

    /**
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id ;

    /**
     * @ORM\Column(name="closed", type="integer")
     */
    private $closed;

    /**
     * @ORM\Column(name="reference", type="integer")
     */
    private $reference;

    /**
     * @ORM\Column(name="check_date", type="date")
     */
    private $check_date;

    /**
     * @ORM\Column(name="deposit_date", type="date")
     */
    private $deposit_date;

    /**
     * @ORM\Column(name="pay_total", type="decimal")
     */
    private $pay_total ;

    /**
     * @ORM\Column(name="created_time", type="datetime")
     */
    private $created_time;

    /**
     * @ORM\Column(name="modified_time", type="datetime")
     */
    private $modified_time;

    /**
     * @ORM\Column(name="global_amount", type="decimal")
     */
    private $global_amount;

    /**
     * @ORM\Column(name="payment_type", type="string")
     */
    private $payment_type;

    /**
     * @ORM\Column(name="description", type="string")
     */
    private $description;

    /**
     * @ORM\Column(name="adjustment_code", type="integer")
     */
    private $adjustment_code;

    /**
     * @ORM\Column(name="post_to_date", type="date")
     */
    private $post_to_date;

    /**
     * @ORM\Column(name="patient_id", type="integer")
     */
    private $patient_id;

    /**
     * @ORM\Column(name="payment_method", type="string")
     */
    private $payment_method;

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * @return mixed
     */
    public function getPayerId()
    {
        return $this->payer_id;
    }

    /**
     * @param mixed $payer_id
     */
    public function setPayerId($payer_id)
    {
        $this->payer_id = $payer_id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * @param mixed $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return mixed
     */
    public function getCheckDate()
    {
        return $this->check_date;
    }

    /**
     * @param mixed $check_date
     */
    public function setCheckDate($check_date)
    {
        $this->check_date = $check_date;
    }

    /**
     * @return mixed
     */
    public function getDepositDate()
    {
        return $this->deposit_date;
    }

    /**
     * @param mixed $deposit_date
     */
    public function setDepositDate($deposit_date)
    {
        $this->deposit_date = $deposit_date;
    }

    /**
     * @return mixed
     */
    public function getPayTotal()
    {
        return $this->pay_total;
    }

    /**
     * @param mixed $pay_total
     */
    public function setPayTotal($pay_total)
    {
        $this->pay_total = $pay_total;
    }

    /**
     * @return mixed
     */
    public function getCreatedTime()
    {
        return $this->created_time;
    }

    /**
     * @param mixed $created_time
     */
    public function setCreatedTime($created_time)
    {
        $this->created_time = $created_time;
    }

    /**
     * @return mixed
     */
    public function getModifiedTime()
    {
        return $this->modified_time;
    }

    /**
     * @param mixed $modified_time
     */
    public function setModifiedTime($modified_time)
    {
        $this->modified_time = $modified_time;
    }

    /**
     * @return mixed
     */
    public function getGlobalAmount()
    {
        return $this->global_amount;
    }

    /**
     * @param mixed $global_amount
     */
    public function setGlobalAmount($global_amount)
    {
        $this->global_amount = $global_amount;
    }

    /**
     * @return mixed
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * @param mixed $payment_type
     */
    public function setPaymentType($payment_type)
    {
        $this->payment_type = $payment_type;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getAdjustmentCode()
    {
        return $this->adjustment_code;
    }

    /**
     * @param mixed $adjustment_code
     */
    public function setAdjustmentCode($adjustment_code)
    {
        $this->adjustment_code = $adjustment_code;
    }

    /**
     * @return mixed
     */
    public function getPostToDate()
    {
        return $this->post_to_date;
    }

    /**
     * @param mixed $post_to_date
     */
    public function setPostToDate($post_to_date)
    {
        $this->post_to_date = $post_to_date;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    /**
     * @param mixed $patient_id
     */
    public function setPatientId($patient_id)
    {
        $this->patient_id = $patient_id;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @param mixed $payment_method
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
    }

    public function findBy()
    {

    }


}
