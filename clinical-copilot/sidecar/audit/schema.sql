-- AI audit log: hash-chained append-only.
-- ARCHITECTURE.md §6.3 — separate from OpenEMR's `audit_master`.
--
-- Retained 7 years (HIPAA 6 + buffer). Anchored periodically to
-- write-once external storage; that anchoring lives outside the
-- application, not in this schema.
CREATE TABLE IF NOT EXISTS ai_audit_log (
    id                       BIGSERIAL PRIMARY KEY,
    prev_hash                BYTEA NOT NULL,
    this_hash                BYTEA NOT NULL,
    occurred_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    user_id                  TEXT NOT NULL,
    patient_id               TEXT NOT NULL,
    purpose_of_use           TEXT NOT NULL,
    model_name               TEXT NOT NULL,
    prompt_version           TEXT NOT NULL,
    prompt_token_count       INTEGER NOT NULL DEFAULT 0,
    completion_token_count   INTEGER NOT NULL DEFAULT 0,
    tool_calls               JSONB NOT NULL DEFAULT '[]'::jsonb,
    verifier_outcome         TEXT NOT NULL CHECK (verifier_outcome IN ('passed', 'warned', 'blocked')),
    response_summary         TEXT NOT NULL  -- redacted summary, never raw PHI
);

CREATE INDEX IF NOT EXISTS ix_ai_audit_log_occurred_at ON ai_audit_log(occurred_at);
CREATE INDEX IF NOT EXISTS ix_ai_audit_log_patient ON ai_audit_log(patient_id);
CREATE INDEX IF NOT EXISTS ix_ai_audit_log_user ON ai_audit_log(user_id);

-- Append-only: deny UPDATE and DELETE at the SQL surface.
-- (Operational deletion of expired rows is performed by a separate
-- privileged role outside the application connection.)
REVOKE UPDATE, DELETE ON ai_audit_log FROM PUBLIC;
