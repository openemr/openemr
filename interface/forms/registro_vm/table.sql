-- ============================================================
-- Mechanical Ventilation Record Form - table.sql
-- Creates the form_registro_vm table and inserts translations
-- for 8 languages into lang_constants / lang_definitions.
-- ============================================================

CREATE TABLE IF NOT EXISTS `form_registro_vm` (
  `id`                          bigint(20)   NOT NULL AUTO_INCREMENT,
  `date`                        datetime     DEFAULT NULL,
  `pid`                         bigint(20)   NOT NULL DEFAULT 0,
  `encounter`                   bigint(20)   NOT NULL DEFAULT 0,
  `user`                        varchar(255) DEFAULT NULL,
  `groupname`                   varchar(255) DEFAULT NULL,
  `authorized`                  tinyint(4)   DEFAULT 0,
  `activity`                    tinyint(4)   DEFAULT 1,
  `modo_ventilacion`            varchar(30)  DEFAULT NULL,
  `obs_modo`                    varchar(255) DEFAULT NULL,
  `presion`                     tinyint(1)   DEFAULT 0,
  `obs_presion`                 varchar(255) DEFAULT NULL,
  `volumen`                     tinyint(1)   DEFAULT 0,
  `obs_volumen`                 varchar(255) DEFAULT NULL,
  `simv`                        tinyint(1)   DEFAULT 0,
  `obs_simv`                    varchar(255) DEFAULT NULL,
  `psv`                         tinyint(1)   DEFAULT 0,
  `obs_psv`                     varchar(255) DEFAULT NULL,
  `otros`                       tinyint(1)   DEFAULT 0,
  `obs_otros`                   varchar(255) DEFAULT NULL,
  `frecuencia_respiratoria`     tinyint(1)   DEFAULT 0,
  `obs_frecuencia_respiratoria` varchar(255) DEFAULT NULL,
  `p_inspiratorio`              tinyint(1)   DEFAULT 0,
  `obs_p_inspiratorio`          varchar(255) DEFAULT NULL,
  `p_media`                     tinyint(1)   DEFAULT 0,
  `obs_p_media`                 varchar(255) DEFAULT NULL,
  `p_max`                       tinyint(1)   DEFAULT 0,
  `obs_p_max`                   varchar(255) DEFAULT NULL,
  `chst`                        tinyint(1)   DEFAULT 0,
  `obs_chst`                    varchar(255) DEFAULT NULL,
  `disparo`                     tinyint(1)   DEFAULT 0,
  `obs_disparo`                 varchar(255) DEFAULT NULL,
  `fvt`                         tinyint(1)   DEFAULT 0,
  `obs_fvt`                     varchar(255) DEFAULT NULL,
  `vol_tidal`                   tinyint(1)   DEFAULT 0,
  `obs_vol_tidal`               varchar(255) DEFAULT NULL,
  `vm_programado`               tinyint(1)   DEFAULT 0,
  `obs_vm_programado`           varchar(255) DEFAULT NULL,
  `petco2`                      tinyint(1)   DEFAULT 0,
  `obs_petco2`                  varchar(255) DEFAULT NULL,
  `vdvt`                        tinyint(1)   DEFAULT 0,
  `obs_vdvt`                    varchar(255) DEFAULT NULL,
  `ko2`                         tinyint(1)   DEFAULT 0,
  `obs_ko2`                     varchar(255) DEFAULT NULL,
  `hora_registro`               time         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Translations (INSERT IGNORE to avoid duplicates)
-- lang_id: 3=es-ES, 4=es-419, 5=de, 8=fr, 9=fr-CA,
--          17=pt-PT, 18=pt-BR, 23=it
-- ============================================================

-- Mechanical Ventilation Record
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Mechanical Ventilation Record');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Registro de Ventilación Mecánica'      FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Registro de Ventilación Mecánica'      FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Beatmungsprotokoll'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Dossier de ventilation mécanique'      FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Dossier de ventilation mécanique'      FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Registo de Ventilação Mecânica'        FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Registro de Ventilação Mecânica'       FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Registro di Ventilazione Meccanica'    FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation Record';

-- MECHANICAL VENTILATION RECORD (uppercase for PDF header)
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('MECHANICAL VENTILATION RECORD');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'REGISTRO DE VENTILACIÓN MECÁNICA'      FROM `lang_constants` c WHERE c.`constant_name` = 'MECHANICAL VENTILATION RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'REGISTRO DE VENTILACIÓN MECÁNICA'      FROM `lang_constants` c WHERE c.`constant_name` = 'MECHANICAL VENTILATION RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'BEATMUNGSPROTOKOLL'                    FROM `lang_constants` c WHERE c.`constant_name` = 'MECHANICAL VENTILATION RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'DOSSIER DE VENTILATION MÉCANIQUE'      FROM `lang_constants` c WHERE c.`constant_name` = 'MECHANICAL VENTILATION RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'DOSSIER DE VENTILATION MÉCANIQUE'      FROM `lang_constants` c WHERE c.`constant_name` = 'MECHANICAL VENTILATION RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'REGISTO DE VENTILAÇÃO MECÂNICA'        FROM `lang_constants` c WHERE c.`constant_name` = 'MECHANICAL VENTILATION RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'REGISTRO DE VENTILAÇÃO MECÂNICA'       FROM `lang_constants` c WHERE c.`constant_name` = 'MECHANICAL VENTILATION RECORD';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'REGISTRO DI VENTILAZIONE MECCANICA'    FROM `lang_constants` c WHERE c.`constant_name` = 'MECHANICAL VENTILATION RECORD';

-- Ventilation Record
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Ventilation Record');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Registro de Ventilación'               FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Registro de Ventilación'               FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Beatmungsaufzeichnung'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Enregistrement de ventilation'         FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Enregistrement de ventilation'         FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Registo de Ventilação'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Registro de Ventilação'                FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Registro di Ventilazione'              FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Record';

-- Ventilation Parameters
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Ventilation Parameters');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Parámetros de Ventilación'             FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Parámetros de Ventilación'             FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Beatmungsparameter'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Paramètres de ventilation'             FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Paramètres de ventilation'             FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Parâmetros de Ventilação'              FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Parâmetros de Ventilação'              FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Parametri di Ventilazione'             FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Parameters';

-- Ventilation Mode
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Ventilation Mode');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Modo de Ventilación'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Mode';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Modo de Ventilación'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Mode';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Beatmungsmodus'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Mode';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Mode de ventilation'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Mode';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Mode de ventilation'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Mode';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Modo de Ventilação'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Mode';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Modo de Ventilação'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Mode';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Modalità di Ventilazione'              FROM `lang_constants` c WHERE c.`constant_name` = 'Ventilation Mode';

-- Spontaneous
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Spontaneous');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Espontánea'                            FROM `lang_constants` c WHERE c.`constant_name` = 'Spontaneous';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Espontánea'                            FROM `lang_constants` c WHERE c.`constant_name` = 'Spontaneous';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Spontan'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Spontaneous';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Spontanée'                             FROM `lang_constants` c WHERE c.`constant_name` = 'Spontaneous';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Spontanée'                             FROM `lang_constants` c WHERE c.`constant_name` = 'Spontaneous';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Espontânea'                            FROM `lang_constants` c WHERE c.`constant_name` = 'Spontaneous';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Espontânea'                            FROM `lang_constants` c WHERE c.`constant_name` = 'Spontaneous';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Spontanea'                             FROM `lang_constants` c WHERE c.`constant_name` = 'Spontaneous';

-- Mechanical Ventilation (mode option)
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Mechanical Ventilation');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Ventilación Mecánica'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Ventilación Mecánica'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Mechanische Beatmung'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Ventilation mécanique'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Ventilation mécanique'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Ventilação Mecânica'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Ventilação Mecânica'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Ventilazione Meccanica'                FROM `lang_constants` c WHERE c.`constant_name` = 'Mechanical Ventilation';

-- Pressure
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Pressure');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Presión'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Pressure';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Presión'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Pressure';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Druck'                                 FROM `lang_constants` c WHERE c.`constant_name` = 'Pressure';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Pression'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Pressure';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Pression'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Pressure';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Pressão'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Pressure';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Pressão'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Pressure';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Pressione'                             FROM `lang_constants` c WHERE c.`constant_name` = 'Pressure';

-- Volume
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Volume');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Volumen'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Volume';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Volumen'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Volume';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Volumen'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Volume';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Volume'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Volume';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Volume'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Volume';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Volume'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Volume';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Volume'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Volume';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Volume'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Volume';

-- Respiratory Rate
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Respiratory Rate');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Frecuencia Respiratoria'               FROM `lang_constants` c WHERE c.`constant_name` = 'Respiratory Rate';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Frecuencia Respiratoria'               FROM `lang_constants` c WHERE c.`constant_name` = 'Respiratory Rate';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Atemfrequenz'                          FROM `lang_constants` c WHERE c.`constant_name` = 'Respiratory Rate';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Fréquence respiratoire'                FROM `lang_constants` c WHERE c.`constant_name` = 'Respiratory Rate';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Fréquence respiratoire'                FROM `lang_constants` c WHERE c.`constant_name` = 'Respiratory Rate';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Frequência Respiratória'               FROM `lang_constants` c WHERE c.`constant_name` = 'Respiratory Rate';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Frequência Respiratória'               FROM `lang_constants` c WHERE c.`constant_name` = 'Respiratory Rate';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Frequenza Respiratoria'                FROM `lang_constants` c WHERE c.`constant_name` = 'Respiratory Rate';

-- P.Inspiratory / T.Inspiratory
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('P.Inspiratory / T.Inspiratory');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'P.Inspiratorio / T.Inspiratorio'       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Inspiratory / T.Inspiratory';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'P.Inspiratorio / T.Inspiratorio'       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Inspiratory / T.Inspiratory';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'P.Inspiratorisch / T.Inspiratorisch'   FROM `lang_constants` c WHERE c.`constant_name` = 'P.Inspiratory / T.Inspiratory';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'P.Inspiratoire / T.Inspiratoire'       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Inspiratory / T.Inspiratory';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'P.Inspiratoire / T.Inspiratoire'       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Inspiratory / T.Inspiratory';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'P.Inspiratório / T.Inspiratório'       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Inspiratory / T.Inspiratory';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'P.Inspiratório / T.Inspiratório'       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Inspiratory / T.Inspiratory';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'P.Inspiratorio / T.Inspiratorio'       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Inspiratory / T.Inspiratory';

-- P.Mean / PEEP
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('P.Mean / PEEP');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'P.Media / PEEP'                        FROM `lang_constants` c WHERE c.`constant_name` = 'P.Mean / PEEP';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'P.Media / PEEP'                        FROM `lang_constants` c WHERE c.`constant_name` = 'P.Mean / PEEP';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'P.Mittel / PEEP'                       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Mean / PEEP';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'P.Moyenne / PEEP'                      FROM `lang_constants` c WHERE c.`constant_name` = 'P.Mean / PEEP';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'P.Moyenne / PEEP'                      FROM `lang_constants` c WHERE c.`constant_name` = 'P.Mean / PEEP';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'P.Média / PEEP'                        FROM `lang_constants` c WHERE c.`constant_name` = 'P.Mean / PEEP';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'P.Média / PEEP'                        FROM `lang_constants` c WHERE c.`constant_name` = 'P.Mean / PEEP';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'P.Media / PEEP'                        FROM `lang_constants` c WHERE c.`constant_name` = 'P.Mean / PEEP';

-- P.Max / P.Plateau
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('P.Max / P.Plateau');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'P.Max / P.Plateau'                     FROM `lang_constants` c WHERE c.`constant_name` = 'P.Max / P.Plateau';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'P.Max / P.Plateau'                     FROM `lang_constants` c WHERE c.`constant_name` = 'P.Max / P.Plateau';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'P.Max / P.Plateau'                     FROM `lang_constants` c WHERE c.`constant_name` = 'P.Max / P.Plateau';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'P.Max / P.Plateau'                     FROM `lang_constants` c WHERE c.`constant_name` = 'P.Max / P.Plateau';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'P.Max / P.Plateau'                     FROM `lang_constants` c WHERE c.`constant_name` = 'P.Max / P.Plateau';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'P.Max / P.Platô'                       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Max / P.Plateau';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'P.Max / P.Platô'                       FROM `lang_constants` c WHERE c.`constant_name` = 'P.Max / P.Plateau';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'P.Max / P.Plateau'                     FROM `lang_constants` c WHERE c.`constant_name` = 'P.Max / P.Plateau';

-- Trigger F/P
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Trigger F/P');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Disparo por F/P'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Trigger F/P';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Disparo por F/P'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Trigger F/P';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Trigger F/P'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Trigger F/P';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Déclencheur F/P'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Trigger F/P';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Déclencheur F/P'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Trigger F/P';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Gatilho F/P'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Trigger F/P';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Gatilho F/P'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Trigger F/P';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Trigger F/P'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Trigger F/P';

-- Tidal Volume / Flow
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Tidal Volume / Flow');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Volumen Tidal / Flujo'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Tidal Volume / Flow';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Volumen Tidal / Flujo'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Tidal Volume / Flow';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Tidalvolumen / Fluss'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Tidal Volume / Flow';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Volume courant / Débit'                FROM `lang_constants` c WHERE c.`constant_name` = 'Tidal Volume / Flow';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Volume courant / Débit'                FROM `lang_constants` c WHERE c.`constant_name` = 'Tidal Volume / Flow';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Volume Corrente / Fluxo'               FROM `lang_constants` c WHERE c.`constant_name` = 'Tidal Volume / Flow';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Volume Corrente / Fluxo'               FROM `lang_constants` c WHERE c.`constant_name` = 'Tidal Volume / Flow';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Volume Corrente / Flusso'              FROM `lang_constants` c WHERE c.`constant_name` = 'Tidal Volume / Flow';

-- Programmed/Measured MV
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Programmed/Measured MV');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'VM Programado / Medido'                FROM `lang_constants` c WHERE c.`constant_name` = 'Programmed/Measured MV';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'VM Programado / Medido'                FROM `lang_constants` c WHERE c.`constant_name` = 'Programmed/Measured MV';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Programmiertes/Gemessenes MV'          FROM `lang_constants` c WHERE c.`constant_name` = 'Programmed/Measured MV';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'VM Programmé / Mesuré'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Programmed/Measured MV';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'VM Programmé / Mesuré'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Programmed/Measured MV';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'VM Programado / Medido'                FROM `lang_constants` c WHERE c.`constant_name` = 'Programmed/Measured MV';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'VM Programado / Medido'                FROM `lang_constants` c WHERE c.`constant_name` = 'Programmed/Measured MV';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'VM Programmato / Misurato'             FROM `lang_constants` c WHERE c.`constant_name` = 'Programmed/Measured MV';

-- Active parameters
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Active parameters');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Parámetros activos'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Active parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Parámetros activos'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Active parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Aktive Parameter'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Active parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Paramètres actifs'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Active parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Paramètres actifs'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Active parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Parâmetros ativos'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Active parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Parâmetros ativos'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Active parameters';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Parametri attivi'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Active parameters';

-- No ventilation records found
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('No ventilation records found');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'No se encontraron registros de ventilación' FROM `lang_constants` c WHERE c.`constant_name` = 'No ventilation records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'No se encontraron registros de ventilación' FROM `lang_constants` c WHERE c.`constant_name` = 'No ventilation records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Keine Beatmungsaufzeichnungen gefunden'     FROM `lang_constants` c WHERE c.`constant_name` = 'No ventilation records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Aucun dossier de ventilation trouvé'        FROM `lang_constants` c WHERE c.`constant_name` = 'No ventilation records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Aucun dossier de ventilation trouvé'        FROM `lang_constants` c WHERE c.`constant_name` = 'No ventilation records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Nenhum registo de ventilação encontrado'    FROM `lang_constants` c WHERE c.`constant_name` = 'No ventilation records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Nenhum registro de ventilação encontrado'   FROM `lang_constants` c WHERE c.`constant_name` = 'No ventilation records found';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Nessun record di ventilazione trovato'      FROM `lang_constants` c WHERE c.`constant_name` = 'No ventilation records found';

-- New Ventilation Record
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('New Ventilation Record');
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 3,  'Nuevo Registro de Ventilación'       FROM `lang_constants` c WHERE c.`constant_name` = 'New Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 4,  'Nuevo Registro de Ventilación'       FROM `lang_constants` c WHERE c.`constant_name` = 'New Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 5,  'Neues Beatmungsprotokoll'            FROM `lang_constants` c WHERE c.`constant_name` = 'New Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 8,  'Nouveau dossier de ventilation'      FROM `lang_constants` c WHERE c.`constant_name` = 'New Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 9,  'Nouveau dossier de ventilation'      FROM `lang_constants` c WHERE c.`constant_name` = 'New Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 17, 'Novo Registo de Ventilação'          FROM `lang_constants` c WHERE c.`constant_name` = 'New Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 18, 'Novo Registro de Ventilação'         FROM `lang_constants` c WHERE c.`constant_name` = 'New Ventilation Record';
INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
  SELECT c.`cons_id`, 23, 'Nuovo registro di ventilazione'      FROM `lang_constants` c WHERE c.`constant_name` = 'New Ventilation Record';
