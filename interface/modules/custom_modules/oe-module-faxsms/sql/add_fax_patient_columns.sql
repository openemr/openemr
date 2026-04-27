-- Migration: Add patient association and site tracking columns to oe_faxsms_queue
-- Author: SignalWire Integration Enhancement
-- Date: 2024-12-13

-- Add patient_id column for patient association
#IfMissingColumn oe_faxsms_queue patient_id
ALTER TABLE `oe_faxsms_queue`
ADD COLUMN `patient_id` INT(11) DEFAULT NULL COMMENT 'Patient ID if assigned',
ADD INDEX `idx_patient_id` (`patient_id`);
#EndIf

-- Add document_id column to link to documents table
#IfMissingColumn oe_faxsms_queue document_id
ALTER TABLE `oe_faxsms_queue`
ADD COLUMN `document_id` INT(11) DEFAULT NULL COMMENT 'Document ID in documents table',
ADD INDEX `idx_document_id` (`document_id`);
#EndIf

-- Add media_path column for local file storage
#IfMissingColumn oe_faxsms_queue media_path
ALTER TABLE `oe_faxsms_queue`
ADD COLUMN `media_path` VARCHAR(255) DEFAULT NULL COMMENT 'Local path to downloaded fax media';
#EndIf

-- Add site_id column for multi-site support
#IfMissingColumn oe_faxsms_queue site_id
ALTER TABLE `oe_faxsms_queue`
ADD COLUMN `site_id` VARCHAR(63) DEFAULT 'default' COMMENT 'OpenEMR site ID',
ADD INDEX `idx_site_id` (`site_id`);
#EndIf

-- Add status column for better tracking
#IfMissingColumn oe_faxsms_queue status
ALTER TABLE `oe_faxsms_queue`
ADD COLUMN `status` VARCHAR(31) DEFAULT NULL COMMENT 'Fax status from vendor';
#EndIf

-- Add direction column if missing
#IfMissingColumn oe_faxsms_queue direction
ALTER TABLE `oe_faxsms_queue`
ADD COLUMN `direction` VARCHAR(15) DEFAULT 'inbound' COMMENT 'Fax direction: inbound or outbound';
#EndIf
