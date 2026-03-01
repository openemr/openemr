-- Migration 006: Placeholder tracked by OpenEMR module system.
-- The actual ss_fee_schedule table lives in the Safety Sentinel Python DB
-- (see agents/safety-sentinel/sql/migrations/006_create_fee_schedule.sql).
-- BillingService writes directly to OpenEMR's native `billing` table.
SELECT 1;
