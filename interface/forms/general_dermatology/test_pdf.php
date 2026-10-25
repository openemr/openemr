<?php
/**
 * Test script for PDF generation
 * 
 * This script tests the PDF generation functionality without requiring a database connection.
 * It uses mock data to simulate the PDF generation process.
 */

// Include required files
require_once(__DIR__ . "/../../globals.php");
require_once(__DIR__ . "/autoload.php");
require_once(__DIR__ . "/vendor/autoload.php");

// Import classes
use GeneralDermatology\Models\Patient;
use GeneralDermatology\Models\MedicalHistory;
use GeneralDermatology\Services\PDFGenerationService;

// Create mock data
$patient = new Patient([
    'pid' => '12345',
    'fname' => 'John',
    'lname' => 'Doe',
    'DOB' => '1980-01-01',
    'sex' => 'Male'
]);

$medicalHistory = new MedicalHistory([
    'general_history' => 'Hypertension, Diabetes',
    'surgical_history' => 'Appendectomy (2010)',
    'skin_cancer_history' => 'Basal Cell Carcinoma (2018)',
    'skin_disease_history' => 'Eczema, Psoriasis',
    'medications' => 'Lisinopril 10mg daily, Metformin 500mg twice daily',
    'alerts' => 'Allergic to penicillin',
    'review_of_systems' => 'No fever, No chills, No weight loss'
]);

$examinationData = [
    'exams' => 'Face, Neck, Arms',
    'exams_observation_1' => 'General Appearance of the patient is well developed and well nourished.',
    'exams_observation_2' => '',
    'exams_observation_3' => '',
    'exams_observation_text' => 'Patient has multiple erythematous papules on the face.',
    'lymph_nodes' => ['Preauricular', 'Occipital'],
    'vitals' => ['BP: 120/80', 'P: 72', 'R: 16'],
    'orientation' => ['Time', 'Place', 'Person'],
    'effect' => 'Normal'
];

$plans = [
    [
        'plan_number' => 1,
        'plan_data' => [
            'finding_location' => 'Face, bilateral cheeks',
            'diagnosis_description' => 'Acne vulgaris',
            'icd_alt' => 'L70.0',
            'diagnosis_alt' => '',
            'severity' => 'Moderate',
            'procedure_plans' => 'Medical Treatment',
            'plan_complete' => "1. Tretinoin 0.025% cream apply to affected areas nightly\n2. Benzoyl peroxide 5% wash daily\n3. Avoid oil-based cosmetics",
            'plan_description' => 'Follow up in 4 weeks to assess response to treatment',
            'biopsy_location_title' => '',
            'biopsy_areas' => '',
            'rule_out_text' => '',
            'biopsy_methods' => []
        ]
    ],
    [
        'plan_number' => 2,
        'plan_data' => [
            'finding_location' => 'Left forearm',
            'diagnosis_description' => 'Suspicious pigmented lesion',
            'icd_alt' => 'D48.5',
            'diagnosis_alt' => '',
            'severity' => '',
            'procedure_plans' => 'Biopsy',
            'plan_complete' => "Performed shave biopsy of 5mm pigmented lesion on left forearm\nSpecimen sent to pathology",
            'plan_description' => 'Follow up in 2 weeks to review pathology results',
            'biopsy_location_title' => 'Left forearm, proximal',
            'biopsy_areas' => 'Left forearm, 5cm distal to antecubital fossa',
            'rule_out_text' => 'Melanoma',
            'biopsy_methods' => ['Shave']
        ]
    ]
];

$formData = [
    'subjective_text' => "Patient presents with facial acne that has been worsening over the past 3 months. Also concerned about a changing mole on the left forearm.\n\nPatient reports trying over-the-counter benzoyl peroxide with minimal improvement."
];

$encounterData = [
    'visit_description' => 'Dermatology Follow-up',
    'note_date' => date('Y-m-d'),
    'provider_name' => 'Dr. Jane Smith'
];

$facilityData = [
    'facility_name' => 'Main Street Dermatology Clinic',
    'website' => 'www.mainstderm.com',
    'phone' => '555-123-4567',
    'fax' => '555-123-4568',
    'address' => '123 Main St, Anytown, USA 12345'
];

// Prepare data for PDF generation
$pdfData = [
    'patient' => $patient,
    'encounter' => $encounterData,
    'facility' => $facilityData,
    'medical_history' => $medicalHistory,
    'examination' => $examinationData,
    'plans' => $plans,
    'form_data' => $formData,
];

// Generate PDF
$pdfService = new PDFGenerationService();
$pdfService->generatePDF($pdfData);
