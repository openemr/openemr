# OpenEMR OAuth2 Scopes Reference

This document describes all OAuth2 scopes available in OpenEMR and their relationships to ACL permissions.

## Overview

OpenEMR implements **SMART on FHIR v2.2.0** scopes. The authorization system has two layers:

| Layer         | Purpose                      | Check Method                      |
|---------------|------------------------------|-----------------------------------|
| OAuth2 Scopes | Token-based API permissions  | `RestConfig::scope_check()`       |
| ACL System    | Role-based user permissions  | `AclMain::aclCheckCore()`         |

**Both layers must pass** for access to be granted.

## Scope Format

```
<context>/<Resource>.<permissions>[?<query>]
```

| Component     | Description                                      | Examples                           |
|---------------|--------------------------------------------------|-------------------------------------|
| `context`     | Who is requesting access                         | `patient`, `user`, `system`         |
| `Resource`    | Resource type (FHIR) or resource name (Standard) | `Patient`, `Observation`, `allergy` |
| `permissions` | CRUDS flags or V1 read/write                     | `.rs`, `.cruds`, `.read`, `.write`  |
| `query`       | Optional granular restrictions                   | `?category=http://...`              |

### Permission Flags (V2)

| Flag | Meaning | HTTP Operations |
|------|---------|-----------------|
| `c`  | Create  | POST            |
| `r`  | Read    | GET (single)    |
| `u`  | Update  | PUT/PATCH       |
| `d`  | Delete  | DELETE          |
| `s`  | Search  | GET (search)    |

**Note:** Flags must be in order: `c` → `r` → `u` → `d` → `s`

### V1 vs V2 Permissions

| V1 Scope        | V2 Equivalent  | Meaning                    |
|-----------------|----------------|----------------------------|
| `.read`         | `.rs`          | Read + Search              |
| `.write`        | `.cud`         | Create + Update + Delete   |

---

## Core Scopes

Required infrastructure scopes for authentication and API access.

| Scope             | Description                                               | Required |
|-------------------|-----------------------------------------------------------|----------|
| `openid`          | OpenID Connect authentication                             | Yes      |
| `fhirUser`        | Identify authorized user                                  | No       |
| `online_access`   | Session-based access (while user logged in)               | No       |
| `offline_access`  | Refresh token access (persist after logout)               | No       |
| `launch`          | EHR launch capability                                     | No       |
| `launch/patient`  | Patient selection at launch                               | No       |
| `profile`         | User profile information                                  | No       |
| `email`           | User email address                                        | No       |
| `phone`           | User phone number                                         | No       |
| `address`         | User address                                              | No       |

### API Type Scopes

| Scope      | Description                           | API Base Path   |
|------------|---------------------------------------|-----------------|
| `api:oemr` | Standard OpenEMR API access           | `/api/`         |
| `api:fhir` | FHIR API access                       | `/fhir/`        |
| `api:port` | Patient Portal API access             | `/portal/`      |

---

## FHIR API Scopes (api:fhir)

### Patient Context Scopes

| Scope (V1)                         | Scope (V2)                    | FHIR Resource        | ACL Section  | ACL Value    |
|------------------------------------|-------------------------------|----------------------|--------------|--------------|
| `patient/AllergyIntolerance.read`  | `patient/AllergyIntolerance.rs` | AllergyIntolerance   | `patients`   | `med`        |
| `patient/Appointment.read`         | `patient/Appointment.rs`      | Appointment          | `patients`   | `appt`       |
| `patient/CarePlan.read`            | `patient/CarePlan.rs`         | CarePlan             | `patients`   | `med`        |
| `patient/CareTeam.read`            | `patient/CareTeam.rs`         | CareTeam             | `patients`   | `med`        |
| `patient/Condition.read`           | `patient/Condition.rs`        | Condition            | `patients`   | `med`        |
| `patient/Coverage.read`            | `patient/Coverage.rs`         | Coverage             | `patients`   | `demo`       |
| `patient/Device.read`              | `patient/Device.rs`           | Device               | `patients`   | `med`        |
| `patient/DiagnosticReport.read`    | `patient/DiagnosticReport.rs` | DiagnosticReport     | `patients`   | `med`        |
| `patient/DocumentReference.read`   | `patient/DocumentReference.rs`| DocumentReference    | `patients`   | `docs`       |
| `patient/Binary.read`              | `patient/Binary.rs`           | Binary               | `patients`   | `docs`       |
| `patient/Encounter.read`           | `patient/Encounter.rs`        | Encounter            | `encounters` | `notes`      |
| `patient/Goal.read`                | `patient/Goal.rs`             | Goal                 | `patients`   | `med`        |
| `patient/Group.read`               | `patient/Group.rs`            | Group                | `patients`   | `demo`       |
| `patient/Immunization.read`        | `patient/Immunization.rs`     | Immunization         | `patients`   | `med`        |
| `patient/Location.read`            | `patient/Location.rs`         | Location             | `admin`      | `practice`   |
| `patient/Medication.read`          | `patient/Medication.rs`       | Medication           | `patients`   | `rx`         |
| `patient/MedicationRequest.read`   | `patient/MedicationRequest.rs`| MedicationRequest    | `patients`   | `rx`         |
| `patient/Observation.read`         | `patient/Observation.rs`      | Observation          | `patients`   | `med`        |
| `patient/Organization.read`        | `patient/Organization.rs`     | Organization         | `admin`      | `practice`   |
| `patient/Patient.read`             | `patient/Patient.rs`          | Patient              | `patients`   | `demo`       |
| `patient/Person.read`              | `patient/Person.rs`           | Person               | `patients`   | `demo`       |
| `patient/Practitioner.read`        | `patient/Practitioner.rs`     | Practitioner         | `admin`      | `users`      |
| `patient/PractitionerRole.read`    | `patient/PractitionerRole.rs` | PractitionerRole     | `admin`      | `users`      |
| `patient/Procedure.read`           | `patient/Procedure.rs`        | Procedure            | `patients`   | `med`        |
| `patient/Provenance.read`          | `patient/Provenance.rs`       | Provenance           | `patients`   | `med`        |
| `patient/ValueSet.read`            | `patient/ValueSet.rs`         | ValueSet             | -            | -            |
| `patient/OperationDefinition.read` | `patient/OperationDefinition.rs` | OperationDefinition | -           | -            |

### User Context Scopes

| Scope (V1)                       | Scope (V2)                  | FHIR Resource        | ACL Section  | ACL Value    |
|----------------------------------|-----------------------------|----------------------|--------------|--------------|
| `user/AllergyIntolerance.read`   | `user/AllergyIntolerance.rs`| AllergyIntolerance   | `patients`   | `med`        |
| `user/Appointment.read`          | `user/Appointment.rs`       | Appointment          | `patients`   | `appt`       |
| `user/CarePlan.read`             | `user/CarePlan.rs`          | CarePlan             | `patients`   | `med`        |
| `user/CareTeam.read`             | `user/CareTeam.rs`          | CareTeam             | `patients`   | `med`        |
| `user/Condition.read`            | `user/Condition.rs`         | Condition            | `patients`   | `med`        |
| `user/Coverage.read`             | `user/Coverage.rs`          | Coverage             | `patients`   | `demo`       |
| `user/Device.read`               | `user/Device.rs`            | Device               | `patients`   | `med`        |
| `user/DiagnosticReport.read`     | `user/DiagnosticReport.rs`  | DiagnosticReport     | `patients`   | `med`        |
| `user/DocumentReference.read`    | `user/DocumentReference.rs` | DocumentReference    | `patients`   | `docs`       |
| `user/Encounter.read`            | `user/Encounter.rs`         | Encounter            | `encounters` | `notes`      |
| `user/Goal.read`                 | `user/Goal.rs`              | Goal                 | `patients`   | `med`        |
| `user/Immunization.read`         | `user/Immunization.rs`      | Immunization         | `patients`   | `med`        |
| `user/Location.read`             | `user/Location.rs`          | Location             | `admin`      | `practice`   |
| `user/Medication.read`           | `user/Medication.rs`        | Medication           | `patients`   | `rx`         |
| `user/MedicationRequest.read`    | `user/MedicationRequest.rs` | MedicationRequest    | `patients`   | `rx`         |
| `user/Observation.read`          | `user/Observation.rs`       | Observation          | `patients`   | `med`        |
| `user/Organization.read`         | `user/Organization.rs`      | Organization         | `admin`      | `practice`   |
| `user/Organization.write`        | `user/Organization.cud`     | Organization         | `admin`      | `practice`   |
| `user/Patient.read`              | `user/Patient.rs`           | Patient              | `patients`   | `demo`       |
| `user/Patient.write`             | `user/Patient.cud`          | Patient              | `patients`   | `demo`       |
| `user/Practitioner.read`         | `user/Practitioner.rs`      | Practitioner         | `admin`      | `users`      |
| `user/Practitioner.write`        | `user/Practitioner.cud`     | Practitioner         | `admin`      | `users`      |
| `user/PractitionerRole.read`     | `user/PractitionerRole.rs`  | PractitionerRole     | `admin`      | `users`      |
| `user/Procedure.read`            | `user/Procedure.rs`         | Procedure            | `patients`   | `med`        |
| `user/Provenance.read`           | `user/Provenance.rs`        | Provenance           | `patients`   | `med`        |

### System Context Scopes (Optional)

System scopes are prefixed with `system/` instead of `user/` or `patient/`. They are **disabled by default** and must be enabled via configuration.

Same resources as user context apply.

### Granular Scopes with Category Restrictions (V2)

| Scope                                                                                           | Category Filter           |
|-------------------------------------------------------------------------------------------------|---------------------------|
| `patient/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category\|health-concern`         | Health Concern            |
| `patient/Condition.rs?category=http://terminology.hl7.org/CodeSystem/condition-category\|encounter-diagnosis`     | Encounter Diagnosis       |
| `patient/Condition.rs?category=http://terminology.hl7.org/CodeSystem/condition-category\|problem-list-item`       | Problem List Item         |
| `patient/Observation.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-category\|sdoh`                   | Social Determinants       |
| `patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category\|social-history`      | Social History            |
| `patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category\|laboratory`          | Laboratory                |
| `patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category\|survey`              | Survey                    |
| `patient/Observation.rs?category=http://terminology.hl7.org/CodeSystem/observation-category\|vital-signs`         | Vital Signs               |
| `patient/DocumentReference.rs?category=http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category\|clinical-note` | Clinical Note |

### Special Operations

| Scope                               | Description                          | ACL Section | ACL Value |
|-------------------------------------|--------------------------------------|-------------|-----------|
| `patient/DocumentReference.$docref` | Generate Clinical Summary (CCD)      | `patients`  | `docs`    |
| `user/DocumentReference.$docref`    | Generate Clinical Summary (CCD)      | `patients`  | `docs`    |
| `system/Patient.$export`            | Bulk export patient compartment      | `admin`     | `super`   |
| `system/Group.$export`              | Bulk export group-specific patients  | `admin`     | `super`   |
| `system/*.$export`                  | Bulk export entire system            | `admin`     | `super`   |
| `system/*.$bulkdata-status`         | Check bulk export job status         | `admin`     | `super`   |

---

## Standard API Scopes (api:oemr)

### User Context Scopes (V1)

| Scope (V1)                    | Scope (V2)             | Resource           | ACL Section  | ACL Value    |
|-------------------------------|------------------------|--------------------|--------------|--------------|
| `user/allergy.read`           | `user/allergy.rs`      | allergy            | `patients`   | `med`        |
| `user/allergy.write`          | `user/allergy.cud`     | allergy            | `patients`   | `med`        |
| `user/appointment.read`       | `user/appointment.rs`  | appointment        | `patients`   | `appt`       |
| `user/appointment.write`      | `user/appointment.cud` | appointment        | `patients`   | `appt`       |
| `user/dental_issue.read`      | `user/dental_issue.rs` | dental_issue       | `patients`   | `med`        |
| `user/dental_issue.write`     | `user/dental_issue.cud`| dental_issue       | `patients`   | `med`        |
| `user/document.read`          | `user/document.rs`     | document           | `patients`   | `docs`       |
| `user/document.write`         | `user/document.cud`    | document           | `patients`   | `docs`       |
| `user/drug.read`              | `user/drug.rs`         | drug               | -            | -            |
| `user/employer.read`          | `user/employer.s`      | employer           | -            | -            |
| `user/encounter.read`         | `user/encounter.rs`    | encounter          | `encounters` | `notes`      |
| `user/encounter.write`        | `user/encounter.cud`   | encounter          | `encounters` | `notes`      |
| `user/facility.read`          | `user/facility.rs`     | facility           | `admin`      | `practice`   |
| `user/facility.write`         | `user/facility.cud`    | facility           | `admin`      | `practice`   |
| `user/immunization.read`      | `user/immunization.rs` | immunization       | `patients`   | `med`        |
| `user/insurance.read`         | `user/insurance.rs`    | insurance          | `patients`   | `demo`       |
| `user/insurance.write`        | `user/insurance.cud`   | insurance          | `patients`   | `demo`       |
| `user/insurance_company.read` | `user/insurance_company.rs`  | insurance_company | `admin`  | `practice`   |
| `user/insurance_company.write`| `user/insurance_company.cud` | insurance_company | `admin`  | `practice`   |
| `user/insurance_type.read`    | `user/insurance_type.s`| insurance_type     | -            | -            |
| `user/list.read`              | `user/list.r`          | list               | `lists`      | `default`    |
| `user/medical_problem.read`   | `user/medical_problem.rs`  | medical_problem | `patients`   | `med`        |
| `user/medical_problem.write`  | `user/medical_problem.cud` | medical_problem | `patients`   | `med`        |
| `user/medication.read`        | `user/medication.rs`   | medication         | `patients`   | `rx`         |
| `user/medication.write`       | `user/medication.cud`  | medication         | `patients`   | `rx`         |
| `user/message.write`          | `user/message.cud`     | message            | -            | -            |
| `user/patient.read`           | `user/patient.rs`      | patient            | `patients`   | `demo`       |
| `user/patient.write`          | `user/patient.cud`     | patient            | `patients`   | `demo`       |
| `user/practitioner.read`      | `user/practitioner.rs` | practitioner       | `admin`      | `users`      |
| `user/practitioner.write`     | `user/practitioner.cud`| practitioner       | `admin`      | `users`      |
| `user/prescription.read`      | `user/prescription.rs` | prescription       | `patients`   | `rx`         |
| `user/procedure.read`         | `user/procedure.rs`    | procedure          | `patients`   | `med`        |
| `user/product.read`           | `user/product.s`       | product            | `inventory`  | `lots`       |
| `user/soap_note.read`         | `user/soap_note.rs`    | soap_note          | `encounters` | `notes`      |
| `user/soap_note.write`        | `user/soap_note.cud`   | soap_note          | `encounters` | `notes`      |
| `user/surgery.read`           | `user/surgery.rs`      | surgery            | `patients`   | `med`        |
| `user/surgery.write`          | `user/surgery.cud`     | surgery            | `patients`   | `med`        |
| `user/transaction.read`       | `user/transaction.rs`  | transaction        | `patients`   | `trans`      |
| `user/transaction.write`      | `user/transaction.cud` | transaction        | `patients`   | `trans`      |
| `user/user.read`              | `user/user.rs`         | user               | `admin`      | `users`      |
| `user/vital.read`             | `user/vital.rs`        | vital              | `patients`   | `med`        |
| `user/vital.write`            | `user/vital.cud`       | vital              | `patients`   | `med`        |
| `user/version.read`           | `user/version.s`       | version            | -            | -            |
| `user/member.read`            | `user/member.rs`       | member             | `admin`      | `groups`     |
| `user/member.write`           | `user/member.cud`      | member             | `admin`      | `groups`     |
| `user/group.read`             | `user/group.rs`        | group              | `admin`      | `groups`     |
| `user/group.write`            | `user/group.cud`       | group              | `admin`      | `groups`     |

### Patient Context Scopes

| Scope                    | Resource    | Description                   |
|--------------------------|-------------|-------------------------------|
| `patient/patient.read`   | patient     | Read own patient record       |
| `patient/appointment.read`| appointment| Read own appointments         |
| `patient/encounter.read` | encounter   | Read own encounters           |

### Special Operations

| Scope                          | Description                          |
|--------------------------------|--------------------------------------|
| `user/insurance.$swap-insurance` | Swap insurance position            |

---

## Patient Portal Scopes (api:port)

| Scope                    | Resource    | Permissions | Description                |
|--------------------------|-------------|-------------|----------------------------|
| `patient/patient.s`      | patient     | Search      | Search own patient record  |
| `patient/encounter.rs`   | encounter   | Read+Search | Read own encounters        |
| `patient/appointment.rs` | appointment | Read+Search | Read own appointments      |

---

## ACL Sections Reference

| Section (`gacl_aco_sections.value`) | Description             |
|-------------------------------------|-------------------------|
| `admin`                             | Administration          |
| `acct`                              | Accounting              |
| `patients`                          | Patient Information     |
| `encounters`                        | Encounter Information   |
| `lists`                             | Lists                   |
| `sensitivities`                     | Sensitivity Levels      |
| `groups`                            | Group Management        |
| `inventory`                         | Inventory               |
| `patientportal`                     | Patient Portal          |
| `menus`                             | Menu Access             |
| `nationnotes`                       | Nation Notes Config     |

---

## ACL Values Reference

### admin Section

| Value (`gacl_aco.value`) | Description                        |
|--------------------------|------------------------------------|
| `super`                  | Superuser (bypasses all checks)    |
| `users`                  | Users/Groups/Logs Administration   |
| `calendar`               | Calendar Settings                  |
| `database`               | Database Administration            |
| `forms`                  | Forms Administration               |
| `practice`               | Practice Settings                  |
| `superbill`              | Superbill Management               |
| `drugs`                  | Drug Administration                |
| `acl`                    | ACL Administration                 |
| `manage_modules`         | Module Management                  |

### patients Section

| Value (`gacl_aco.value`) | Description                        |
|--------------------------|------------------------------------|
| `appt`                   | Appointments                       |
| `demo`                   | Demographics                       |
| `med`                    | Medical/History                    |
| `trans`                  | Transactions                       |
| `docs`                   | Documents                          |
| `docs_rm`                | Documents Delete                   |
| `notes`                  | Patient Notes                      |
| `sign`                   | Sign Lab Results                   |
| `reminder`               | Patient Reminders                  |
| `alert`                  | Clinical Reminders/Alerts          |
| `disclosure`             | Disclosures                        |
| `rx`                     | Prescriptions                      |
| `amendment`              | Amendments                         |
| `lab`                    | Lab Results                        |
| `pat_rep`                | Patient Report                     |

### encounters Section

| Value (`gacl_aco.value`) | Description                        |
|--------------------------|------------------------------------|
| `auth`                   | Authorize Encounters               |
| `auth_a`                 | Authorize Any Encounter            |
| `coding`                 | Coding Own Encounters              |
| `coding_a`               | Coding Any Encounter               |
| `notes`                  | Notes Own Encounters               |
| `notes_a`                | Notes Any Encounter                |
| `date_a`                 | Fix Encounter Dates                |
| `relaxed`                | Relaxed Billing                    |

### acct Section

| Value (`gacl_aco.value`) | Description                        |
|--------------------------|------------------------------------|
| `bill`                   | Billing (write optional)           |
| `disc`                   | Discount                           |
| `eob`                    | EOB Posting                        |
| `rep`                    | Financial Reports (limited)        |
| `rep_a`                  | Financial Reports (full)           |

### inventory Section

| Value (`gacl_aco.value`) | Description                        |
|--------------------------|------------------------------------|
| `lots`                   | Lots                               |
| `sales`                  | Sales                              |
| `purchases`              | Purchases                          |
| `transfers`              | Transfers                          |
| `adjustments`            | Adjustments                        |
| `consumption`            | Consumption                        |
| `destruction`            | Destruction                        |
| `reporting`              | Reporting                          |

---

## Source Files

| File                                                                 | Description                        |
|----------------------------------------------------------------------|------------------------------------|
| `src/Common/Auth/OpenIDConnect/Entities/ServerScopeListEntity.php`   | All scope definitions              |
| `src/Common/Auth/OpenIDConnect/Repositories/ScopeRepository.php`     | Scope validation and lookup        |
| `src/Common/Auth/OpenIDConnect/Entities/ScopeEntity.php`             | Scope parsing                      |
| `src/Common/Auth/OpenIDConnect/Entities/ScopePermissionObject.php`   | Permission flag handling           |
| `src/RestControllers/Config/RestConfig.php`                          | Scope and ACL checking             |
| `src/Common/Acl/AclMain.php`                                         | ACL core implementation            |
| `src/Fixture/data/clean/acl/gacl_aco_sections.json`                  | ACL sections data                  |
| `src/Fixture/data/clean/acl/gacl_aco.json`                           | ACL values data                    |
