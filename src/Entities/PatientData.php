<?php
/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PatientData
 *
 * @ORM\Table(name="patient_data")
 * @ORM\Entity(repositoryClass="OpenEMR\Repositories\PatientDataRepository")
 */
class PatientData
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    public function __construct()
    {
        $this->id = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     */
    private $language;

    /**
     * @ORM\Column(type="string")
     */
    private $financial;

    /**
     * @ORM\Column(type="string")
     */
    private $fname;

    /**
     * @ORM\Column(type="string")
     */
    private $lname;

    /**
     * @ORM\Column(type="string")
     */
    private $mname;

    /**
     * @ORM\Column(type="string")
     */
    private $DOB;

    /**
     * @ORM\Column(type="string")
     */
    private $street;

    /**
     * @ORM\Column(type="string")
     */
    private $postal_code;

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
    private $country_code;

    /**
     * @ORM\Column(type="string")
     */
    private $drivers_license;

    /**
     * @ORM\Column(type="string")
     */
    private $ss;

    /**
     * @ORM\Column(type="string")
     */
    private $occupation;

    /**
     * @ORM\Column(type="string")
     */
    private $phone_home;

    /**
     * @ORM\Column(type="string")
     */
    private $phone_biz;

    /**
     * @ORM\Column(type="string")
     */
    private $phone_contact;

    /**
     * @ORM\Column(type="string")
     */
    private $phone_cell;

    /**
     * @ORM\Column(type="integer")
     */
    private $pharmacy_id;

    /**
     * @ORM\Column(type="string")
     */
    private $status;

    /**
     * @ORM\Column(type="string")
     */
    private $contact_relationship;

    /**
     * @ORM\Column(type="string")
     */
    private $date;

    /**
     * @ORM\Column(type="string")
     */
    private $sex;

    /**
     * @ORM\Column(type="string")
     */
    private $referrer;

    /**
     * @ORM\Column(type="string")
     */
    private $referrerID;

    /**
     * @ORM\Column(type="string")
     */
    private $providerID;

    /**
     * @ORM\Column(type="string")
     */
    private $ref_providerID;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     */
    private $email_direct;

    /**
     * @ORM\Column(type="string")
     */
    private $ethnoracial;

    /**
     * @ORM\Column(type="string")
     */
    private $race;

    /**
     * @ORM\Column(type="string")
     */
    private $ethnicity;

    /**
     * @ORM\Column(type="string")
     */
    private $religion;

    /**
     * @ORM\Column(type="string")
     */
    private $interpretter;

    /**
     * @ORM\Column(type="string")
     */
    private $migrantseasonal;

    /**
     * @ORM\Column(type="string")
     */
    private $family_size;

    /**
     * @ORM\Column(type="string")
     */
    private $monthly_income;

    /**
     * @ORM\Column(type="string")
     */
    private $billing_note;

    /**
     * @ORM\Column(type="string")
     */
    private $homeless;

    /**
     * @ORM\Column(type="string")
     */
    private $financial_review;

    /**
     * @ORM\Column(type="string")
     */
    private $pubpid;

    /**
     * @ORM\Column(type="string")
     * @ORM\ManyToMany(targetEntity="CcLedger",)
     */
    private $pid;

    /**
     * @ORM\Column(type="string")
     */
    private $genericname1;

    /**
     * @ORM\Column(type="string")
     */
    private $genericval1;

    /**
     * @ORM\Column(type="string")
     */
    private $genericname2;

    /**
     * @ORM\Column(type="string")
     */
    private $hipaa_mail;

    /**
     * @ORM\Column(type="string")
     */
    private $hipaa_voice;

    /**
     * @ORM\Column(type="string")
     */
    private $hipaa_notice;

    /**
     * @ORM\Column(type="string")
     */
    private $hipaa_message;

    /**
     * @ORM\Column(type="string")
     */
    private $hipaa_allowsms;

    /**
     * @ORM\Column(type="string")
     */
    private $hipaa_allowemail;

    /**
     * @ORM\Column(type="string")
     */
    private $squad;

    /**
     * @ORM\Column(type="string")
     */
    private $fitness;

    /**
     * @ORM\Column(type="string")
     */
    private $referral_source;

    /**
     * @ORM\Column(type="string")
     */
    private $onhospice;

    /**
     * @ORM\Column(type="string")
     */
    private $statusnotes;

    /**
     * @ORM\Column(type="string")
     */
    private $discharged;

    /**
     * @ORM\Column(type="string")
     */
    private $dischargedate;

    /**
     * @ORM\Column(type="string")
     */
    private $space1;

    /**
     * @ORM\Column(type="string")
     */
    private $space2;

    /**
     * @ORM\Column(type="string")
     */
    private $usertext8;

    /**
     * @ORM\Column(type="string")
     */
    private $userlist1;

    /**
     * @ORM\Column(type="string")
     */
    private $userlist2;

    /**
     * @ORM\Column(type="string")
     */
    private $userlist3;

    /**
     * @ORM\Column(type="string")
     */
    private $userlist4;

    /**
     * @ORM\Column(type="string")
     */
    private $userlist5;

    /**
     * @ORM\Column(type="string")
     */
    private $space4;

    /**
     * @ORM\Column(type="string")
     */
    private $space3;

    /**
     * @ORM\Column(type="string")
     */
    private $pricelevel;

    /**
     * @ORM\Column(type="string")
     */
    private $regdate;

    /**
     * @ORM\Column(type="string")
     */
    private $contrastart;

    /**
     * @ORM\Column(type="string")
     */
    private $completed_ad;

    /**
     * @ORM\Column(type="string")
     */
    private $ad_reviewed;

    /**
     * @ORM\Column(type="string")
     */
    private $vfc;

    /**
     * @ORM\Column(type="string")
     */
    private $mothersname;

    /**
     * @ORM\Column(type="string")
     */
    private $guardiansname;

    /**
     * @ORM\Column(type="string")
     */
    private $allow_imm_reg_use;

    /**
     * @ORM\Column(type="string")
     */
    private $allow_imm_info_share;

    /**
     * @ORM\Column(type="string")
     */
    private $allow_health_info_ex;

    /**
     * @ORM\Column(type="string")
     */
    private $allow_patient_portal;

    /**
     * @ORM\Column(type="string")
     */
    private $deceased_date;

    /**
     * @ORM\Column(type="string")
     */
    private $soap_import_status;

    /**
     * @ORM\Column(type="string")
     */
    private $cmsportal_login;

    /**
     * @ORM\Column(type="string")
     */
    private $care_team;

    /**
     * @ORM\Column(type="string")
     */
    private $county;

    /**
     * @ORM\Column(type="string")
     */
    private $employment_status;

    /**
     * @ORM\Column(type="string")
     */
    private $imm_reg_status;

    /**
     * @ORM\Column(type="string")
     */
    private $imm_reg_stat_effdate;

    /**
     * @ORM\Column(type="string")
     */
    private $publicity_code;

    /**
     * @ORM\Column(type="string")
     */
    private $publ_code_eff_date;

    /**
     * @ORM\Column(type="string")
     */
    private $protect_indicator;

    /**
     * @ORM\Column(type="string")
     */
    private $prot_indi_effdate;

    /**
     * @ORM\Column(type="string")
     */
    private $guardianrelationship;

    /**
     * @ORM\Column(type="string")
     */
    private $guardiansex;

    /**
     * @ORM\Column(type="string")
     */
    private $guardianaddress;

    /**
     * @ORM\Column(type="string")
     */
    private $guardiancity;

    /**
     * @ORM\Column(type="string")
     */
    private $guardianstate;

    /**
     * @ORM\Column(type="string")
     */
    private $guardianpostalcode;

    /**
     * @ORM\Column(type="string")
     */
    private $guardiancountry;

    /**
     * @ORM\Column(type="string")
     */
    private $guardianphone;

    /**
     * @ORM\Column(type="string")
     */
    private $guardianaltphone;

    /**
     * @ORM\Column(type="string")
     */
    private $guardianemail;

    /**
     * @ORM\Column(type="string")
     */
    private $referraldate;

    /**
     * @ORM\Column(type="string")
     */
    private $referrcompany;

    /**
     * @ORM\Column(type="string")
     */
    private $referrperson;

    /**
     * @ORM\Column(type="string")
     */
    private $referralphone;

    /**
     * @ORM\Column(type="string")
     */
    private $referralemail;

    /**
     * @ORM\Column(type="string")
     */

    private $provider_info;

    /**
     * @ORM\Column(type="string")
     */
    private $nname;

    /**
     * @ORM\Column(type="string")
     */
    private $payorInfo;

    /**
     * @ORM\Column(type="string")
     */
    private $estcopay;

    /**
     * @ORM\Column(type="string")
     */
    private $emergency_email;

    /**
     * @ORM\Column(type="string")
     */
    private $altcont1;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1name;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1relationship;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1address;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1city;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1state;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1postal;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1phone1;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1phone2;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1notes;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1email;

    /**
     * @ORM\Column(type="string")
     */
    private $alt1address2;

    /**
     * @ORM\Column(type="string")
     */
    private $ptfacility;

    /**
     * @ORM\Column(type="string")
     */
    private $providerIDalt;

    /**
     * @ORM\Column(type="string")
     */
    private $genericval2;

    /**
     * @ORM\Column(type="string")
     */
    private $emergency_relat;

    /**
     * @ORM\Column(type="string")
     */
    private $address2;

    /**
     * @ORM\Column(type="string")
     */
    private $email_contact;

    /**
     * @ORM\Column(type="string")
     */
    private $relationship;

    /**
     * @ORM\Column(type="string")
     */
    private $resaddress1;

    /**
     * @ORM\Column(type="string")
     */
    private $resaddress2;

    /**
     * @ORM\Column(type="string")
     */
    private $rescity;

    /**
     * @ORM\Column(type="string")
     */
    private $resstate;

    /**
     * @ORM\Column(type="string")
     */
    private $respostal;

    /**
     * @ORM\Column(type="string")
     */
    private $resfacility;

    /**
     * @ORM\Column(type="string")
     */
    private $resphone;

    /**
     * @ORM\Column(type="string")
     */
    private $curemail;

    /**
     * @ORM\Column(type="string")
     */
    private $resnotes;

    /**
     * @return ArrayCollection
     */
    public function getId(): ArrayCollection
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language): void
    {
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getFinancial()
    {
        return $this->financial;
    }

    /**
     * @param mixed $financial
     */
    public function setFinancial($financial): void
    {
        $this->financial = $financial;
    }

    /**
     * @return mixed
     */
    public function getFname()
    {
        return $this->fname;
    }

    /**
     * @param mixed $fname
     */
    public function setFname($fname): void
    {
        $this->fname = $fname;
    }

    /**
     * @return mixed
     */
    public function getLname()
    {
        return $this->lname;
    }

    /**
     * @param mixed $lname
     */
    public function setLname($lname): void
    {
        $this->lname = $lname;
    }

    /**
     * @return mixed
     */
    public function getMname()
    {
        return $this->mname;
    }

    /**
     * @param mixed $mname
     */
    public function setMname($mname): void
    {
        $this->mname = $mname;
    }

    /**
     * @return mixed
     */
    public function getDOB()
    {
        return $this->DOB;
    }

    /**
     * @param mixed $DOB
     */
    public function setDOB($DOB): void
    {
        $this->DOB = $DOB;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street): void
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param mixed $postal_code
     */
    public function setPostalCode($postal_code): void
    {
        $this->postal_code = $postal_code;
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
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param mixed $country_code
     */
    public function setCountryCode($country_code): void
    {
        $this->country_code = $country_code;
    }

    /**
     * @return mixed
     */
    public function getDriversLicense()
    {
        return $this->drivers_license;
    }

    /**
     * @param mixed $drivers_license
     */
    public function setDriversLicense($drivers_license): void
    {
        $this->drivers_license = $drivers_license;
    }

    /**
     * @return mixed
     */
    public function getSs()
    {
        return $this->ss;
    }

    /**
     * @param mixed $ss
     */
    public function setSs($ss): void
    {
        $this->ss = $ss;
    }

    /**
     * @return mixed
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * @param mixed $occupation
     */
    public function setOccupation($occupation): void
    {
        $this->occupation = $occupation;
    }

    /**
     * @return mixed
     */
    public function getPhoneHome()
    {
        return $this->phone_home;
    }

    /**
     * @param mixed $phone_home
     */
    public function setPhoneHome($phone_home): void
    {
        $this->phone_home = $phone_home;
    }

    /**
     * @return mixed
     */
    public function getPhoneBiz()
    {
        return $this->phone_biz;
    }

    /**
     * @param mixed $phone_biz
     */
    public function setPhoneBiz($phone_biz): void
    {
        $this->phone_biz = $phone_biz;
    }

    /**
     * @return mixed
     */
    public function getPhoneContact()
    {
        return $this->phone_contact;
    }

    /**
     * @param mixed $phone_contact
     */
    public function setPhoneContact($phone_contact): void
    {
        $this->phone_contact = $phone_contact;
    }

    /**
     * @return mixed
     */
    public function getPhoneCell()
    {
        return $this->phone_cell;
    }

    /**
     * @param mixed $phone_cell
     */
    public function setPhoneCell($phone_cell): void
    {
        $this->phone_cell = $phone_cell;
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getContactRelationship()
    {
        return $this->contact_relationship;
    }

    /**
     * @param mixed $contact_relationship
     */
    public function setContactRelationship($contact_relationship): void
    {
        $this->contact_relationship = $contact_relationship;
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
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param mixed $sex
     */
    public function setSex($sex): void
    {
        $this->sex = $sex;
    }

    /**
     * @return mixed
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * @param mixed $referrer
     */
    public function setReferrer($referrer): void
    {
        $this->referrer = $referrer;
    }

    /**
     * @return mixed
     */
    public function getReferrerID()
    {
        return $this->referrerID;
    }

    /**
     * @param mixed $referrerID
     */
    public function setReferrerID($referrerID): void
    {
        $this->referrerID = $referrerID;
    }

    /**
     * @return mixed
     */
    public function getProviderID()
    {
        return $this->providerID;
    }

    /**
     * @param mixed $providerID
     */
    public function setProviderID($providerID): void
    {
        $this->providerID = $providerID;
    }

    /**
     * @return mixed
     */
    public function getRefProviderID()
    {
        return $this->ref_providerID;
    }

    /**
     * @param mixed $ref_providerID
     */
    public function setRefProviderID($ref_providerID): void
    {
        $this->ref_providerID = $ref_providerID;
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
    public function getEmailDirect()
    {
        return $this->email_direct;
    }

    /**
     * @param mixed $email_direct
     */
    public function setEmailDirect($email_direct): void
    {
        $this->email_direct = $email_direct;
    }

    /**
     * @return mixed
     */
    public function getEthnoracial()
    {
        return $this->ethnoracial;
    }

    /**
     * @param mixed $ethnoracial
     */
    public function setEthnoracial($ethnoracial): void
    {
        $this->ethnoracial = $ethnoracial;
    }

    /**
     * @return mixed
     */
    public function getRace()
    {
        return $this->race;
    }

    /**
     * @param mixed $race
     */
    public function setRace($race): void
    {
        $this->race = $race;
    }

    /**
     * @return mixed
     */
    public function getEthnicity()
    {
        return $this->ethnicity;
    }

    /**
     * @param mixed $ethnicity
     */
    public function setEthnicity($ethnicity): void
    {
        $this->ethnicity = $ethnicity;
    }

    /**
     * @return mixed
     */
    public function getReligion()
    {
        return $this->religion;
    }

    /**
     * @param mixed $religion
     */
    public function setReligion($religion): void
    {
        $this->religion = $religion;
    }

    /**
     * @return mixed
     */
    public function getInterpretter()
    {
        return $this->interpretter;
    }

    /**
     * @param mixed $interpretter
     */
    public function setInterpretter($interpretter): void
    {
        $this->interpretter = $interpretter;
    }

    /**
     * @return mixed
     */
    public function getMigrantseasonal()
    {
        return $this->migrantseasonal;
    }

    /**
     * @param mixed $migrantseasonal
     */
    public function setMigrantseasonal($migrantseasonal): void
    {
        $this->migrantseasonal = $migrantseasonal;
    }

    /**
     * @return mixed
     */
    public function getFamilySize()
    {
        return $this->family_size;
    }

    /**
     * @param mixed $family_size
     */
    public function setFamilySize($family_size): void
    {
        $this->family_size = $family_size;
    }

    /**
     * @return mixed
     */
    public function getMonthlyIncome()
    {
        return $this->monthly_income;
    }

    /**
     * @param mixed $monthly_income
     */
    public function setMonthlyIncome($monthly_income): void
    {
        $this->monthly_income = $monthly_income;
    }

    /**
     * @return mixed
     */
    public function getBillingNote()
    {
        return $this->billing_note;
    }

    /**
     * @param mixed $billing_note
     */
    public function setBillingNote($billing_note): void
    {
        $this->billing_note = $billing_note;
    }

    /**
     * @return mixed
     */
    public function getHomeless()
    {
        return $this->homeless;
    }

    /**
     * @param mixed $homeless
     */
    public function setHomeless($homeless): void
    {
        $this->homeless = $homeless;
    }

    /**
     * @return mixed
     */
    public function getFinancialReview()
    {
        return $this->financial_review;
    }

    /**
     * @param mixed $financial_review
     */
    public function setFinancialReview($financial_review): void
    {
        $this->financial_review = $financial_review;
    }

    /**
     * @return mixed
     */
    public function getPubpid()
    {
        return $this->pubpid;
    }

    /**
     * @param mixed $pubpid
     */
    public function setPubpid($pubpid): void
    {
        $this->pubpid = $pubpid;
    }

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
    public function setPid($pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return mixed
     */
    public function getGenericname1()
    {
        return $this->genericname1;
    }

    /**
     * @param mixed $genericname1
     */
    public function setGenericname1($genericname1): void
    {
        $this->genericname1 = $genericname1;
    }

    /**
     * @return mixed
     */
    public function getGenericval1()
    {
        return $this->genericval1;
    }

    /**
     * @param mixed $genericval1
     */
    public function setGenericval1($genericval1): void
    {
        $this->genericval1 = $genericval1;
    }

    /**
     * @return mixed
     */
    public function getGenericname2()
    {
        return $this->genericname2;
    }

    /**
     * @param mixed $genericname2
     */
    public function setGenericname2($genericname2): void
    {
        $this->genericname2 = $genericname2;
    }

    /**
     * @return mixed
     */
    public function getHipaaMail()
    {
        return $this->hipaa_mail;
    }

    /**
     * @param mixed $hipaa_mail
     */
    public function setHipaaMail($hipaa_mail): void
    {
        $this->hipaa_mail = $hipaa_mail;
    }

    /**
     * @return mixed
     */
    public function getHipaaVoice()
    {
        return $this->hipaa_voice;
    }

    /**
     * @param mixed $hipaa_voice
     */
    public function setHipaaVoice($hipaa_voice): void
    {
        $this->hipaa_voice = $hipaa_voice;
    }

    /**
     * @return mixed
     */
    public function getHipaaNotice()
    {
        return $this->hipaa_notice;
    }

    /**
     * @param mixed $hipaa_notice
     */
    public function setHipaaNotice($hipaa_notice): void
    {
        $this->hipaa_notice = $hipaa_notice;
    }

    /**
     * @return mixed
     */
    public function getHipaaMessage()
    {
        return $this->hipaa_message;
    }

    /**
     * @param mixed $hipaa_message
     */
    public function setHipaaMessage($hipaa_message): void
    {
        $this->hipaa_message = $hipaa_message;
    }

    /**
     * @return mixed
     */
    public function getHipaaAllowsms()
    {
        return $this->hipaa_allowsms;
    }

    /**
     * @param mixed $hipaa_allowsms
     */
    public function setHipaaAllowsms($hipaa_allowsms): void
    {
        $this->hipaa_allowsms = $hipaa_allowsms;
    }

    /**
     * @return mixed
     */
    public function getHipaaAllowemail()
    {
        return $this->hipaa_allowemail;
    }

    /**
     * @param mixed $hipaa_allowemail
     */
    public function setHipaaAllowemail($hipaa_allowemail): void
    {
        $this->hipaa_allowemail = $hipaa_allowemail;
    }

    /**
     * @return mixed
     */
    public function getSquad()
    {
        return $this->squad;
    }

    /**
     * @param mixed $squad
     */
    public function setSquad($squad): void
    {
        $this->squad = $squad;
    }

    /**
     * @return mixed
     */
    public function getFitness()
    {
        return $this->fitness;
    }

    /**
     * @param mixed $fitness
     */
    public function setFitness($fitness): void
    {
        $this->fitness = $fitness;
    }

    /**
     * @return mixed
     */
    public function getReferralSource()
    {
        return $this->referral_source;
    }

    /**
     * @param mixed $referral_source
     */
    public function setReferralSource($referral_source): void
    {
        $this->referral_source = $referral_source;
    }

    /**
     * @return mixed
     */
    public function getOnhospice()
    {
        return $this->onhospice;
    }

    /**
     * @param mixed $onhospice
     */
    public function setOnhospice($onhospice): void
    {
        $this->onhospice = $onhospice;
    }

    /**
     * @return mixed
     */
    public function getStatusnotes()
    {
        return $this->statusnotes;
    }

    /**
     * @param mixed $statusnotes
     */
    public function setStatusnotes($statusnotes): void
    {
        $this->statusnotes = $statusnotes;
    }

    /**
     * @return mixed
     */
    public function getDischarged()
    {
        return $this->discharged;
    }

    /**
     * @param mixed $discharged
     */
    public function setDischarged($discharged): void
    {
        $this->discharged = $discharged;
    }

    /**
     * @return mixed
     */
    public function getDischargedate()
    {
        return $this->dischargedate;
    }

    /**
     * @param mixed $dischargedate
     */
    public function setDischargedate($dischargedate): void
    {
        $this->dischargedate = $dischargedate;
    }

    /**
     * @return mixed
     */
    public function getSpace1()
    {
        return $this->space1;
    }

    /**
     * @param mixed $space1
     */
    public function setSpace1($space1): void
    {
        $this->space1 = $space1;
    }

    /**
     * @return mixed
     */
    public function getSpace2()
    {
        return $this->space2;
    }

    /**
     * @param mixed $space2
     */
    public function setSpace2($space2): void
    {
        $this->space2 = $space2;
    }

    /**
     * @return mixed
     */
    public function getUsertext8()
    {
        return $this->usertext8;
    }

    /**
     * @param mixed $usertext8
     */
    public function setUsertext8($usertext8): void
    {
        $this->usertext8 = $usertext8;
    }

    /**
     * @return mixed
     */
    public function getUserlist1()
    {
        return $this->userlist1;
    }

    /**
     * @param mixed $userlist1
     */
    public function setUserlist1($userlist1): void
    {
        $this->userlist1 = $userlist1;
    }

    /**
     * @return mixed
     */
    public function getUserlist2()
    {
        return $this->userlist2;
    }

    /**
     * @param mixed $userlist2
     */
    public function setUserlist2($userlist2): void
    {
        $this->userlist2 = $userlist2;
    }

    /**
     * @return mixed
     */
    public function getUserlist3()
    {
        return $this->userlist3;
    }

    /**
     * @param mixed $userlist3
     */
    public function setUserlist3($userlist3): void
    {
        $this->userlist3 = $userlist3;
    }

    /**
     * @return mixed
     */
    public function getUserlist4()
    {
        return $this->userlist4;
    }

    /**
     * @param mixed $userlist4
     */
    public function setUserlist4($userlist4): void
    {
        $this->userlist4 = $userlist4;
    }

    /**
     * @return mixed
     */
    public function getUserlist5()
    {
        return $this->userlist5;
    }

    /**
     * @param mixed $userlist5
     */
    public function setUserlist5($userlist5): void
    {
        $this->userlist5 = $userlist5;
    }

    /**
     * @return mixed
     */
    public function getSpace4()
    {
        return $this->space4;
    }

    /**
     * @param mixed $space4
     */
    public function setSpace4($space4): void
    {
        $this->space4 = $space4;
    }

    /**
     * @return mixed
     */
    public function getSpace3()
    {
        return $this->space3;
    }

    /**
     * @param mixed $space3
     */
    public function setSpace3($space3): void
    {
        $this->space3 = $space3;
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
    public function getRegdate()
    {
        return $this->regdate;
    }

    /**
     * @param mixed $regdate
     */
    public function setRegdate($regdate): void
    {
        $this->regdate = $regdate;
    }

    /**
     * @return mixed
     */
    public function getContrastart()
    {
        return $this->contrastart;
    }

    /**
     * @param mixed $contrastart
     */
    public function setContrastart($contrastart): void
    {
        $this->contrastart = $contrastart;
    }

    /**
     * @return mixed
     */
    public function getCompletedAd()
    {
        return $this->completed_ad;
    }

    /**
     * @param mixed $completed_ad
     */
    public function setCompletedAd($completed_ad): void
    {
        $this->completed_ad = $completed_ad;
    }

    /**
     * @return mixed
     */
    public function getAdReviewed()
    {
        return $this->ad_reviewed;
    }

    /**
     * @param mixed $ad_reviewed
     */
    public function setAdReviewed($ad_reviewed): void
    {
        $this->ad_reviewed = $ad_reviewed;
    }

    /**
     * @return mixed
     */
    public function getVfc()
    {
        return $this->vfc;
    }

    /**
     * @param mixed $vfc
     */
    public function setVfc($vfc): void
    {
        $this->vfc = $vfc;
    }

    /**
     * @return mixed
     */
    public function getMothersname()
    {
        return $this->mothersname;
    }

    /**
     * @param mixed $mothersname
     */
    public function setMothersname($mothersname): void
    {
        $this->mothersname = $mothersname;
    }

    /**
     * @return mixed
     */
    public function getGuardiansname()
    {
        return $this->guardiansname;
    }

    /**
     * @param mixed $guardiansname
     */
    public function setGuardiansname($guardiansname): void
    {
        $this->guardiansname = $guardiansname;
    }

    /**
     * @return mixed
     */
    public function getAllowImmRegUse()
    {
        return $this->allow_imm_reg_use;
    }

    /**
     * @param mixed $allow_imm_reg_use
     */
    public function setAllowImmRegUse($allow_imm_reg_use): void
    {
        $this->allow_imm_reg_use = $allow_imm_reg_use;
    }

    /**
     * @return mixed
     */
    public function getAllowImmInfoShare()
    {
        return $this->allow_imm_info_share;
    }

    /**
     * @param mixed $allow_imm_info_share
     */
    public function setAllowImmInfoShare($allow_imm_info_share): void
    {
        $this->allow_imm_info_share = $allow_imm_info_share;
    }

    /**
     * @return mixed
     */
    public function getAllowHealthInfoEx()
    {
        return $this->allow_health_info_ex;
    }

    /**
     * @param mixed $allow_health_info_ex
     */
    public function setAllowHealthInfoEx($allow_health_info_ex): void
    {
        $this->allow_health_info_ex = $allow_health_info_ex;
    }

    /**
     * @return mixed
     */
    public function getAllowPatientPortal()
    {
        return $this->allow_patient_portal;
    }

    /**
     * @param mixed $allow_patient_portal
     */
    public function setAllowPatientPortal($allow_patient_portal): void
    {
        $this->allow_patient_portal = $allow_patient_portal;
    }

    /**
     * @return mixed
     */
    public function getDeceasedDate()
    {
        return $this->deceased_date;
    }

    /**
     * @param mixed $deceased_date
     */
    public function setDeceasedDate($deceased_date): void
    {
        $this->deceased_date = $deceased_date;
    }

    /**
     * @return mixed
     */
    public function getSoapImportStatus()
    {
        return $this->soap_import_status;
    }

    /**
     * @param mixed $soap_import_status
     */
    public function setSoapImportStatus($soap_import_status): void
    {
        $this->soap_import_status = $soap_import_status;
    }

    /**
     * @return mixed
     */
    public function getCmsportalLogin()
    {
        return $this->cmsportal_login;
    }

    /**
     * @param mixed $cmsportal_login
     */
    public function setCmsportalLogin($cmsportal_login): void
    {
        $this->cmsportal_login = $cmsportal_login;
    }

    /**
     * @return mixed
     */
    public function getCareTeam()
    {
        return $this->care_team;
    }

    /**
     * @param mixed $care_team
     */
    public function setCareTeam($care_team): void
    {
        $this->care_team = $care_team;
    }

    /**
     * @return mixed
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @param mixed $county
     */
    public function setCounty($county): void
    {
        $this->county = $county;
    }

    /**
     * @return mixed
     */
    public function getEmploymentStatus()
    {
        return $this->employment_status;
    }

    /**
     * @param mixed $employment_status
     */
    public function setEmploymentStatus($employment_status): void
    {
        $this->employment_status = $employment_status;
    }

    /**
     * @return mixed
     */
    public function getImmRegStatus()
    {
        return $this->imm_reg_status;
    }

    /**
     * @param mixed $imm_reg_status
     */
    public function setImmRegStatus($imm_reg_status): void
    {
        $this->imm_reg_status = $imm_reg_status;
    }

    /**
     * @return mixed
     */
    public function getImmRegStatEffdate()
    {
        return $this->imm_reg_stat_effdate;
    }

    /**
     * @param mixed $imm_reg_stat_effdate
     */
    public function setImmRegStatEffdate($imm_reg_stat_effdate): void
    {
        $this->imm_reg_stat_effdate = $imm_reg_stat_effdate;
    }

    /**
     * @return mixed
     */
    public function getPublicityCode()
    {
        return $this->publicity_code;
    }

    /**
     * @param mixed $publicity_code
     */
    public function setPublicityCode($publicity_code): void
    {
        $this->publicity_code = $publicity_code;
    }

    /**
     * @return mixed
     */
    public function getPublCodeEffDate()
    {
        return $this->publ_code_eff_date;
    }

    /**
     * @param mixed $publ_code_eff_date
     */
    public function setPublCodeEffDate($publ_code_eff_date): void
    {
        $this->publ_code_eff_date = $publ_code_eff_date;
    }

    /**
     * @return mixed
     */
    public function getProtectIndicator()
    {
        return $this->protect_indicator;
    }

    /**
     * @param mixed $protect_indicator
     */
    public function setProtectIndicator($protect_indicator): void
    {
        $this->protect_indicator = $protect_indicator;
    }

    /**
     * @return mixed
     */
    public function getProtIndiEffdate()
    {
        return $this->prot_indi_effdate;
    }

    /**
     * @param mixed $prot_indi_effdate
     */
    public function setProtIndiEffdate($prot_indi_effdate): void
    {
        $this->prot_indi_effdate = $prot_indi_effdate;
    }

    /**
     * @return mixed
     */
    public function getGuardianrelationship()
    {
        return $this->guardianrelationship;
    }

    /**
     * @param mixed $guardianrelationship
     */
    public function setGuardianrelationship($guardianrelationship): void
    {
        $this->guardianrelationship = $guardianrelationship;
    }

    /**
     * @return mixed
     */
    public function getGuardiansex()
    {
        return $this->guardiansex;
    }

    /**
     * @param mixed $guardiansex
     */
    public function setGuardiansex($guardiansex): void
    {
        $this->guardiansex = $guardiansex;
    }

    /**
     * @return mixed
     */
    public function getGuardianaddress()
    {
        return $this->guardianaddress;
    }

    /**
     * @param mixed $guardianaddress
     */
    public function setGuardianaddress($guardianaddress): void
    {
        $this->guardianaddress = $guardianaddress;
    }

    /**
     * @return mixed
     */
    public function getGuardiancity()
    {
        return $this->guardiancity;
    }

    /**
     * @param mixed $guardiancity
     */
    public function setGuardiancity($guardiancity): void
    {
        $this->guardiancity = $guardiancity;
    }

    /**
     * @return mixed
     */
    public function getGuardianstate()
    {
        return $this->guardianstate;
    }

    /**
     * @param mixed $guardianstate
     */
    public function setGuardianstate($guardianstate): void
    {
        $this->guardianstate = $guardianstate;
    }

    /**
     * @return mixed
     */
    public function getGuardianpostalcode()
    {
        return $this->guardianpostalcode;
    }

    /**
     * @param mixed $guardianpostalcode
     */
    public function setGuardianpostalcode($guardianpostalcode): void
    {
        $this->guardianpostalcode = $guardianpostalcode;
    }

    /**
     * @return mixed
     */
    public function getGuardiancountry()
    {
        return $this->guardiancountry;
    }

    /**
     * @param mixed $guardiancountry
     */
    public function setGuardiancountry($guardiancountry): void
    {
        $this->guardiancountry = $guardiancountry;
    }

    /**
     * @return mixed
     */
    public function getGuardianphone()
    {
        return $this->guardianphone;
    }

    /**
     * @param mixed $guardianphone
     */
    public function setGuardianphone($guardianphone): void
    {
        $this->guardianphone = $guardianphone;
    }

    /**
     * @return mixed
     */
    public function getGuardianaltphone()
    {
        return $this->guardianaltphone;
    }

    /**
     * @param mixed $guardianaltphone
     */
    public function setGuardianaltphone($guardianaltphone): void
    {
        $this->guardianaltphone = $guardianaltphone;
    }

    /**
     * @return mixed
     */
    public function getGuardianemail()
    {
        return $this->guardianemail;
    }

    /**
     * @param mixed $guardianemail
     */
    public function setGuardianemail($guardianemail): void
    {
        $this->guardianemail = $guardianemail;
    }

    /**
     * @return mixed
     */
    public function getReferraldate()
    {
        return $this->referraldate;
    }

    /**
     * @param mixed $referraldate
     */
    public function setReferraldate($referraldate): void
    {
        $this->referraldate = $referraldate;
    }

    /**
     * @return mixed
     */
    public function getReferrcompany()
    {
        return $this->referrcompany;
    }

    /**
     * @param mixed $referrcompany
     */
    public function setReferrcompany($referrcompany): void
    {
        $this->referrcompany = $referrcompany;
    }

    /**
     * @return mixed
     */
    public function getReferrperson()
    {
        return $this->referrperson;
    }

    /**
     * @param mixed $referrperson
     */
    public function setReferrperson($referrperson): void
    {
        $this->referrperson = $referrperson;
    }

    /**
     * @return mixed
     */
    public function getReferralphone()
    {
        return $this->referralphone;
    }

    /**
     * @param mixed $referralphone
     */
    public function setReferralphone($referralphone): void
    {
        $this->referralphone = $referralphone;
    }

    /**
     * @return mixed
     */
    public function getReferralemail()
    {
        return $this->referralemail;
    }

    /**
     * @param mixed $referralemail
     */
    public function setReferralemail($referralemail): void
    {
        $this->referralemail = $referralemail;
    }

    /**
     * @return mixed
     */
    public function getProviderInfo()
    {
        return $this->provider_info;
    }

    /**
     * @param mixed $provider_info
     */
    public function setProviderInfo($provider_info): void
    {
        $this->provider_info = $provider_info;
    }

    /**
     * @return mixed
     */
    public function getNname()
    {
        return $this->nname;
    }

    /**
     * @param mixed $nname
     */
    public function setNname($nname): void
    {
        $this->nname = $nname;
    }

    /**
     * @return mixed
     */
    public function getPayorInfo()
    {
        return $this->payorInfo;
    }

    /**
     * @param mixed $payorInfo
     */
    public function setPayorInfo($payorInfo): void
    {
        $this->payorInfo = $payorInfo;
    }

    /**
     * @return mixed
     */
    public function getEstcopay()
    {
        return $this->estcopay;
    }

    /**
     * @param mixed $estcopay
     */
    public function setEstcopay($estcopay): void
    {
        $this->estcopay = $estcopay;
    }

    /**
     * @return mixed
     */
    public function getEmergencyEmail()
    {
        return $this->emergency_email;
    }

    /**
     * @param mixed $emergency_email
     */
    public function setEmergencyEmail($emergency_email): void
    {
        $this->emergency_email = $emergency_email;
    }

    /**
     * @return mixed
     */
    public function getAltcont1()
    {
        return $this->altcont1;
    }

    /**
     * @param mixed $altcont1
     */
    public function setAltcont1($altcont1): void
    {
        $this->altcont1 = $altcont1;
    }

    /**
     * @return mixed
     */
    public function getAlt1name()
    {
        return $this->alt1name;
    }

    /**
     * @param mixed $alt1name
     */
    public function setAlt1name($alt1name): void
    {
        $this->alt1name = $alt1name;
    }

    /**
     * @return mixed
     */
    public function getAlt1relationship()
    {
        return $this->alt1relationship;
    }

    /**
     * @param mixed $alt1relationship
     */
    public function setAlt1relationship($alt1relationship): void
    {
        $this->alt1relationship = $alt1relationship;
    }

    /**
     * @return mixed
     */
    public function getAlt1address()
    {
        return $this->alt1address;
    }

    /**
     * @param mixed $alt1address
     */
    public function setAlt1address($alt1address): void
    {
        $this->alt1address = $alt1address;
    }

    /**
     * @return mixed
     */
    public function getAlt1city()
    {
        return $this->alt1city;
    }

    /**
     * @param mixed $alt1city
     */
    public function setAlt1city($alt1city): void
    {
        $this->alt1city = $alt1city;
    }

    /**
     * @return mixed
     */
    public function getAlt1state()
    {
        return $this->alt1state;
    }

    /**
     * @param mixed $alt1state
     */
    public function setAlt1state($alt1state): void
    {
        $this->alt1state = $alt1state;
    }

    /**
     * @return mixed
     */
    public function getAlt1postal()
    {
        return $this->alt1postal;
    }

    /**
     * @param mixed $alt1postal
     */
    public function setAlt1postal($alt1postal): void
    {
        $this->alt1postal = $alt1postal;
    }

    /**
     * @return mixed
     */
    public function getAlt1phone1()
    {
        return $this->alt1phone1;
    }

    /**
     * @param mixed $alt1phone1
     */
    public function setAlt1phone1($alt1phone1): void
    {
        $this->alt1phone1 = $alt1phone1;
    }

    /**
     * @return mixed
     */
    public function getAlt1phone2()
    {
        return $this->alt1phone2;
    }

    /**
     * @param mixed $alt1phone2
     */
    public function setAlt1phone2($alt1phone2): void
    {
        $this->alt1phone2 = $alt1phone2;
    }

    /**
     * @return mixed
     */
    public function getAlt1notes()
    {
        return $this->alt1notes;
    }

    /**
     * @param mixed $alt1notes
     */
    public function setAlt1notes($alt1notes): void
    {
        $this->alt1notes = $alt1notes;
    }

    /**
     * @return mixed
     */
    public function getAlt1email()
    {
        return $this->alt1email;
    }

    /**
     * @param mixed $alt1email
     */
    public function setAlt1email($alt1email): void
    {
        $this->alt1email = $alt1email;
    }

    /**
     * @return mixed
     */
    public function getAlt1address2()
    {
        return $this->alt1address2;
    }

    /**
     * @param mixed $alt1address2
     */
    public function setAlt1address2($alt1address2): void
    {
        $this->alt1address2 = $alt1address2;
    }

    /**
     * @return mixed
     */
    public function getPtfacility()
    {
        return $this->ptfacility;
    }

    /**
     * @param mixed $ptfacility
     */
    public function setPtfacility($ptfacility): void
    {
        $this->ptfacility = $ptfacility;
    }

    /**
     * @return mixed
     */
    public function getProviderIDalt()
    {
        return $this->providerIDalt;
    }

    /**
     * @param mixed $providerIDalt
     */
    public function setProviderIDalt($providerIDalt): void
    {
        $this->providerIDalt = $providerIDalt;
    }

    /**
     * @return mixed
     */
    public function getGenericval2()
    {
        return $this->genericval2;
    }

    /**
     * @param mixed $genericval2
     */
    public function setGenericval2($genericval2): void
    {
        $this->genericval2 = $genericval2;
    }

    /**
     * @return mixed
     */
    public function getEmergencyRelat()
    {
        return $this->emergency_relat;
    }

    /**
     * @param mixed $emergency_relat
     */
    public function setEmergencyRelat($emergency_relat): void
    {
        $this->emergency_relat = $emergency_relat;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param mixed $address2
     */
    public function setAddress2($address2): void
    {
        $this->address2 = $address2;
    }

    /**
     * @return mixed
     */
    public function getEmailContact()
    {
        return $this->email_contact;
    }

    /**
     * @param mixed $email_contact
     */
    public function setEmailContact($email_contact): void
    {
        $this->email_contact = $email_contact;
    }

    /**
     * @return mixed
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * @param mixed $relationship
     */
    public function setRelationship($relationship): void
    {
        $this->relationship = $relationship;
    }

    /**
     * @return mixed
     */
    public function getResaddress1()
    {
        return $this->resaddress1;
    }

    /**
     * @param mixed $resaddress1
     */
    public function setResaddress1($resaddress1): void
    {
        $this->resaddress1 = $resaddress1;
    }

    /**
     * @return mixed
     */
    public function getResaddress2()
    {
        return $this->resaddress2;
    }

    /**
     * @param mixed $resaddress2
     */
    public function setResaddress2($resaddress2): void
    {
        $this->resaddress2 = $resaddress2;
    }

    /**
     * @return mixed
     */
    public function getRescity()
    {
        return $this->rescity;
    }

    /**
     * @param mixed $rescity
     */
    public function setRescity($rescity): void
    {
        $this->rescity = $rescity;
    }

    /**
     * @return mixed
     */
    public function getResstate()
    {
        return $this->resstate;
    }

    /**
     * @param mixed $resstate
     */
    public function setResstate($resstate): void
    {
        $this->resstate = $resstate;
    }

    /**
     * @return mixed
     */
    public function getRespostal()
    {
        return $this->respostal;
    }

    /**
     * @param mixed $respostal
     */
    public function setRespostal($respostal): void
    {
        $this->respostal = $respostal;
    }

    /**
     * @return mixed
     */
    public function getResfacility()
    {
        return $this->resfacility;
    }

    /**
     * @param mixed $resfacility
     */
    public function setResfacility($resfacility): void
    {
        $this->resfacility = $resfacility;
    }

    /**
     * @return mixed
     */
    public function getResphone()
    {
        return $this->resphone;
    }

    /**
     * @param mixed $resphone
     */
    public function setResphone($resphone): void
    {
        $this->resphone = $resphone;
    }

    /**
     * @return mixed
     */
    public function getCuremail()
    {
        return $this->curemail;
    }

    /**
     * @param mixed $curemail
     */
    public function setCuremail($curemail): void
    {
        $this->curemail = $curemail;
    }

    /**
     * @return mixed
     */
    public function getResnotes()
    {
        return $this->resnotes;
    }

    /**
     * @param mixed $resnotes
     */
    public function setResnotes($resnotes): void
    {
        $this->resnotes = $resnotes;
    }
}
