<?php

/**
 * PatientReporter.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** import supporting libraries */
require_once("verysimple/Phreeze/Reporter.php");

/**
 * This is an example Reporter based on the Patient object.  The reporter object
 * allows you to run arbitrary queries that return data which may or may not fith within
 * the data access API.  This can include aggregate data or subsets of data.
 *
 * Note that Reporters are read-only and cannot be used for saving data.
 *
 * @package Openemr::Model::DAO
 * @author ClassBuilder
 * @version 1.0
 */
class PatientReporter extends Reporter
{

    // the properties in this class must match the columns returned by GetCustomQuery().
    public $Id;
    public $Title;
    public $Language;
    public $Financial;
    public $Fname;
    public $Lname;
    public $Mname;
    public $Dob;
    public $Street;
    public $PostalCode;
    public $City;
    public $State;
    public $CountryCode;
    public $DriversLicense;
    public $Ss;
    public $Occupation;
    public $PhoneHome;
    public $PhoneBiz;
    public $PhoneContact;
    public $PhoneCell;
    public $PharmacyId;
    public $Status;
    public $ContactRelationship;
    public $Date;
    public $Sex;
    public $Referrer;
    public $Referrerid;
    public $Providerid;
    public $RefProviderid;
    public $Email;
    public $EmailDirect;
    public $Ethnoracial;
    public $Race;
    public $Ethnicity;
    public $Religion;
    public $Interpretter;
    public $Migrantseasonal;
    public $FamilySize;
    public $MonthlyIncome;
    public $BillingNote;
    public $Homeless;
    public $FinancialReview;
    public $Pubpid;
    public $Pid;
    public $Genericname1;
    public $Genericval1;
    public $Genericname2;
    public $Genericval2;
    public $HipaaMail;
    public $HipaaVoice;
    public $HipaaNotice;
    public $HipaaMessage;
    public $HipaaAllowsms;
    public $HipaaAllowemail;
    public $Squad;
    public $Fitness;
    public $ReferralSource;
    public $Usertext1;
    public $Usertext2;
    public $Usertext3;
    public $Usertext4;
    public $Usertext5;
    public $Usertext6;
    public $Usertext7;
    public $Usertext8;
    public $Userlist1;
    public $Userlist2;
    public $Userlist3;
    public $Userlist4;
    public $Userlist5;
    public $Userlist6;
    public $Userlist7;
    public $Pricelevel;
    public $Regdate;
    public $Contrastart;
    public $CompletedAd;
    public $AdReviewed;
    public $Vfc;
    public $Mothersname;
    public $Guardiansname;
    public $AllowImmRegUse;
    public $AllowImmInfoShare;
    public $AllowHealthInfoEx;
    public $AllowPatientPortal;
    public $DeceasedDate;
    public $DeceasedReason;
    public $SoapImportStatus;
    public $CmsportalLogin;
    public $CareTeam;
    public $County;
    public $Industry;

    /*
    * GetCustomQuery returns a fully formed SQL statement.  The result columns
    * must match with the properties of this reporter object.
    *
    * @see Reporter::GetCustomQuery
    * @param Criteria $criteria
    * @return string SQL statement
    */
    static function GetCustomQuery($criteria)
    {
        $sql = "select
			 `patient_data`.`id` as Id
			,`patient_data`.`title` as Title
			,`patient_data`.`language` as Language
			,`patient_data`.`financial` as Financial
			,`patient_data`.`fname` as Fname
			,`patient_data`.`lname` as Lname
			,`patient_data`.`mname` as Mname
			,`patient_data`.`DOB` as Dob
			,`patient_data`.`street` as Street
			,`patient_data`.`postal_code` as PostalCode
			,`patient_data`.`city` as City
			,`patient_data`.`state` as State
			,`patient_data`.`country_code` as CountryCode
			,`patient_data`.`drivers_license` as DriversLicense
			,`patient_data`.`ss` as Ss
			,`patient_data`.`occupation` as Occupation
			,`patient_data`.`phone_home` as PhoneHome
			,`patient_data`.`phone_biz` as PhoneBiz
			,`patient_data`.`phone_contact` as PhoneContact
			,`patient_data`.`phone_cell` as PhoneCell
			,`patient_data`.`pharmacy_id` as PharmacyId
			,`patient_data`.`status` as Status
			,`patient_data`.`contact_relationship` as ContactRelationship
			,`patient_data`.`date` as Date
			,`patient_data`.`sex` as Sex
			,`patient_data`.`referrer` as Referrer
			,`patient_data`.`referrerID` as Referrerid
			,`patient_data`.`providerID` as Providerid
			,`patient_data`.`ref_providerID` as RefProviderid
			,`patient_data`.`email` as Email
			,`patient_data`.`email_direct` as EmailDirect
			,`patient_data`.`ethnoracial` as Ethnoracial
			,`patient_data`.`race` as Race
			,`patient_data`.`ethnicity` as Ethnicity
			,`patient_data`.`religion` as Religion
			,`patient_data`.`interpretter` as Interpretter
			,`patient_data`.`migrantseasonal` as Migrantseasonal
			,`patient_data`.`family_size` as FamilySize
			,`patient_data`.`monthly_income` as MonthlyIncome
			,`patient_data`.`billing_note` as BillingNote
			,`patient_data`.`homeless` as Homeless
			,`patient_data`.`financial_review` as FinancialReview
			,`patient_data`.`pubpid` as Pubpid
			,`patient_data`.`pid` as Pid
			,`patient_data`.`genericname1` as Genericname1
			,`patient_data`.`genericval1` as Genericval1
			,`patient_data`.`genericname2` as Genericname2
			,`patient_data`.`genericval2` as Genericval2
			,`patient_data`.`hipaa_mail` as HipaaMail
			,`patient_data`.`hipaa_voice` as HipaaVoice
			,`patient_data`.`hipaa_notice` as HipaaNotice
			,`patient_data`.`hipaa_message` as HipaaMessage
			,`patient_data`.`hipaa_allowsms` as HipaaAllowsms
			,`patient_data`.`hipaa_allowemail` as HipaaAllowemail
			,`patient_data`.`squad` as Squad
			,`patient_data`.`fitness` as Fitness
			,`patient_data`.`referral_source` as ReferralSource
			,`patient_data`.`usertext1` as Usertext1
			,`patient_data`.`usertext2` as Usertext2
			,`patient_data`.`usertext3` as Usertext3
			,`patient_data`.`usertext4` as Usertext4
			,`patient_data`.`usertext5` as Usertext5
			,`patient_data`.`usertext6` as Usertext6
			,`patient_data`.`usertext7` as Usertext7
			,`patient_data`.`usertext8` as Usertext8
			,`patient_data`.`userlist1` as Userlist1
			,`patient_data`.`userlist2` as Userlist2
			,`patient_data`.`userlist3` as Userlist3
			,`patient_data`.`userlist4` as Userlist4
			,`patient_data`.`userlist5` as Userlist5
			,`patient_data`.`userlist6` as Userlist6
			,`patient_data`.`userlist7` as Userlist7
			,`patient_data`.`pricelevel` as Pricelevel
			,`patient_data`.`regdate` as Regdate
			,`patient_data`.`contrastart` as Contrastart
			,`patient_data`.`completed_ad` as CompletedAd
			,`patient_data`.`ad_reviewed` as AdReviewed
			,`patient_data`.`vfc` as Vfc
			,`patient_data`.`mothersname` as Mothersname
			,`patient_data`.`guardiansname` as Guardiansname
			,`patient_data`.`allow_imm_reg_use` as AllowImmRegUse
			,`patient_data`.`allow_imm_info_share` as AllowImmInfoShare
			,`patient_data`.`allow_health_info_ex` as AllowHealthInfoEx
			,`patient_data`.`allow_patient_portal` as AllowPatientPortal
			,`patient_data`.`deceased_date` as DeceasedDate
			,`patient_data`.`deceased_reason` as DeceasedReason
			,`patient_data`.`soap_import_status` as SoapImportStatus
			,`patient_data`.`cmsportal_login` as CmsportalLogin
			,`patient_data`.`care_team_provider` as CareTeam
			,`patient_data`.`county` as County
			,`patient_data`.`industry` as Industry
		from `patient_data`";

        // the criteria can be used or you can write your own custom logic.
        // be sure to escape any user input with $criteria->Escape()
        $sql .= $criteria->GetWhere();
        $sql .= $criteria->GetOrder();

        if ($criteria->Pid_Equals == 0) {
            $sql = "DESCRIBE patient_data";
        }

        return $sql;
    }

    /*
    * GetCustomCountQuery returns a fully formed SQL statement that will count
    * the results.  This query must return the correct number of results that
    * GetCustomQuery would, given the same criteria
    *
    * @see Reporter::GetCustomCountQuery
    * @param Criteria $criteria
    * @return string SQL statement
    */
    static function GetCustomCountQuery($criteria)
    {
        $sql = "select count(1) as counter from `patient_data`";

        // the criteria can be used or you can write your own custom logic.
        // be sure to escape any user input with $criteria->Escape()
        $sql .= $criteria->GetWhere();

        return $sql;
    }
}
