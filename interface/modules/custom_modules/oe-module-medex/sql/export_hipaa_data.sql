-- MedEx HIPAA Data Export SQL
-- Use this script to export patient-related MedEx data before cleanup
-- Required for HIPAA compliance and data retention policies
--
-- USAGE:
--   mysql -u[user] -p[password] [database] < export_hipaa_data.sql > medex_data_backup_YYYY-MM-DD.sql
--
-- This creates a complete backup of:
--   - Patient recall records
--   - Campaign communication history (SMS/EMAIL/AVM events)
--   - Practice preferences and configuration
--
-- RETENTION: Store this backup according to your practice's data retention policy
-- (typically 7 years for HIPAA compliance)

-- Export recall board data with patient identifiers
SELECT 'RECALL_BOARD_DATA' AS data_type;
SELECT
    r.r_ID,
    r.r_PRACTID,
    r.r_pid,
    pd.fname,
    pd.lname,
    pd.DOB,
    r.r_eventDate,
    r.r_facility,
    r.r_provider,
    r.r_reason,
    r.r_created
FROM medex_recalls r
LEFT JOIN patient_data pd ON r.r_pid = pd.pid
ORDER BY r.r_created DESC;

-- Export campaign communication history
SELECT 'CAMPAIGN_HISTORY_DATA' AS data_type;
SELECT
    mo.msg_uid,
    mo.msg_pid,
    pd.fname,
    pd.lname,
    pd.DOB,
    mo.msg_pc_eid,
    mo.campaign_uid,
    mo.msg_date,
    mo.msg_type,
    mo.msg_reply,
    mo.msg_extra_text,
    mo.medex_uid
FROM medex_outgoing mo
LEFT JOIN patient_data pd ON mo.msg_pid = pd.pid
ORDER BY mo.msg_date DESC;

-- Export practice preferences (no patient data, but needed for audit trail)
SELECT 'PRACTICE_PREFERENCES_DATA' AS data_type;
SELECT
    MedEx_id,
    ME_username,
    -- ME_api_key is encrypted, include for restore capability
    ME_api_key,
    ME_facilities,
    ME_providers,
    ME_hipaa_default_override,
    PHONE_country_code,
    MSGS_default_yes,
    POSTCARDS_local,
    POSTCARDS_remote,
    LABELS_local,
    LABELS_choice,
    combine_time,
    postcard_top,
    MedEx_lastupdated,
    status,
    sms_bot_phone_style
FROM medex_prefs;

-- Export icon definitions (for restore capability)
SELECT 'ICON_DEFINITIONS_DATA' AS data_type;
SELECT
    i_UID,
    msg_type,
    msg_status,
    i_description,
    i_html
    -- i_blob excluded for size, can be regenerated
FROM medex_icons
ORDER BY msg_type, msg_status;

-- Export summary statistics
SELECT 'EXPORT_SUMMARY' AS data_type;
SELECT
    'Total Recalls' AS metric,
    COUNT(*) AS count
FROM medex_recalls
UNION ALL
SELECT
    'Total Campaign Events' AS metric,
    COUNT(*) AS count
FROM medex_outgoing
UNION ALL
SELECT
    'Date Range - Oldest Event' AS metric,
    MIN(msg_date) AS count
FROM medex_outgoing
UNION ALL
SELECT
    'Date Range - Newest Event' AS metric,
    MAX(msg_date) AS count
FROM medex_outgoing;

-- Add export metadata
SELECT NOW() AS export_timestamp,
       USER() AS exported_by,
       DATABASE() AS database_name,
       VERSION() AS mysql_version;
