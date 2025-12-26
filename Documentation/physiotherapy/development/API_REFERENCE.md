# Vietnamese PT Module - REST API Reference

**Version:** 1.0
**Last Updated:** 2025-11-22
**Base URL:** `/apis/default/api/vietnamese-pt`

## Table of Contents

- [Authentication](#authentication)
- [Response Format](#response-format)
- [Error Codes](#error-codes)
- [Rate Limiting](#rate-limiting)
- [API Endpoints](#api-endpoints)
  - [PT Assessments](#pt-assessments)
  - [Exercise Prescriptions](#exercise-prescriptions)
  - [Treatment Plans](#treatment-plans)
  - [Outcome Measures](#outcome-measures)
  - [Medical Terms](#medical-terms)
  - [Translations](#translations)
  - [Insurance (BHYT)](#insurance-bhyt)
  - [Assessment Templates](#assessment-templates)

---

## Authentication

All API endpoints require OAuth2 authentication with appropriate ACL permissions.

### Required ACL Permission
- **Section:** `patients`
- **Level:** `med` (Medical records access)

### Authentication Header
```http
Authorization: Bearer {access_token}
```

### Getting an Access Token

```bash
# Register OAuth2 client (one-time setup)
docker compose exec openemr /root/devtools register-oauth2-client

# Request access token
curl -X POST https://your-openemr-instance/oauth2/default/token \
  -d "grant_type=client_credentials" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "scope=api:oemr"
```

---

## Response Format

### Success Response
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "error_description": [],
  "data": [
    {
      // Resource data
    }
  ]
}
```

### Error Response
```json
{
  "validationErrors": [
    {
      "field": "patient_id",
      "message": "Patient ID is required"
    }
  ],
  "internalErrors": [
    "Database connection error"
  ],
  "error_description": ["Validation failed"],
  "data": []
}
```

---

## Error Codes

| HTTP Status | Meaning | Description |
|------------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request data or validation error |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Insufficient permissions (ACL check failed) |
| 404 | Not Found | Resource not found |
| 500 | Internal Server Error | Server-side error |

---

## Rate Limiting

Currently, no rate limiting is implemented. Standard OpenEMR server limits apply.

---

## API Endpoints

## PT Assessments

Manage bilingual physiotherapy assessments with comprehensive patient evaluation data.

### List All Assessments

```http
GET /api/vietnamese-pt/assessments
```

**Query Parameters:**
- `patient_id` (integer, optional) - Filter by patient ID
- `status` (string, optional) - Filter by status: `draft`, `completed`, `reviewed`, `cancelled`
- `language_preference` (string, optional) - Filter by language: `en`, `vi`

**Example Request:**
```bash
curl -X GET "https://your-openemr/apis/default/api/vietnamese-pt/assessments?patient_id=123&status=completed" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "error_description": [],
  "data": [
    {
      "id": 1,
      "patient_id": 123,
      "encounter_id": 456,
      "assessment_date": "2025-11-22 10:30:00",
      "therapist_id": 10,
      "chief_complaint_en": "Lower back pain",
      "chief_complaint_vi": "Đau lưng dưới",
      "pain_level": 7,
      "pain_location_en": "Lumbar region L3-L5",
      "pain_location_vi": "Vùng thắt lưng L3-L5",
      "pain_description_en": "Sharp, shooting pain on movement",
      "pain_description_vi": "Đau nhói khi cử động",
      "functional_goals_en": "Return to normal daily activities",
      "functional_goals_vi": "Trở lại sinh hoạt bình thường",
      "treatment_plan_en": "Manual therapy and exercise program",
      "treatment_plan_vi": "Trị liệu thủ công và chương trình tập luyện",
      "language_preference": "vi",
      "status": "completed",
      "rom_measurements": {
        "lumbar_flexion": 45,
        "lumbar_extension": 20,
        "left_rotation": 30,
        "right_rotation": 35
      },
      "strength_measurements": {
        "hip_flexors": "4/5",
        "knee_extensors": "5/5",
        "ankle_dorsiflexors": "5/5"
      },
      "balance_assessment": {
        "single_leg_stance_left": 25,
        "single_leg_stance_right": 30,
        "romberg_test": "negative"
      },
      "created_at": "2025-11-22 10:00:00",
      "updated_at": "2025-11-22 11:00:00"
    }
  ]
}
```

---

### Get Single Assessment

```http
GET /api/vietnamese-pt/assessments/:id
```

**Path Parameters:**
- `id` (integer, required) - Assessment ID

**Example Request:**
```bash
curl -X GET "https://your-openemr/apis/default/api/vietnamese-pt/assessments/1" \
  -H "Authorization: Bearer {token}"
```

**Response:** Same as individual item in list response above.

---

### Create Assessment

```http
POST /api/vietnamese-pt/assessments
```

**Request Body:**
```json
{
  "patient_id": 123,
  "encounter_id": 456,
  "assessment_date": "2025-11-22 10:30:00",
  "therapist_id": 10,
  "chief_complaint_en": "Lower back pain",
  "chief_complaint_vi": "Đau lưng dưới",
  "pain_level": 7,
  "pain_location_en": "Lumbar region L3-L5",
  "pain_location_vi": "Vùng thắt lưng L3-L5",
  "pain_description_en": "Sharp, shooting pain on movement",
  "pain_description_vi": "Đau nhói khi cử động",
  "functional_goals_en": "Return to normal daily activities",
  "functional_goals_vi": "Trở lại sinh hoạt bình thường",
  "treatment_plan_en": "Manual therapy and exercise program",
  "treatment_plan_vi": "Trị liệu thủ công và chương trình tập luyện",
  "language_preference": "vi",
  "status": "completed",
  "rom_measurements": {
    "lumbar_flexion": 45,
    "lumbar_extension": 20
  },
  "strength_measurements": {
    "hip_flexors": "4/5",
    "knee_extensors": "5/5"
  },
  "balance_assessment": {
    "single_leg_stance_left": 25,
    "single_leg_stance_right": 30
  }
}
```

**Whitelisted Fields:**
- `patient_id` (required)
- `encounter_id` (required)
- `assessment_date` (required)
- `therapist_id`
- `chief_complaint_en`, `chief_complaint_vi`
- `pain_level` (0-10 scale)
- `pain_location_en`, `pain_location_vi`
- `pain_description_en`, `pain_description_vi`
- `functional_goals_en`, `functional_goals_vi`
- `treatment_plan_en`, `treatment_plan_vi`
- `language_preference` (`en` or `vi`)
- `status` (`draft`, `completed`, `reviewed`, `cancelled`)
- `rom_measurements` (JSON object)
- `strength_measurements` (JSON object)
- `balance_assessment` (JSON object)

**Example Request:**
```bash
curl -X POST "https://your-openemr/apis/default/api/vietnamese-pt/assessments" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d @assessment.json
```

**Response:** HTTP 201 Created with created assessment data.

---

### Update Assessment

```http
PUT /api/vietnamese-pt/assessments/:id
```

**Path Parameters:**
- `id` (integer, required) - Assessment ID

**Request Body:** Same as create, all fields optional

**Example Request:**
```bash
curl -X PUT "https://your-openemr/apis/default/api/vietnamese-pt/assessments/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "reviewed", "pain_level": 5}'
```

**Response:** HTTP 200 OK with updated assessment data.

---

### Delete Assessment

```http
DELETE /api/vietnamese-pt/assessments/:id
```

**Path Parameters:**
- `id` (integer, required) - Assessment ID

**Example Request:**
```bash
curl -X DELETE "https://your-openemr/apis/default/api/vietnamese-pt/assessments/1" \
  -H "Authorization: Bearer {token}"
```

**Response:** HTTP 200 OK

---

### Get Patient Assessments

```http
GET /api/vietnamese-pt/assessments/patient/:patientId
```

**Path Parameters:**
- `patientId` (integer, required) - Patient ID

**Example Request:**
```bash
curl -X GET "https://your-openemr/apis/default/api/vietnamese-pt/assessments/patient/123" \
  -H "Authorization: Bearer {token}"
```

**Response:** Array of all assessments for the specified patient.

---

## Exercise Prescriptions

Manage bilingual exercise prescriptions with detailed parameters and tracking.

### List All Exercise Prescriptions

```http
GET /api/vietnamese-pt/exercises
```

**Query Parameters:**
- `patient_id` (integer, optional)
- `status` (string, optional) - `active`, `completed`, `discontinued`, `modified`

**Example Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": 1,
      "patient_id": 123,
      "encounter_id": 456,
      "exercise_name": "Lumbar Extension Stretch",
      "exercise_name_vi": "Kéo giãn duỗi thắt lưng",
      "description": "Gentle backward bending exercise",
      "description_vi": "Bài tập uốn người về phía sau nhẹ nhàng",
      "sets_prescribed": 3,
      "reps_prescribed": 10,
      "duration_minutes": 15,
      "frequency_per_week": 5,
      "intensity_level": "moderate",
      "instructions": "Perform slowly with controlled breathing",
      "instructions_vi": "Thực hiện chậm rãi với hơi thở kiểm soát",
      "equipment_needed": "Exercise mat",
      "precautions": "Stop if pain increases",
      "precautions_vi": "Dừng lại nếu đau tăng",
      "start_date": "2025-11-22",
      "end_date": "2025-12-22",
      "prescribed_by": 10
    }
  ]
}
```

---

### Get Single Exercise Prescription

```http
GET /api/vietnamese-pt/exercises/:id
```

---

### Create Exercise Prescription

```http
POST /api/vietnamese-pt/exercises
```

**Request Body:**
```json
{
  "patient_id": 123,
  "encounter_id": 456,
  "exercise_name": "Lumbar Extension Stretch",
  "exercise_name_vi": "Kéo giãn duỗi thắt lưng",
  "description": "Gentle backward bending exercise",
  "description_vi": "Bài tập uốn người về phía sau nhẹ nhàng",
  "sets_prescribed": 3,
  "reps_prescribed": 10,
  "duration_minutes": 15,
  "frequency_per_week": 5,
  "intensity_level": "moderate",
  "instructions": "Perform slowly with controlled breathing",
  "instructions_vi": "Thực hiện chậm rãi với hơi thở kiểm soát",
  "equipment_needed": "Exercise mat",
  "precautions": "Stop if pain increases",
  "precautions_vi": "Dừng lại nếu đau tăng",
  "start_date": "2025-11-22",
  "prescribed_by": 10
}
```

**Whitelisted Fields:**
- `patient_id`, `encounter_id` (required)
- `exercise_name`, `exercise_name_vi` (required)
- `description`, `description_vi`
- `sets_prescribed`, `reps_prescribed`
- `duration_minutes`, `frequency_per_week`
- `intensity_level`
- `instructions`, `instructions_vi`
- `equipment_needed`
- `precautions`, `precautions_vi`
- `start_date`, `end_date`
- `prescribed_by`

---

### Update Exercise Prescription

```http
PUT /api/vietnamese-pt/exercises/:id
```

---

### Delete Exercise Prescription

```http
DELETE /api/vietnamese-pt/exercises/:id
```

---

### Get Patient Exercise Prescriptions

```http
GET /api/vietnamese-pt/exercises/patient/:patientId
```

---

## Treatment Plans

Manage treatment plans for physiotherapy patients.

### List All Treatment Plans

```http
GET /api/vietnamese-pt/treatment-plans
```

**Example Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": 1,
      "patient_id": 123,
      "encounter_id": 456,
      "plan_name_en": "Lower Back Pain Rehabilitation",
      "plan_name_vi": "Phục hồi đau lưng dưới",
      "start_date": "2025-11-22",
      "end_date": "2025-12-22",
      "status": "active",
      "goals_en": "Reduce pain, improve mobility",
      "goals_vi": "Giảm đau, cải thiện vận động",
      "created_at": "2025-11-22 10:00:00"
    }
  ]
}
```

---

### Get Single Treatment Plan

```http
GET /api/vietnamese-pt/treatment-plans/:id
```

---

### Create Treatment Plan

```http
POST /api/vietnamese-pt/treatment-plans
```

---

### Update Treatment Plan

```http
PUT /api/vietnamese-pt/treatment-plans/:id
```

---

### Delete Treatment Plan

```http
DELETE /api/vietnamese-pt/treatment-plans/:id
```

---

## Outcome Measures

Track patient progress using standardized outcome measures.

### List All Outcome Measures

```http
GET /api/vietnamese-pt/outcomes
```

**Example Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": 1,
      "patient_id": 123,
      "assessment_id": 456,
      "measure_name_en": "Visual Analog Scale (VAS) - Pain",
      "measure_name_vi": "Thang đo tương tự trực quan - Đau",
      "measurement_date": "2025-11-22 10:30:00",
      "raw_score": 7.0,
      "percentage_score": 70.0,
      "interpretation_en": "Moderate to severe pain",
      "interpretation_vi": "Đau mức độ trung bình đến nặng",
      "unit_of_measure": "cm",
      "clinical_significance": "stable",
      "baseline_measurement": true
    }
  ]
}
```

---

### Get Single Outcome Measure

```http
GET /api/vietnamese-pt/outcomes/:id
```

---

### Create Outcome Measure

```http
POST /api/vietnamese-pt/outcomes
```

---

### Update Outcome Measure

```http
PUT /api/vietnamese-pt/outcomes/:id
```

---

### Delete Outcome Measure

```http
DELETE /api/vietnamese-pt/outcomes/:id
```

---

## Medical Terms

Access Vietnamese-English medical terminology dictionary.

### List All Medical Terms

```http
GET /api/vietnamese-pt/medical-terms
```

**Example Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": 1,
      "english_term": "pain",
      "vietnamese_term": "đau",
      "category": "symptom",
      "description_en": "Physical suffering or discomfort",
      "description_vi": "Cảm giác đau đớn hoặc khó chịu về thể chất",
      "usage_context": "general"
    }
  ]
}
```

---

### Search Medical Terms

```http
GET /api/vietnamese-pt/medical-terms/search/:term
```

**Path Parameters:**
- `term` (string, required) - Search term

**Query Parameters:**
- `language` (string, optional) - Search language: `en` (default), `vi`

**Example Request:**
```bash
curl -X GET "https://your-openemr/apis/default/api/vietnamese-pt/medical-terms/search/pain?language=en" \
  -H "Authorization: Bearer {token}"
```

---

### Translate Medical Term

```http
GET /api/vietnamese-pt/medical-terms/translate/:term
```

**Path Parameters:**
- `term` (string, required) - Term to translate

**Query Parameters:**
- `from` (string, optional) - Source language: `en` (default), `vi`

**Example Request:**
```bash
curl -X GET "https://your-openemr/apis/default/api/vietnamese-pt/medical-terms/translate/pain?from=en" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": {
    "source_term": "pain",
    "translated_term": "đau",
    "source_language": "en",
    "target_language": "vi"
  }
}
```

---

### Get Medical Term Categories

```http
GET /api/vietnamese-pt/medical-terms/categories
```

**Example Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    "symptom",
    "diagnosis",
    "treatment",
    "anatomy",
    "procedure",
    "assessment"
  ]
}
```

---

## Translations

Manage custom translation entries (beyond medical terms).

### List All Translations

```http
GET /api/vietnamese-pt/translations
```

---

### Get Single Translation

```http
GET /api/vietnamese-pt/translations/:id
```

---

### Create Translation

```http
POST /api/vietnamese-pt/translations
```

---

### Update Translation

```http
PUT /api/vietnamese-pt/translations/:id
```

---

### Delete Translation

```http
DELETE /api/vietnamese-pt/translations/:id
```

---

## Insurance (BHYT)

Manage Vietnamese health insurance (Bảo hiểm Y tế - BHYT) information.

### List All Insurance Records

```http
GET /api/vietnamese-pt/insurance
```

**Example Response:**
```json
{
  "validationErrors": [],
  "internalErrors": [],
  "data": [
    {
      "id": 1,
      "patient_id": 123,
      "bhyt_card_number": "DN1234567890123",
      "insurance_provider": "Bảo hiểm Xã hội Việt Nam",
      "coverage_type": "Toàn dân",
      "coverage_percentage": 80.00,
      "valid_from": "2025-01-01",
      "valid_to": "2025-12-31",
      "registered_hospital": "Bệnh viện Đà Nẵng",
      "hospital_code": "79001",
      "is_active": 1
    }
  ]
}
```

---

### Get Single Insurance Record

```http
GET /api/vietnamese-pt/insurance/:id
```

---

### Create Insurance Record

```http
POST /api/vietnamese-pt/insurance
```

**Request Body:**
```json
{
  "patient_id": 123,
  "bhyt_card_number": "DN1234567890123",
  "insurance_provider": "Bảo hiểm Xã hội Việt Nam",
  "coverage_type": "Toàn dân",
  "coverage_percentage": 80.00,
  "valid_from": "2025-01-01",
  "valid_to": "2025-12-31",
  "registered_hospital": "Bệnh viện Đà Nẵng",
  "hospital_code": "79001"
}
```

---

### Update Insurance Record

```http
PUT /api/vietnamese-pt/insurance/:id
```

---

### Delete Insurance Record

```http
DELETE /api/vietnamese-pt/insurance/:id
```

---

## Assessment Templates

Manage reusable assessment templates for standardized evaluations.

### List All Assessment Templates

```http
GET /api/vietnamese-pt/assessment-templates
```

---

### Get Single Assessment Template

```http
GET /api/vietnamese-pt/assessment-templates/:id
```

---

### Create Assessment Template

```http
POST /api/vietnamese-pt/assessment-templates
```

---

### Update Assessment Template

```http
PUT /api/vietnamese-pt/assessment-templates/:id
```

---

### Delete Assessment Template

```http
DELETE /api/vietnamese-pt/assessment-templates/:id
```

---

## Postman Collection

A Postman collection for testing these endpoints is available:

**Location:** `/Documentation/physiotherapy/development/Vietnamese_PT_API.postman_collection.json`

**Import Steps:**
1. Open Postman
2. Click "Import" → "Choose Files"
3. Select the collection file
4. Configure environment variables:
   - `base_url`: Your OpenEMR instance URL
   - `access_token`: Your OAuth2 token

---

## Code Examples

### PHP Service Layer Example

```php
use OpenEMR\Services\VietnamesePT\PTAssessmentService;

$service = new PTAssessmentService();

// Create new assessment
$data = [
    'patient_id' => 123,
    'encounter_id' => 456,
    'chief_complaint_en' => 'Lower back pain',
    'chief_complaint_vi' => 'Đau lưng dưới',
    'pain_level' => 7,
    'language_preference' => 'vi'
];

$result = $service->insert($data);

if (!$result->hasErrors()) {
    $assessmentId = $result->getData()['id'];
    echo "Assessment created: $assessmentId";
} else {
    print_r($result->getValidationMessages());
}
```

### JavaScript/Fetch Example

```javascript
const baseUrl = 'https://your-openemr/apis/default/api/vietnamese-pt';
const token = 'your-oauth2-token';

// Create assessment
async function createAssessment(data) {
  const response = await fetch(`${baseUrl}/assessments`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });

  return await response.json();
}

// Usage
const newAssessment = await createAssessment({
  patient_id: 123,
  encounter_id: 456,
  chief_complaint_en: 'Lower back pain',
  chief_complaint_vi: 'Đau lưng dưới',
  pain_level: 7,
  language_preference: 'vi'
});
```

---

## Testing

### Running API Tests

```bash
# Run all Vietnamese PT API tests
docker compose exec openemr /root/devtools vietnamese-test

# Run specific test suite
./vendor/bin/phpunit --testsuite vietnamese-pt-api
```

### Manual Testing with cURL

```bash
# Set variables
export BASE_URL="https://your-openemr/apis/default/api/vietnamese-pt"
export TOKEN="your-oauth2-token"

# List assessments
curl -X GET "$BASE_URL/assessments" \
  -H "Authorization: Bearer $TOKEN"

# Create assessment
curl -X POST "$BASE_URL/assessments" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "patient_id": 123,
    "encounter_id": 456,
    "chief_complaint_en": "Lower back pain",
    "chief_complaint_vi": "Đau lưng dưới",
    "pain_level": 7
  }'
```

---

## Support

For API issues or questions:

- **Documentation:** `/Documentation/physiotherapy/`
- **Troubleshooting:** `/Documentation/physiotherapy/user-guides/TROUBLESHOOTING.md`
- **Developer Contact:** Dang Tran <tqvdang@msn.com>

---

**API Documentation Generated:** 2025-11-22
**Compatible with:** OpenEMR 7.0.0+ with Vietnamese PT Module
