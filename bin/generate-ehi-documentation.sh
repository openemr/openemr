#!/bin/bash

DB_USER=openemr
DB_NAME=openemr
DB_SCHEMA=openemr
DB_HOST=localhost
DB_PORT=8320
DB_PASSWORD=openemr
EHI_ROOT_FILE=../Documentation/EHI_Export/schemaspy
SCHEMA_LOCATION=$EHI_ROOT_FILE/schemas/
TEMPLATE_LOCATION=$EHI_ROOT_FILE/layout/
DOC_OUTPUT=../Documentation/EHI_Export/docs/
JAR_FILES=$EHI_ROOT_FILE/jars

EYE_FORMS="form_eye_acuity|form_eye_antseg|form_eye_base|form_eye_biometrics|form_eye_external|form_eye_hpi|form_eye_locking|form_eye_mag_dispense|form_eye_mag_impplan|form_eye_mag_orders|form_eye_mag_wearing|form_eye_neuro|form_eye_postseg|form_eye_refraction|form_eye_ros|form_eye_vitals|form_taskman"
CAMOS_FORMS="form_CAMOS|form_CAMOS_category|form_CAMOS_item|form_CAMOS_subcategory"
FORM_ENCOUNTERS="forms|form_encounter|form_vitals|form_misc_billing_options|form_care_plan|form_clinical_instructions|form_clinical_notes|form_functional_cognitive_status|form_observation|form_reviewofs|form_ros|form_soap|form_dictation|form_questionnaire_assessments|form_aftercare_plan|form_ankleinjury|form_bronchitis|form_track_anything|form_track_anything_type|form_track_anything_results|form_sdoh|form_painmap|form_treatment_plan|form_prior_auth|form_gad7|form_phq9|form_note|form_transfer_summary|form_clinic_note|form_physical_exam|form_physical_exam_diagnoses|$CAMOS_FORMS|$EYE_FORMS"
BILLING_TABLES="billing|claims|voids|drug_sales|ar_activity|ar_session|insurance_data|eligibility_verification|benefit_eligibility|insurance_companies|insurance_type_codes|facility"
PATIENT_REMINDER_TABLES="patient_reminders|rule_patient_data|rule_action_item"
ASSESSMENT_TABLES="pro_assessments|questionnaire_response|questionnaire_repository"
CALENDAR_TABLES="openemr_postcalendar_events|openemr_postcalendar_categories|medex_recalls"
STATIC_TABLES="issue_types|list_options|layout_options|layout_group_properties"
BASE_PATIENT_TABLES="patient_data|lists|lists_medication|prescriptions|immunizations|immunization_observation|pnotes|amendments|documents|transactions|amendments|users|groups|extended_log|history_data|patient_history|pharmacies|shared_attributes|drugs|lbt_data|lbf_data|esign_signatures|external_encounters|external_procedures|patient_tracker|patient_tracker_element|clinical_plans"
THERAPY_PATIENT_TABLES="therapy_groups_participant_attendance|therapy_groups_participants|therapy_groups|therapy_groups_counselors|form_groups_encounter|form_group_attendance"
PORTAL_TABLES="onsite_messages|onsite_mail|patient_access_onsite|onsite_documents|onsite_signatures|onsite_portal_activity"
PROCEDURE_TABLES="procedure_order|procedure_providers|procedure_answers|procedure_questions|procedure_type|procedure_report|procedure_order_code|procedure_result"
TABLES_INCLUDE="($BASE_PATIENT_TABLES|$BILLING_TABLES|$FORM_ENCOUNTERS|$CALENDAR_TABLES|$STATIC_TABLES|$PATIENT_REMINDER_TABLES|$ASSESSMENT_TABLES|$THERAPY_PATIENT_TABLES|$PROCEDURE_TABLES|$PORTAL_TABLES)"
#FILTER_CLAUSE=-I "dac_.*"
FILTER_CLAUSE="-i $TABLES_INCLUDE"

#vizjs uses javascript visual
#pfp says to prompt to a password use -p to supply password on commandline
#dp is the mysql connector
#-norows we don't want to include row totals
# -I excludeTableRegex
# -i includeTableRegex
# -noimplied
# -desc description
# -template path Path to custom mustache template/css directory, needs to contain full set of templates. Bundled templates can be found in jar ‘/layout’ and can be extracted with jar tool or any zip capable tool.
# -noDbObjectPaging
# schema file is named openemr.meta.xml

#java -jar schemaspy.jar -t mysql -host 10.0.12 -port 8320 -db openemr -u openemr -p openemr -o schemaspy -s openemr -dp ./mysql-connector-j-8.1.0/mysql-connector-j-8.1.0.jar -vizjs -pfp -norows -noimplied -dbObjectPageLength 50 -I "(openemr.dac_[a-zA-Z_])*" -meta ../schemas
java -jar $JAR_FILES/schemaspy.jar -t mysql -host $DB_HOST -port $DB_PORT -db $DB_NAME -u $DB_USER -p $DB_PASSWORD -o $DOC_OUTPUT -s $DB_SCHEMA -dp $JAR_FILES/mysql-connector-j-8.1.0.jar -vizjs -norows -noimplied -nopages $FILTER_CLAUSE -meta $SCHEMA_LOCATION -template $TEMPLATE_LOCATION
