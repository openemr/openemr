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
 * @ORM\Table(name="billing")
 * @ORM\Entity(repositoryClass="OpenEMR\Repositories\BillingRepository")
 */
class Billing
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $code_type;

    /**
     * @ORM\Column(type="string")
     */
    private $code;

    /**
     * @ORM\Column(type="integer")
     */
    private $pidIndex;

    /**
     * @ORM\Column(type="integer")
     */
    private $provider_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     */
    private $groupname;

    /**
     * @ORM\ManyToMany(targetEntity="ArActivity", inversedBy="billing")
     * @ORM\JoinTable(name="ar_activity")
     * @ORM\Column(type="smallint")
     */
    private $authorized;

    /**
     * @ORM\Column(type="string")
     */
    private $encounter;

    /**
     * @ORM\Column(type="string")
     */
    private $code_text;

    /**
     * @ORM\Column(type="smallint")
     */
    private $billed;

    /**
     * @ORM\Column(type="smallint")
     */
    private $activity;

    /**
     * @ORM\Column(type="integer")
     */
    private $payer_id;

    /**
     * @ORM\Column(type="string")
     */
    private $bill_process;

    /**
     * @ORM\Column(type="string")
     */
    private $bill_date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $process_date;

    /**
     * @ORM\Column(type="string")
     */
    private $process_file;

    /**
     * @ORM\Column(type="string")
     */
    private $modifier;

    /**
     * @ORM\Column(type="integer")
     */
    private $units;

    /**
     * @ORM\Column(type="decimal")
     */
    private $fee;

    /**
     * @ORM\Column(type="string")
     */
    private $justify;

    /**
     * @ORM\Column(type="string")
     */
    private $target;

    /**
     * @ORM\Column(type="integer")
     */
    private $x12_partner_id;

    /**
     * @ORM\Column(type="string")
     */
    private $ndc_info;

    /**
     * @ORM\Column(type="string")
     */
    private $notecodes;

    /**
     * @ORM\Column(type="integer")
     */
    private $external_id;

    /**
     * @ORM\Column(type="string")
     */
    private $pricelevel;

    /**
     * @ORM\Column(type="string")
     */
    private $revenue_code;

    /**
     * @ORM\Column(type="integer")
     */
    private $noncovered;

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
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
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
    public function setCodeType($code_type): void
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
    public function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getPidIndex()
    {
        return $this->pidIndex;
    }

    /**
     * @param mixed $pidIndex
     */
    public function setPidIndex($pidIndex): void
    {
        $this->pidIndex = $pidIndex;
    }

    /**
     * @return mixed
     */
    public function getProviderId()
    {
        return $this->provider_id;
    }

    /**
     * @param mixed $provider_id
     */
    public function setProviderId($provider_id): void
    {
        $this->provider_id = $provider_id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getGroupname()
    {
        return $this->groupname;
    }

    /**
     * @param mixed $groupname
     */
    public function setGroupname($groupname): void
    {
        $this->groupname = $groupname;
    }

    /**
     * @return mixed
     */
    public function getAuthorized()
    {
        return $this->authorized;
    }

    /**
     * @param mixed $authorized
     */
    public function setAuthorized($authorized): void
    {
        $this->authorized = $authorized;
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
    public function setEncounter($encounter): void
    {
        $this->encounter = $encounter;
    }

    /**
     * @return mixed
     */
    public function getCodeText()
    {
        return $this->code_text;
    }

    /**
     * @param mixed $code_text
     */
    public function setCodeText($code_text): void
    {
        $this->code_text = $code_text;
    }

    /**
     * @return mixed
     */
    public function getBilled()
    {
        return $this->billed;
    }

    /**
     * @param mixed $billed
     */
    public function setBilled($billed): void
    {
        $this->billed = $billed;
    }

    /**
     * @return mixed
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param mixed $activity
     */
    public function setActivity($activity): void
    {
        $this->activity = $activity;
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
    public function setPayerId($payer_id): void
    {
        $this->payer_id = $payer_id;
    }

    /**
     * @return mixed
     */
    public function getBillProcess()
    {
        return $this->bill_process;
    }

    /**
     * @param mixed $bill_process
     */
    public function setBillProcess($bill_process): void
    {
        $this->bill_process = $bill_process;
    }

    /**
     * @return mixed
     */
    public function getBillDate()
    {
        return $this->bill_date;
    }

    /**
     * @param mixed $bill_date
     */
    public function setBillDate($bill_date): void
    {
        $this->bill_date = $bill_date;
    }

    /**
     * @return mixed
     */
    public function getProcessDate()
    {
        return $this->process_date;
    }

    /**
     * @param mixed $process_date
     */
    public function setProcessDate($process_date): void
    {
        $this->process_date = $process_date;
    }

    /**
     * @return mixed
     */
    public function getProcessFile()
    {
        return $this->process_file;
    }

    /**
     * @param mixed $process_file
     */
    public function setProcessFile($process_file): void
    {
        $this->process_file = $process_file;
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
    public function setModifier($modifier): void
    {
        $this->modifier = $modifier;
    }

    /**
     * @return mixed
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * @param mixed $units
     */
    public function setUnits($units): void
    {
        $this->units = $units;
    }

    /**
     * @return mixed
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * @param mixed $fee
     */
    public function setFee($fee): void
    {
        $this->fee = $fee;
    }

    /**
     * @return mixed
     */
    public function getJustify()
    {
        return $this->justify;
    }

    /**
     * @param mixed $justify
     */
    public function setJustify($justify): void
    {
        $this->justify = $justify;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target): void
    {
        $this->target = $target;
    }

    /**
     * @return mixed
     */
    public function getX12PartnerId()
    {
        return $this->x12_partner_id;
    }

    /**
     * @param mixed $x12_partner_id
     */
    public function setX12PartnerId($x12_partner_id): void
    {
        $this->x12_partner_id = $x12_partner_id;
    }

    /**
     * @return mixed
     */
    public function getNdcInfo()
    {
        return $this->ndc_info;
    }

    /**
     * @param mixed $ndc_info
     */
    public function setNdcInfo($ndc_info): void
    {
        $this->ndc_info = $ndc_info;
    }

    /**
     * @return mixed
     */
    public function getNotecodes()
    {
        return $this->notecodes;
    }

    /**
     * @param mixed $notecodes
     */
    public function setNotecodes($notecodes): void
    {
        $this->notecodes = $notecodes;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @param mixed $external_id
     */
    public function setExternalId($external_id): void
    {
        $this->external_id = $external_id;
    }

    /**
     * @return mixed
     */
    public function getPricelevel()
    {
        return $this->pricelevel;
    }

    /**
     * @param mixed $pricelevel
     */
    public function setPricelevel($pricelevel): void
    {
        $this->pricelevel = $pricelevel;
    }

    /**
     * @return mixed
     */
    public function getRevenueCode()
    {
        return $this->revenue_code;
    }

    /**
     * @param mixed $revenue_code
     */
    public function setRevenueCode($revenue_code): void
    {
        $this->revenue_code = $revenue_code;
    }

    /**
     * @return mixed
     */
    public function getNoncovered()
    {
        return $this->noncovered;
    }

    /**
     * @param mixed $noncovered
     */
    public function setNoncovered($noncovered): void
    {
        $this->noncovered = $noncovered;
    }


}
