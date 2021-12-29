<?php

/**
 * OnsiteDocumentReporter.php
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
 * This is an example Reporter based on the OnsiteDocument object.  The reporter object
 * allows you to run arbitrary queries that return data which may or may not fith within
 * the data access API.  This can include aggregate data or subsets of data.
 *
 * Note that Reporters are read-only and cannot be used for saving data.
 *
 * @package Openemr::Model::DAO
 * @author ClassBuilder
 * @version 1.0
 */
class OnsiteDocumentReporter extends Reporter
{

    // the properties in this class must match the columns returned by GetCustomQuery().
    // 'CustomFieldExample' is an example that is not part of the `onsite_documents` table
    public $CustomFieldExample;

    public $Id;
    public $Pid;
    public $Facility;
    public $Provider;
    public $Encounter;
    public $CreateDate;
    public $DocType;
    public $PatientSignedStatus;
    public $PatientSignedTime;
    public $AuthorizeSignedTime;
    public $AcceptSignedStatus;
    public $AuthorizingSignator;
    public $ReviewDate;
    public $DenialReason;
    public $AuthorizedSignature;
    public $PatientSignature;
    public $FullDocument;
    public $FileName;
    public $FilePath;

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
			'custom value here...' as CustomFieldExample
			,`onsite_documents`.`id` as Id
			,`onsite_documents`.`pid` as Pid
			,`onsite_documents`.`facility` as Facility
			,`onsite_documents`.`provider` as Provider
			,`onsite_documents`.`encounter` as Encounter
			,`onsite_documents`.`create_date` as CreateDate
			,`onsite_documents`.`doc_type` as DocType
			,`onsite_documents`.`patient_signed_status` as PatientSignedStatus
			,`onsite_documents`.`patient_signed_time` as PatientSignedTime
			,`onsite_documents`.`authorize_signed_time` as AuthorizeSignedTime
			,`onsite_documents`.`accept_signed_status` as AcceptSignedStatus
			,`onsite_documents`.`authorizing_signator` as AuthorizingSignator
			,`onsite_documents`.`review_date` as ReviewDate
			,`onsite_documents`.`denial_reason` as DenialReason
			,`onsite_documents`.`authorized_signature` as AuthorizedSignature
			,`onsite_documents`.`patient_signature` as PatientSignature
			,`onsite_documents`.`full_document` as FullDocument
			,`onsite_documents`.`file_name` as FileName
			,`onsite_documents`.`file_path` as FilePath
		from `onsite_documents`";

        // the criteria can be used or you can write your own custom logic.
        // be sure to escape any user input with $criteria->Escape()
        $sql .= $criteria->GetWhere();
        $sql .= $criteria->GetOrder();

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
        $sql = "select count(1) as counter from `onsite_documents`";

        // the criteria can be used or you can write your own custom logic.
        // be sure to escape any user input with $criteria->Escape()
        $sql .= $criteria->GetWhere();

        return $sql;
    }
}
