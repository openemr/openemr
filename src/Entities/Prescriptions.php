<?php
/**
 * Prescription Entity
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="prescriptions")
 * @ORM\Entity(repositoryClass="OpenEMR\Repositories\PrescriptionsRepository")
 */
class Prescriptions
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;
    public function __construct()
    {
        $this->id = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="integer")
     */
    private $patient_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $filled_by_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $pharmacy_id;

    /**
     * @ORM\Column(type="string")
     */
    private $date_added;

    /**
     * @ORM\Column(type="string")
     */
    private $date_modified;

    /**
     * @ORM\Column(type="integer")
     */
    private $provider_id;

    /**
     * @ORM\Column(type="string")
     */
    private $encounter;

    /**
     * @ORM\Column(type="string")
     */
    private $drug;

    /**
     * @ORM\Column(type="integer")
     */
    private $drug_id;

    /**
     * @ORM\Column(type="string")
     */
    private $rxnorm_drugcode;

    /**
     * @ORM\Column(type="string")
     */
    private $form;

    /**
     * @ORM\Column(type="string")
     */
    private $dosage;

    /**
     * @ORM\Column(type="string")
     */
    private $quantity;

    /**
     * @ORM\Column(type="string")
     */
    private $size;

    /**
     * @ORM\Column(type="string")
     */
    private $unit;

    /**
     * @ORM\Column(type="string")
     */
    private $route;

    /**
     * @ORM\Column(type="string")
     */
    private $interval;

    /**
     * @ORM\Column(type="string")
     */
    private $substitute;

    /**
     * @ORM\Column(type="string")
     */
    private $refills;

    /**
     * @ORM\Column(type="string")
     */
    private $per_refill;

    /**
     * @ORM\Column(type="string")
     */
    private $filled_date;

    /**
     * @ORM\Column(type="string")
     */
    private $medication;

    /**
     * @ORM\Column(type="string")
     */
    private $note;

    /**
     * @ORM\Column(type="string")
     */
    private $active;

    /**
     * @ORM\Column(type="string")
     */
    private $datetime;

    /**
     * @ORM\Column(type="string")
     */
    private $user;

    /**
     * @ORM\Column(type="string")
     */
    private $site;

    /**
     * @ORM\Column(type="string")
     */
    private $prescriptionguid;

    /**
     * @ORM\Column(type="string")
     */
    private $erx_source;

    /**
     * @ORM\Column(type="string")
     */
    private $erx_uploaded;

    /**
     * @ORM\Column(type="string")
     */
    private $drug_info_erx;

    /**
     * @ORM\Column(type="integer")
     */
    private $external_id;

    /**
     * @ORM\Column(type="string")
     */
    private $end_date;

    /**
     * @ORM\Column(type="string")
     */
    private $indication;

    /**
     * @ORM\Column(type="string")
     */
    private $prn;

    /**
     * @ORM\Column(type="string")
     */
    private $ntx;

    /**
     * @ORM\Column(type="string")
     */
    private $rtx;

    /**
     * @ORM\Column(type="string")
     */
    private $txDate;

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
    public function getPatientId()
    {
        return $this->patient_id;
    }

    /**
     * @param mixed $patient_id
     */
    public function setPatientId($patient_id): void
    {
        $this->patient_id = $patient_id;
    }

    /**
     * @return mixed
     */
    public function getFilledById()
    {
        return $this->filled_by_id;
    }

    /**
     * @param mixed $filled_by_id
     */
    public function setFilledById($filled_by_id): void
    {
        $this->filled_by_id = $filled_by_id;
    }

    /**
     * @return mixed
     */
    public function getPharmacyId()
    {
        return $this->pharmacy_id;
    }

    /**
     * @param mixed $pharmacy_id
     */
    public function setPharmacyId($pharmacy_id): void
    {
        $this->pharmacy_id = $pharmacy_id;
    }

    /**
     * @return mixed
     */
    public function getDateAdded()
    {
        return $this->date_added;
    }

    /**
     * @param mixed $date_added
     */
    public function setDateAdded($date_added): void
    {
        $this->date_added = $date_added;
    }

    /**
     * @return mixed
     */
    public function getDateModified()
    {
        return $this->date_modified;
    }

    /**
     * @param mixed $date_modified
     */
    public function setDateModified($date_modified): void
    {
        $this->date_modified = $date_modified;
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
    public function getDrug()
    {
        return $this->drug;
    }

    /**
     * @param mixed $drug
     */
    public function setDrug($drug): void
    {
        $this->drug = $drug;
    }

    /**
     * @return mixed
     */
    public function getDrugId()
    {
        return $this->drug_id;
    }

    /**
     * @param mixed $drug_id
     */
    public function setDrugId($drug_id): void
    {
        $this->drug_id = $drug_id;
    }

    /**
     * @return mixed
     */
    public function getRxnormDrugcode()
    {
        return $this->rxnorm_drugcode;
    }

    /**
     * @param mixed $rxnorm_drugcode
     */
    public function setRxnormDrugcode($rxnorm_drugcode): void
    {
        $this->rxnorm_drugcode = $rxnorm_drugcode;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     */
    public function setForm($form): void
    {
        $this->form = $form;
    }

    /**
     * @return mixed
     */
    public function getDosage()
    {
        return $this->dosage;
    }

    /**
     * @param mixed $dosage
     */
    public function setDosage($dosage): void
    {
        $this->dosage = $dosage;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size): void
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit): void
    {
        $this->unit = $unit;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route): void
    {
        $this->route = $route;
    }

    /**
     * @return mixed
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param mixed $interval
     */
    public function setInterval($interval): void
    {
        $this->interval = $interval;
    }

    /**
     * @return mixed
     */
    public function getSubstitute()
    {
        return $this->substitute;
    }

    /**
     * @param mixed $substitute
     */
    public function setSubstitute($substitute): void
    {
        $this->substitute = $substitute;
    }

    /**
     * @return mixed
     */
    public function getRefills()
    {
        return $this->refills;
    }

    /**
     * @param mixed $refills
     */
    public function setRefills($refills): void
    {
        $this->refills = $refills;
    }

    /**
     * @return mixed
     */
    public function getPerRefill()
    {
        return $this->per_refill;
    }

    /**
     * @param mixed $per_refill
     */
    public function setPerRefill($per_refill): void
    {
        $this->per_refill = $per_refill;
    }

    /**
     * @return mixed
     */
    public function getFilledDate()
    {
        return $this->filled_date;
    }

    /**
     * @param mixed $filled_date
     */
    public function setFilledDate($filled_date): void
    {
        $this->filled_date = $filled_date;
    }

    /**
     * @return mixed
     */
    public function getMedication()
    {
        return $this->medication;
    }

    /**
     * @param mixed $medication
     */
    public function setMedication($medication): void
    {
        $this->medication = $medication;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note): void
    {
        $this->note = $note;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active): void
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param mixed $datetime
     */
    public function setDatetime($datetime): void
    {
        $this->datetime = $datetime;
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
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site): void
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getPrescriptionguid()
    {
        return $this->prescriptionguid;
    }

    /**
     * @param mixed $prescriptionguid
     */
    public function setPrescriptionguid($prescriptionguid): void
    {
        $this->prescriptionguid = $prescriptionguid;
    }

    /**
     * @return mixed
     */
    public function getErxSource()
    {
        return $this->erx_source;
    }

    /**
     * @param mixed $erx_source
     */
    public function setErxSource($erx_source): void
    {
        $this->erx_source = $erx_source;
    }

    /**
     * @return mixed
     */
    public function getErxUploaded()
    {
        return $this->erx_uploaded;
    }

    /**
     * @param mixed $erx_uploaded
     */
    public function setErxUploaded($erx_uploaded): void
    {
        $this->erx_uploaded = $erx_uploaded;
    }

    /**
     * @return mixed
     */
    public function getDrugInfoErx()
    {
        return $this->drug_info_erx;
    }

    /**
     * @param mixed $drug_info_erx
     */
    public function setDrugInfoErx($drug_info_erx): void
    {
        $this->drug_info_erx = $drug_info_erx;
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
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @param mixed $end_date
     */
    public function setEndDate($end_date): void
    {
        $this->end_date = $end_date;
    }

    /**
     * @return mixed
     */
    public function getIndication()
    {
        return $this->indication;
    }

    /**
     * @param mixed $indication
     */
    public function setIndication($indication): void
    {
        $this->indication = $indication;
    }

    /**
     * @return mixed
     */
    public function getPrn()
    {
        return $this->prn;
    }

    /**
     * @param mixed $prn
     */
    public function setPrn($prn): void
    {
        $this->prn = $prn;
    }

    /**
     * @return mixed
     */
    public function getNtx()
    {
        return $this->ntx;
    }

    /**
     * @param mixed $ntx
     */
    public function setNtx($ntx): void
    {
        $this->ntx = $ntx;
    }

    /**
     * @return mixed
     */
    public function getRtx()
    {
        return $this->rtx;
    }

    /**
     * @param mixed $rtx
     */
    public function setRtx($rtx): void
    {
        $this->rtx = $rtx;
    }

    /**
     * @return mixed
     */
    public function getTxDate()
    {
        return $this->txDate;
    }

    /**
     * @param mixed $txDate
     */
    public function setTxDate($txDate): void
    {
        $this->txDate = $txDate;
    }
}
