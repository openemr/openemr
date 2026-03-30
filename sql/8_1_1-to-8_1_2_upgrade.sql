--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist, the block will be executed
--
--  #EndIf
--    all blocks are terminated with a #EndIf statement.
--

-- Nursing Module (v8.1.2) -------------------------------------------------
-- Adds inpatient nursing forms: Wound Care, Applications, Care Bundle,
-- Evaluations, Mechanical Ventilation, and Admission tracking.
-- -------------------------------------------------------------------------

#IfNotTable form_curaciones
CREATE TABLE `form_curaciones` (
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
#EndIf

#IfNotTable form_aplicaciones
CREATE TABLE `form_aplicaciones` (
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
#EndIf

#IfNotTable form_cuidados
CREATE TABLE `form_cuidados` (
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
#EndIf

#IfNotTable form_evaluaciones
CREATE TABLE `form_evaluaciones` (
  `id`                  bigint(20)   NOT NULL AUTO_INCREMENT,
  `date`                datetime     DEFAULT NULL,
  `pid`                 bigint(20)   DEFAULT NULL,
  `encounter`           int(11)      NOT NULL,
  `user`                varchar(255) DEFAULT NULL,
  `groupname`           varchar(255) DEFAULT NULL,
  `authorized`          tinyint(4)   DEFAULT NULL,
  `activity`            tinyint(4)   DEFAULT NULL,
  `conciencia`          varchar(50)  DEFAULT NULL,
  `obs_conciencia`      varchar(255) DEFAULT NULL,
  `tono`                varchar(50)  DEFAULT NULL,
  `obs_tono`            varchar(255) DEFAULT NULL,
  `pupilas`             varchar(50)  DEFAULT NULL,
  `obs_pupilas`         varchar(255) DEFAULT NULL,
  `mucosas`             varchar(50)  DEFAULT NULL,
  `obs_mucosas`         varchar(255) DEFAULT NULL,
  `glasgow_ojos`        varchar(50)  DEFAULT NULL,
  `obs_glasgow_ojos`    varchar(255) DEFAULT NULL,
  `glasgow_motora`      varchar(50)  DEFAULT NULL,
  `obs_glasgow_motora`  varchar(255) DEFAULT NULL,
  `glasgow_verbal`      varchar(50)  DEFAULT NULL,
  `obs_glasgow_verbal`  varchar(255) DEFAULT NULL,
  `glasgow_total`       int(2)       DEFAULT NULL,
  `hora_evaluacion`     time         DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`, `encounter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
#EndIf

#IfNotTable form_registro_vm
CREATE TABLE `form_registro_vm` (
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
#EndIf

#IfNotTable form_nursing_admission
CREATE TABLE `form_nursing_admission` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
#EndIf
