<?php
/**
 * ArAtivity Entity
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Entities;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Table(name="ar_activity")
 * @ORM\Entity(repositoryClass="OpenEMR\Repositories\ArActivityRepository")
 */
class ArActivity
{
    /**
     * @ORM\Column(name="pid", type="integer")
     */
    private $pid;

    /**
     * @ORM\ManyToMany(targetEntity="Billing", inversedBy="ar_activity")
     * @ORM\JoinTable(name="billing")
     * @ORM\Column(name="encounter", type="integer")
     */
    private $encounter;

    /**
     * @ORM\Column(name="sequence_no",type="integer")
     */
    private $sequence_no;

    /**
     * @ORM\Column(name="code_type",type="string")
     */
    private $code_type;

    /**
     * @ORM\Column(name="code",type="string")
     */
    private $code;

    /**
     * @ORM\Column(name="modifier",type="string")
     */
    private $modifier;

    /**
     * @ORM\Column(name="payer_type", type="integer")
     */
    private $payer_type;

    /**
     * @ORM\Column(name="post_time", type="datetime")
     */
    private $post_time;

    /**
     * @ORM\Column(name="post_user", type="integer")
     */
    private $post_user;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\ManyToOne(targetEntity="OpenEMR\Entities\ArSession")
     * @ORM\JoinColumn(nullable=false, name="session_id", referencedColumnName="session_id")
     */
    private $session_id;

    /**
     * @ORM\Column(name="memo", type="string")
     */
    private $memo;

    /**
     * @ORM\Column(name="pay_amount", type="decimal")
     */
    private $pay_amount;

    /**
     * @ORM\Column(name="adj_amount", type="decimal")
     */
    private $adj_amount;

    /**
     * @ORM\Column(name="modified_time", type="datetime")
     */
    private $modified_time;

    /**
     * @ORM\Column(name="follow_up", type="text")
     */
    private $follow_up;

    /**
     * @ORM\Column(name="follow_up_note", type="string")
     */
    private $follow_up_note;

    /**
     * @ORM\Column(name="account_code", type="string")
     */
    private $account_code;

    /**
     * @ORM\Column(name="reason_code", type="string")
     */
    private $reason_code;

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return mixed
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * @param mixed $encounter
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
    }

    /**
     * @return mixed
     */
    public function getSequenceNo()
    {
        return $this->sequence_no;
    }

    /**
     * @param mixed $sequence_no
     */
    public function setSequenceNo($sequence_no)
    {
        $this->sequence_no = $sequence_no;
    }

    /**
     * @return mixed
     */
    public function getCodeType()
    {
        return $this->code_type;
    }

    /**
     * @param mixed $code_type
     */
    public function setCodeType($code_type)
    {
        $this->code_type = $code_type;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * @param mixed $modifier
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * @return mixed
     */
    public function getPayerType()
    {
        return $this->payer_type;
    }

    /**
     * @param mixed $payer_type
     */
    public function setPayerType($payer_type)
    {
        $this->payer_type = $payer_type;
    }

    /**
     * @return mixed
     */
    public function getPostTime()
    {
        return $this->post_time;
    }

    /**
     * @param mixed $post_time
     */
    public function setPostTime($post_time)
    {
        $this->post_time = $post_time;
    }

    /**
     * @return mixed
     */
    public function getPostUser()
    {
        return $this->post_user;
    }

    /**
     * @param mixed $post_user
     */
    public function setPostUser($post_user)
    {
        $this->post_user = $post_user;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * @param mixed $session_id
     */
    public function setSessionId($session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * @return mixed
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param mixed $memo
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;
    }

    /**
     * @return mixed
     */
    public function getPayAmount()
    {
        return $this->pay_amount;
    }

    /**
     * @param mixed $pay_amount
     */
    public function setPayAmount($pay_amount)
    {
        $this->pay_amount = $pay_amount;
    }

    /**
     * @return mixed
     */
    public function getAdjAmount()
    {
        return $this->adj_amount;
    }

    /**
     * @param mixed $adj_amount
     */
    public function setAdjAmount($adj_amount)
    {
        $this->adj_amount = $adj_amount;
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
    public function getFollowUp()
    {
        return $this->follow_up;
    }

    /**
     * @param mixed $follow_up
     */
    public function setFollowUp($follow_up)
    {
        $this->follow_up = $follow_up;
    }

    /**
     * @return mixed
     */
    public function getFollowUpNote()
    {
        return $this->follow_up_note;
    }

    /**
     * @param mixed $follow_up_note
     */
    public function setFollowUpNote($follow_up_note)
    {
        $this->follow_up_note = $follow_up_note;
    }

    /**
     * @return mixed
     */
    public function getAccountCode()
    {
        return $this->account_code;
    }

    /**
     * @param mixed $account_code
     */
    public function setAccountCode($account_code)
    {
        $this->account_code = $account_code;
    }

    /**
     * @return mixed
     */
    public function getReasonCode()
    {
        return $this->reason_code;
    }

    /**
     * @param mixed $reason_code
     */
    public function setReasonCode($reason_code)
    {
        $this->reason_code = $reason_code;
    }




}
