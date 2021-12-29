<?php

/**
 * OnsitePortalActivityCriteriaDAO.php
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
 * OnsitePortalActivityCriteria allows custom querying for the OnsitePortalActivity object.
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
class OnsitePortalActivityCriteriaDAO extends Criteria
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
    public $Date_Equals;
    public $Date_NotEquals;
    public $Date_IsLike;
    public $Date_IsNotLike;
    public $Date_BeginsWith;
    public $Date_EndsWith;
    public $Date_GreaterThan;
    public $Date_GreaterThanOrEqual;
    public $Date_LessThan;
    public $Date_LessThanOrEqual;
    public $Date_In;
    public $Date_IsNotEmpty;
    public $Date_IsEmpty;
    public $Date_BitwiseOr;
    public $Date_BitwiseAnd;
    public $PatientId_Equals;
    public $PatientId_NotEquals;
    public $PatientId_IsLike;
    public $PatientId_IsNotLike;
    public $PatientId_BeginsWith;
    public $PatientId_EndsWith;
    public $PatientId_GreaterThan;
    public $PatientId_GreaterThanOrEqual;
    public $PatientId_LessThan;
    public $PatientId_LessThanOrEqual;
    public $PatientId_In;
    public $PatientId_IsNotEmpty;
    public $PatientId_IsEmpty;
    public $PatientId_BitwiseOr;
    public $PatientId_BitwiseAnd;
    public $Activity_Equals;
    public $Activity_NotEquals;
    public $Activity_IsLike;
    public $Activity_IsNotLike;
    public $Activity_BeginsWith;
    public $Activity_EndsWith;
    public $Activity_GreaterThan;
    public $Activity_GreaterThanOrEqual;
    public $Activity_LessThan;
    public $Activity_LessThanOrEqual;
    public $Activity_In;
    public $Activity_IsNotEmpty;
    public $Activity_IsEmpty;
    public $Activity_BitwiseOr;
    public $Activity_BitwiseAnd;
    public $RequireAudit_Equals;
    public $RequireAudit_NotEquals;
    public $RequireAudit_IsLike;
    public $RequireAudit_IsNotLike;
    public $RequireAudit_BeginsWith;
    public $RequireAudit_EndsWith;
    public $RequireAudit_GreaterThan;
    public $RequireAudit_GreaterThanOrEqual;
    public $RequireAudit_LessThan;
    public $RequireAudit_LessThanOrEqual;
    public $RequireAudit_In;
    public $RequireAudit_IsNotEmpty;
    public $RequireAudit_IsEmpty;
    public $RequireAudit_BitwiseOr;
    public $RequireAudit_BitwiseAnd;
    public $PendingAction_Equals;
    public $PendingAction_NotEquals;
    public $PendingAction_IsLike;
    public $PendingAction_IsNotLike;
    public $PendingAction_BeginsWith;
    public $PendingAction_EndsWith;
    public $PendingAction_GreaterThan;
    public $PendingAction_GreaterThanOrEqual;
    public $PendingAction_LessThan;
    public $PendingAction_LessThanOrEqual;
    public $PendingAction_In;
    public $PendingAction_IsNotEmpty;
    public $PendingAction_IsEmpty;
    public $PendingAction_BitwiseOr;
    public $PendingAction_BitwiseAnd;
    public $ActionTaken_Equals;
    public $ActionTaken_NotEquals;
    public $ActionTaken_IsLike;
    public $ActionTaken_IsNotLike;
    public $ActionTaken_BeginsWith;
    public $ActionTaken_EndsWith;
    public $ActionTaken_GreaterThan;
    public $ActionTaken_GreaterThanOrEqual;
    public $ActionTaken_LessThan;
    public $ActionTaken_LessThanOrEqual;
    public $ActionTaken_In;
    public $ActionTaken_IsNotEmpty;
    public $ActionTaken_IsEmpty;
    public $ActionTaken_BitwiseOr;
    public $ActionTaken_BitwiseAnd;
    public $Status_Equals;
    public $Status_NotEquals;
    public $Status_IsLike;
    public $Status_IsNotLike;
    public $Status_BeginsWith;
    public $Status_EndsWith;
    public $Status_GreaterThan;
    public $Status_GreaterThanOrEqual;
    public $Status_LessThan;
    public $Status_LessThanOrEqual;
    public $Status_In;
    public $Status_IsNotEmpty;
    public $Status_IsEmpty;
    public $Status_BitwiseOr;
    public $Status_BitwiseAnd;
    public $Narrative_Equals;
    public $Narrative_NotEquals;
    public $Narrative_IsLike;
    public $Narrative_IsNotLike;
    public $Narrative_BeginsWith;
    public $Narrative_EndsWith;
    public $Narrative_GreaterThan;
    public $Narrative_GreaterThanOrEqual;
    public $Narrative_LessThan;
    public $Narrative_LessThanOrEqual;
    public $Narrative_In;
    public $Narrative_IsNotEmpty;
    public $Narrative_IsEmpty;
    public $Narrative_BitwiseOr;
    public $Narrative_BitwiseAnd;
    public $TableAction_Equals;
    public $TableAction_NotEquals;
    public $TableAction_IsLike;
    public $TableAction_IsNotLike;
    public $TableAction_BeginsWith;
    public $TableAction_EndsWith;
    public $TableAction_GreaterThan;
    public $TableAction_GreaterThanOrEqual;
    public $TableAction_LessThan;
    public $TableAction_LessThanOrEqual;
    public $TableAction_In;
    public $TableAction_IsNotEmpty;
    public $TableAction_IsEmpty;
    public $TableAction_BitwiseOr;
    public $TableAction_BitwiseAnd;
    public $TableArgs_Equals;
    public $TableArgs_NotEquals;
    public $TableArgs_IsLike;
    public $TableArgs_IsNotLike;
    public $TableArgs_BeginsWith;
    public $TableArgs_EndsWith;
    public $TableArgs_GreaterThan;
    public $TableArgs_GreaterThanOrEqual;
    public $TableArgs_LessThan;
    public $TableArgs_LessThanOrEqual;
    public $TableArgs_In;
    public $TableArgs_IsNotEmpty;
    public $TableArgs_IsEmpty;
    public $TableArgs_BitwiseOr;
    public $TableArgs_BitwiseAnd;
    public $ActionUser_Equals;
    public $ActionUser_NotEquals;
    public $ActionUser_IsLike;
    public $ActionUser_IsNotLike;
    public $ActionUser_BeginsWith;
    public $ActionUser_EndsWith;
    public $ActionUser_GreaterThan;
    public $ActionUser_GreaterThanOrEqual;
    public $ActionUser_LessThan;
    public $ActionUser_LessThanOrEqual;
    public $ActionUser_In;
    public $ActionUser_IsNotEmpty;
    public $ActionUser_IsEmpty;
    public $ActionUser_BitwiseOr;
    public $ActionUser_BitwiseAnd;
    public $ActionTakenTime_Equals;
    public $ActionTakenTime_NotEquals;
    public $ActionTakenTime_IsLike;
    public $ActionTakenTime_IsNotLike;
    public $ActionTakenTime_BeginsWith;
    public $ActionTakenTime_EndsWith;
    public $ActionTakenTime_GreaterThan;
    public $ActionTakenTime_GreaterThanOrEqual;
    public $ActionTakenTime_LessThan;
    public $ActionTakenTime_LessThanOrEqual;
    public $ActionTakenTime_In;
    public $ActionTakenTime_IsNotEmpty;
    public $ActionTakenTime_IsEmpty;
    public $ActionTakenTime_BitwiseOr;
    public $ActionTakenTime_BitwiseAnd;
    public $Checksum_Equals;
    public $Checksum_NotEquals;
    public $Checksum_IsLike;
    public $Checksum_IsNotLike;
    public $Checksum_BeginsWith;
    public $Checksum_EndsWith;
    public $Checksum_GreaterThan;
    public $Checksum_GreaterThanOrEqual;
    public $Checksum_LessThan;
    public $Checksum_LessThanOrEqual;
    public $Checksum_In;
    public $Checksum_IsNotEmpty;
    public $Checksum_IsEmpty;
    public $Checksum_BitwiseOr;
    public $Checksum_BitwiseAnd;
}
