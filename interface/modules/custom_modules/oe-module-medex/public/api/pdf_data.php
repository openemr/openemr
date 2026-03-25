<?php
/**
 * MedEx PDF Data API
 *
 * Provides patient, provider, encounter, and facility data for PDF form filling
 * Also handles saving filled PDFs back to OpenEMR patient documents
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

// Start output buffering BEFORE globals.php so any ADOdb/PHP warnings that
// call echo() are captured and do not corrupt our JSON response.
ob_start();

require_once(__DIR__ . '/../../../../../globals.php');
require_once(__DIR__ . '/../../src/MedExAPI.php');

// Redirect ADOdb error output to error_log instead of HTTP response body.
// This prevents "SQL Statement failed on preparation: ..." from prepending
// to JSON output when a query fails.
if (!defined('ADODB_OUTP')) {
    $GLOBALS['ADODB_OUTP'] = function ($msg, $newline) {
        error_log('[MedEx pdf_data] ADOdb: ' . strip_tags($msg));
    };
}

use OpenEMR\Common\Csrf\CsrfUtils;

/**
 * Emit a JSON response, discarding any buffered ADOdb/PHP output first.
 * Ends ALL active output buffers so that module shutdown functions which
 * echo HTML (e.g., critical-update modal) cannot corrupt the JSON body.
 */
function jsonOut(array $data, int $status = 200): void
{
    // End every active output buffer (discard its contents).
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    // Flush directly to the network so shutdown functions receive an already-
    // sent response context and any further echo()s become a separate chunk
    // (or are silently discarded by Apache after the connection closes).
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    exit;
}

// Verify authentication
if (!isset($_SESSION['authUserID'])) {
    jsonOut(['success' => false, 'error' => 'Unauthorized'], 401);
}

$pdfApi = new \OpenEMR\Modules\MedEx\MedExAPI();
if (!$pdfApi->hasServiceEntitlement('pdf_management')) {
    jsonOut(['success' => false, 'error' => 'PDF Management subscription required'], 403);
}

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '', 'default')) {
    jsonOut(['success' => false, 'error' => 'Invalid CSRF token'], 403);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_patient_data':
        handleGetPatientData();
        break;
        
    case 'save_pdf':
        handleSavePdf();
        break;
        
    case 'get_document_categories':
        handleGetCategories();
        break;
        
    default:
        jsonOut(['success' => false, 'error' => 'Invalid action'], 400);
}

/**
 * Get comprehensive patient data for PDF form filling
 * Includes patient demographics, provider info, encounter details, and facility
 */
function handleGetPatientData(): void
{
    $pid = (int)($_POST['pid'] ?? $_GET['pid'] ?? 0);
    $encounter = (int)($_POST['encounter'] ?? $_GET['encounter'] ?? 0);
    
    if (!$pid) {
        jsonOut(['success' => false, 'error' => 'Patient ID required']);
    }
    
    // Get patient demographics
    $patientSql = "
        SELECT 
            pd.pid, pd.pubpid, pd.title, pd.fname, pd.mname, pd.lname, pd.suffix,
            pd.DOB, pd.sex, pd.ss,
            pd.street, pd.city, pd.state, pd.postal_code, pd.country_code,
            pd.phone_home, pd.phone_cell, pd.phone_biz, pd.phone_contact,
            pd.email, pd.email_direct,
            pd.drivers_license, pd.occupation, pd.employer_name,
            pd.emergency_contact, pd.emergency_phone,
            pd.hipaa_allowsms, pd.hipaa_allowemail, pd.hipaa_voice,
            pd.language, pd.race, pd.ethnicity,
            pd.status AS marital_status
        FROM patient_data pd
        WHERE pd.pid = ?
    ";
    $patient = sqlQuery($patientSql, [$pid]);
    
    if (!$patient) {
        jsonOut(['success' => false, 'error' => 'Patient not found']);
    }
    
    // Format patient data for PDF field mapping
    $patientData = [
        'pid' => $patient['pid'],
        'pubpid' => $patient['pubpid'],
        'name_full' => trim($patient['fname'] . ' ' . $patient['mname'] . ' ' . $patient['lname']),
        'fname' => $patient['fname'],
        'mname' => $patient['mname'],
        'lname' => $patient['lname'],
        'suffix' => $patient['suffix'],
        'title' => $patient['title'],
        'DOB' => $patient['DOB'],
        'DOB_mdy' => $patient['DOB'] ? date('m/d/Y', strtotime($patient['DOB'])) : '',
        'age' => $patient['DOB'] ? (int)((time() - strtotime($patient['DOB'])) / 31536000) : '',
        'sex' => $patient['sex'],
        'sex_full' => $patient['sex'] === 'M' ? 'Male' : ($patient['sex'] === 'F' ? 'Female' : $patient['sex']),
        'ssn' => $patient['ss'],
        'ssn_masked' => $patient['ss'] ? 'XXX-XX-' . substr($patient['ss'], -4) : '',
        'street' => $patient['street'],
        'city' => $patient['city'],
        'state' => $patient['state'],
        'zip' => $patient['postal_code'],
        'address_full' => trim($patient['street'] . ', ' . $patient['city'] . ', ' . $patient['state'] . ' ' . $patient['postal_code']),
        'phone_home' => $patient['phone_home'],
        'phone_cell' => $patient['phone_cell'],
        'phone_work' => $patient['phone_biz'],
        'email' => $patient['email'],
        'drivers_license' => $patient['drivers_license'],
        'employer' => $patient['employer_name'],
        'occupation' => $patient['occupation'],
        'emergency_contact' => $patient['emergency_contact'],
        'emergency_phone' => $patient['emergency_phone'],
        'marital_status' => $patient['marital_status'],
        'language' => $patient['language'],
        'race' => $patient['race'],
        'ethnicity' => $patient['ethnicity'],
    ];
    
    // Get encounter and provider data if encounter specified
    $providerData = [];
    $encounterData = [];
    $facilityData = [];
    
    if ($encounter) {
        $encounterSql = "
            SELECT 
                e.encounter, e.date AS encounter_date, e.reason, e.onset_date,
                e.pc_catid, e.facility_id, e.provider_id, e.supervisor_id,
                e.billing_facility,
                u.id AS provider_id, u.fname AS prov_fname, u.mname AS prov_mname, 
                u.lname AS prov_lname, u.suffix AS prov_suffix, u.title AS prov_title,
                u.npi AS prov_npi, u.state_license_number AS prov_license,
                u.phonew1 AS prov_phone, u.phonew2 AS prov_phone2, 
                u.fax AS prov_fax, u.email AS prov_email,
                u.specialty AS prov_specialty, u.taxonomy AS prov_taxonomy,
                f.id AS facility_id, f.name AS facility_name, 
                f.phone AS facility_phone, f.fax AS facility_fax,
                f.street AS facility_street, f.city AS facility_city, 
                f.state AS facility_state, f.postal_code AS facility_zip,
                f.federal_ein AS facility_ein, f.facility_npi AS facility_npi
            FROM form_encounter e
            LEFT JOIN users u ON e.provider_id = u.id
            LEFT JOIN facility f ON e.facility_id = f.id
            WHERE e.encounter = ? AND e.pid = ?
        ";
        $enc = sqlQuery($encounterSql, [$encounter, $pid]);
        
        if ($enc) {
            $encounterData = [
                'encounter_id' => $enc['encounter'],
                'encounter_date' => $enc['encounter_date'],
                'encounter_date_mdy' => $enc['encounter_date'] ? date('m/d/Y', strtotime($enc['encounter_date'])) : '',
                'reason' => $enc['reason'],
                'onset_date' => $enc['onset_date'],
            ];
            
            $providerData = [
                'provider_id' => $enc['provider_id'],
                'provider_name' => trim(($enc['prov_title'] ? $enc['prov_title'] . ' ' : '') . 
                                       $enc['prov_fname'] . ' ' . $enc['prov_lname'] .
                                       ($enc['prov_suffix'] ? ', ' . $enc['prov_suffix'] : '')),
                'provider_fname' => $enc['prov_fname'],
                'provider_lname' => $enc['prov_lname'],
                'provider_npi' => $enc['prov_npi'],
                'provider_license' => $enc['prov_license'],
                'provider_phone' => $enc['prov_phone'],
                'provider_fax' => $enc['prov_fax'],
                'provider_email' => $enc['prov_email'],
                'provider_specialty' => $enc['prov_specialty'],
                'provider_taxonomy' => $enc['prov_taxonomy'],
            ];
            
            $facilityData = [
                'facility_id' => $enc['facility_id'],
                'facility_name' => $enc['facility_name'],
                'facility_phone' => $enc['facility_phone'],
                'facility_fax' => $enc['facility_fax'],
                'facility_street' => $enc['facility_street'],
                'facility_city' => $enc['facility_city'],
                'facility_state' => $enc['facility_state'],
                'facility_zip' => $enc['facility_zip'],
                'facility_address' => trim($enc['facility_street'] . ', ' . $enc['facility_city'] . ', ' . 
                                          $enc['facility_state'] . ' ' . $enc['facility_zip']),
                'facility_ein' => $enc['facility_ein'],
                'facility_npi' => $enc['facility_npi'],
            ];
        }
    }
    
    // If no encounter, get primary facility
    if (empty($facilityData)) {
        $facilitySql = "SELECT * FROM facility WHERE primary_business_entity = 1 LIMIT 1";
        $fac = sqlQuery($facilitySql);
        if ($fac) {
            $facilityData = [
                'facility_id' => $fac['id'],
                'facility_name' => $fac['name'],
                'facility_phone' => $fac['phone'],
                'facility_fax' => $fac['fax'],
                'facility_street' => $fac['street'],
                'facility_city' => $fac['city'],
                'facility_state' => $fac['state'],
                'facility_zip' => $fac['postal_code'],
                'facility_address' => trim($fac['street'] . ', ' . $fac['city'] . ', ' . 
                                          $fac['state'] . ' ' . $fac['postal_code']),
                'facility_ein' => $fac['federal_ein'] ?? '',
                'facility_npi' => $fac['facility_npi'] ?? '',
            ];
        }
    }
    
    // Add today's date in various formats
    $today = [
        'TODAY' => date('Y-m-d'),
        'TODAY_mdy' => date('m/d/Y'),
        'TODAY_long' => date('F j, Y'),
        'CURRENT_TIME' => date('H:i'),
        'CURRENT_TIME_ampm' => date('g:i A'),
    ];
    
    jsonOut([
        'success' => true,
        'patient' => $patientData,
        'provider' => $providerData,
        'encounter' => $encounterData,
        'facility' => $facilityData,
        'dates' => $today,
    ]);
}

/**
 * Save a filled PDF to the patient's documents
 */
function handleSavePdf(): void
{
    $pid = (int)($_POST['pid'] ?? 0);
    $encounter = (int)($_POST['encounter'] ?? 0);
    $filename = $_POST['filename'] ?? 'filled_form.pdf';
    $pdfBase64 = $_POST['pdf_base64'] ?? '';
    $categoryName = $_POST['category'] ?? 'Forms';
    
    if (!$pid || !$pdfBase64) {
        jsonOut(['success' => false, 'error' => 'Missing required fields']);
    }
    
    // Decode PDF
    $pdfData = base64_decode($pdfBase64);
    if (!$pdfData) {
        jsonOut(['success' => false, 'error' => 'Invalid PDF data']);
    }
    
    // Find or create document category
    $catSql = "SELECT id FROM categories WHERE name = ?";
    $cat = sqlQuery($catSql, [$categoryName]);
    $categoryId = $cat['id'] ?? 1; // Default to category 1 if not found
    
    // Create temp file
    $tempFile = tempnam(sys_get_temp_dir(), 'medex_pdf_');
    file_put_contents($tempFile, $pdfData);
    
    // Use OpenEMR document API to save
    require_once($GLOBALS['srcdir'] . '/documents.php');
    
    try {
        // Add document to patient chart
        $docId = addNewDocument(
            $filename,
            'application/pdf',
            $tempFile,
            0, // parent_id
            $categoryId,
            $pid,
            $_SESSION['authUserID'] ?? 0
        );
        
        // Link to encounter if specified
        if ($encounter && $docId) {
            $linkSql = "INSERT INTO issue_encounter (pid, list_id, encounter) VALUES (?, ?, ?) 
                       ON DUPLICATE KEY UPDATE encounter = ?";
            // Note: This may need adjustment based on OpenEMR version
        }
        
        unlink($tempFile);
        
        jsonOut([
            'success' => true,
            'document_id' => $docId,
            'message' => 'PDF saved to patient chart'
        ]);
        
    } catch (Exception $e) {
        unlink($tempFile);
        jsonOut(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Get document categories for save dialog
 */
function handleGetCategories(): void
{
    $sql = "SELECT id, name FROM categories ORDER BY name";
    $result = sqlStatement($sql);
    
    $categories = [];
    while ($row = sqlFetchArray($result)) {
        $categories[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    jsonOut([
        'success' => true,
        'categories' => $categories
    ]);
}
