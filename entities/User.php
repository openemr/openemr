<?php
/**
 * User entity.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Entities;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Table(name="users")
 * @Entity(repositoryClass="OpenEMR\Repositories\UserRepository")
 */
class User
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
    }

    /**
     * @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @OneToMany(targetEntity="ONote", mappedBy="user")
     */
    private $oNotes;

    /**
     * @Id
     * @Column(name="username", type="string")
     */
    private $username;

    /**
     * @Column(name="calendar", type="boolean")
     */
    private $calendar;

    /**
     * @Column(name="irnpool", type="string")
     */
    private $irnPool;

    /**
     * @Column(name="cpoe", type="boolean")
     */
    private $cpoe;

    /**
     * @Column(name="physician_type", type="string")
     */
    private $physicianType;

    /**
     * @Column(name="newcrop_user_role", type="string")
     */
    private $newCropUserRole;

    /**
     * @Column(name="state_license_number", type="string")
     */
    private $stateLicenseNumber;

    /**
     * @Column(name="default_warehouse", type="string")
     */
    private $defaultWarehouse;

    /**
     * @Column(name="pwd_history2", type="text")
     */
    private $pwdHistory2;

    /**
     * @Column(name="pwd_history1", type="text")
     */
    private $pwdHistory1;

    /**
     * @Column(name="pwd_expiration_date", type="string")
     */
    private $pwdExpirationDate;

    /**
     * @Column(name="abook_type", type="string")
     */
    private $aBookType;

    /**
     * @Column(name="password", type="text")
     */
    private $password;

    /**
     * @Column(name="authorized", type="boolean")
     */
    private $authorized;

    /**
     * @Column(name="info", type="text")
     */
    private $info;

    /**
     * @Column(name="taxonomy", type="string")
     */
    private $taxonomy;

    /**
     * @Column(name="cal_ui", type="boolean")
     */
    private $calUi;

    /**
     * @Column(name="notes", type="text")
     */
    private $notes;

    /**
     * @Column(name="phonecell", type="string")
     */
    private $phoneCell;

    /**
     * @Column(name="phonew2", type="string")
     */
    private $phoneW2;

    /**
     * @Column(name="phonew1", type="string")
     */
    private $phoneW1;

    /**
     * @Column(name="fax", type="string")
     */
    private $fax;

    /**
     * @Column(name="phone", type="string")
     */
    private $phone;

    /**
     * @Column(name="zip2", type="string")
     */
    private $zip2;

    /**
     * @Column(name="state2", type="string")
     */
    private $state2;

    /**
     * @Column(name="city2", type="string")
     */
    private $city2;

    /**
     * @Column(name="streetb2", type="string")
     */
    private $streetB2;

    /**
     * @Column(name="street2", type="string")
     */
    private $street2;

    /**
     * @Column(name="zip", type="string")
     */
    private $zip;

    /**
     * @Column(name="state", type="string")
     */
    private $state;

    /**
     * @Column(name="city", type="string")
     */
    private $city;

    /**
     * @Column(name="streetb", type="string")
     */
    private $streetB;

    /**
     * @Column(name="street", type="string")
     */
    private $street;

    /**
     * @Column(name="valedictory", type="string")
     */
    private $valedictory;

    /**
     * @Column(name="organization", type="string")
     */
    private $organization;

    /**
     * @Column(name="assistant", type="string")
     */
    private $assistant;

    /**
     * @Column(name="url", type="string")
     */
    private $url;

    /**
     * @Column(name="email_direct", type="string")
     */
    private $emailDirect;

    /**
     * @Column(name="email", type="string")
     */
    private $email;

    /**
     * @Column(name="billname", type="string")
     */
    private $billName;

    /**
     * @Column(name="specialty", type="string")
     */
    private $specialty;

    /**
     * @Column(name="title", type="string")
     */
    private $title;

    /**
     * @Column(name="npi", type="string")
     */
    private $npi;

    /**
     * @Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @Column(name="see_auth", type="integer")
     */
    private $seeAuth;

    /**
     * @Column(name="facility_id", type="integer")
     */
    private $facilityId;

    /**
     * @Column(name="facility", type="string")
     */
    private $facility;

    /**
     * @Column(name="upin", type="string")
     */
    private $upin;

    /**
     * @Column(name="federalDrugId", type="string")
     */
    private $federalDrugId;

    /**
     * @Column(name="federaltaxid", type="string")
     */
    private $federalTaxId;

    /**
     * @Column(name="suffix", type="string")
     */
    private $suffix;

    /**
     * @Column(name="lname", type="string")
     */
    private $lname;

    /**
     * @Column(name="mname", type="string")
     */
    private $mname;

    /**
     * @Column(name="fname", type="string")
     */
    private $fname;

    /**
     * @Column(name="source", type="boolean")
     */
    private $source;

    /**
     * @Column(name="main_menu_role", type="string")
     */
    private $mainMenuRole;

    /**
     * @Column(name="patient_menu_role", type="string")
     */
    private $patientMenuRole;

    /**
     * @Column(name="weno_prov_id", type="string")
     */
    private $wenoProvId;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($value)
    {
        $this->username = $value;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getAuthorized()
    {
        return $this->authorized;
    }

    public function setAuthorized($value)
    {
        $this->authorized = $value;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($value)
    {
        $this->info = $value;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSource($value)
    {
        $this->source = $value;
    }

    public function getFname()
    {
        return $this->fname;
    }

    public function setFname($value)
    {
        $this->fname = $value;
    }

    public function getMname()
    {
        return $this->mname;
    }

    public function setMname($value)
    {
        $this->mname = $value;
    }

    public function getLname()
    {
        return $this->lname;
    }

    public function setLname($value)
    {
        $this->lname = $value;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setSuffix($value)
    {
        $this->suffix = $value;
    }

    public function getFederalTaxId()
    {
        return $this->federalTaxId;
    }

    public function setFederalTaxId($value)
    {
        $this->federalTaxId = $value;
    }

    public function getFederalDrugId()
    {
        return $this->federalDrugId;
    }

    public function setFederalDrugId($value)
    {
        $this->federalDrugId = $value;
    }

    public function getUpin()
    {
        return $this->upin;
    }

    public function setUpin($value)
    {
        $this->upin = $value;
    }

    public function getFacility()
    {
        return $this->facility;
    }

    public function setFacility($value)
    {
        $this->facility = $value;
    }

    public function getFacilityId()
    {
        return $this->facilityId;
    }

    public function setFacilityId($value)
    {
        $this->facilityId = $value;
    }

    public function getSeeAuth()
    {
        return $this->seeAuth;
    }

    public function setSeeAuth($value)
    {
        $this->seeAuth = $value;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function setActive($value)
    {
        $this->active = $value;
    }

    public function getNpi()
    {
        return $this->npi;
    }

    public function setNpi($value)
    {
        $this->npi = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getSpecialty()
    {
        return $this->specialty;
    }

    public function setSpecialty($value)
    {
        $this->specialty = $value;
    }

    public function getBillName()
    {
        return $this->billName;
    }

    public function setBillName($value)
    {
        $this->billName = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getEmailDirect()
    {
        return $this->emailDirect;
    }

    public function setEmailDirect($value)
    {
        $this->emailDirect = $value;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($value)
    {
        $this->url = $value;
    }

    public function getAssistant()
    {
        return $this->assistant;
    }

    public function setAssistant($value)
    {
        $this->assistant = $value;
    }

    public function getOrganization()
    {
        return $this->organization;
    }

    public function setOrganization($value)
    {
        $this->organization = $value;
    }

    public function getValedictory()
    {
        return $this->valedictory;
    }

    public function setValedictory($value)
    {
        $this->valedictory = $value;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setStreet($value)
    {
        $this->street = $value;
    }

    public function getStreetB()
    {
        return $this->streetB;
    }

    public function setStreetB($value)
    {
        $this->streetB = $value;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($value)
    {
        $this->city = $value;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($value)
    {
        $this->state = $value;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setZip($value)
    {
        $this->zip = $value;
    }

    public function getStreet2()
    {
        return $this->street2;
    }

    public function setStreet2($value)
    {
        $this->street2 = $value;
    }

    public function getStreetB2()
    {
        return $this->streetB2;
    }

    public function setStreetB2($value)
    {
        $this->streetB2 = $value;
    }

    public function getCity2()
    {
        return $this->city2;
    }

    public function setCity2($value)
    {
        $this->city2 = $value;
    }

    public function getState2()
    {
        return $this->state2;
    }

    public function setState2($value)
    {
        $this->state2 = $value;
    }

    public function getZip2()
    {
        return $this->zip2;
    }

    public function setZip2($value)
    {
        $this->zip2 = $value;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($value)
    {
        $this->phone = $value;
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function setFax($value)
    {
        $this->fax = $value;
    }

    public function getPhoneW1()
    {
        return $this->phoneW1;
    }

    public function setPhoneW1($value)
    {
        $this->phoneW1 = $value;
    }

    public function getPhoneW2()
    {
        return $this->phoneW2;
    }

    public function setPhoneW2($value)
    {
        $this->phoneW2 = $value;
    }

    public function getPhoneCell()
    {
        return $this->phoneCell;
    }

    public function setPhoneCell($value)
    {
        $this->phoneCell = $value;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($value)
    {
        $this->notes = $value;
    }

    public function getCalUi()
    {
        return $this->calUi;
    }

    public function setCalUi($value)
    {
        $this->calUi = $value;
    }

    public function getTaxonomy()
    {
        return $this->taxonomy;
    }

    public function setTaxonomy($value)
    {
        $this->taxonomy = $value;
    }

    public function getCalendar()
    {
        return $this->calendar;
    }

    public function setCalendar($value)
    {
        $this->calendar = $value;
    }

    public function getaBookType()
    {
        return $this->aBookType;
    }

    public function setaBookType($value)
    {
        $this->aBookType = $value;
    }

    public function getPwdExpirationDate()
    {
        return $this->pwdExpirationDate;
    }

    public function setPwdExpirationDate($value)
    {
        $this->pwdExpirationDate = $value;
    }

    public function getPwdHistory1()
    {
        return $this->pwdHistory1;
    }

    public function setPwdHistory1($value)
    {
        $this->pwdHistory1 = $value;
    }

    public function getPwdHistory2()
    {
        return $this->pwdHistory2;
    }

    public function setPwdHistory2($value)
    {
        $this->pwdHistory2 = $value;
    }

    public function getDefaultWarehouse()
    {
        return $this->defaultWarehouse;
    }

    public function setDefaultWarehouse($value)
    {
        $this->defaultWarehouse = $value;
    }

    public function getIrnPool()
    {
        return $this->irnPool;
    }

    public function setIrnPool($value)
    {
        $this->irnPool = $value;
    }

    public function getStateLicenseNumber()
    {
        return $this->stateLicenseNumber;
    }

    public function setStateLicenseNumber($value)
    {
        $this->stateLicenseNumber = $value;
    }

    public function getNewCropUserRole()
    {
        return $this->newCropUserRole;
    }

    public function setNewCropUserRole($value)
    {
        $this->newCropUserRole = $value;
    }

    public function getCpoe()
    {
        return $this->cpoe;
    }

    public function setCpoe($value)
    {
        $this->cpoe = $value;
    }

    public function getPhysicianType()
    {
        return $this->physicianType;
    }

    public function setPhysicianType($value)
    {
        $this->physicianType = $value;
    }

    public function getMainMenuRole()
    {
        return $this->mainMenuRole;
    }

    public function setMainMenuRole($value)
    {
        $this->mainMenuRole = $value;
    }

    public function getPatientMenuRole()
    {
        return $this->patientMenuRole;
    }

    public function setPatientMenuRole($value)
    {
        $this->patientMenuRole = $value;
    }

    public function getWenoProvId()
    {
        return $this->wenoProvId;
    }

    public function setWenoProvId($value)
    {
        $this->wenoProvId = $value;
    }

    /**
     * ToString of the entire object.
     *
     * @return object as string
     */
    public function __toString()
    {
        return "id: '" . $this->getId() . "' " .
               "username: '" . $this->getUsername() . "' " .
               "password: '" . $this->getPassword() . "' " .
               "authorized: '" . $this->getAuthorized() . "' " .
               "info: '" . $this->getInfo() . "' " .
               "source: '" . $this->getSource() . "' " .
               "fname: '" . $this->getFname() . "' " .
               "mname: '" . $this->getMname() . "' " .
               "lname: '" . $this->getLname() . "' " .
               "suffix: '" . $this->getSuffix() . "' " .
               "federalTaxId: '" . $this->getFederalTaxId() . "' " .
               "federalDrugId: '" . $this->getFederalDrugId() . "' " .
               "upin: '" . $this->getUpin() . "' " .
               "facility: '" . $this->getFacility() . "' " .
               "facilityId: '" . $this->getFacilityId() . "' " .
               "seeAuth: '" . $this->getSeeAuth() . "' " .
               "active: '" . $this->getActive() . "' " .
               "npi: '" . $this->getNpi() . "' " .
               "title: '" . $this->getTitle() . "' " .
               "specialty: '" . $this->getSpecialty() . "' " .
               "billName: '" . $this->getBillName() . "' " .
               "email: '" . $this->getEmail() . "' " .
               "emailDirect: '" . $this->getEmailDirect() . "' " .
               "url: '" . $this->getUrl() . "' " .
               "assistant: '" . $this->getAssistant() . "' " .
               "organization: '" . $this->getOrganization() . "' " .
               "valedictory: '" . $this->getValedictory() . "' " .
               "street: '" . $this->getStreet() . "' " .
               "streetB: '" . $this->getStreetB() . "' " .
               "city: '" . $this->getCity() . "' " .
               "state: '" . $this->getState() . "' " .
               "zip: '" . $this->getZip() . "' " .
               "street2: '" . $this->getStreet2() . "' " .
               "streetB2: '" . $this->getStreetB2() . "' " .
               "city2: '" . $this->getCity2() . "' " .
               "state2: '" . $this->getState2() . "' " .
               "zip2: '" . $this->getZip2() . "' " .
               "phone: '" . $this->getPhone() . "' " .
               "fax: '" . $this->getFax() . "' " .
               "phoneW1: '" . $this->getPhoneW1() . "' " .
               "phoneW2: '" . $this->getPhoneW2() . "' " .
               "phoneCell: '" . $this->getPhoneCell() . "' " .
               "notes: '" . $this->getNotes() . "' " .
               "calUi: '" . $this->getCalUi() . "' " .
               "taxonomy: '" . $this->getTaxonomy() . "' " .
               "calendar: '" . $this->getCalendar() . "' " .
               "aBookType: '" . $this->getaBookType() . "' " .
               "pwdExpirationDate: '" . $this->getPwdExpirationDate() . "' " .
               "pwdHistory1: '" . $this->getPwdHistory1() . "' " .
               "pwdHistory2: '" . $this->getPwdHistory2() . "' " .
               "defaultWarehouse: '" . $this->getDefaultWarehouse() . "' " .
               "irnPool: '" . $this->getIrnPool() . "' " .
               "stateLicenseNumber: '" . $this->getStateLicenseNumber() . "' " .
               "newCropUserRole: '" . $this->getNewCropUserRole() . "' " .
               "cpoe: '" . $this->getCpoe() . "' " .
               "physicianType: '" . $this->getPhysicianType() . "' " .
               "mainMenuRole: '" . $this->getMainMenuRole() . "' " .
               "patientMenuRole: '" . $this->getPatientMenuRole() . "' " .
               "wenoProvId: '" . $this->getWenoProvId() . "' ";
    }

    /**
     * ToSerializedObject of the entire object.
     *
     * @return object as serialized object.
     */
    public function toSerializedObject()
    {
        return get_object_vars($this);
    }
}
