-- MedEx Module Upgrade: v1.0.0 to v1.1.0
-- This is a template for future schema changes
-- OpenEMR module system will automatically run upgrade scripts based on version numbers

-- Example: Add new column to track SMS delivery status
-- ALTER TABLE medex_outgoing ADD COLUMN IF NOT EXISTS delivery_status VARCHAR(50) DEFAULT 'pending' AFTER msg_reply;

-- Example: Add index for performance
-- CREATE INDEX IF NOT EXISTS idx_msg_date ON medex_outgoing(msg_date);

-- Example: Update existing data
-- UPDATE medex_outgoing SET delivery_status = 'delivered' WHERE msg_reply = 'READ';

-- NOTE: This file is a placeholder for future upgrades
-- Currently no schema changes are needed between v1.0.0 and v1.1.0
