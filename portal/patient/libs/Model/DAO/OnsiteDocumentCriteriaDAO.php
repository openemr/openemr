<?php

/**
 * OnsiteDocumentCriteriaDAO.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** import supporting libraries */
require_once("verysimple/Phreeze/Criteria.php");

/**
 * OnsiteDocumentCriteria allows custom querying for the OnsiteDocument object.
 *
 * WARNING: THIS IS AN AUTO-GENERATED FILE
 *
 * This file should generally not be edited by hand except in special circumstances.
 * Add any custom business logic to the ModelCriteria class which is extended from this class.
 * Leaving this file alone will allow easy re-generation of all DAOs in the event of schema changes
 *
 * @inheritdocs
 * @package Openemr::Model::DAO
 * @author ClassBuilder
 * @version 1.0
 */
class OnsiteDocumentCriteriaDAO extends Criteria
{

    public $Id_Equals;
    public $Id_NotEquals;
    public $Id_IsLike;
    public $Id_IsNotLike;
    public $Id_BeginsWith;
    public $Id_EndsWith;
    public $Id_GreaterThan;
    public $Id_GreaterThanOrEqual;
    public $Id_LessThan;
    public $Id_LessThanOrEqual;
    public $Id_In;
    public $Id_IsNotEmpty;
    public $Id_IsEmpty;
    public $Id_BitwiseOr;
    public $Id_BitwiseAnd;
    public $Pid_Equals;
    public $Pid_NotEquals;
    public $Pid_IsLike;
    public $Pid_IsNotLike;
    public $Pid_BeginsWith;
    public $Pid_EndsWith;
    public $Pid_GreaterThan;
    public $Pid_GreaterThanOrEqual;
    public $Pid_LessThan;
    public $Pid_LessThanOrEqual;
    public $Pid_In;
    public $Pid_IsNotEmpty;
    public $Pid_IsEmpty;
    public $Pid_BitwiseOr;
    public $Pid_BitwiseAnd;
    public $Facility_Equals;
    public $Facility_NotEquals;
    public $Facility_IsLike;
    public $Facility_IsNotLike;
    public $Facility_BeginsWith;
    public $Facility_EndsWith;
    public $Facility_GreaterThan;
    public $Facility_GreaterThanOrEqual;
    public $Facility_LessThan;
    public $Facility_LessThanOrEqual;
    public $Facility_In;
    public $Facility_IsNotEmpty;
    public $Facility_IsEmpty;
    public $Facility_BitwiseOr;
    public $Facility_BitwiseAnd;
    public $Provider_Equals;
    public $Provider_NotEquals;
    public $Provider_IsLike;
    public $Provider_IsNotLike;
    public $Provider_BeginsWith;
    public $Provider_EndsWith;
    public $Provider_GreaterThan;
    public $Provider_GreaterThanOrEqual;
    public $Provider_LessThan;
    public $Provider_LessThanOrEqual;
    public $Provider_In;
    public $Provider_IsNotEmpty;
    public $Provider_IsEmpty;
    public $Provider_BitwiseOr;
    public $Provider_BitwiseAnd;
    public $Encounter_Equals;
    public $Encounter_NotEquals;
    public $Encounter_IsLike;
    public $Encounter_IsNotLike;
    public $Encounter_BeginsWith;
    public $Encounter_EndsWith;
    public $Encounter_GreaterThan;
    public $Encounter_GreaterThanOrEqual;
    public $Encounter_LessThan;
    public $Encounter_LessThanOrEqual;
    public $Encounter_In;
    public $Encounter_IsNotEmpty;
    public $Encounter_IsEmpty;
    public $Encounter_BitwiseOr;
    public $Encounter_BitwiseAnd;
    public $CreateDate_Equals;
    public $CreateDate_NotEquals;
    public $CreateDate_IsLike;
    public $CreateDate_IsNotLike;
    public $CreateDate_BeginsWith;
    public $CreateDate_EndsWith;
    public $CreateDate_GreaterThan;
    public $CreateDate_GreaterThanOrEqual;
    public $CreateDate_LessThan;
    public $CreateDate_LessThanOrEqual;
    public $CreateDate_In;
    public $CreateDate_IsNotEmpty;
    public $CreateDate_IsEmpty;
    public $CreateDate_BitwiseOr;
    public $CreateDate_BitwiseAnd;
    public $DocType_Equals;
    public $DocType_NotEquals;
    public $DocType_IsLike;
    public $DocType_IsNotLike;
    public $DocType_BeginsWith;
    public $DocType_EndsWith;
    public $DocType_GreaterThan;
    public $DocType_GreaterThanOrEqual;
    public $DocType_LessThan;
    public $DocType_LessThanOrEqual;
    public $DocType_In;
    public $DocType_IsNotEmpty;
    public $DocType_IsEmpty;
    public $DocType_BitwiseOr;
    public $DocType_BitwiseAnd;
    public $PatientSignedStatus_Equals;
    public $PatientSignedStatus_NotEquals;
    public $PatientSignedStatus_IsLike;
    public $PatientSignedStatus_IsNotLike;
    public $PatientSignedStatus_BeginsWith;
    public $PatientSignedStatus_EndsWith;
    public $PatientSignedStatus_GreaterThan;
    public $PatientSignedStatus_GreaterThanOrEqual;
    public $PatientSignedStatus_LessThan;
    public $PatientSignedStatus_LessThanOrEqual;
    public $PatientSignedStatus_In;
    public $PatientSignedStatus_IsNotEmpty;
    public $PatientSignedStatus_IsEmpty;
    public $PatientSignedStatus_BitwiseOr;
    public $PatientSignedStatus_BitwiseAnd;
    public $PatientSignedTime_Equals;
    public $PatientSignedTime_NotEquals;
    public $PatientSignedTime_IsLike;
    public $PatientSignedTime_IsNotLike;
    public $PatientSignedTime_BeginsWith;
    public $PatientSignedTime_EndsWith;
    public $PatientSignedTime_GreaterThan;
    public $PatientSignedTime_GreaterThanOrEqual;
    public $PatientSignedTime_LessThan;
    public $PatientSignedTime_LessThanOrEqual;
    public $PatientSignedTime_In;
    public $PatientSignedTime_IsNotEmpty;
    public $PatientSignedTime_IsEmpty;
    public $PatientSignedTime_BitwiseOr;
    public $PatientSignedTime_BitwiseAnd;
    public $AuthorizeSignedTime_Equals;
    public $AuthorizeSignedTime_NotEquals;
    public $AuthorizeSignedTime_IsLike;
    public $AuthorizeSignedTime_IsNotLike;
    public $AuthorizeSignedTime_BeginsWith;
    public $AuthorizeSignedTime_EndsWith;
    public $AuthorizeSignedTime_GreaterThan;
    public $AuthorizeSignedTime_GreaterThanOrEqual;
    public $AuthorizeSignedTime_LessThan;
    public $AuthorizeSignedTime_LessThanOrEqual;
    public $AuthorizeSignedTime_In;
    public $AuthorizeSignedTime_IsNotEmpty;
    public $AuthorizeSignedTime_IsEmpty;
    public $AuthorizeSignedTime_BitwiseOr;
    public $AuthorizeSignedTime_BitwiseAnd;
    public $AcceptSignedStatus_Equals;
    public $AcceptSignedStatus_NotEquals;
    public $AcceptSignedStatus_IsLike;
    public $AcceptSignedStatus_IsNotLike;
    public $AcceptSignedStatus_BeginsWith;
    public $AcceptSignedStatus_EndsWith;
    public $AcceptSignedStatus_GreaterThan;
    public $AcceptSignedStatus_GreaterThanOrEqual;
    public $AcceptSignedStatus_LessThan;
    public $AcceptSignedStatus_LessThanOrEqual;
    public $AcceptSignedStatus_In;
    public $AcceptSignedStatus_IsNotEmpty;
    public $AcceptSignedStatus_IsEmpty;
    public $AcceptSignedStatus_BitwiseOr;
    public $AcceptSignedStatus_BitwiseAnd;
    public $AuthorizingSignator_Equals;
    public $AuthorizingSignator_NotEquals;
    public $AuthorizingSignator_IsLike;
    public $AuthorizingSignator_IsNotLike;
    public $AuthorizingSignator_BeginsWith;
    public $AuthorizingSignator_EndsWith;
    public $AuthorizingSignator_GreaterThan;
    public $AuthorizingSignator_GreaterThanOrEqual;
    public $AuthorizingSignator_LessThan;
    public $AuthorizingSignator_LessThanOrEqual;
    public $AuthorizingSignator_In;
    public $AuthorizingSignator_IsNotEmpty;
    public $AuthorizingSignator_IsEmpty;
    public $AuthorizingSignator_BitwiseOr;
    public $AuthorizingSignator_BitwiseAnd;
    public $ReviewDate_Equals;
    public $ReviewDate_NotEquals;
    public $ReviewDate_IsLike;
    public $ReviewDate_IsNotLike;
    public $ReviewDate_BeginsWith;
    public $ReviewDate_EndsWith;
    public $ReviewDate_GreaterThan;
    public $ReviewDate_GreaterThanOrEqual;
    public $ReviewDate_LessThan;
    public $ReviewDate_LessThanOrEqual;
    public $ReviewDate_In;
    public $ReviewDate_IsNotEmpty;
    public $ReviewDate_IsEmpty;
    public $ReviewDate_BitwiseOr;
    public $ReviewDate_BitwiseAnd;
    public $DenialReason_Equals;
    public $DenialReason_NotEquals;
    public $DenialReason_IsLike;
    public $DenialReason_IsNotLike;
    public $DenialReason_BeginsWith;
    public $DenialReason_EndsWith;
    public $DenialReason_GreaterThan;
    public $DenialReason_GreaterThanOrEqual;
    public $DenialReason_LessThan;
    public $DenialReason_LessThanOrEqual;
    public $DenialReason_In;
    public $DenialReason_IsNotEmpty;
    public $DenialReason_IsEmpty;
    public $DenialReason_BitwiseOr;
    public $DenialReason_BitwiseAnd;
    public $AuthorizedSignature_Equals;
    public $AuthorizedSignature_NotEquals;
    public $AuthorizedSignature_IsLike;
    public $AuthorizedSignature_IsNotLike;
    public $AuthorizedSignature_BeginsWith;
    public $AuthorizedSignature_EndsWith;
    public $AuthorizedSignature_GreaterThan;
    public $AuthorizedSignature_GreaterThanOrEqual;
    public $AuthorizedSignature_LessThan;
    public $AuthorizedSignature_LessThanOrEqual;
    public $AuthorizedSignature_In;
    public $AuthorizedSignature_IsNotEmpty;
    public $AuthorizedSignature_IsEmpty;
    public $AuthorizedSignature_BitwiseOr;
    public $AuthorizedSignature_BitwiseAnd;
    public $PatientSignature_Equals;
    public $PatientSignature_NotEquals;
    public $PatientSignature_IsLike;
    public $PatientSignature_IsNotLike;
    public $PatientSignature_BeginsWith;
    public $PatientSignature_EndsWith;
    public $PatientSignature_GreaterThan;
    public $PatientSignature_GreaterThanOrEqual;
    public $PatientSignature_LessThan;
    public $PatientSignature_LessThanOrEqual;
    public $PatientSignature_In;
    public $PatientSignature_IsNotEmpty;
    public $PatientSignature_IsEmpty;
    public $PatientSignature_BitwiseOr;
    public $PatientSignature_BitwiseAnd;
    public $FullDocument_Equals;
    public $FullDocument_NotEquals;
    public $FullDocument_IsLike;
    public $FullDocument_IsNotLike;
    public $FullDocument_BeginsWith;
    public $FullDocument_EndsWith;
    public $FullDocument_GreaterThan;
    public $FullDocument_GreaterThanOrEqual;
    public $FullDocument_LessThan;
    public $FullDocument_LessThanOrEqual;
    public $FullDocument_In;
    public $FullDocument_IsNotEmpty;
    public $FullDocument_IsEmpty;
    public $FullDocument_BitwiseOr;
    public $FullDocument_BitwiseAnd;
    public $FileName_Equals;
    public $FileName_NotEquals;
    public $FileName_IsLike;
    public $FileName_IsNotLike;
    public $FileName_BeginsWith;
    public $FileName_EndsWith;
    public $FileName_GreaterThan;
    public $FileName_GreaterThanOrEqual;
    public $FileName_LessThan;
    public $FileName_LessThanOrEqual;
    public $FileName_In;
    public $FileName_IsNotEmpty;
    public $FileName_IsEmpty;
    public $FileName_BitwiseOr;
    public $FileName_BitwiseAnd;
    public $FilePath_Equals;
    public $FilePath_NotEquals;
    public $FilePath_IsLike;
    public $FilePath_IsNotLike;
    public $FilePath_BeginsWith;
    public $FilePath_EndsWith;
    public $FilePath_GreaterThan;
    public $FilePath_GreaterThanOrEqual;
    public $FilePath_LessThan;
    public $FilePath_LessThanOrEqual;
    public $FilePath_In;
    public $FilePath_IsNotEmpty;
    public $FilePath_IsEmpty;
    public $FilePath_BitwiseOr;
    public $FilePath_BitwiseAnd;
}
