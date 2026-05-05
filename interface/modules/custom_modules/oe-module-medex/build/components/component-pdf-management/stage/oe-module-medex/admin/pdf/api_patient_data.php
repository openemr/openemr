<?php
/**
 * Patient Data API for PDF Field Mapping
 * Returns specific field values from OpenEMR tables
 * Called by MedExBank when filling PDFs
 * 
 * @package   OpenEMR
 * @copyright Copyright (c) 2025 MedEx
 */

// Set session before including globals
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_GET['site'] ?? ($_POST['site'] ?? ($_SESSION['site_id'] ?? 'default'))));
if ($siteId === '') {
    $siteId = 'default';
}
$_SESSION['site_id'] = $siteId;

$ignoreAuth = true;
require_once(dirname(__FILE__, 6) . '/globals.php');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_fields';

// Sample action doesn't need API key - it's for editor preview only
// and doesn't expose sensitive patient data (just shows field format examples)
if ($action !== 'sample') {
    // Authenticate via API key for all other actions
    $api_key = $_GET['api_key'] ?? $_POST['api_key'] ?? '';
    if (empty($api_key)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'API key required']);
        exit;
    }

    // Verify API key matches stored MedEx key
    $stored = sqlQuery("SELECT ME_api_key FROM medex_prefs LIMIT 1");
    if (empty($stored) || $stored['ME_api_key'] !== $api_key) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid API key']);
        exit;
    }
}
$pid = (int)($_GET['pid'] ?? $_POST['pid'] ?? 0);
$encounter = (int)($_GET['encounter'] ?? $_POST['encounter'] ?? 0);

if ($action === 'get_fields') {
    // Get specific fields from specific tables
    // Expects: fields = JSON array like ["patient_data.fname", "patient_data.lname"]
    
    $fields_raw = $_GET['fields'] ?? $_POST['fields'] ?? '';
    if (is_string($fields_raw)) {
        $fields = json_decode($fields_raw, true) ?? [];
    } else {
        $fields = $fields_raw;
    }
    
    if (empty($fields) || empty($pid)) {
        echo json_encode(['success' => false, 'error' => 'pid and fields[] required']);
        exit;
    }
    
    $result = [];
    $allowed_tables = [
        'patient_data' => ['key' => 'pid', 'value' => $pid],
        'patient_history' => ['key' => 'pid', 'value' => $pid],
        'insurance_data' => ['key' => 'pid', 'value' => $pid, 'extra' => "AND type = 'primary' ORDER BY date DESC LIMIT 1"],
        'insurance_data_secondary' => ['table' => 'insurance_data', 'key' => 'pid', 'value' => $pid, 'extra' => "AND type = 'secondary' ORDER BY date DESC LIMIT 1"],
        'employer_data' => ['key' => 'pid', 'value' => $pid, 'extra' => "ORDER BY date DESC LIMIT 1"],
        'form_encounter' => ['key' => 'encounter', 'value' => $encounter, 'require_encounter' => true],
        'users' => ['custom' => true],
        'facility' => ['custom' => true],
        // Eye form related sub-tables - use eye_mag formdir
        'form_eye_acuity' => ['form' => true, 'formdir' => 'eye_mag'],
        'form_eye_vitals' => ['form' => true, 'formdir' => 'eye_mag'],
        'form_eye_refraction' => ['form' => true, 'formdir' => 'eye_mag'],
        'form_eye_neuro' => ['form' => true, 'formdir' => 'eye_mag'],
        'form_eye_ros' => ['form' => true, 'formdir' => 'eye_mag'],
        'form_eye_postseg' => ['form' => true, 'formdir' => 'eye_mag'],
        // Standard forms - formdir matches table name without form_ prefix
        'form_vitals' => ['form' => true],
        'form_ros' => ['form' => true],
        'form_misc_billing_options' => ['form' => true],
        'form_soap' => ['form' => true],
    ];
    
    // Group fields by table for efficient queries
    $table_fields = [];
    foreach ($fields as $field_path) {
        // Handle FHIR paths
        if (strpos($field_path, 'FHIR:') === 0) {
             // It's a FHIR field, add to 'FHIR' group
             // format: FHIR:Patient.name.given[0]
             $fhirPath = substr($field_path, 5); // remove 'FHIR:'
             if (!isset($table_fields['FHIR'])) {
                 $table_fields['FHIR'] = [];
             }
             $table_fields['FHIR'][] = $fhirPath;
             continue;
        }

        if (strpos($field_path, '.') !== false) {
            list($table, $column) = explode('.', $field_path, 2);
            if (!isset($table_fields[$table])) {
                $table_fields[$table] = [];
            }
            $table_fields[$table][] = $column;
        }
    }
    
    // Query each table once
    foreach ($table_fields as $table => $columns) {
        // Handle FHIR
        if ($table === 'FHIR') {
             // Execute FHIR logic
             require_once(__DIR__ . '/fhir_helper.php');
             foreach($columns as $fhirPath) {
                 // Fetch the value using internal FHIR bridge
                 $val = MedExFullFHIRExecutor::executePath($pid, $encounter, $fhirPath);
                 $result["FHIR:$fhirPath"] = $val;
             }
             continue;
        }

        // Handle computed patient fields
        if ($table === 'computed_patient' || $table === 'computed') {
            // Handle date/time computed fields that don't need patient data
            foreach ($columns as $col) {
                $value = '';
                switch ($col) {
                    case 'todays_date':
                    case 'current_date':
                        $value = date('m/d/Y');
                        break;
                    case 'current_time':
                        $value = date('g:i A');
                        break;
                    case 'current_datetime':
                        $value = date('m/d/Y g:i A');
                        break;
                }
                if ($value !== '') {
                    $result["$table.$col"] = $value;
                }
            }
            
            // Handle patient-specific computed fields
            $patient = sqlQuery("SELECT fname, mname, lname, street, city, state, postal_code, DOB, phone_cell, phone_home FROM patient_data WHERE pid = ?", [$pid]);
            if ($patient) {
                foreach ($columns as $col) {
                    $value = '';
                    switch ($col) {
                        case 'full_name':
                            $value = trim($patient['fname'] . ' ' . $patient['lname']);
                            break;
                        case 'full_name_lfm':
                            $value = $patient['lname'] . ', ' . $patient['fname'] . ($patient['mname'] ? ' ' . substr($patient['mname'], 0, 1) : '');
                            break;
                        case 'full_name_fml':
                            $value = $patient['fname'] . ($patient['mname'] ? ' ' . substr($patient['mname'], 0, 1) : '') . ' ' . $patient['lname'];
                            break;
                        case 'name_last_first':
                            $value = $patient['lname'] . ', ' . $patient['fname'];
                            break;
                        case 'full_address':
                            $value = $patient['street'] . ', ' . $patient['city'] . ', ' . $patient['state'] . ' ' . $patient['postal_code'];
                            break;
                        case 'full_address_multiline':
                            $value = $patient['street'] . "\n" . $patient['city'] . ', ' . $patient['state'] . ' ' . $patient['postal_code'];
                            break;
                        case 'city_state_zip':
                            $value = $patient['city'] . ', ' . $patient['state'] . ' ' . $patient['postal_code'];
                            break;
                        case 'age':
                            if ($patient['DOB']) {
                                $dob = new DateTime($patient['DOB']);
                                $now = new DateTime();
                                $value = $dob->diff($now)->y;
                            }
                            break;
                        case 'dob_formatted':
                            if ($patient['DOB']) {
                                $value = date('m/d/Y', strtotime($patient['DOB']));
                            }
                            break;
                        case 'phone_primary':
                            $value = $patient['phone_cell'] ?: $patient['phone_home'];
                            break;
                    }
                    if ($value !== '') {
                        $result["$table.$col"] = $value;
                    }
                }
            }
            continue; // Skip database query for computed fields
        }
        
        // Handle computed provider fields
        if ($table === 'computed_provider') {
            if ($encounter) {
                $enc = sqlQuery("SELECT provider_id FROM form_encounter WHERE encounter = ?", [$encounter]);
                if ($enc && $enc['provider_id']) {
                    $provider = sqlQuery("SELECT fname, mname, lname, npi, street, city, state, zip, federaltaxid, specialty FROM users WHERE id = ?", [$enc['provider_id']]);
                    if ($provider) {
                        foreach ($columns as $col) {
                            $value = '';
                            switch ($col) {
                                case 'full_name':
                                    $value = trim($provider['fname'] . ' ' . $provider['lname']);
                                    break;
                                case 'full_name_lfm':
                                    $value = $provider['lname'] . ', ' . $provider['fname'] . ($provider['mname'] ? ' ' . substr($provider['mname'], 0, 1) : '');
                                    break;
                                case 'credentials':
                                case 'name_credentials':
                                    // Just return the name - credentials field doesn't exist in users table
                                    $value = trim($provider['fname'] . ' ' . $provider['lname']);
                                    break;
                                case 'physician_type':
                                    // Conditional logic: OD if specialty contains 'optomet', otherwise MD
                                    $specialty_lower = strtolower($provider['specialty'] ?? '');
                                    if (strpos($specialty_lower, 'optomet') !== false) {
                                        $value = 'OD';
                                    } else {
                                        $value = 'MD';
                                    }
                                    break;
                                case 'specialty':
                                    $value = $provider['specialty'] ?? '';
                                    break;
                                case 'full_address':
                                    $value = $provider['street'] . ', ' . $provider['city'] . ', ' . $provider['state'] . ' ' . $provider['zip'];
                                    break;
                            }
                            if ($value !== '') {
                                $result["$table.$col"] = $value;
                            }
                        }
                    }
                }
            }
            continue; // Skip database query for computed fields
        }
        
        // Sanitize table and column names
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) continue;
        
        $safe_columns = [];
        foreach ($columns as $col) {
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $col)) {
                $safe_columns[] = "`$col`";
            }
        }
        if (empty($safe_columns)) continue;
        
        // Determine how to query this table
        $actual_table = $table;
        $where_key = 'pid';
        $where_value = $pid;
        $extra = '';
        
        // Check if it's a form_* table (dynamic handling for any form table)
        $is_form_table = (strpos($table, 'form_') === 0 && $table !== 'form_encounter');
        
        if (isset($allowed_tables[$table])) {
            $config = $allowed_tables[$table];
            $actual_table = $config['table'] ?? $table;
            $where_key = $config['key'] ?? 'pid';
            $where_value = $config['value'] ?? $pid;
            $extra = $config['extra'] ?? '';
            
            if (!empty($config['require_encounter']) && !$encounter) {
                continue;
            }
            
            if (!empty($config['custom'])) {
                if ($table === 'users' && $encounter) {
                    $enc = sqlQuery("SELECT provider_id FROM form_encounter WHERE encounter = ?", [$encounter]);
                    if ($enc && $enc['provider_id']) {
                        $where_key = 'id';
                        $where_value = $enc['provider_id'];
                    } else {
                        continue;
                    }
                } elseif ($table === 'facility' && $encounter) {
                    $enc = sqlQuery("SELECT facility_id FROM form_encounter WHERE encounter = ?", [$encounter]);
                    if ($enc && $enc['facility_id']) {
                        $where_key = 'id';
                        $where_value = $enc['facility_id'];
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            }
            
            // Handle form_* tables - need to lookup via forms table
            if (!empty($config['form'])) {
                if (!$encounter) {
                    continue; // Forms require encounter
                }
                // Use custom formdir if specified, otherwise derive from table name
                $form_dir = $config['formdir'] ?? preg_replace('/^form_/', '', $table);
                // Lookup most recent form_id for this encounter
                $form_row = sqlQuery(
                    "SELECT form_id FROM forms WHERE encounter = ? AND formdir = ? AND deleted = 0 ORDER BY date DESC LIMIT 1",
                    [$encounter, $form_dir]
                );
                if ($form_row && $form_row['form_id']) {
                    $where_key = 'id';
                    $where_value = $form_row['form_id'];
                } else {
                    continue; // No form found for this encounter
                }
            }
        } elseif ($is_form_table) {
            // Dynamic handling for any form_* table not in allowed_tables
            if (!$encounter) {
                continue; // Forms require encounter
            }
            // Get formdir from table name (e.g., form_vitals -> vitals)
            $form_dir = preg_replace('/^form_/', '', $table);
            // Lookup most recent form_id for this encounter
            $form_row = sqlQuery(
                "SELECT form_id FROM forms WHERE encounter = ? AND formdir = ? AND deleted = 0 ORDER BY date DESC LIMIT 1",
                [$encounter, $form_dir]
            );
            if ($form_row && $form_row['form_id']) {
                $where_key = 'id';
                $where_value = $form_row['form_id'];
            } else {
                continue; // No form found for this encounter
            }
        }
        
        // Check if table exists first to avoid SQL errors
        // Use direct query since SHOW TABLES doesn't work well with prepared statements
        $table_check_query = "SHOW TABLES LIKE '" . mysqli_real_escape_string($GLOBALS['dbh'], $actual_table) . "'";
        $table_check = sqlQuery($table_check_query);
        if (!$table_check) {
            continue; // Table doesn't exist, skip this data source
        }
        
        // Get list of all column names in this table
        $valid_columns_result = sqlStatement("SHOW COLUMNS FROM `$actual_table`");
        if (!$valid_columns_result) {
            continue; // Failed to get columns
        }
        $valid_column_names = [];
        while ($col_info = sqlFetchArray($valid_columns_result)) {
            $valid_column_names[] = $col_info['Field'];
        }
        
        // Filter safe_columns to only include columns that actually exist
        $verified_columns = [];
        foreach ($safe_columns as $quoted_col) {
            $col_name = trim($quoted_col, '`');
            if (in_array($col_name, $valid_column_names)) {
                $verified_columns[] = $quoted_col;
            }
        }
        
        if (empty($verified_columns)) {
            continue; // No valid columns for this table
        }
        
        $sql = "SELECT " . implode(', ', $verified_columns) . " FROM `$actual_table` WHERE `$where_key` = ? $extra";
        
        // Suppress errors from queries with invalid column names - just skip those fields
        try {
            $row = sqlQuery($sql, [$where_value]);
            
            if ($row) {
                foreach ($columns as $col) {
                    if (isset($row[$col])) {
                        $result["$table.$col"] = $row[$col];
                    }
                }
            }
        } catch (Exception $e) {
            // Query failed - skip it silently
            // This allows templates with custom fields to work with partial data
            continue;
        }
    }
    
    // Add computed fields
    $result['today'] = date('Y-m-d');
    $result['today_mdy'] = date('m/d/Y');
    $result['today_long'] = date('F j, Y');
    
    echo json_encode([
        'success' => true,
        'pid' => $pid,
        'encounter' => $encounter,
        'data' => $result
    ]);

} elseif ($action === 'get_all_patient') {
    if (empty($pid)) {
        echo json_encode(['success' => false, 'error' => 'pid required']);
        exit;
    }
    
    $patient = sqlQuery("SELECT * FROM patient_data WHERE pid = ?", [$pid]);
    if ($patient) {
        echo json_encode([
            'success' => true,
            'pid' => $pid,
            'data' => $patient
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Patient not found']);
    }

} elseif ($action === 'sample') {
    // Get sample value for a field from the first available patient
    // Used for preview purposes in the PDF editor
    $field = $_GET['field'] ?? '';
    
    if (empty($field) || strpos($field, '.') === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid field format. Use table.column']);
        exit;
    }
    
    list($table, $column) = explode('.', $field, 2);
    
    // Sanitize
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
        echo json_encode(['success' => false, 'error' => 'Invalid table or column name']);
        exit;
    }
    
    $sample_value = '';
    
    // Handle computed fields
    if ($table === 'computed' || $table === 'computed_patient') {
        // Get a sample patient for computed fields
        $patient = sqlQuery("SELECT fname, mname, lname, street, city, state, postal_code, DOB, phone_cell, phone_home, email FROM patient_data WHERE fname != '' AND fname IS NOT NULL ORDER BY pid DESC LIMIT 1");
        
        if ($patient) {
            switch ($column) {
                case 'full_name':
                    $sample_value = trim($patient['fname'] . ' ' . $patient['lname']);
                    break;
                case 'full_name_lfm':
                    $sample_value = $patient['lname'] . ', ' . $patient['fname'] . ($patient['mname'] ? ' ' . substr($patient['mname'], 0, 1) : '');
                    break;
                case 'full_name_fml':
                    $sample_value = $patient['fname'] . ($patient['mname'] ? ' ' . substr($patient['mname'], 0, 1) : '') . ' ' . $patient['lname'];
                    break;
                case 'name_last_first':
                    $sample_value = $patient['lname'] . ', ' . $patient['fname'];
                    break;
                case 'full_address':
                    $sample_value = $patient['street'] . ', ' . $patient['city'] . ', ' . $patient['state'] . ' ' . $patient['postal_code'];
                    break;
                case 'full_address_multiline':
                    $sample_value = $patient['street'] . "\n" . $patient['city'] . ', ' . $patient['state'] . ' ' . $patient['postal_code'];
                    break;
                case 'city_state_zip':
                    $sample_value = $patient['city'] . ', ' . $patient['state'] . ' ' . $patient['postal_code'];
                    break;
                case 'age':
                    if ($patient['DOB']) {
                        $dob = new DateTime($patient['DOB']);
                        $now = new DateTime();
                        $sample_value = $dob->diff($now)->y . ' years';
                    }
                    break;
                case 'dob_formatted':
                    if ($patient['DOB']) {
                        $sample_value = date('m/d/Y', strtotime($patient['DOB']));
                    }
                    break;
                case 'phone_primary':
                    $sample_value = $patient['phone_cell'] ?: $patient['phone_home'];
                    break;
                case 'todays_date':
                    $sample_value = date('m/d/Y');
                    break;
            }
        }
    } elseif ($table === 'patient_data') {
        $row = sqlQuery("SELECT `$column` FROM patient_data WHERE `$column` IS NOT NULL AND `$column` != '' ORDER BY pid DESC LIMIT 1");
        if ($row) {
            $sample_value = $row[$column];
        }
    } elseif ($table === 'insurance_data' || $table === 'insurance') {
        $row = sqlQuery("SELECT `$column` FROM insurance_data WHERE `$column` IS NOT NULL AND `$column` != '' ORDER BY id DESC LIMIT 1");
        if ($row) {
            $sample_value = $row[$column];
        }
    } elseif ($table === 'users' || $table === 'provider') {
        $row = sqlQuery("SELECT `$column` FROM users WHERE `$column` IS NOT NULL AND `$column` != '' AND authorized = 1 LIMIT 1");
        if ($row) {
            $sample_value = $row[$column];
        }
    } elseif ($table === 'facility') {
        $row = sqlQuery("SELECT `$column` FROM facility WHERE `$column` IS NOT NULL AND `$column` != '' AND primary_business_entity = 1 LIMIT 1");
        if ($row) {
            $sample_value = $row[$column];
        }
    } elseif ($table === 'employer_data') {
        $row = sqlQuery("SELECT `$column` FROM employer_data WHERE `$column` IS NOT NULL AND `$column` != '' ORDER BY id DESC LIMIT 1");
        if ($row) {
            $sample_value = $row[$column];
        }
    } else {
        // Try generic table lookup
        $check = sqlQuery("SHOW TABLES LIKE ?", [$table]);
        if ($check) {
            $row = sqlQuery("SELECT `$column` FROM `$table` WHERE `$column` IS NOT NULL AND `$column` != '' LIMIT 1");
            if ($row) {
                $sample_value = $row[$column];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'field' => $field,
        'sample' => $sample_value ?: '(no sample data)',
        'table' => $table,
        'column' => $column
    ]);

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
