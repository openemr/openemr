SET NAMES utf8mb4;

-- ============================================================
-- Nursing Wound Care Form - table.sql
-- Crea la tabla del formulario e inserta traducciones
-- Idiomas incluidos:
--   lang_id 3  = Spanish (Spain)
--   lang_id 4  = Spanish (Latin American)
--   lang_id 5  = German
--   lang_id 8  = French (Standard)
--   lang_id 9  = French (Canadian)
--   lang_id 17 = Portuguese (European)
--   lang_id 18 = Portuguese (Brazilian)
--   lang_id 23 = Italian
-- Seguro para ejecutar múltiples veces (IF NOT EXISTS / INSERT IGNORE)
-- ============================================================

CREATE TABLE IF NOT EXISTS `form_curaciones` (
  `id`                      bigint(20)   NOT NULL AUTO_INCREMENT,
  `date`                    datetime     DEFAULT NULL,
  `pid`                     bigint(20)   DEFAULT NULL,
  `encounter`               int(11)      NOT NULL,
  `user`                    varchar(255) DEFAULT NULL,
  `groupname`               varchar(255) DEFAULT NULL,
  `authorized`              tinyint(4)   DEFAULT NULL,
  `activity`                tinyint(4)   DEFAULT NULL,
  `herida_operatoria`       tinyint(1)   DEFAULT 0,
  `obs_herida_operatoria`   varchar(255) DEFAULT NULL,
  `traqueostomia`           tinyint(1)   DEFAULT 0,
  `obs_traqueostomia`       varchar(255) DEFAULT NULL,
  `ostomias`                tinyint(1)   DEFAULT 0,
  `obs_ostomias`            varchar(255) DEFAULT NULL,
  `escaras`                 tinyint(1)   DEFAULT 0,
  `obs_escaras`             varchar(255) DEFAULT NULL,
  `via_venosa_central`      tinyint(1)   DEFAULT 0,
  `obs_via_venosa_central`  varchar(255) DEFAULT NULL,
  `via_venosa`              tinyint(1)   DEFAULT 0,
  `obs_via_venosa`          varchar(255) DEFAULT NULL,
  `hora_operacion`          time         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Traducciones
-- ============================================================

-- Constantes base (tabla lang_constants)
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES
('Nursing Wound Care'),
('Wound Care Record'),
('Wound Care Detail'),
('Wound Care List'),
('New Wound Care'),
('Edit Wound Care'),
('Surgical Wound'),
('Tracheostomy'),
('Ostomies'),
('Pressure Sores'),
('Central Venous Line'),
('Peripheral IV Line'),
('Care Time'),
('Active procedures'),
('NURSING WOUND CARE RECORD'),
('Responsible Signature'),
('Clarification'),
('Error: Missing required parameters.'),
('No wound care records found');

-- ============================================================
-- lang_id 3 — Spanish (Spain)
-- ============================================================
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Nursing Wound Care' AS constant_name, 'Registro de Curaciones de Enfermería' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Record' AS constant_name, 'Registro de Curaciones' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Detail' AS constant_name, 'Detalle de Curaciones' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care List' AS constant_name, 'Lista de Curaciones' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'New Wound Care' AS constant_name, 'Nueva Curación' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Edit Wound Care' AS constant_name, 'Editar Curación' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Surgical Wound' AS constant_name, 'Herida Operatoria' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Tracheostomy' AS constant_name, 'Traqueostomía' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Ostomies' AS constant_name, 'Ostomías' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Pressure Sores' AS constant_name, 'Escaras' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Central Venous Line' AS constant_name, 'Vía Venosa Central' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Peripheral IV Line' AS constant_name, 'Vía Venosa Periférica' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Care Time' AS constant_name, 'Hora de Curación' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Active procedures' AS constant_name, 'Procedimientos activos' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'NURSING WOUND CARE RECORD' AS constant_name, 'REGISTRO DE CURACIONES DE ENFERMERÍA' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Responsible Signature' AS constant_name, 'Firma del Responsable' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Clarification' AS constant_name, 'Aclaración' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'Error: Missing required parameters.' AS constant_name, 'Error: Faltan parámetros requeridos.' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 3, d.definition FROM `lang_constants` c JOIN (SELECT 'No wound care records found' AS constant_name, 'No hay curaciones registradas' AS definition) d ON c.constant_name = d.constant_name;

-- ============================================================
-- lang_id 4 — Spanish (Latin American)
-- ============================================================
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Nursing Wound Care' AS constant_name, 'Registro de Curaciones de Enfermería' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Record' AS constant_name, 'Registro de Curaciones' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Detail' AS constant_name, 'Detalle de Curaciones' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care List' AS constant_name, 'Lista de Curaciones' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'New Wound Care' AS constant_name, 'Nueva Curación' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Edit Wound Care' AS constant_name, 'Editar Curación' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Surgical Wound' AS constant_name, 'Herida Operatoria' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Tracheostomy' AS constant_name, 'Traqueostomía' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Ostomies' AS constant_name, 'Ostomías' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Pressure Sores' AS constant_name, 'Escaras' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Central Venous Line' AS constant_name, 'Vía Venosa Central' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Peripheral IV Line' AS constant_name, 'Vía Venosa Periférica' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Care Time' AS constant_name, 'Hora de Curación' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Active procedures' AS constant_name, 'Procedimientos activos' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'NURSING WOUND CARE RECORD' AS constant_name, 'REGISTRO DE CURACIONES DE ENFERMERÍA' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Responsible Signature' AS constant_name, 'Firma del Responsable' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Clarification' AS constant_name, 'Aclaración' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'Error: Missing required parameters.' AS constant_name, 'Error: Faltan parámetros requeridos.' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 4, d.definition FROM `lang_constants` c JOIN (SELECT 'No wound care records found' AS constant_name, 'No hay curaciones registradas' AS definition) d ON c.constant_name = d.constant_name;

-- ============================================================
-- lang_id 5 — German
-- ============================================================
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Nursing Wound Care' AS constant_name, 'Pflegewundversorgung' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Record' AS constant_name, 'Wundversorgungsprotokoll' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Surgical Wound' AS constant_name, 'Operationswunde' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Tracheostomy' AS constant_name, 'Tracheostomie' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Ostomies' AS constant_name, 'Stomata' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Pressure Sores' AS constant_name, 'Druckgeschwüre' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Central Venous Line' AS constant_name, 'Zentraler Venenkatheter' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Peripheral IV Line' AS constant_name, 'Peripherer Venenzugang' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Care Time' AS constant_name, 'Pflegezeit' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Active procedures' AS constant_name, 'Aktive Prozeduren' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Responsible Signature' AS constant_name, 'Unterschrift des Verantwortlichen' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 5, d.definition FROM `lang_constants` c JOIN (SELECT 'Clarification' AS constant_name, 'Klärung' AS definition) d ON c.constant_name = d.constant_name;

-- ============================================================
-- lang_id 8 — French (Standard)
-- ============================================================
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Nursing Wound Care' AS constant_name, 'Soins des plaies infirmiers' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Record' AS constant_name, 'Dossier de soins des plaies' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Surgical Wound' AS constant_name, 'Plaie chirurgicale' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Tracheostomy' AS constant_name, 'Trachéotomie' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Ostomies' AS constant_name, 'Stomies' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Pressure Sores' AS constant_name, 'Escarres' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Central Venous Line' AS constant_name, 'Voie veineuse centrale' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Peripheral IV Line' AS constant_name, 'Voie veineuse périphérique' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Care Time' AS constant_name, 'Heure de soin' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Active procedures' AS constant_name, 'Procédures actives' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Responsible Signature' AS constant_name, 'Signature du responsable' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 8, d.definition FROM `lang_constants` c JOIN (SELECT 'Clarification' AS constant_name, 'Clarification' AS definition) d ON c.constant_name = d.constant_name;

-- ============================================================
-- lang_id 9 — French (Canadian)
-- ============================================================
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Nursing Wound Care' AS constant_name, 'Soins des plaies infirmiers' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Record' AS constant_name, 'Dossier de soins des plaies' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Surgical Wound' AS constant_name, 'Plaie chirurgicale' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Tracheostomy' AS constant_name, 'Trachéotomie' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Ostomies' AS constant_name, 'Stomies' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Pressure Sores' AS constant_name, 'Escarres' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Central Venous Line' AS constant_name, 'Voie veineuse centrale' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Peripheral IV Line' AS constant_name, 'Voie veineuse périphérique' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Care Time' AS constant_name, 'Heure de soin' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Active procedures' AS constant_name, 'Procédures actives' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Responsible Signature' AS constant_name, 'Signature du responsable' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 9, d.definition FROM `lang_constants` c JOIN (SELECT 'Clarification' AS constant_name, 'Clarification' AS definition) d ON c.constant_name = d.constant_name;

-- ============================================================
-- lang_id 17 — Portuguese (European)
-- ============================================================
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Nursing Wound Care' AS constant_name, 'Cuidados de feridas de enfermagem' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Record' AS constant_name, 'Registo de cuidados de feridas' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Surgical Wound' AS constant_name, 'Ferida cirúrgica' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Tracheostomy' AS constant_name, 'Traqueostomia' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Ostomies' AS constant_name, 'Ostomias' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Pressure Sores' AS constant_name, 'Úlceras de pressão' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Central Venous Line' AS constant_name, 'Acesso venoso central' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Peripheral IV Line' AS constant_name, 'Acesso venoso periférico' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Care Time' AS constant_name, 'Hora do curativo' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Responsible Signature' AS constant_name, 'Assinatura do responsável' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 17, d.definition FROM `lang_constants` c JOIN (SELECT 'Clarification' AS constant_name, 'Esclarecimento' AS definition) d ON c.constant_name = d.constant_name;

-- ============================================================
-- lang_id 18 — Portuguese (Brazilian)
-- ============================================================
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Nursing Wound Care' AS constant_name, 'Cuidados de feridas de enfermagem' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Record' AS constant_name, 'Registro de cuidados de feridas' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Surgical Wound' AS constant_name, 'Ferida cirúrgica' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Tracheostomy' AS constant_name, 'Traqueostomia' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Ostomies' AS constant_name, 'Ostomias' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Pressure Sores' AS constant_name, 'Úlceras por pressão' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Central Venous Line' AS constant_name, 'Acesso venoso central' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Peripheral IV Line' AS constant_name, 'Acesso venoso periférico' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Care Time' AS constant_name, 'Hora do curativo' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Responsible Signature' AS constant_name, 'Assinatura do responsável' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 18, d.definition FROM `lang_constants` c JOIN (SELECT 'Clarification' AS constant_name, 'Esclarecimento' AS definition) d ON c.constant_name = d.constant_name;

-- ============================================================
-- lang_id 23 — Italian
-- ============================================================
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Nursing Wound Care' AS constant_name, 'Cure delle ferite infermieristiche' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Wound Care Record' AS constant_name, 'Registro cure delle ferite' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Surgical Wound' AS constant_name, 'Ferita chirurgica' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Tracheostomy' AS constant_name, 'Tracheostomia' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Ostomies' AS constant_name, 'Stomie' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Pressure Sores' AS constant_name, 'Piaghe da decubito' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Central Venous Line' AS constant_name, 'Catetere venoso centrale' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Peripheral IV Line' AS constant_name, 'Accesso venoso periferico' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Care Time' AS constant_name, 'Ora della medicazione' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Responsible Signature' AS constant_name, 'Firma del responsabile' AS definition) d ON c.constant_name = d.constant_name;
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT c.cons_id, 23, d.definition FROM `lang_constants` c JOIN (SELECT 'Clarification' AS constant_name, 'Chiarimento' AS definition) d ON c.constant_name = d.constant_name;
