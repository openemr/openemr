-- ============================================================
-- Nursing Care Bundle Form - table.sql
-- Creates the form_cuidados table and inserts translations
-- for 8 languages into lang_constants / lang_definitions.
-- ============================================================

CREATE TABLE IF NOT EXISTS `form_cuidados` (
  `id`                       bigint(20)   NOT NULL AUTO_INCREMENT,
  `date`                     datetime     DEFAULT NULL,
  `pid`                      bigint(20)   NOT NULL DEFAULT 0,
  `encounter`                bigint(20)   NOT NULL DEFAULT 0,
  `user`                     varchar(255) DEFAULT NULL,
  `groupname`                varchar(255) DEFAULT NULL,
  `authorized`               tinyint(4)   DEFAULT 0,
  `activity`                 tinyint(4)   DEFAULT 1,
  `posicion_paciente`        varchar(20)  DEFAULT NULL,
  `obs_posicion_paciente`    varchar(255) DEFAULT NULL,
  `enjuague_bucal`           tinyint(1)   DEFAULT 0,
  `obs_enjuague_bucal`       varchar(255) DEFAULT NULL,
  `higiene_manos`            tinyint(1)   DEFAULT 0,
  `obs_higiene_manos`        varchar(255) DEFAULT NULL,
  `aspirado_secreciones`     tinyint(1)   DEFAULT 0,
  `obs_aspirado_secreciones` varchar(255) DEFAULT NULL,
  `suspension_sedacion`      tinyint(1)   DEFAULT 0,
  `obs_suspension_sedacion`  varchar(255) DEFAULT NULL,
  `medicion_cuff`            tinyint(1)   DEFAULT 0,
  `obs_medicion_cuff`        varchar(255) DEFAULT NULL,
  `hora_cuidado`             time         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Translations (INSERT IGNORE to avoid duplicates)
-- lang_id: 3=es-ES, 4=es-419, 5=de, 8=fr, 9=fr-CA,
--          17=pt-PT, 18=pt-BR, 23=it
-- ============================================================

-- Nursing Care Bundle
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Nursing Care Bundle');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Paquete de Cuidados de Enfermería'   FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Paquete de Cuidados de Enfermería'   FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Pflegepaket'                         FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Ensemble de soins infirmiers'        FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Ensemble de soins infirmiers'        FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Pacote de Cuidados de Enfermagem'    FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Pacote de Cuidados de Enfermagem'    FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Pacchetto di cure infermieristiche'  FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Care Bundle';

-- NURSING CARE BUNDLE RECORD
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('NURSING CARE BUNDLE RECORD');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'REGISTRO DE PAQUETE DE CUIDADOS'     FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING CARE BUNDLE RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'REGISTRO DE PAQUETE DE CUIDADOS'     FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING CARE BUNDLE RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'PFLEGEPAKET-PROTOKOLL'               FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING CARE BUNDLE RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'DOSSIER ENSEMBLE DE SOINS'           FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING CARE BUNDLE RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'DOSSIER ENSEMBLE DE SOINS'           FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING CARE BUNDLE RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'REGISTO DE PACOTE DE CUIDADOS'       FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING CARE BUNDLE RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'REGISTRO DE PACOTE DE CUIDADOS'      FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING CARE BUNDLE RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'REGISTRO PACCHETTO DI CURE'          FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING CARE BUNDLE RECORD';

-- Care Bundle Record
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Care Bundle Record');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Registro de Paquete de Cuidados'     FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Registro de Paquete de Cuidados'     FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Pflegepaket-Protokoll'               FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Dossier ensemble de soins'           FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Dossier ensemble de soins'           FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Registo de Pacote de Cuidados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Registro de Pacote de Cuidados'      FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Registro Pacchetto di Cure'          FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Record';

-- Care Bundle Detail
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Care Bundle Detail');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Detalle de Paquete de Cuidados'      FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Detalle de Paquete de Cuidados'      FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Pflegepaket-Detail'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Détail ensemble de soins'            FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Détail ensemble de soins'            FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Detalhe de Pacote de Cuidados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Detalhe de Pacote de Cuidados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Dettaglio Pacchetto di Cure'         FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle Detail';

-- Care Bundle List
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Care Bundle List');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Lista de Paquetes de Cuidados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Lista de Paquetes de Cuidados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Pflegepaket-Liste'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Liste des ensembles de soins'        FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Liste des ensembles de soins'        FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Lista de Pacotes de Cuidados'        FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Lista de Pacotes de Cuidados'        FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Elenco Pacchetti di Cure'            FROM `lang_constants` c WHERE c.`constant_name` = 'Care Bundle List';

-- Patient Position
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Patient Position');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Posición del Paciente'               FROM `lang_constants` c WHERE c.`constant_name` = 'Patient Position';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Posición del Paciente'               FROM `lang_constants` c WHERE c.`constant_name` = 'Patient Position';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Patientenposition'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Patient Position';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Position du patient'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Patient Position';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Position du patient'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Patient Position';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Posição do Paciente'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Patient Position';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Posição do Paciente'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Patient Position';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Posizione del Paziente'              FROM `lang_constants` c WHERE c.`constant_name` = 'Patient Position';

-- Oral Rinse
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Oral Rinse');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Enjuague Bucal'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Oral Rinse';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Enjuague Bucal'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Oral Rinse';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Mundspülung'                         FROM `lang_constants` c WHERE c.`constant_name` = 'Oral Rinse';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Bain de bouche'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Oral Rinse';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Bain de bouche'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Oral Rinse';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Bochecho Oral'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Oral Rinse';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Bochecho Oral'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Oral Rinse';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Collutorio Orale'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Oral Rinse';

-- Hand Hygiene Pre/Post Suctioning
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Hand Hygiene Pre/Post Suctioning');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Higiene de Manos Pre/Post Aspirado'  FROM `lang_constants` c WHERE c.`constant_name` = 'Hand Hygiene Pre/Post Suctioning';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Higiene de Manos Pre/Post Aspirado'  FROM `lang_constants` c WHERE c.`constant_name` = 'Hand Hygiene Pre/Post Suctioning';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Handhygiene vor/nach Absaugen'        FROM `lang_constants` c WHERE c.`constant_name` = 'Hand Hygiene Pre/Post Suctioning';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Hygiène des mains pré/post aspiration' FROM `lang_constants` c WHERE c.`constant_name` = 'Hand Hygiene Pre/Post Suctioning';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Hygiène des mains pré/post aspiration' FROM `lang_constants` c WHERE c.`constant_name` = 'Hand Hygiene Pre/Post Suctioning';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Higiene das Mãos Pré/Pós Aspiração'  FROM `lang_constants` c WHERE c.`constant_name` = 'Hand Hygiene Pre/Post Suctioning';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Higiene das Mãos Pré/Pós Aspiração'  FROM `lang_constants` c WHERE c.`constant_name` = 'Hand Hygiene Pre/Post Suctioning';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Igiene delle Mani Pre/Post Aspirazione' FROM `lang_constants` c WHERE c.`constant_name` = 'Hand Hygiene Pre/Post Suctioning';

-- Secretion Suctioning with Gloves and Assistant
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Secretion Suctioning with Gloves and Assistant');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Aspirado de Secreciones con Guantes y Ayudante' FROM `lang_constants` c WHERE c.`constant_name` = 'Secretion Suctioning with Gloves and Assistant';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Aspirado de Secreciones con Guantes y Ayudante' FROM `lang_constants` c WHERE c.`constant_name` = 'Secretion Suctioning with Gloves and Assistant';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Sekretabsaugung mit Handschuhen und Assistent' FROM `lang_constants` c WHERE c.`constant_name` = 'Secretion Suctioning with Gloves and Assistant';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Aspiration des sécrétions avec gants et assistant' FROM `lang_constants` c WHERE c.`constant_name` = 'Secretion Suctioning with Gloves and Assistant';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Aspiration des sécrétions avec gants et assistant' FROM `lang_constants` c WHERE c.`constant_name` = 'Secretion Suctioning with Gloves and Assistant';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Aspiração de Secreções com Luvas e Assistente' FROM `lang_constants` c WHERE c.`constant_name` = 'Secretion Suctioning with Gloves and Assistant';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Aspiração de Secreções com Luvas e Assistente' FROM `lang_constants` c WHERE c.`constant_name` = 'Secretion Suctioning with Gloves and Assistant';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Aspirazione Secrezioni con Guanti e Assistente' FROM `lang_constants` c WHERE c.`constant_name` = 'Secretion Suctioning with Gloves and Assistant';

-- Daily Sedation Suspension and Extubation Evaluation
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Daily Sedation Suspension and Extubation Evaluation');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Suspensión Diaria de Sedación y Evaluación de Extubación' FROM `lang_constants` c WHERE c.`constant_name` = 'Daily Sedation Suspension and Extubation Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Suspensión Diaria de Sedación y Evaluación de Extubación' FROM `lang_constants` c WHERE c.`constant_name` = 'Daily Sedation Suspension and Extubation Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Tägliche Sedierungsunterbrechung und Extubationsbewertung' FROM `lang_constants` c WHERE c.`constant_name` = 'Daily Sedation Suspension and Extubation Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Interruption quotidienne de sédation et évaluation d''extubation' FROM `lang_constants` c WHERE c.`constant_name` = 'Daily Sedation Suspension and Extubation Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Interruption quotidienne de sédation et évaluation d''extubation' FROM `lang_constants` c WHERE c.`constant_name` = 'Daily Sedation Suspension and Extubation Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Suspensão Diária de Sedação e Avaliação de Extubação' FROM `lang_constants` c WHERE c.`constant_name` = 'Daily Sedation Suspension and Extubation Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Suspensão Diária de Sedação e Avaliação de Extubação' FROM `lang_constants` c WHERE c.`constant_name` = 'Daily Sedation Suspension and Extubation Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Sospensione Giornaliera della Sedazione e Valutazione dell''Estubazione' FROM `lang_constants` c WHERE c.`constant_name` = 'Daily Sedation Suspension and Extubation Evaluation';

-- Cuff Pressure Measurement
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Cuff Pressure Measurement');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Medición de Presión de Cuff'          FROM `lang_constants` c WHERE c.`constant_name` = 'Cuff Pressure Measurement';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Medición de Presión de Cuff'          FROM `lang_constants` c WHERE c.`constant_name` = 'Cuff Pressure Measurement';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Cuff-Druckmessung'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Cuff Pressure Measurement';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Mesure de pression du cuff'           FROM `lang_constants` c WHERE c.`constant_name` = 'Cuff Pressure Measurement';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Mesure de pression du cuff'           FROM `lang_constants` c WHERE c.`constant_name` = 'Cuff Pressure Measurement';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Medição de Pressão do Cuff'           FROM `lang_constants` c WHERE c.`constant_name` = 'Cuff Pressure Measurement';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Medição de Pressão do Cuff'           FROM `lang_constants` c WHERE c.`constant_name` = 'Cuff Pressure Measurement';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Misurazione della Pressione del Cuff' FROM `lang_constants` c WHERE c.`constant_name` = 'Cuff Pressure Measurement';

-- Care Time
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Care Time');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Hora de Cuidado'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Care Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Hora de Cuidado'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Care Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Pflegezeit'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Care Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Heure de soin'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Care Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Heure de soin'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Care Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Hora de Cuidado'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Care Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Hora de Cuidado'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Care Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Orario Cura'                          FROM `lang_constants` c WHERE c.`constant_name` = 'Care Time';

-- No care bundle records found
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('No care bundle records found');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'No se encontraron registros de cuidados' FROM `lang_constants` c WHERE c.`constant_name` = 'No care bundle records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'No se encontraron registros de cuidados' FROM `lang_constants` c WHERE c.`constant_name` = 'No care bundle records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Keine Pflegepaket-Datensätze gefunden'   FROM `lang_constants` c WHERE c.`constant_name` = 'No care bundle records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Aucun dossier d''ensemble de soins trouvé' FROM `lang_constants` c WHERE c.`constant_name` = 'No care bundle records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Aucun dossier d''ensemble de soins trouvé' FROM `lang_constants` c WHERE c.`constant_name` = 'No care bundle records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Nenhum registo de pacote de cuidados encontrado' FROM `lang_constants` c WHERE c.`constant_name` = 'No care bundle records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Nenhum registro de pacote de cuidados encontrado' FROM `lang_constants` c WHERE c.`constant_name` = 'No care bundle records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Nessun record pacchetto di cure trovato' FROM `lang_constants` c WHERE c.`constant_name` = 'No care bundle records found';

-- New Care Bundle
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('New Care Bundle');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Nuevo Paquete de Cuidados'    FROM `lang_constants` c WHERE c.`constant_name` = 'New Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Nuevo Paquete de Cuidados'    FROM `lang_constants` c WHERE c.`constant_name` = 'New Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Neues Pflegepaket'            FROM `lang_constants` c WHERE c.`constant_name` = 'New Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Nouvel ensemble de soins'     FROM `lang_constants` c WHERE c.`constant_name` = 'New Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Nouvel ensemble de soins'     FROM `lang_constants` c WHERE c.`constant_name` = 'New Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Novo Pacote de Cuidados'      FROM `lang_constants` c WHERE c.`constant_name` = 'New Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Novo Pacote de Cuidados'      FROM `lang_constants` c WHERE c.`constant_name` = 'New Care Bundle';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Nuovo pacchetto di cura'      FROM `lang_constants` c WHERE c.`constant_name` = 'New Care Bundle';
