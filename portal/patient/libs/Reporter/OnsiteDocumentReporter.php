<?php
/** @package    Openemr::Reporter */

/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
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

?>