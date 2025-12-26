# Vietnamese PT Module - ACL & Audit Logging Quick Reference

**AI-GENERATED CODE - Claude Sonnet 4.5 (2025-01-22)**

## Quick Reference for Developers

### ACL Permissions Required

| Service | Read Permission | Write Permission | Special Permission |
|---------|----------------|------------------|-------------------|
| PTAssessmentService | `patients/med` | `patients/med/write` | - |
| PTExercisePrescriptionService | `patients/med` | `patients/med/write` | - |
| PTTreatmentPlanService | `patients/med` | `patients/med/write` | - |
| PTOutcomeMeasuresService | `patients/med` | `patients/med/write` | - |
| PTAssessmentTemplateService | `admin/super` | `admin/super` | Admin only |
| VietnameseMedicalTermsService | `patients/med` | - | Read-only |
| VietnameseInsuranceService | `patients/med` | `patients/med/write` | `acct/bill` for billing |

### Audit Events Reference

| Event Code | Description | Logged On | Patient ID Logged |
|------------|-------------|-----------|------------------|
| `pt-assessment-create` | Assessment created | Insert | Yes |
| `pt-assessment-update` | Assessment updated | Update | Yes |
| `pt-assessment-delete` | Assessment cancelled | Delete | Yes |
| `pt-assessment-access` | Assessment viewed | getOne | Yes |
| `pt-exercise-create` | Exercise prescription created | Insert | Yes |
| `pt-exercise-update` | Exercise prescription updated | Update | Yes |
| `pt-exercise-delete` | Exercise prescription discontinued | Delete | No |
| `pt-treatment-plan-create` | Treatment plan created | Insert | Yes |
| `pt-treatment-plan-update` | Treatment plan status changed | updateStatus | No |
| `pt-treatment-plan-access` | Treatment plans accessed | getActivePlans | Yes |
| `pt-outcome-create` | Outcome measure recorded | Insert | Yes |
| `pt-outcome-access` | Outcome measures accessed | getPatientOutcomes | Yes |
| `pt-template-access` | Templates accessed | getActiveTemplates | No |
| `pt-insurance-create` | Insurance record created | Insert | Yes |
| `pt-insurance-access` | Insurance info accessed | getPatientInsurance | Yes |
| `pt-billing-create` | Billing entry created | createBillingEntry | Yes |

### Common Code Patterns

#### Check User Has Read Access
```php
if (!AclMain::aclCheckCore('patients', 'med')) {
    $processingResult->setValidationMessages(['Access Denied']);
    return $processingResult;
}
```

#### Check User Has Write Access
```php
if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
    $processingResult->setValidationMessages(['Access Denied']);
    return $processingResult;
}
```

#### Log Successful Creation
```php
EventAuditLogger::instance()->newEvent(
    'pt-assessment-create',
    $_SESSION['authUser'] ?? 'system',
    $_SESSION['authProvider'] ?? 0,
    1, // 1 = success, 0 = failure
    "Created PT Assessment ID: {$insertId} for patient: {$patientId}",
    $patientId
);
```

#### Log Error with System Logger
```php
SystemLogger::instance()->error('PT Assessment insert failed', [
    'error' => $e->getMessage(),
    'patient_id' => $data['patient_id'] ?? 'unknown'
]);
```

#### Sanitize Vietnamese Input
```php
$data = $this->sanitizeInput($data);
```

### BHYT Card Format

**Valid Format**: `XX#-####-#####-#####`

**Example**: `HC1-8421-12345-67890`

**Valid Prefixes**:
- HC1, HC2, HC3, HC4 (Full coverage)
- DN1, DN2, DN3 (Enterprise)
- TE1, TE2, TE3 (Voluntary)
- CB1, CB2 (Civil servants)
- XK1, XK2 (Export zone)
- NN1, NN2, NN3 (Agricultural)
- TN1, TN2 (Family)
- TX1, TX2 (Students)

### Billing Service Codes

| Code | Name (EN) | Name (VI) | Price (VND) |
|------|-----------|-----------|-------------|
| PT001 | Initial PT Assessment | Đánh giá vật lý trị liệu ban đầu | 500,000 |
| PT002 | Follow-up PT Assessment | Đánh giá vật lý trị liệu tái khám | 300,000 |
| PT101 | Therapeutic Exercise Session | Liệu pháp vận động trị liệu | 250,000 |
| PT102 | Manual Therapy | Liệu pháp thủ công | 350,000 |
| PT103 | Electrotherapy | Liệu pháp điện | 200,000 |
| PT104 | Heat/Cold Therapy | Liệu pháp nhiệt/lạnh | 150,000 |
| PT105 | Ultrasound Therapy | Liệu pháp siêu âm | 180,000 |
| PT106 | Therapeutic Massage | Massage trị liệu | 300,000 |

### Usage Examples

#### Create PT Assessment with Full Logging
```php
use OpenEMR\Services\VietnamesePT\PTAssessmentService;

$service = new PTAssessmentService();
$result = $service->insert([
    'patient_id' => 123,
    'chief_complaint_en' => 'Lower back pain',
    'chief_complaint_vi' => 'Đau lưng dưới',
    'pain_level' => 7,
    'language_preference' => 'vi'
]);

// ACL is checked automatically
// Input is sanitized automatically
// Audit log is created automatically
// Errors are logged automatically

if ($result->hasData()) {
    echo "Assessment created successfully";
} else {
    $errors = $result->getValidationMessages();
    echo "Failed: " . implode(', ', $errors);
}
```

#### Validate BHYT Card
```php
use OpenEMR\Services\VietnamesePT\VietnameseInsuranceService;

$service = new VietnameseInsuranceService();
$validation = $service->validateBHYTCard('HC1-8421-12345-67890');

if ($validation['valid']) {
    echo "Valid card with prefix: " . $validation['prefix_code'];
} else {
    echo "Invalid: " . $validation['message'];
}
```

#### Check Insurance Coverage
```php
$service = new VietnameseInsuranceService();
$coverage = $service->checkCoverage(123, '2025-01-22');

if ($coverage['eligible']) {
    echo "Coverage: " . $coverage['coverage_percent'] . "%";
    echo "Card: " . $coverage['card_number'];
} else {
    echo "Not covered: " . $coverage['message'];
}
```

#### Calculate Copay
```php
$service = new VietnameseInsuranceService();
$copay = $service->calculateCopay('PT001', 123, 500000, '2025-01-22');

echo "Total: " . $copay['total_amount'] . " VND\n";
echo "Insurance pays: " . $copay['insurance_pays'] . " VND\n";
echo "Patient pays: " . $copay['patient_pays'] . " VND\n";
echo "Coverage: " . $copay['coverage_percent'] . "%\n";
```

#### Create Billing Entry
```php
$service = new VietnameseInsuranceService();
$result = $service->createBillingEntry(
    assessmentId: 456,
    encounterId: 789,
    serviceCode: 'PT_ASSESS_INITIAL'
);

if ($result->hasData()) {
    $data = $result->getData()[0];
    echo "Billing ID: " . $data['billing_id'];
    echo "Insurance pays: " . $data['insurance_pays'];
    echo "Patient pays: " . $data['patient_pays'];
}
```

### Troubleshooting

#### User Can't Access PT Data

**Problem**: User receives "Access Denied" message

**Solutions**:
1. Check user has `patients/med` permission in ACL
2. For write operations, check user has `patients/med/write` permission
3. For templates, check user has `admin/super` permission
4. For billing, check user has `acct/bill` permission

**Verify**:
```sql
SELECT ar.rule_id, ar.rule_section, ar.rule_name
FROM users u
JOIN gacl_aro_map gam ON u.username = gam.value
JOIN gacl_acl acl ON gam.group_id = acl.aro_group_id
JOIN gacl_aro_sections_seq ars ON ars.section_value = gam.section_value
JOIN acl_rules ar ON ar.rule_id = acl.acl_id
WHERE u.username = 'USERNAME';
```

#### Audit Logs Not Appearing

**Problem**: Operations succeed but no audit logs

**Check**:
1. Verify `log` table exists and is writable
2. Check `$_SESSION['authUser']` is set
3. Verify EventAuditLogger is working: `SELECT * FROM log ORDER BY date DESC LIMIT 10;`

#### BHYT Card Validation Fails

**Problem**: Valid-looking card is rejected

**Check**:
1. Card format is exactly: `XX#-####-#####-#####`
2. Prefix is one of 18 valid codes
3. No spaces in the card number
4. All characters after prefix are numeric

#### Billing Entry Creation Fails

**Problem**: `createBillingEntry` returns error

**Check**:
1. User has `acct/bill` permission
2. Encounter ID exists in `form_encounter` table
3. Service code is one of: `PT_ASSESS_INITIAL`, `PT_ASSESS_FOLLOWUP`, `PT_EXERCISE_THERAPY`, etc.
4. `billing` table is writable

### Performance Tips

1. **ACL checks are cached** - First check queries database, subsequent checks use cache
2. **Audit logging is fast** - Inserts are batched and non-blocking
3. **Input sanitization is cheap** - O(n) with early termination
4. **BHYT validation is instant** - Regex matching with no database queries

### Security Best Practices

1. **Always use service layer** - Never bypass services to access database directly
2. **Never disable ACL checks** - Even for admin users
3. **Log all access** - Especially for sensitive operations like billing
4. **Sanitize all input** - Even if data comes from trusted sources
5. **Use ProcessingResult** - Always return ProcessingResult, never throw exceptions to UI

### Compliance Checklist

- [x] ACL checks on all CRUD operations
- [x] Audit logging for all data modifications
- [x] Audit logging for sensitive data access
- [x] Error handling with no sensitive data leakage
- [x] Input sanitization for Vietnamese UTF-8
- [x] BHYT validation per Vietnamese standards
- [x] Billing integration with insurance
- [x] Copay calculation per coverage rules

---

**Generated by**: Claude Sonnet 4.5
**Date**: 2025-01-22
**Version**: 1.0
