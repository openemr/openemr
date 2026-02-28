-- Safety Sentinel: Scribe Encounters Table
-- Run this migration after deploying the ScribeEncounterService.
--
-- Stores ambient AI scribe encounters (transcript, SOAP note, accepted codes).
-- No foreign keys to OpenEMR native tables — consistent with safety_audit_log
-- and safety_conversations patterns.

CREATE TABLE IF NOT EXISTS scribe_encounters (
    id                      INT AUTO_INCREMENT PRIMARY KEY,

    -- Patient & encounter identity
    patient_uuid            VARCHAR(36) NOT NULL,
    encounter_date          DATE NOT NULL,
    status                  ENUM('draft', 'finalized') NOT NULL DEFAULT 'draft',

    -- Transcript (stored for provenance; not browsed directly)
    transcript              MEDIUMTEXT,
    transcript_word_count   INT,
    transcript_duration_s   FLOAT,

    -- SOAP note stored as JSON for flexibility; preview column avoids JSON parse for list views
    soap_note               JSON NOT NULL,
    soap_subjective_preview VARCHAR(250),

    -- Clinician-accepted code subsets
    accepted_icd10_codes    JSON,
    accepted_cpt_codes      JSON,

    -- Full suggestion lists kept for evaluation and audit (not clinician-facing)
    all_icd10_suggestions   JSON,
    all_cpt_suggestions     JSON,

    -- Patient instructions (plain text copy from SOAP JSON for quick access)
    patient_instructions    TEXT,

    -- Generation metadata
    generation_model        VARCHAR(100),
    confidence_overall      FLOAT,

    -- Audit timestamps
    created_by              VARCHAR(36),
    created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    finalized_at            DATETIME,

    INDEX idx_patient_uuid   (patient_uuid),
    INDEX idx_encounter_date (encounter_date),
    INDEX idx_status         (status),
    INDEX idx_patient_date   (patient_uuid, encounter_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
