-- Nursing Admission table
-- Stores inpatient-specific fields alongside the standard form_encounter record.
-- Uses form_encounter.date_end (upstream field) for discharge; this table holds
-- ward/bed assignment and death date without modifying the core schema.

CREATE TABLE IF NOT EXISTS `form_nursing_admission` (
  `id`           bigint(20)   NOT NULL AUTO_INCREMENT,
  `pid`          bigint(20)   NOT NULL,
  `encounter`    bigint(20)   NOT NULL COMMENT 'References form_encounter.encounter',
  `nro_registro` varchar(50)  DEFAULT NULL  COMMENT 'Internal admission registration number',
  `departamento` varchar(100) DEFAULT NULL  COMMENT 'Unit/Department (e.g. terapia_adulto)',
  `servicio`     varchar(50)  DEFAULT NULL  COMMENT 'Service level: intensivo, intermedia, minima',
  `cuarto`       varchar(20)  DEFAULT NULL  COMMENT 'Room/Ward identifier',
  `cama`         varchar(20)  DEFAULT NULL  COMMENT 'Bed number',
  `death_date`   date         DEFAULT NULL  COMMENT 'Date of death if applicable',
  `created_at`   timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `encounter` (`encounter`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=1;

-- Migration: move data from old form_encounter custom columns (local fork only)
-- Safe to run even if the old columns do not exist (IF statements guard each block)
-- Run this ONCE on existing installations before updating lista_internados.php

SET @col_out := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'form_encounter'
      AND COLUMN_NAME  = 'out_date'
);

-- Migrate existing inpatient data to form_nursing_admission
SET @migrate := IF(
    @col_out > 0,
    "INSERT IGNORE INTO form_nursing_admission (pid, encounter, nro_registro, departamento, servicio, cuarto, cama, death_date)
     SELECT pid, encounter,
            COALESCE(nro_registro, ''),
            COALESCE(departamento, ''),
            COALESCE(servicio, ''),
            COALESCE(cuarto, ''),
            COALESCE(cama, ''),
            death_date
     FROM form_encounter
     WHERE out_date IS NOT NULL OR servicio IS NOT NULL OR cuarto IS NOT NULL",
    "SELECT 1"
);
PREPARE stmt FROM @migrate; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Migrate out_date → date_end (upstream column)
SET @sync_end := IF(
    @col_out > 0,
    "UPDATE form_encounter SET date_end = out_date WHERE out_date IS NOT NULL AND date_end IS NULL",
    "SELECT 1"
);
PREPARE stmt FROM @sync_end; EXECUTE stmt; DEALLOCATE PREPARE stmt;
