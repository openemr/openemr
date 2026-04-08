<?php
/**
 * Database Schema API for PDF Field Mapping
 * Returns table and column information for OpenEMR database
 * 
 * Expanded to include all commonly used tables for comprehensive PDF filling
 */

$ignoreAuth = true;
require_once(dirname(__FILE__, 6) . '/globals.php');

// Ensure site_id is set for standalone calls
if (empty($_SESSION['site_id'])) {
    $siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_GET['site'] ?? ($_POST['site'] ?? 'default')));
    if ($siteId === '') {
        $siteId = 'default';
    }
    $_SESSION['site_id'] = $siteId;
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$action = $_GET["action"] ?? "tables";

if ($action === "tables") {
    // Return comprehensive list of tables for field mapping
    // Organized by category for better UX
    $tables = [
        // Patient Demographics
        "patient_data",
        "patient_history",
        
        // Insurance
        "insurance_data",
        "insurance_companies",
        
        // Employer
        "employer_data",
        
        // Encounters & Forms
        "form_encounter",
        "form_vitals",
        "form_clinical_notes",
        "form_care_plan",
        "form_clinical_instructions",
        "form_dictation",
        "form_observation",
        "form_misc_billing_options",
        
        // Eye Forms (comprehensive)
        "form_eye_base",
        "form_eye_hpi",
        "form_eye_ros",
        "form_eye_vitals",
        "form_eye_acuity",
        "form_eye_refraction",
        "form_eye_biometrics",
        "form_eye_external",
        "form_eye_antseg",
        "form_eye_postseg",
        "form_eye_neuro",
        "form_eye_locking",
        "form_eye_mag_dispense",
        "form_eye_mag_orders",
        "form_eye_mag_wearing",
        "form_eye_mag_prefs",
        "form_eye_mag_impplan",
        
        // Other Clinical
        "form_functional_cognitive_status",
        "form_history_sdoh",
        "form_history_sdoh_health_concerns",
        "form_questionnaire_assessments",
        
        // Lists & Options
        "lists",
        "list_options",
        
        // Pharmacy
        "pharmacies",
        
        // Users & Facilities
        "users",
        "facility",
        
        // Billing
        "billing",
        "codes",
        
        // Documents
        "documents",
        "categories",
        
        // Prescriptions
        "prescriptions",
        
        // Procedures
        "procedure_order",
        "procedure_report",
        "procedure_result",
        
        // Immunizations
        "immunizations",
        
        // Allergies (via lists)
        // "lists" already included
        
        // Referrals
        "transactions",
    ];

    echo json_encode([
        "success" => true,
        "tables" => $tables,
        "categories" => [
            "patient" => ["patient_data", "patient_history"],
            "insurance" => ["insurance_data", "insurance_companies"],
            "employer" => ["employer_data"],
            "encounter" => ["form_encounter", "form_vitals", "form_clinical_notes", "form_care_plan"],
            "eye" => ["form_eye_base", "form_eye_hpi", "form_eye_acuity", "form_eye_refraction", "form_eye_external", "form_eye_antseg", "form_eye_postseg", "form_eye_neuro"],
            "users" => ["users", "facility"],
            "billing" => ["billing", "codes"],
            "prescriptions" => ["prescriptions"],
            "procedures" => ["procedure_order", "procedure_report", "procedure_result"],
        ]
    ]);

} elseif ($action === "columns") {
    $table = $_GET["table"] ?? "";

    if (empty($table)) {
        echo json_encode(["success" => false, "error" => "table parameter required"]);
        exit;
    }

    // Sanitize table name - only allow alphanumeric and underscore
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
        echo json_encode(["success" => false, "error" => "Invalid table name"]);
        exit;
    }

    // Get columns for the specified table
    try {
        $sql = "SHOW COLUMNS FROM `" . $table . "`";
        $result = sqlStatement($sql);

        $columns = [];
        while ($row = sqlFetchArray($result)) {
            $columns[] = [
                "name" => $row["Field"],
                "type" => $row["Type"],
                "null" => $row["Null"],
                "key" => $row["Key"],
                "default" => $row["Default"],
                // Add friendly label based on field name
                "label" => ucwords(str_replace('_', ' ', $row["Field"]))
            ];
        }

        echo json_encode([
            "success" => true,
            "table" => $table,
            "columns" => $columns
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => "Table not found: " . $table]);
    }

} elseif ($action === "all_fields") {
    // Return all fields from key tables in a flat structure for easy mapping
    $key_tables = ["patient_data", "insurance_data", "form_encounter", "users", "facility"];
    $all_fields = [];
    
    foreach ($key_tables as $table) {
        try {
            $result = sqlStatement("SHOW COLUMNS FROM `" . $table . "`");
            while ($row = sqlFetchArray($result)) {
                $all_fields[] = [
                    "table" => $table,
                    "field" => $row["Field"],
                    "full_path" => $table . "." . $row["Field"],
                    "label" => ucwords(str_replace('_', ' ', $row["Field"])) . " (" . $table . ")",
                    "type" => $row["Type"]
                ];
            }
        } catch (Exception $e) {
            // Skip tables that don't exist
        }
    }
    
    echo json_encode([
        "success" => true,
        "fields" => $all_fields
    ]);

} else {
    echo json_encode(["success" => false, "error" => "Invalid action. Valid actions: tables, columns, all_fields"]);
}
