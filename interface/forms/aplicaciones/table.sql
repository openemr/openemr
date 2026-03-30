-- ============================================================
-- Nursing Applications Form - table.sql
-- Creates the form_aplicaciones table and inserts translations
-- for 8 languages into lang_constants / lang_definitions.
-- ============================================================

CREATE TABLE IF NOT EXISTS `form_aplicaciones` (
  `id`               bigint(20)   NOT NULL AUTO_INCREMENT,
  `date`             datetime     DEFAULT NULL,
  `pid`              bigint(20)   NOT NULL DEFAULT 0,
  `encounter`        bigint(20)   NOT NULL DEFAULT 0,
  `user`             varchar(255) DEFAULT NULL,
  `groupname`        varchar(255) DEFAULT NULL,
  `authorized`       tinyint(4)   DEFAULT 0,
  `activity`         tinyint(4)   DEFAULT 1,
  `medicamentos`     tinyint(1)   DEFAULT 0,
  `obs_medicamentos` varchar(255) DEFAULT NULL,
  `sueros`           tinyint(1)   DEFAULT 0,
  `obs_sueros`       varchar(255) DEFAULT NULL,
  `vacunas`          tinyint(1)   DEFAULT 0,
  `obs_vacunas`      varchar(255) DEFAULT NULL,
  `expansiones`      tinyint(1)   DEFAULT 0,
  `obs_expansiones`  varchar(255) DEFAULT NULL,
  `sangre`           tinyint(1)   DEFAULT 0,
  `obs_sangre`       varchar(255) DEFAULT NULL,
  `hora_registro`    time         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Translations (INSERT IGNORE to avoid duplicates)
-- lang_id: 3=es-ES, 4=es-419, 5=de, 8=fr, 9=fr-CA,
--          17=pt-PT, 18=pt-BR, 23=it
-- ============================================================

-- Nursing Applications
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Nursing Applications');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Aplicaciones de Enfermería'  FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Applications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Aplicaciones de Enfermería'  FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Applications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Pflegeanwendungen'            FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Applications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Applications infirmières'     FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Applications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Applications infirmières'     FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Applications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Aplicações de Enfermagem'     FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Applications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Aplicações de Enfermagem'     FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Applications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Applicazioni infermieristiche' FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Applications';

-- Nursing Applications Record
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('NURSING APPLICATIONS RECORD');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'REGISTRO DE APLICACIONES DE ENFERMERÍA' FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING APPLICATIONS RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'REGISTRO DE APLICACIONES DE ENFERMERÍA' FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING APPLICATIONS RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'PFLEGEANWENDUNGSPROTOKOLL'              FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING APPLICATIONS RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'DOSSIER D''APPLICATIONS INFIRMIÈRES'   FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING APPLICATIONS RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'DOSSIER D''APPLICATIONS INFIRMIÈRES'   FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING APPLICATIONS RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'REGISTO DE APLICAÇÕES DE ENFERMAGEM'   FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING APPLICATIONS RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'REGISTRO DE APLICAÇÕES DE ENFERMAGEM'  FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING APPLICATIONS RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'REGISTRO APPLICAZIONI INFERMIERISTICHE' FROM `lang_constants` c WHERE c.`constant_name` = 'NURSING APPLICATIONS RECORD';

-- Application Record
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Application Record');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Registro de Aplicación'      FROM `lang_constants` c WHERE c.`constant_name` = 'Application Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Registro de Aplicación'      FROM `lang_constants` c WHERE c.`constant_name` = 'Application Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Anwendungsprotokoll'          FROM `lang_constants` c WHERE c.`constant_name` = 'Application Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Dossier d''application'       FROM `lang_constants` c WHERE c.`constant_name` = 'Application Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Dossier d''application'       FROM `lang_constants` c WHERE c.`constant_name` = 'Application Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Registo de Aplicação'         FROM `lang_constants` c WHERE c.`constant_name` = 'Application Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Registro de Aplicação'        FROM `lang_constants` c WHERE c.`constant_name` = 'Application Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Registro Applicazione'        FROM `lang_constants` c WHERE c.`constant_name` = 'Application Record';

-- Application Detail
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Application Detail');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Detalle de Aplicación'        FROM `lang_constants` c WHERE c.`constant_name` = 'Application Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Detalle de Aplicación'        FROM `lang_constants` c WHERE c.`constant_name` = 'Application Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Anwendungsdetail'             FROM `lang_constants` c WHERE c.`constant_name` = 'Application Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Détail d''application'        FROM `lang_constants` c WHERE c.`constant_name` = 'Application Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Détail d''application'        FROM `lang_constants` c WHERE c.`constant_name` = 'Application Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Detalhe de Aplicação'         FROM `lang_constants` c WHERE c.`constant_name` = 'Application Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Detalhe de Aplicação'         FROM `lang_constants` c WHERE c.`constant_name` = 'Application Detail';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Dettaglio Applicazione'       FROM `lang_constants` c WHERE c.`constant_name` = 'Application Detail';

-- Application List
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Application List');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Lista de Aplicaciones'        FROM `lang_constants` c WHERE c.`constant_name` = 'Application List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Lista de Aplicaciones'        FROM `lang_constants` c WHERE c.`constant_name` = 'Application List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Anwendungsliste'              FROM `lang_constants` c WHERE c.`constant_name` = 'Application List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Liste des applications'       FROM `lang_constants` c WHERE c.`constant_name` = 'Application List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Liste des applications'       FROM `lang_constants` c WHERE c.`constant_name` = 'Application List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Lista de Aplicações'          FROM `lang_constants` c WHERE c.`constant_name` = 'Application List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Lista de Aplicações'          FROM `lang_constants` c WHERE c.`constant_name` = 'Application List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Elenco Applicazioni'          FROM `lang_constants` c WHERE c.`constant_name` = 'Application List';

-- New Application
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('New Application');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Nueva Aplicación'             FROM `lang_constants` c WHERE c.`constant_name` = 'New Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Nueva Aplicación'             FROM `lang_constants` c WHERE c.`constant_name` = 'New Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Neue Anwendung'               FROM `lang_constants` c WHERE c.`constant_name` = 'New Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Nouvelle application'          FROM `lang_constants` c WHERE c.`constant_name` = 'New Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Nouvelle application'          FROM `lang_constants` c WHERE c.`constant_name` = 'New Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Nova Aplicação'               FROM `lang_constants` c WHERE c.`constant_name` = 'New Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Nova Aplicação'               FROM `lang_constants` c WHERE c.`constant_name` = 'New Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Nuova Applicazione'           FROM `lang_constants` c WHERE c.`constant_name` = 'New Application';

-- Edit Application
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Edit Application');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Editar Aplicación'            FROM `lang_constants` c WHERE c.`constant_name` = 'Edit Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Editar Aplicación'            FROM `lang_constants` c WHERE c.`constant_name` = 'Edit Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Anwendung bearbeiten'         FROM `lang_constants` c WHERE c.`constant_name` = 'Edit Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Modifier l''application'      FROM `lang_constants` c WHERE c.`constant_name` = 'Edit Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Modifier l''application'      FROM `lang_constants` c WHERE c.`constant_name` = 'Edit Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Editar Aplicação'             FROM `lang_constants` c WHERE c.`constant_name` = 'Edit Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Editar Aplicação'             FROM `lang_constants` c WHERE c.`constant_name` = 'Edit Application';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Modifica Applicazione'        FROM `lang_constants` c WHERE c.`constant_name` = 'Edit Application';

-- Medications
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Medications');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Medicamentos'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Medications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Medicamentos'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Medications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Medikamente'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Medications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Médicaments'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Medications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Médicaments'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Medications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Medicamentos'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Medications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Medicamentos'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Medications';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Farmaci'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Medications';

-- Saline Solutions
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Saline Solutions');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Sueros'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Saline Solutions';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Sueros'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Saline Solutions';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Kochsalzlösungen'             FROM `lang_constants` c WHERE c.`constant_name` = 'Saline Solutions';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Solutions salines'            FROM `lang_constants` c WHERE c.`constant_name` = 'Saline Solutions';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Solutions salines'            FROM `lang_constants` c WHERE c.`constant_name` = 'Saline Solutions';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Soluções Salinas'             FROM `lang_constants` c WHERE c.`constant_name` = 'Saline Solutions';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Soluções Salinas'             FROM `lang_constants` c WHERE c.`constant_name` = 'Saline Solutions';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Soluzioni Saline'             FROM `lang_constants` c WHERE c.`constant_name` = 'Saline Solutions';

-- Vaccines
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Vaccines');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Vacunas'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Vaccines';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Vacunas'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Vaccines';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Impfstoffe'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Vaccines';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Vaccins'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Vaccines';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Vaccins'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Vaccines';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Vacinas'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Vaccines';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Vacinas'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Vaccines';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Vaccini'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Vaccines';

-- Plasma Expanders
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Plasma Expanders');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Expansiones Plasmáticas'      FROM `lang_constants` c WHERE c.`constant_name` = 'Plasma Expanders';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Expansiones Plasmáticas'      FROM `lang_constants` c WHERE c.`constant_name` = 'Plasma Expanders';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Plasmaexpander'               FROM `lang_constants` c WHERE c.`constant_name` = 'Plasma Expanders';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Expanseurs plasmatiques'      FROM `lang_constants` c WHERE c.`constant_name` = 'Plasma Expanders';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Expanseurs plasmatiques'      FROM `lang_constants` c WHERE c.`constant_name` = 'Plasma Expanders';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Expansores Plasmáticos'       FROM `lang_constants` c WHERE c.`constant_name` = 'Plasma Expanders';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Expansores Plasmáticos'       FROM `lang_constants` c WHERE c.`constant_name` = 'Plasma Expanders';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Espansori Plasmatici'         FROM `lang_constants` c WHERE c.`constant_name` = 'Plasma Expanders';

-- Blood and Blood Products
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Blood and Blood Products');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Sangre y Hemoderivados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Blood and Blood Products';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Sangre y Hemoderivados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Blood and Blood Products';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Blut und Blutprodukte'        FROM `lang_constants` c WHERE c.`constant_name` = 'Blood and Blood Products';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Sang et produits sanguins'    FROM `lang_constants` c WHERE c.`constant_name` = 'Blood and Blood Products';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Sang et produits sanguins'    FROM `lang_constants` c WHERE c.`constant_name` = 'Blood and Blood Products';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Sangue e Hemoderivados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Blood and Blood Products';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Sangue e Hemoderivados'       FROM `lang_constants` c WHERE c.`constant_name` = 'Blood and Blood Products';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Sangue ed Emoderivati'        FROM `lang_constants` c WHERE c.`constant_name` = 'Blood and Blood Products';

-- Record Time
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Record Time');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Hora de Registro'             FROM `lang_constants` c WHERE c.`constant_name` = 'Record Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Hora de Registro'             FROM `lang_constants` c WHERE c.`constant_name` = 'Record Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Erfassungszeit'               FROM `lang_constants` c WHERE c.`constant_name` = 'Record Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Heure d''enregistrement'      FROM `lang_constants` c WHERE c.`constant_name` = 'Record Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Heure d''enregistrement'      FROM `lang_constants` c WHERE c.`constant_name` = 'Record Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Hora de Registo'              FROM `lang_constants` c WHERE c.`constant_name` = 'Record Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Hora de Registro'             FROM `lang_constants` c WHERE c.`constant_name` = 'Record Time';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Orario Registrazione'         FROM `lang_constants` c WHERE c.`constant_name` = 'Record Time';

-- No application records found
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('No application records found');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'No se encontraron registros de aplicaciones' FROM `lang_constants` c WHERE c.`constant_name` = 'No application records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'No se encontraron registros de aplicaciones' FROM `lang_constants` c WHERE c.`constant_name` = 'No application records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Keine Anwendungsdatensätze gefunden'         FROM `lang_constants` c WHERE c.`constant_name` = 'No application records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Aucun dossier d''application trouvé'         FROM `lang_constants` c WHERE c.`constant_name` = 'No application records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Aucun dossier d''application trouvé'         FROM `lang_constants` c WHERE c.`constant_name` = 'No application records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Nenhum registo de aplicação encontrado'      FROM `lang_constants` c WHERE c.`constant_name` = 'No application records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Nenhum registro de aplicação encontrado'     FROM `lang_constants` c WHERE c.`constant_name` = 'No application records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Nessun record applicazione trovato'          FROM `lang_constants` c WHERE c.`constant_name` = 'No application records found';

-- Active items
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Active items');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Ítems activos'                FROM `lang_constants` c WHERE c.`constant_name` = 'Active items';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Ítems activos'                FROM `lang_constants` c WHERE c.`constant_name` = 'Active items';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Aktive Einträge'              FROM `lang_constants` c WHERE c.`constant_name` = 'Active items';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Éléments actifs'              FROM `lang_constants` c WHERE c.`constant_name` = 'Active items';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Éléments actifs'              FROM `lang_constants` c WHERE c.`constant_name` = 'Active items';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Itens ativos'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Active items';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Itens ativos'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Active items';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Voci attive'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Active items';
