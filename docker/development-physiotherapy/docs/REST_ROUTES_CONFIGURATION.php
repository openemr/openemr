<?php
/**
 * Vietnamese PT REST Routes Configuration
 *
 * Add these routes to: _rest_routes.inc.php
 *
 * Location: /path/to/openemr/_rest_routes.inc.php
 *
 * Copy the routes below and paste them into the appropriate section
 * of the _rest_routes.inc.php file
 */

// ============================================
// Vietnamese PT Assessment Routes
// ============================================

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/assessments'] = [
    'VietnamesePT\PTAssessmentRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/assessments/:id'] = [
    'VietnamesePT\PTAssessmentRestController' => 'getOne',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['POST /api/vietnamese-pt/assessments'] = [
    'VietnamesePT\PTAssessmentRestController' => 'post',
    'method' => 'POST',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['PUT /api/vietnamese-pt/assessments/:id'] = [
    'VietnamesePT\PTAssessmentRestController' => 'put',
    'method' => 'PUT',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['DELETE /api/vietnamese-pt/assessments/:id'] = [
    'VietnamesePT\PTAssessmentRestController' => 'delete',
    'method' => 'DELETE',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/patients/:patientId/assessments'] = [
    'VietnamesePT\PTAssessmentRestController' => 'getPatientAssessments',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/assessments/search/:term'] = [
    'VietnamesePT\PTAssessmentRestController' => 'searchVietnamese',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

// ============================================
// Vietnamese Medical Terms Routes
// ============================================

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/medical-terms'] = [
    'VietnamesePT\VietnameseMedicalTermsRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/medical-terms/search/:term'] = [
    'VietnamesePT\VietnameseMedicalTermsRestController' => 'search',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/medical-terms/translate/:term'] = [
    'VietnamesePT\VietnameseMedicalTermsRestController' => 'translate',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/medical-terms/categories'] = [
    'VietnamesePT\VietnameseMedicalTermsRestController' => 'getCategories',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

// ============================================
// PT Exercise Prescription Routes
// ============================================

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/exercises'] = [
    'VietnamesePT\PTExercisePrescriptionRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/exercises/:id'] = [
    'VietnamesePT\PTExercisePrescriptionRestController' => 'getOne',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['POST /api/vietnamese-pt/exercises'] = [
    'VietnamesePT\PTExercisePrescriptionRestController' => 'post',
    'method' => 'POST',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['PUT /api/vietnamese-pt/exercises/:id'] = [
    'VietnamesePT\PTExercisePrescriptionRestController' => 'put',
    'method' => 'PUT',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['DELETE /api/vietnamese-pt/exercises/:id'] = [
    'VietnamesePT\PTExercisePrescriptionRestController' => 'delete',
    'method' => 'DELETE',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/patients/:patientId/exercises'] = [
    'VietnamesePT\PTExercisePrescriptionRestController' => 'getPatientPrescriptions',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

// ============================================
// PT Outcome Measures Routes
// ============================================

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/outcomes'] = [
    'VietnamesePT\PTOutcomeMeasuresRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/outcomes/:id'] = [
    'VietnamesePT\PTOutcomeMeasuresRestController' => 'getOne',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['POST /api/vietnamese-pt/outcomes'] = [
    'VietnamesePT\PTOutcomeMeasuresRestController' => 'post',
    'method' => 'POST',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['PUT /api/vietnamese-pt/outcomes/:id'] = [
    'VietnamesePT\PTOutcomeMeasuresRestController' => 'put',
    'method' => 'PUT',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['DELETE /api/vietnamese-pt/outcomes/:id'] = [
    'VietnamesePT\PTOutcomeMeasuresRestController' => 'delete',
    'method' => 'DELETE',
    'acl' => ['vietnamese-pt', 'write']
];

// ============================================
// PT Treatment Plan Routes
// ============================================

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/treatment-plans'] = [
    'VietnamesePT\PTTreatmentPlanRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/treatment-plans/:id'] = [
    'VietnamesePT\PTTreatmentPlanRestController' => 'getOne',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['POST /api/vietnamese-pt/treatment-plans'] = [
    'VietnamesePT\PTTreatmentPlanRestController' => 'post',
    'method' => 'POST',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['PUT /api/vietnamese-pt/treatment-plans/:id'] = [
    'VietnamesePT\PTTreatmentPlanRestController' => 'put',
    'method' => 'PUT',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['DELETE /api/vietnamese-pt/treatment-plans/:id'] = [
    'VietnamesePT\PTTreatmentPlanRestController' => 'delete',
    'method' => 'DELETE',
    'acl' => ['vietnamese-pt', 'write']
];

// ============================================
// PT Assessment Templates Routes
// ============================================

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/templates'] = [
    'VietnamesePT\PTAssessmentTemplateRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/templates/:id'] = [
    'VietnamesePT\PTAssessmentTemplateRestController' => 'getOne',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

// ============================================
// Vietnamese Insurance Routes
// ============================================

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/insurance'] = [
    'VietnamesePT\VietnameseInsuranceRestController' => 'getAll',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/insurance/:id'] = [
    'VietnamesePT\VietnameseInsuranceRestController' => 'getOne',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['POST /api/vietnamese-pt/insurance'] = [
    'VietnamesePT\VietnameseInsuranceRestController' => 'post',
    'method' => 'POST',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['PUT /api/vietnamese-pt/insurance/:id'] = [
    'VietnamesePT\VietnameseInsuranceRestController' => 'put',
    'method' => 'PUT',
    'acl' => ['vietnamese-pt', 'write']
];

RestConfig::$ROUTE_MAP['DELETE /api/vietnamese-pt/insurance/:id'] = [
    'VietnamesePT\VietnameseInsuranceRestController' => 'delete',
    'method' => 'DELETE',
    'acl' => ['vietnamese-pt', 'write']
];

// ============================================
// Vietnamese Translation Routes
// ============================================

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/translate/to-vietnamese/:term'] = [
    'VietnamesePT\VietnameseTranslationRestController' => 'translateToVietnamese',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

RestConfig::$ROUTE_MAP['GET /api/vietnamese-pt/translate/to-english/:term'] = [
    'VietnamesePT\VietnameseTranslationRestController' => 'translateToEnglish',
    'method' => 'GET',
    'acl' => ['vietnamese-pt', 'view']
];

/**
 * USAGE EXAMPLES:
 *
 * 1. Get all assessments:
 *    GET /apis/default/api/vietnamese-pt/assessments
 *
 * 2. Get patient assessments:
 *    GET /apis/default/api/vietnamese-pt/patients/123/assessments
 *
 * 3. Create assessment:
 *    POST /apis/default/api/vietnamese-pt/assessments
 *    Body: {
 *      "patient_id": 1,
 *      "chief_complaint_vi": "Đau lưng",
 *      "chief_complaint_en": "Back pain",
 *      "pain_level": 7
 *    }
 *
 * 4. Search medical terms:
 *    GET /apis/default/api/vietnamese-pt/medical-terms/search/đau
 *
 * 5. Translate term:
 *    GET /apis/default/api/vietnamese-pt/medical-terms/translate/Physiotherapy
 */