--
-- Table structure for table `form_clinical_notes`
--

CREATE TABLE IF NOT EXISTS `form_clinical_notes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `form_id` bigint(20) NOT NULL,
  `uuid` binary(16) DEFAULT NULL,
  `date` DATE DEFAULT NULL,
  `pid` bigint(20) DEFAULT NULL,
  `encounter` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `authorized` tinyint(4) DEFAULT NULL,
  `activity` tinyint(4) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `codetext` text,
  `description` text,
  `external_id` VARCHAR(30) DEFAULT NULL,
  `clinical_notes_type` varchar(100) DEFAULT NULL,
  `clinical_notes_category` varchar(100) DEFAULT NULL,
  `note_related_to` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB;

--
-- Table structure for linking clinical notes to documents
--
CREATE TABLE IF NOT EXISTS `clinical_notes_documents` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `clinical_note_id` bigint(20) NOT NULL COMMENT 'Foreign key to form_clinical_notes.id',
  `document_id` bigint(20) NOT NULL COMMENT 'Foreign key to documents.id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the link was created',
  `created_by` varchar(255) DEFAULT NULL COMMENT 'Username who created the link',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note_document` (`clinical_note_id`, `document_id`),
  KEY `idx_clinical_note_id` (`clinical_note_id`),
  KEY `idx_document_id` (`document_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB COMMENT='Links clinical notes to patient documents';

--
-- Table structure for linking clinical notes to procedure results
--
CREATE TABLE IF NOT EXISTS `clinical_notes_procedure_results` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `clinical_note_id` bigint(20) NOT NULL COMMENT 'Foreign key to form_clinical_notes.id',
  `procedure_result_id` bigint(20) NOT NULL COMMENT 'Foreign key to procedure_result.procedure_result_id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the link was created',
  `created_by` varchar(255) DEFAULT NULL COMMENT 'Username who created the link',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_note_result` (`clinical_note_id`, `procedure_result_id`),
  KEY `idx_clinical_note_id` (`clinical_note_id`),
  KEY `idx_procedure_result_id` (`procedure_result_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB COMMENT='Links clinical notes to procedure results/lab values';
