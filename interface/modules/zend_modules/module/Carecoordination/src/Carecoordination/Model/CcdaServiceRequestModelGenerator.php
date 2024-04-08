<?php

/**
 * CcdaServiceRequestModelGenerator is responsible for generating an xml model file that is used to generate a CCDA file by the ccda generator
 * service.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Riju K P <rijukp@zhservices.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

use OpenEMR\Services\EncounterService;
use OpenEMR\Services\PatientService;
use OpenEMR\Validators\ProcessingResult;

class CcdaServiceRequestModelGenerator
{
    private $data;
    private $createdtime;
    private $exportwithDocuments;

    /**
     * @var EncounterccdadispatchTable
     */
    private $encounterCCDADispatchTable;

    public function __construct(EncounterccdadispatchTable $table)
    {
        $this->encounterCCDADispatchTable = $table;
        $this->data = "";
        $this->exportwithDocuments = $_REQUEST['with_documents'] ?? false;
    }
    public function getEncounterccdadispatchTable(): EncounterccdadispatchTable
    {
        return $this->encounterCCDADispatchTable;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getCreatedTime(): int
    {
        return $this->createdtime;
    }

    private function getServiceStartDates($pid, $encounter, $document_type, $date_options)
    {
        $start = $date_options['date_start'];
        $end = $date_options['date_end'];
        $document_type = $document_type ?? "ccd"; // default to ccd
        if ($document_type == 'referral') {
            // we are basing things on a single encounter so we need to try and grab our encounter date
            if (!empty($encounter)) {
                // we can adjust our dates here
                // populate our dates based on our encounter
                $encounterService = new EncounterService();
                $encounterRecord = ProcessingResult::extractDataArray($encounterService->getEncounterById(intval($encounter)));
                if (!empty($encounterRecord[0])) {
                    $start = strtotime($encounterRecord[0]['date']);
                    if (!empty($end)) {
                        $end = strtotime($end);
                        // TODO: if we add encounter end dates we can populate this
                        $end = $end > $start ? $end : $start + 1800; // 30 minutes
                    }
                    $start = date('YmdHisO', $start);
                    $end = date('YmdHisO', $end);
                }
            }
        } elseif ($document_type == 'ccd' || $document_type == 'toc') {
            if (empty($end)) {
                $end = date('YmdHisO'); // current date
            }
            if (empty($start) && !empty($pid)) {
                $patientService = new PatientService();
                $patientRecord = $patientService->findByPid($pid);
                if (!empty($patientRecord)) {
                    $start = date('YmdHisO', strtotime($patientRecord['DOB']));
                }
            }
            if (empty($end) && !empty($pid)) {
                $encounterId = $this->getEncounterccdadispatchTable()->getLatestEncounter($pid);
                if (!empty($encounterId)) {
                    $encounterService = new EncounterService();
                    $encounterRecord = ProcessingResult::extractDataArray($encounterService->getEncounterById(intval($encounterId)));
                    if (!empty($encounterRecord[0])) {
                        $end = date('YmdHisO', strtotime($encounterRecord[0]['date']));
                    }
                }
            }
        }
        // TODO: this is the result of late night coding, hopefully we can come and fix this to be better in the future
        // we essentially need to handle the fall through case where we have a date range that's been sent us
        // that's not in our timestamp format
        if (strpos($start, "-") !== false) {
            $start = date('YmdHisO', strtotime($start));
        }
        if (strpos($end, "-") !== false) {
            $end = date('YmdHisO', strtotime($end));
        }
        return [
            'start' => $start ?? date('YmdHisO')
            ,'end' => $end ?? date('YmdHisO')
        ];
    }

    public function create_data($pid, $encounter, $sections, $components, $recipients, $params, $document_type, $referral_reason, int $send = null, $date_options = [])
    {
        global $assignedEntity;
        global $representedOrganization;

        $this->getEncounterccdadispatchTable()->setOptions($pid, $encounter, $date_options);

        if (!$send) {
            $send = 0;
        }

        $serviceStartDates = $this->getServiceStartDates($pid, $encounter, $document_type, $date_options);

        $sections_list = explode('|', $sections);
        $components_list = explode('|', $components);
        $this->createdtime = time();
        $this->data .= "<CCDA>";
        $this->data .= "<serverRoot>" . $GLOBALS['webroot'] . "</serverRoot>";
        $this->data .= "<document_location>" . $GLOBALS['OE_SITE_DIR'] . "</document_location>";
        $this->data .= "<username></username>";
        $this->data .= "<password></password>";
        $this->data .= "<hie>MyHealth</hie>";
        $this->data .= "<time_start>" . xmlEscape($serviceStartDates['start']) . "</time_start>";
        $this->data .= "<time_end>" . xmlEscape($serviceStartDates['end']) . "</time_end>";
        $this->data .= "<client_id></client_id>";
        $this->data .= "<created_time>" . date('YmdHis') . "</created_time>";
        $this->data .= "<created_time_timezone>" . date('YmdHisO') . "</created_time_timezone>";
        $this->data .= "<timezone_local_offset>" . date('O') . "</timezone_local_offset>";
        $this->data .= "<send>" . htmlspecialchars($send, ENT_QUOTES) . "</send>";
        $this->data .= "<doc_type>" . $document_type . "</doc_type>";
        $this->data .= "<assignedEntity>
                <streetAddressLine>" . htmlspecialchars($assignedEntity['streetAddressLine'] ?? '', ENT_QUOTES) . "</streetAddressLine>
                <city>" . htmlspecialchars($assignedEntity['city'] ?? '', ENT_QUOTES) . "</city>
                <state>" . htmlspecialchars($assignedEntity['state'] ?? '', ENT_QUOTES) . "</state>
                <postalCode>" . htmlspecialchars($assignedEntity['postalCode'] ?? '', ENT_QUOTES) . "</postalCode>
                <country>" . htmlspecialchars($assignedEntity['country'] ?? '', ENT_QUOTES) . "</country>
            </assignedEntity>
            <telecom use='WP' value='" . htmlspecialchars($assignedEntity['telecom'] ?? '', ENT_QUOTES) . "'/>
            <representedOrganization>
                <name>" . htmlspecialchars($representedOrganization['name'] ?? '', ENT_QUOTES) . "</name>
                <telecom use='WP' value='" . htmlspecialchars($representedOrganization['phone'] ?? '', ENT_QUOTES) . "'/>
                <streetAddressLine>" . htmlspecialchars($representedOrganization['street'] ?? '', ENT_QUOTES) . "</streetAddressLine>
                <city>" . htmlspecialchars($representedOrganization['city'] ?? '', ENT_QUOTES) . "</city>
                <state>" . htmlspecialchars($representedOrganization['state'] ?? '', ENT_QUOTES) . "</state>
                <postalCode>" . htmlspecialchars($representedOrganization['postal_code'] ?? '', ENT_QUOTES) . "</postalCode>
                <country>" . htmlspecialchars($representedOrganization['country'] ?? '', ENT_QUOTES) . "</country>
            </representedOrganization>";
        $this->data .= "<referral_reason><text>" . htmlspecialchars($referral_reason ?: '', ENT_QUOTES) . "</text></referral_reason>";

        /***************CCDA Header Information***************/
        $this->data .= $this->getEncounterccdadispatchTable()->getPatientdata($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getProviderDetails($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getAuthor($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getDataEnterer($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getInformant($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getCustodian($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getInformationRecipient($pid, $encounter, $recipients, $params);
        $this->data .= $this->getEncounterccdadispatchTable()->getLegalAuthenticator($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getDocumentParticipants($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getAuthenticator($pid, $encounter);
        $this->data .= $this->getEncounterccdadispatchTable()->getPrimaryCareProvider($pid, $encounter);
        /***************CCDA Header Information***************/

        /***************CCDA Body Information***************/
        if (in_array('encounters', $components_list)) {
            $this->data .= $this->getEncounterccdadispatchTable()->getEncounterHistory($pid);
        }

        if (in_array('continuity_care_document', $sections_list)) {
            $this->data .= $this->getContinuityCareDocument($pid, $components_list, $encounter);
        }

        // we're sending everything anyway. document type will tell engine what to include in cda.
        $this->data .= $this->getEncounterccdadispatchTable()->getClinicalNotes($pid, $encounter);

        if (in_array('progress_note', $sections_list)) {
            $this->data .= $this->getEncounterccdadispatchTable()->getProgressNotes($pid, $encounter);
        }

        if (in_array('discharge_summary', $sections_list)) {
            $this->data .= $this->getDischargeSummary($pid, $encounter);
        }

        if (in_array('procedure_note', $sections_list)) {
            $this->data .= $this->getProcedureNotes($pid, $encounter);
        }

        if (in_array('operative_note', $sections_list)) {
            $this->data .= $this->getOperativeNotes($pid, $encounter);
        }

        if (in_array('consultation_note', $sections_list)) {
            $this->data .= $this->getConsultationNote($pid, $encounter);
        }

        if (in_array('history_physical_note', $sections_list)) {
            $this->data .= $this->getHistoryAndPhysicalNotes($pid, $encounter, $components_list);
        }

        if (in_array('unstructured_document', $sections_list)) {
            $this->data .= $this->getEncounterccdadispatchTable()->getUnstructuredDocuments($pid, $encounter);
        }

        if ($this->exportwithDocuments) {
            $this->data .= $this->getEncounterccdadispatchTable()->getDocumentsForExport($pid, $encounter);
        }

        /***************CCDA Body Information***************/

        $this->data .= "</CCDA>";
    }

    public function getContinuityCareDocument($pid, $components_list, $encounter)
    {
        $ccd = '';
        if (in_array('allergies', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getAllergies($pid);
        }

        if (in_array('medications', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getMedications($pid);
        }

        if (in_array('problems', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getProblemList($pid);
        }

        if (in_array('procedures', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getProcedures($pid, $encounter);
        }

        if (in_array('results', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getResults($pid, $encounter);
        }

        if (in_array('immunizations', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getImmunization($pid);
        }

        if (in_array('plan_of_care', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getPlanOfCare($pid, $encounter);
        }

        if (in_array('functional_status', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getFunctionalCognitiveStatus($pid);
        }

        if (in_array('instructions', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getClinicalInstructions($pid);
        }

        if (in_array('medical_devices', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getMedicalDeviceList($pid);
        }

        if (in_array('referral', $components_list)) {
            $ccd .= $this->getEncounterccdadispatchTable()->getReferrals($pid);
        }
        return $ccd;
    }


    public function getDischargeSummary($pid, $encounter)
    {
        $discharge_summary = '';

        $discharge_summary .= $this->getEncounterccdadispatchTable()->getHospitalCourse($pid, $encounter);
        $discharge_summary .= $this->getEncounterccdadispatchTable()->getDischargeDiagnosis($pid, $encounter);
        $discharge_summary .= $this->getEncounterccdadispatchTable()->getDischargeMedications($pid, $encounter);

        return $discharge_summary;
    }


    /*
    #***********************************************#
    #       PROCEDURE NOTES section in CCDA.        #
    #***********************************************#
    This function contains call to different sub sections like
    * Complications
    * Postprocedure Diagnosis
    * Postprocedure Description
    * Postprocedure Indications

    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * $return   string  $procedure_notes      XML which contains the details collected from the patient.
    */
    public function getProcedureNotes($pid, $encounter)
    {
        $procedure_notes = '<procedure_notes>';
        $procedure_notes .= $this->getEncounterccdadispatchTable()->getComplications($pid, $encounter);
        $procedure_notes .= $this->getEncounterccdadispatchTable()->getPostProcedureDiag($pid, $encounter);
        $procedure_notes .= $this->getEncounterccdadispatchTable()->getProcedureDescription($pid, $encounter);
        $procedure_notes .= $this->getEncounterccdadispatchTable()->getProcedureIndications($pid, $encounter);
        $procedure_notes .= '</procedure_notes>';
        return $procedure_notes;
    }

    /*
    #***********************************************#
    #       OPERATIVE NOTES section in CCDA.        #
    #***********************************************#
    This function contains call to different sub sections like
    * Anesthesia
    * Complications (already exist in the CCDA section Procedure Notes)
    * Post Operative Diagnosis
    * Pre Operative Diagnosis
    * Procedure Estimated Blood Loss
    * Procedure Findings
    * Procedure Specimens Taken
    * Procedure Description (already exist in the CCDA section Procedure Notes)


    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * $return   string  $operative_notes      XML which contains the details collected from the patient.
    */
    public function getOperativeNotes($pid, $encounter)
    {
        $operative_notes = '<operative_notes>';
        $operative_notes .= $this->getEncounterccdadispatchTable()->getAnesthesia($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getPostoperativeDiag($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getPreOperativeDiag($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getEstimatedBloodLoss($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getProcedureFindings($pid, $encounter);
        $operative_notes .= $this->getEncounterccdadispatchTable()->getProcedureSpecimensTaken($pid, $encounter);
        $operative_notes .= '</operative_notes>';
        return $operative_notes;
    }

    /*
    #***********************************************#
    #       CONSULTATION NOTES section in CCDA.     #
    #***********************************************#
    This function contains call to different sub sections like
    * History of Present Illness
    * Physical Exam

    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * $return   string  $consultation_notes      XML which contains the details collected from the patient.
    */
    public function getConsultationNote($pid, $encounter)
    {
        $consultation_notes = '';
        $consultation_notes .= "<consultation_notes>";
        $consultation_notes .= $this->getEncounterccdadispatchTable()->getHP($pid, $encounter);
        $consultation_notes .= $this->getEncounterccdadispatchTable()->getPhysicalExam($pid, $encounter);
        $consultation_notes .= "</consultation_notes>";
        return $consultation_notes;
    }

    /*
    #********************************************************#
    #       HISTORY AND PHYSICAL NOTES section in CCDA.      #
    #********************************************************#
    This function contains call to different sub sections like
    * Chief Complaint / Reason for Visit
    * Family History
    * General Status
    * History of Past Illness
    * Review of Systems
    * Social History
    * Vital Signs

    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * $return   string  $history_and_physical_notes      XML which contains the details collected from the patient.
    */

    public function getHistoryAndPhysicalNotes($pid, $encounter, $components_list)
    {
        $history_and_physical_notes = '';
        $history_and_physical_notes .= "<history_physical>";
        $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getChiefComplaint($pid, $encounter);
        $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getGeneralStatus($pid, $encounter);
        $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getHistoryOfPastIllness($pid, $encounter);
        $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getReviewOfSystems($pid, $encounter);
        if (in_array('vitals', $components_list)) {
            $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getVitals($pid);
        }

        if (in_array('social_history', $components_list)) {
            $history_and_physical_notes .= $this->getEncounterccdadispatchTable()->getSocialHistory($pid);
        }

        $history_and_physical_notes .= "</history_physical>";
        return $history_and_physical_notes;
    }
}
