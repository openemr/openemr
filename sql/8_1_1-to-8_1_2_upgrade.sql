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

#IfNotRow openemr_postcalendar_categories pc_catname Inpatient
SET @catid = (SELECT MAX(pc_catid) FROM openemr_postcalendar_categories);
INSERT INTO `openemr_postcalendar_categories` (`pc_catid`, `pc_catname`, `pc_catcolor`, `pc_catdesc`, `pc_recurrtype`, `pc_enddate`, `pc_recurrspec`, `pc_recurrfreq`, `pc_duration`, `pc_end_date_flag`, `pc_end_date_type`, `pc_end_date_freq`, `pc_end_all_day`, `pc_dailylimit`, `pc_cattype`, `pc_active`, `pc_seq`) VALUES (@catid+1, 'Inpatient', '#FF6600', 'Inpatient / Hospitalization encounters', 0, NULL, 'a:5:{s:17:"event_repeat_freq";s:1:"0";s:22:"event_repeat_freq_type";s:1:"0";s:19:"event_repeat_on_num";s:1:"1";s:19:"event_repeat_on_day";s:1:"0";s:20:"event_repeat_on_freq";s:1:"0";}', 0, 0, 0, 0, 0, 0, 0, 0, 1, @catid+1);
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

-- Nursing Module: Spanish translations (Latin American + Spain)
#IfNotRow lang_constants constant_name Inpatient List
INSERT IGNORE INTO lang_constants (constant_name) VALUES
  ('Inpatient List'),('New Admission'),('Nursing Options'),('Nursing Applications'),
  ('Nursing Care Bundle'),('Nursing Evaluation'),('Ventilation Record'),('Wound Care'),
  ('Admission'),('Admission Date'),('Record No.'),('Reg. No.'),('Ward'),('Bed'),
  ('Coming Soon'),('Deceased'),('Discharge patient'),('Register patient death'),
  ('Register date of death'),('Are you sure you want to discharge the patient'),
  ('Patient discharged successfully'),('Patient updated successfully'),
  ('Error: Could not retrieve patient data.'),('ID (Patient Record)'),
  ('Select Patient'),('Admit Patient'),('Edit Admission'),('No active inpatient admission.');

INSERT INTO lang_definitions (cons_id, lang_id, definition)
SELECT c.cons_id, ll.lang_id, t.def
FROM lang_constants c
JOIN (SELECT 'Inpatient List' cn,'Lista de Internados' def UNION ALL
      SELECT 'New Admission','Nuevo Ingreso' UNION ALL
      SELECT 'Nursing Options','Opciones de Enfermería' UNION ALL
      SELECT 'Nursing Applications','Aplicaciones de Enfermería' UNION ALL
      SELECT 'Nursing Care Bundle','Bundle de Cuidados' UNION ALL
      SELECT 'Nursing Evaluation','Evaluación de Enfermería' UNION ALL
      SELECT 'Ventilation Record','Registro de Ventilación Mecánica' UNION ALL
      SELECT 'Wound Care','Curaciones' UNION ALL
      SELECT 'Admission','Admisión' UNION ALL
      SELECT 'Admission Date','Fecha de Admisión' UNION ALL
      SELECT 'Record No.','Nro. Registro' UNION ALL
      SELECT 'Reg. No.','Nro. Reg.' UNION ALL
      SELECT 'Ward','Sala' UNION ALL
      SELECT 'Bed','Cama' UNION ALL
      SELECT 'Coming Soon','Próximamente' UNION ALL
      SELECT 'Deceased','Fallecido' UNION ALL
      SELECT 'Discharge patient','Dar de alta al paciente' UNION ALL
      SELECT 'Register patient death','Registrar fallecimiento' UNION ALL
      SELECT 'Register date of death','Fecha de fallecimiento' UNION ALL
      SELECT 'Are you sure you want to discharge the patient','¿Dar de alta al paciente' UNION ALL
      SELECT 'Patient discharged successfully','Paciente dado de alta exitosamente' UNION ALL
      SELECT 'Patient updated successfully','Paciente actualizado exitosamente' UNION ALL
      SELECT 'Error: Could not retrieve patient data.','Error: No se pudieron obtener los datos del paciente.' UNION ALL
      SELECT 'ID (Patient Record)','ID (Registro del Paciente)' UNION ALL
      SELECT 'Select Patient','Seleccionar Paciente' UNION ALL
      SELECT 'Admit Patient','Internar Paciente' UNION ALL
      SELECT 'Edit Admission','Editar Internación' UNION ALL
      SELECT 'No active inpatient admission.','Sin internación activa.'
) t ON c.constant_name = t.cn
JOIN lang_languages ll ON ll.lang_description IN ('Spanish (Latin American)', 'Spanish (Spain)')
ON DUPLICATE KEY UPDATE definition = VALUES(definition);

UPDATE lang_definitions ld
JOIN lang_constants lc ON lc.cons_id = ld.cons_id
JOIN lang_languages ll ON ll.lang_id = ld.lang_id
SET ld.definition = 'Alta'
WHERE lc.constant_name = 'Discharge'
  AND ll.lang_description IN ('Spanish (Latin American)', 'Spanish (Spain)');

-- Nursing form labels translations
INSERT IGNORE INTO lang_constants (constant_name) VALUES
  ('Create Mode'),('Edit Mode'),('Observations...'),('Save Changes'),('Save'),
  ('New Wound Care'),('Edit Wound Care'),('Surgical Wound'),('Tracheostomy'),
  ('Ostomies'),('Pressure Sores'),('Central Venous Line'),('Peripheral IV Line'),('Care Time'),
  ('New Application'),('Edit Application'),('Saline Solutions'),('Vaccines'),
  ('Plasma Expanders'),('Blood and Blood Products'),('Record Time'),
  ('New Care Bundle'),('Edit Care Bundle'),('Patient Position'),('Oral Rinse'),
  ('Hand Hygiene Pre/Post Suctioning'),('Secretion Suctioning with Gloves and Assistant'),
  ('Daily Sedation Suspension and Extubation Evaluation'),('Cuff Pressure Measurement'),
  ('New Ventilation Record'),('Edit Ventilation Record'),('Ventilation Mode'),
  ('Spontaneous'),('Mechanical Ventilation'),('Pressure'),('Volume'),('Respiratory Rate'),
  ('P.Inspiratory / T.Inspiratory'),('P.Mean / PEEP'),('P.Max / P.Plateau'),
  ('Trigger F/P'),('Tidal Volume / Flow'),('Programmed/Measured MV'),
  ('New Nursing Evaluation'),('Edit Nursing Evaluation'),('Consciousness'),('Muscle Tone'),
  ('Mucous Membranes'),('Glasgow Coma Scale'),('Eye Opening'),('Motor Response'),
  ('Verbal Response'),('Glasgow Total Score'),('Evaluation Time'),
  ('Mild'),('Moderate'),('Severe'),
  ('Spontaneously'),('To auditory stimuli'),('To pain'),('No response'),
  ('Obeys commands'),('Localizes pain'),('Withdrawal'),('Abnormal flexion'),
  ('Abnormal extension'),('Oriented and conversing'),('Disoriented and conversing'),
  ('Inappropriate words'),('Incomprehensible sounds');

INSERT INTO lang_definitions (cons_id, lang_id, definition)
SELECT c.cons_id, ll.lang_id, t.def
FROM lang_constants c
JOIN (SELECT 'Create Mode' cn,'Modo Creación' def UNION ALL
      SELECT 'Edit Mode','Modo Edición' UNION ALL
      SELECT 'Observations...','Observaciones...' UNION ALL
      SELECT 'Save Changes','Guardar Cambios' UNION ALL
      SELECT 'Save','Guardar' UNION ALL
      SELECT 'New Wound Care','Nueva Curación' UNION ALL
      SELECT 'Edit Wound Care','Editar Curación' UNION ALL
      SELECT 'Surgical Wound','Herida Operatoria' UNION ALL
      SELECT 'Tracheostomy','Traqueostomía' UNION ALL
      SELECT 'Ostomies','Ostomías' UNION ALL
      SELECT 'Pressure Sores','Escaras' UNION ALL
      SELECT 'Central Venous Line','Vía Venosa Central' UNION ALL
      SELECT 'Peripheral IV Line','Vía Venosa Periférica' UNION ALL
      SELECT 'Care Time','Hora de Curación' UNION ALL
      SELECT 'New Application','Nueva Aplicación' UNION ALL
      SELECT 'Edit Application','Editar Aplicación' UNION ALL
      SELECT 'Saline Solutions','Sueros' UNION ALL
      SELECT 'Vaccines','Vacunas' UNION ALL
      SELECT 'Plasma Expanders','Expansores de Plasma' UNION ALL
      SELECT 'Blood and Blood Products','Sangre y Hemoderivados' UNION ALL
      SELECT 'Record Time','Hora de Registro' UNION ALL
      SELECT 'New Care Bundle','Nuevo Bundle de Cuidados' UNION ALL
      SELECT 'Edit Care Bundle','Editar Bundle de Cuidados' UNION ALL
      SELECT 'Patient Position','Posición del Paciente' UNION ALL
      SELECT 'Oral Rinse','Enjuague Bucal' UNION ALL
      SELECT 'Hand Hygiene Pre/Post Suctioning','Higiene de Manos Pre/Post Aspiración' UNION ALL
      SELECT 'Secretion Suctioning with Gloves and Assistant','Aspirado de Secreciones con Guantes y Asistente' UNION ALL
      SELECT 'Daily Sedation Suspension and Extubation Evaluation','Suspensión Diaria de Sedación y Evaluación de Extubación' UNION ALL
      SELECT 'Cuff Pressure Measurement','Medición de Presión del Cuff' UNION ALL
      SELECT 'New Ventilation Record','Nuevo Registro de VM' UNION ALL
      SELECT 'Edit Ventilation Record','Editar Registro de VM' UNION ALL
      SELECT 'Ventilation Mode','Modo de Ventilación' UNION ALL
      SELECT 'Spontaneous','Espontánea' UNION ALL
      SELECT 'Mechanical Ventilation','Ventilación Mecánica' UNION ALL
      SELECT 'Pressure','Presión' UNION ALL
      SELECT 'Volume','Volumen' UNION ALL
      SELECT 'Respiratory Rate','Frecuencia Respiratoria' UNION ALL
      SELECT 'P.Inspiratory / T.Inspiratory','P.Inspiratoria / T.Inspiratoria' UNION ALL
      SELECT 'P.Mean / PEEP','P.Media / PEEP' UNION ALL
      SELECT 'P.Max / P.Plateau','P.Máx / P.Meseta' UNION ALL
      SELECT 'Trigger F/P','Disparo F/P' UNION ALL
      SELECT 'Tidal Volume / Flow','Volumen Tidal / Flujo' UNION ALL
      SELECT 'Programmed/Measured MV','VM Programado/Medido' UNION ALL
      SELECT 'New Nursing Evaluation','Nueva Evaluación de Enfermería' UNION ALL
      SELECT 'Edit Nursing Evaluation','Editar Evaluación de Enfermería' UNION ALL
      SELECT 'Consciousness','Conciencia' UNION ALL
      SELECT 'Muscle Tone','Tono Muscular' UNION ALL
      SELECT 'Mucous Membranes','Mucosas' UNION ALL
      SELECT 'Glasgow Coma Scale','Escala de Glasgow' UNION ALL
      SELECT 'Eye Opening','Apertura Ocular' UNION ALL
      SELECT 'Motor Response','Respuesta Motora' UNION ALL
      SELECT 'Verbal Response','Respuesta Verbal' UNION ALL
      SELECT 'Glasgow Total Score','Puntaje Total Glasgow' UNION ALL
      SELECT 'Evaluation Time','Hora de Evaluación' UNION ALL
      SELECT 'Mild','Leve' UNION ALL
      SELECT 'Moderate','Moderado' UNION ALL
      SELECT 'Severe','Grave' UNION ALL
      SELECT 'Spontaneously','Espontáneamente' UNION ALL
      SELECT 'To auditory stimuli','A estímulos auditivos' UNION ALL
      SELECT 'To pain','Al dolor' UNION ALL
      SELECT 'No response','Sin respuesta' UNION ALL
      SELECT 'Obeys commands','Obedece órdenes' UNION ALL
      SELECT 'Localizes pain','Localiza el dolor' UNION ALL
      SELECT 'Withdrawal','Flexión de defensa' UNION ALL
      SELECT 'Abnormal flexion','Flexión anormal' UNION ALL
      SELECT 'Abnormal extension','Extensión anormal' UNION ALL
      SELECT 'Oriented and conversing','Orientado y conversa' UNION ALL
      SELECT 'Disoriented and conversing','Desorientado y conversa' UNION ALL
      SELECT 'Inappropriate words','Lenguaje inadecuado' UNION ALL
      SELECT 'Incomprehensible sounds','Sonidos incomprensibles'
) t ON c.constant_name = t.cn
JOIN lang_languages ll ON ll.lang_description IN ('Spanish (Latin American)', 'Spanish (Spain)')
ON DUPLICATE KEY UPDATE definition = VALUES(definition);
#EndIf

-- Nursing Module: German + Portuguese translations (and remaining Spanish strings)
#IfNotRow lang_constants constant_name VIGIL
INSERT IGNORE INTO lang_constants (constant_name) VALUES
  ('VIGIL'),('SOMNOLIENTO'),('ESTUPOROSO'),('COMATOSO'),
  ('FLACIDO'),('ESPASTICO'),('MIDRIASIS'),('MIOSIS'),
  ('SECA'),('HUMEDA'),('PALIDA'),('ICTERICA'),('CIANOSIS'),
  ('NORMAL'),('Nursing'),('Actions'),('Service'),('Ward'),('Bed'),('Department'),
  ('Room / Bed'),('Discharge'),('Deceased'),('Inpatient Admission'),
  ('Admission Details'),('Date of Admission'),('Reg. #'),('Yes'),('No'),
  ('Wound Care'),('Nursing Wound Care'),('Nursing Applications'),('Nursing Care Bundle'),
  ('Nursing Evaluation'),('Ventilation Record'),
  ('Access denied'),('Error: Could not retrieve patient data.'),
  ('Patient discharged successfully'),('Patient updated successfully');

INSERT INTO lang_definitions (cons_id, lang_id, definition)
SELECT c.cons_id, ll.lang_id,
  CASE ll.lang_id
    WHEN 3  THEN t.es WHEN 4  THEN t.es
    WHEN 5  THEN t.de
    WHEN 19 THEN t.pt_eu WHEN 21 THEN t.pt_eu
    WHEN 20 THEN t.pt_br
  END
FROM lang_constants c
JOIN (SELECT 'Nursing' cn,'Enfermería' es,'Pflege' de,'Enfermagem' pt_eu,'Enfermagem' pt_br UNION ALL
      SELECT 'VIGIL','Vigil','Wach','Vigil','Vigil' UNION ALL
      SELECT 'SOMNOLIENTO','Somnoliento','Schläfrig','Sonolento','Sonolento' UNION ALL
      SELECT 'ESTUPOROSO','Estuporoso','Stuporös','Estupuroso','Estupuroso' UNION ALL
      SELECT 'COMATOSO','Comatoso','Komatös','Comatoso','Comatoso' UNION ALL
      SELECT 'NORMAL','Normal','Normal','Normal','Normal' UNION ALL
      SELECT 'FLACIDO','Flácido','Schlaff','Flácido','Flácido' UNION ALL
      SELECT 'ESPASTICO','Espástico','Spastisch','Espástico','Espástico' UNION ALL
      SELECT 'MIDRIASIS','Midriasis','Mydriasis','Midríase','Midríase' UNION ALL
      SELECT 'MIOSIS','Miosis','Miosis','Miose','Miose' UNION ALL
      SELECT 'SECA','Seca','Trocken','Seca','Seca' UNION ALL
      SELECT 'HUMEDA','Húmeda','Feucht','Húmida','Úmida' UNION ALL
      SELECT 'PALIDA','Pálida','Blass','Pálida','Pálida' UNION ALL
      SELECT 'ICTERICA','Ictérica','Ikterisch','Ictérica','Ictérica' UNION ALL
      SELECT 'CIANOSIS','Cianosis','Zyanose','Cianose','Cianose' UNION ALL
      SELECT 'Actions','Acciones','Aktionen','Ações','Ações' UNION ALL
      SELECT 'Service','Servicio','Dienst','Serviço','Serviço' UNION ALL
      SELECT 'Ward','Sala','Station','Enfermaria','Enfermaria' UNION ALL
      SELECT 'Bed','Cama','Bett','Cama','Cama' UNION ALL
      SELECT 'Department','Departamento','Abteilung','Departamento','Departamento' UNION ALL
      SELECT 'Room / Bed','Cuarto / Cama','Zimmer / Bett','Quarto / Cama','Quarto / Cama' UNION ALL
      SELECT 'Yes','Sí','Ja','Sim','Sim' UNION ALL
      SELECT 'No','No','Nein','Não','Não' UNION ALL
      SELECT 'Discharge','Alta','Entlassung','Alta','Alta' UNION ALL
      SELECT 'Deceased','Fallecido','Verstorben','Falecido','Falecido' UNION ALL
      SELECT 'Inpatient Admission','Internación','Stationäre Aufnahme','Internamento','Internação' UNION ALL
      SELECT 'Admission Details','Detalles de Internación','Aufnahmedetails','Detalhes da Admissão','Detalhes da Admissão' UNION ALL
      SELECT 'Date of Admission','Fecha de Internación','Aufnahmedatum','Data de Internamento','Data de Internação' UNION ALL
      SELECT 'Reg. #','Reg. #','Reg.-Nr.','Reg. Nº','Reg. Nº' UNION ALL
      SELECT 'Wound Care','Curaciones','Wundversorgung','Cuidados de Feridas','Cuidados de Feridas' UNION ALL
      SELECT 'Nursing Wound Care','Registro de Curaciones de Enfermería','Pflegewundversorgung','Cuidados de Feridas de Enfermagem','Cuidados de Feridas de Enfermagem' UNION ALL
      SELECT 'Nursing Applications','Aplicaciones de Enfermería','Pflegeanwendungen','Aplicações de Enfermagem','Aplicações de Enfermagem' UNION ALL
      SELECT 'Nursing Care Bundle','Bundle de Cuidados de Enfermería','Pflegebündel','Bundle de Cuidados de Enfermagem','Bundle de Cuidados de Enfermagem' UNION ALL
      SELECT 'Nursing Evaluation','Evaluación de Enfermería','Pflegebewertung','Avaliação de Enfermagem','Avaliação de Enfermagem' UNION ALL
      SELECT 'Ventilation Record','Registro de Ventilación Mecánica','Beatmungsprotokoll','Registo de Ventilação','Registro de Ventilação' UNION ALL
      SELECT 'Access denied','Acceso denegado','Zugriff verweigert','Acesso negado','Acesso negado' UNION ALL
      SELECT 'Patient discharged successfully','Paciente dado de alta exitosamente','Patient erfolgreich entlassen','Paciente dado de alta com sucesso','Paciente dado de alta com sucesso' UNION ALL
      SELECT 'Patient updated successfully','Paciente actualizado exitosamente','Patient erfolgreich aktualisiert','Paciente atualizado com sucesso','Paciente atualizado com sucesso' UNION ALL
      SELECT 'Error: Could not retrieve patient data.','Error: No se pudieron obtener los datos del paciente.','Fehler: Patientendaten konnten nicht abgerufen werden.','Erro: Não foi possível obter os dados do paciente.','Erro: Não foi possível obter os dados do paciente.' UNION ALL
      -- Curaciones
      SELECT 'New Wound Care','Nueva Curación','Neue Wundversorgung','Novo Cuidado de Feridas','Novo Cuidado de Feridas' UNION ALL
      SELECT 'Edit Wound Care','Editar Curación','Wundversorgung bearbeiten','Editar Cuidado de Feridas','Editar Cuidado de Feridas' UNION ALL
      SELECT 'Surgical Wound','Herida Operatoria','Operationswunde','Ferida Operatória','Ferida Cirúrgica' UNION ALL
      SELECT 'Tracheostomy','Traqueostomía','Tracheostomie','Traqueostomia','Traqueostomia' UNION ALL
      SELECT 'Ostomies','Ostomías','Stomata','Ostomias','Ostomias' UNION ALL
      SELECT 'Pressure Sores','Escaras','Druckgeschwüre','Úlceras de Pressão','Úlceras de Pressão' UNION ALL
      SELECT 'Central Venous Line','Vía Venosa Central','Zentraler Venenkatheter','Cateter Venoso Central','Cateter Venoso Central' UNION ALL
      SELECT 'Peripheral IV Line','Vía Venosa Periférica','Peripherer Venenkatheter','Acesso Venoso Periférico','Acesso Venoso Periférico' UNION ALL
      SELECT 'Care Time','Hora de Curación','Pflegezeit','Hora dos Cuidados','Hora dos Cuidados' UNION ALL
      -- Aplicaciones
      SELECT 'New Application','Nueva Aplicación','Neue Anwendung','Nova Aplicação','Nova Aplicação' UNION ALL
      SELECT 'Edit Application','Editar Aplicación','Anwendung bearbeiten','Editar Aplicação','Editar Aplicação' UNION ALL
      SELECT 'Saline Solutions','Sueros','Kochsalzlösungen','Soluções Salinas','Soluções Salinas' UNION ALL
      SELECT 'Vaccines','Vacunas','Impfstoffe','Vacinas','Vacinas' UNION ALL
      SELECT 'Plasma Expanders','Expansores de Plasma','Plasmaexpander','Expansores de Plasma','Expansores de Plasma' UNION ALL
      SELECT 'Blood and Blood Products','Sangre y Hemoderivados','Blut und Blutprodukte','Sangue e Hemoderivados','Sangue e Hemoderivados' UNION ALL
      SELECT 'Record Time','Hora de Registro','Aufzeichnungszeit','Hora de Registo','Hora de Registro' UNION ALL
      -- Cuidados
      SELECT 'New Care Bundle','Nuevo Bundle de Cuidados','Neues Pflegebündel','Novo Bundle de Cuidados','Novo Bundle de Cuidados' UNION ALL
      SELECT 'Edit Care Bundle','Editar Bundle de Cuidados','Pflegebündel bearbeiten','Editar Bundle de Cuidados','Editar Bundle de Cuidados' UNION ALL
      SELECT 'Patient Position','Posición del Paciente','Patientenposition','Posição do Paciente','Posição do Paciente' UNION ALL
      SELECT 'Oral Rinse','Enjuague Bucal','Mundspülung','Higiene Oral','Higiene Oral' UNION ALL
      SELECT 'Hand Hygiene Pre/Post Suctioning','Higiene de Manos Pre/Post Aspiración','Händehygiene vor/nach Absaugung','Higiene das Mãos Pré/Pós Aspiração','Higiene das Mãos Pré/Pós Aspiração' UNION ALL
      SELECT 'Secretion Suctioning with Gloves and Assistant','Aspirado de Secreciones con Guantes y Asistente','Sekretabsaugung mit Handschuhen und Assistent','Aspiração de Secreções com Luvas e Assistente','Aspiração de Secreções com Luvas e Assistente' UNION ALL
      SELECT 'Daily Sedation Suspension and Extubation Evaluation','Suspensión Diaria de Sedación y Evaluación de Extubación','Tägliche Sedierungspause und Extubationsbewertung','Suspensão Diária de Sedação e Avaliação de Extubação','Suspensão Diária de Sedação e Avaliação de Extubação' UNION ALL
      SELECT 'Cuff Pressure Measurement','Medición de Presión del Cuff','Cuff-Druckmessung','Medição da Pressão do Cuff','Medição da Pressão do Cuff' UNION ALL
      -- Evaluaciones
      SELECT 'New Nursing Evaluation','Nueva Evaluación de Enfermería','Neue Pflegebewertung','Nova Avaliação de Enfermagem','Nova Avaliação de Enfermagem' UNION ALL
      SELECT 'Edit Nursing Evaluation','Editar Evaluación de Enfermería','Pflegebewertung bearbeiten','Editar Avaliação de Enfermagem','Editar Avaliação de Enfermagem' UNION ALL
      SELECT 'Consciousness','Conciencia','Bewusstsein','Consciência','Consciência' UNION ALL
      SELECT 'Muscle Tone','Tono Muscular','Muskeltonus','Tónus Muscular','Tônus Muscular' UNION ALL
      SELECT 'Mucous Membranes','Mucosas','Schleimhäute','Mucosas','Mucosas' UNION ALL
      SELECT 'Glasgow Coma Scale','Escala de Glasgow','Glasgow-Koma-Skala','Escala de Coma de Glasgow','Escala de Coma de Glasgow' UNION ALL
      SELECT 'Eye Opening','Apertura Ocular','Augenöffnung','Abertura Ocular','Abertura Ocular' UNION ALL
      SELECT 'Motor Response','Respuesta Motora','Motorische Reaktion','Resposta Motora','Resposta Motora' UNION ALL
      SELECT 'Verbal Response','Respuesta Verbal','Verbale Reaktion','Resposta Verbal','Resposta Verbal' UNION ALL
      SELECT 'Glasgow Total Score','Puntaje Total Glasgow','Glasgow Gesamtpunktzahl','Pontuação Total Glasgow','Pontuação Total Glasgow' UNION ALL
      SELECT 'Evaluation Time','Hora de Evaluación','Bewertungszeit','Hora da Avaliação','Hora da Avaliação' UNION ALL
      SELECT 'Mild','Leve','Leicht','Ligeiro','Leve' UNION ALL
      SELECT 'Moderate','Moderado','Mäßig','Moderado','Moderado' UNION ALL
      SELECT 'Severe','Grave','Schwer','Grave','Grave' UNION ALL
      SELECT 'Spontaneously','Espontáneamente','Spontan','Espontaneamente','Espontaneamente' UNION ALL
      SELECT 'To auditory stimuli','A estímulos auditivos','Auf akustische Reize','A estímulos auditivos','A estímulos auditivos' UNION ALL
      SELECT 'To pain','Al dolor','Auf Schmerzreize','À dor','À dor' UNION ALL
      SELECT 'No response','Sin respuesta','Keine Reaktion','Sem resposta','Sem resposta' UNION ALL
      SELECT 'Obeys commands','Obedece órdenes','Befolgt Befehle','Obedece ordens','Obedece ordens' UNION ALL
      SELECT 'Localizes pain','Localiza el dolor','Lokalisiert Schmerz','Localiza a dor','Localiza a dor' UNION ALL
      SELECT 'Withdrawal','Flexión de defensa','Rückzugsreaktion','Retirada','Retirada' UNION ALL
      SELECT 'Abnormal flexion','Flexión anormal','Abnormale Beugung','Flexão anormal','Flexão anormal' UNION ALL
      SELECT 'Abnormal extension','Extensión anormal','Abnormale Streckung','Extensão anormal','Extensão anormal' UNION ALL
      SELECT 'Oriented and conversing','Orientado y conversa','Orientiert und sprechend','Orientado e a conversar','Orientado e conversando' UNION ALL
      SELECT 'Disoriented and conversing','Desorientado y conversa','Desorientiert und sprechend','Desorientado e a conversar','Desorientado e conversando' UNION ALL
      SELECT 'Inappropriate words','Lenguaje inadecuado','Unangemessene Worte','Palavras inadequadas','Palavras inadequadas' UNION ALL
      SELECT 'Incomprehensible sounds','Sonidos incomprensibles','Unverständliche Laute','Sons incompreensíveis','Sons incompreensíveis' UNION ALL
      -- Registro VM
      SELECT 'New Ventilation Record','Nuevo Registro de VM','Neues Beatmungsprotokoll','Novo Registo de Ventilação','Novo Registro de Ventilação' UNION ALL
      SELECT 'Edit Ventilation Record','Editar Registro de VM','Beatmungsprotokoll bearbeiten','Editar Registo de Ventilação','Editar Registro de Ventilação' UNION ALL
      SELECT 'Ventilation Mode','Modo de Ventilación','Beatmungsmodus','Modo de Ventilação','Modo de Ventilação' UNION ALL
      SELECT 'Spontaneous','Espontánea','Spontanatmung','Espontânea','Espontânea' UNION ALL
      SELECT 'Mechanical Ventilation','Ventilación Mecánica','Mechanische Beatmung','Ventilação Mecânica','Ventilação Mecânica' UNION ALL
      SELECT 'Pressure','Presión','Druck','Pressão','Pressão' UNION ALL
      SELECT 'Volume','Volumen','Volumen','Volume','Volume' UNION ALL
      SELECT 'Other','Otro','Sonstige','Outro','Outro' UNION ALL
      SELECT 'Respiratory Rate','Frecuencia Respiratoria','Atemfrequenz','Frequência Respiratória','Frequência Respiratória' UNION ALL
      SELECT 'P.Inspiratory / T.Inspiratory','P.Inspiratoria / T.Inspiratoria','P.Inspiratorisch / T.Inspir.','P.Inspiratória / T.Inspiratória','P.Inspiratória / T.Inspiratória' UNION ALL
      SELECT 'P.Mean / PEEP','P.Media / PEEP','P.Mittel / PEEP','P.Média / PEEP','P.Média / PEEP' UNION ALL
      SELECT 'P.Max / P.Plateau','P.Máx / P.Meseta','P.Max / P.Plateau','P.Máx / P.Platô','P.Máx / P.Platô' UNION ALL
      SELECT 'Trigger F/P','Disparo F/P','Trigger F/P','Disparo F/P','Disparo F/P' UNION ALL
      SELECT 'Tidal Volume / Flow','Volumen Tidal / Flujo','Tidalvolumen / Fluss','Volume Tidal / Fluxo','Volume Tidal / Fluxo' UNION ALL
      SELECT 'Programmed/Measured MV','VM Programado/Medido','Progr./Gemess. Atemminutenvolumen','VM Programada/Medida','VM Programada/Medida' UNION ALL
      -- Common UI
      SELECT 'Create Mode','Modo Creación','Erstellungsmodus','Modo Criação','Modo Criação' UNION ALL
      SELECT 'Edit Mode','Modo Edición','Bearbeitungsmodus','Modo Edição','Modo Edição' UNION ALL
      SELECT 'Observations...','Observaciones...','Beobachtungen...','Observações...','Observações...' UNION ALL
      SELECT 'Save Changes','Guardar Cambios','Änderungen speichern','Guardar Alterações','Salvar Alterações' UNION ALL
      SELECT 'Save','Guardar','Speichern','Guardar','Salvar' UNION ALL
      SELECT 'No active inpatient admission.','Sin internación activa.','Keine aktive stationäre Aufnahme.','Sem internamento ativo.','Sem internação ativa.' UNION ALL
      -- Error / validation strings
      SELECT 'Encounter not found','Visita no encontrada','Behandlung nicht gefunden','Consulta não encontrada','Consulta não encontrada' UNION ALL
      SELECT 'Missing patient ID','ID de paciente faltante','Patienten-ID fehlt','ID do paciente em falta','ID do paciente ausente' UNION ALL
      SELECT 'Invalid patient or category','Paciente o categoría inválido','Ungültiger Patient oder Kategorie','Paciente ou categoria inválido','Paciente ou categoria inválido' UNION ALL
      SELECT 'Invalid encounter','Visita inválida','Ungültige Behandlung','Consulta inválida','Consulta inválida' UNION ALL
      SELECT 'Invalid mode','Modo inválido','Ungültiger Modus','Modo inválido','Modo inválido' UNION ALL
      SELECT 'Error: Missing required parameters (PID or Encounter)','Error: Faltan parámetros requeridos (PID o Visita)','Fehler: Erforderliche Parameter fehlen (PID oder Behandlung)','Erro: Parâmetros obrigatórios em falta (PID ou Consulta)','Erro: Parâmetros obrigatórios ausentes (PID ou Consulta)' UNION ALL
      SELECT 'Error: Record not found or insufficient permissions.','Error: Registro no encontrado o permisos insuficientes.','Fehler: Datensatz nicht gefunden oder unzureichende Berechtigungen.','Erro: Registo não encontrado ou permissões insuficientes.','Erro: Registro não encontrado ou permissões insuficientes.'
) t ON c.constant_name = t.cn
JOIN lang_languages ll ON ll.lang_id IN (3, 4, 5, 19, 20, 21)
ON DUPLICATE KEY UPDATE definition = VALUES(definition);

-- Fix partial translations for standard OpenEMR strings used in nursing views
SET @cid = (SELECT cons_id FROM lang_constants WHERE constant_name = 'Confirm' LIMIT 1);
INSERT IGNORE INTO lang_definitions (cons_id, lang_id, definition) VALUES (@cid, 3, 'Confirmar');
INSERT IGNORE INTO lang_definitions (cons_id, lang_id, definition) VALUES (@cid, 5, 'Bestätigen');

SET @cid = (SELECT cons_id FROM lang_constants WHERE constant_name = 'This encounter is locked. No new forms can be added.' LIMIT 1);
INSERT IGNORE INTO lang_definitions (cons_id, lang_id, definition) VALUES (@cid, 3, 'Esta visita está bloqueada. No se pueden agregar nuevos formularios.');
INSERT IGNORE INTO lang_definitions (cons_id, lang_id, definition) VALUES (@cid, 4, 'Esta visita está bloqueada. No se pueden agregar nuevos formularios.');
INSERT IGNORE INTO lang_definitions (cons_id, lang_id, definition) VALUES (@cid, 5, 'Diese Behandlung ist gesperrt. Es können keine neuen Formulare hinzugefügt werden.');

SET @cid = (SELECT cons_id FROM lang_constants WHERE constant_name = 'You must first select or add a patient or therapy group.' LIMIT 1);
INSERT IGNORE INTO lang_definitions (cons_id, lang_id, definition) VALUES (@cid, 3, 'Primero debe seleccionar o agregar un paciente o grupo de terapia.');
INSERT IGNORE INTO lang_definitions (cons_id, lang_id, definition) VALUES (@cid, 5, 'Sie müssen zuerst einen Patienten oder eine Therapiegruppe auswählen oder hinzufügen.');
#EndIf

-- Nursing Module: view/print translations + PT lang_ids 19/20/21 for all forms
-- (table.sql files are bypassed when sql_run=1 is set in the registry)
-- Note: standard OpenEMR strings (Date, Status, Mode, etc.) are NOT inserted
-- into lang_constants because they already exist; the JOIN below still finds them.
#IfNotRow lang_constants constant_name Injury level
INSERT IGNORE INTO lang_constants (constant_name) VALUES
  -- New strings not in any previous SQL
  ('Injury level'),('Reference scale'),('Nursing Wound Care'),
  -- Curaciones view/print strings (nursing-specific)
  ('Wound Care Detail'),('Wound Care List'),('NURSING WOUND CARE RECORD'),
  ('No wound care records found'),('Active procedures'),
  ('Responsible Signature'),('Clarification'),('Error: Missing required parameters.'),
  -- Aplicaciones view/print strings
  ('Application Detail'),('Application List'),('NURSING APPLICATIONS RECORD'),
  ('No application records found'),('Application Record'),('Active items'),
  -- Cuidados view/print strings
  ('Care Bundle Detail'),('Care Bundle List'),('NURSING CARE BUNDLE RECORD'),
  ('No care bundle records found'),('Care Bundle Record'),('Not specified'),
  -- Evaluaciones view/print strings
  ('Evaluation Detail'),('Nursing Evaluations'),('Nursing Evaluations List'),
  ('NURSING EVALUATIONS RECORD'),('No evaluations recorded'),
  ('No observations recorded'),('Basic Assessments'),
  ('Patient Information'),('Not available'),('years'),
  ('Glasgow Coma Scale — Total Score'),('Could not retrieve PID or Encounter.'),
  ('Component'),('Score'),('Glasgow'),
  -- Registro VM view/print strings
  ('Ventilation Record Detail'),('Ventilation Record List'),
  ('MECHANICAL VENTILATION RECORD'),('Mechanical Ventilation Record'),
  ('No ventilation records found'),('Ventilation Parameters'),
  ('Ventilation Record'),
  -- Shared print strings that may not exist in older installs
  ('Parameter');

INSERT INTO lang_definitions (cons_id, lang_id, definition)
SELECT c.cons_id, ll.lang_id,
  CASE ll.lang_id
    WHEN 3  THEN t.es WHEN 4  THEN t.es
    WHEN 5  THEN t.de
    WHEN 19 THEN t.pt_eu WHEN 21 THEN t.pt_eu
    WHEN 20 THEN t.pt_br
  END
FROM lang_constants c
JOIN (
  -- Curaciones
  SELECT 'Nursing Wound Care'          cn,'Registro de Curaciones de Enfermería'   es,'Pflegewundversorgung'                                de,'Cuidados de Feridas de Enfermagem'            pt_eu,'Cuidados de Feridas de Enfermagem'            pt_br UNION ALL
  -- Curaciones view/print
  SELECT 'Wound Care Detail'           cn,'Detalle de Curaciones'                  es,'Wundpflegedetail'                                  de,'Detalhe de Curativos'                         pt_eu,'Detalhe de Curativos'                         pt_br UNION ALL
  SELECT 'Wound Care List',               'Lista de Curaciones',                       'Wundpflegeliste',                                       'Lista de Curativos',                                'Lista de Curativos' UNION ALL
  SELECT 'NURSING WOUND CARE RECORD',     'REGISTRO DE CURACIONES DE ENFERMERÍA',      'PFLEGEWUNDVERSORGUNGSPROTOKOLL',                         'REGISTO DE CURATIVOS DE ENFERMAGEM',                'REGISTRO DE CURATIVOS DE ENFERMAGEM' UNION ALL
  SELECT 'No wound care records found',   'No hay curaciones registradas',             'Keine Wundpflegeaufzeichnungen gefunden',                'Nenhum registo de curativos encontrado',             'Nenhum registro de curativos encontrado' UNION ALL
  SELECT 'Active procedures',             'Procedimientos activos',                    'Aktive Prozeduren',                                     'Procedimentos ativos',                              'Procedimentos ativos' UNION ALL
  SELECT 'Responsible Signature',         'Firma del Responsable',                     'Unterschrift des Verantwortlichen',                     'Assinatura do Responsável',                         'Assinatura do Responsável' UNION ALL
  SELECT 'Clarification',                 'Aclaración',                                'Klärung',                                               'Esclarecimento',                                    'Esclarecimento' UNION ALL
  SELECT 'Error: Missing required parameters.','Error: Faltan parámetros requeridos.', 'Fehler: Erforderliche Parameter fehlen.',                'Erro: Parâmetros obrigatórios em falta.',            'Erro: Parâmetros obrigatórios ausentes.' UNION ALL
  -- Aplicaciones view/print
  SELECT 'Application Detail',            'Detalle de Aplicación',                     'Anwendungsdetail',                                      'Detalhe de Aplicação',                              'Detalhe de Aplicação' UNION ALL
  SELECT 'Application List',              'Lista de Aplicaciones',                      'Anwendungsliste',                                       'Lista de Aplicações',                               'Lista de Aplicações' UNION ALL
  SELECT 'NURSING APPLICATIONS RECORD',   'REGISTRO DE APLICACIONES DE ENFERMERÍA',    'PFLEGEANWENDUNGSPROTOKOLL',                              'REGISTO DE APLICAÇÕES DE ENFERMAGEM',               'REGISTRO DE APLICAÇÕES DE ENFERMAGEM' UNION ALL
  SELECT 'No application records found',  'No se encontraron registros de aplicaciones','Keine Anwendungsaufzeichnungen gefunden',               'Nenhum registo de aplicações encontrado',           'Nenhum registro de aplicações encontrado' UNION ALL
  -- Cuidados view/print
  SELECT 'Care Bundle Detail',            'Detalle de Paquete de Cuidados',            'Pflegepaket-Detail',                                    'Detalhe de Pacote de Cuidados',                     'Detalhe de Pacote de Cuidados' UNION ALL
  SELECT 'Care Bundle List',              'Lista de Paquetes de Cuidados',             'Pflegepaketliste',                                      'Lista de Pacotes de Cuidados',                      'Lista de Pacotes de Cuidados' UNION ALL
  SELECT 'NURSING CARE BUNDLE RECORD',    'REGISTRO DE PAQUETE DE CUIDADOS',           'PFLEGEPAKET-PROTOKOLL',                                 'REGISTO DE PACOTE DE CUIDADOS',                     'REGISTRO DE PACOTE DE CUIDADOS' UNION ALL
  SELECT 'No care bundle records found',  'No se encontraron registros de cuidados',   'Keine Pflegepaket-Datensätze gefunden',                  'Nenhum registo de pacote de cuidados encontrado',   'Nenhum registro de pacote de cuidados encontrado' UNION ALL
  SELECT 'Not specified',                 'No especificado',                           'Nicht angegeben',                                       'Não especificado',                                  'Não especificado' UNION ALL
  -- Evaluaciones view/print
  SELECT 'Evaluation Detail',             'Detalle de Evaluación',                     'Bewertungsdetail',                                      'Detalhe da Avaliação',                              'Detalhe da Avaliação' UNION ALL
  SELECT 'Nursing Evaluations',           'Evaluaciones de Enfermería',                'Pflegebewertungen',                                     'Avaliações de Enfermagem',                          'Avaliações de Enfermagem' UNION ALL
  SELECT 'Nursing Evaluations List',      'Lista de Evaluaciones de Enfermería',       'Liste der Pflegebewertungen',                           'Lista de Avaliações de Enfermagem',                 'Lista de Avaliações de Enfermagem' UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',    'REGISTRO DE EVALUACIONES DE ENFERMERÍA',    'PFLEGEBEURTEILUNGSPROTOKOLL',                           'REGISTO DE AVALIAÇÕES DE ENFERMAGEM',               'REGISTRO DE AVALIAÇÕES DE ENFERMAGEM' UNION ALL
  SELECT 'No evaluations recorded',       'No se registraron evaluaciones',            'Keine Bewertungen erfasst',                             'Nenhuma avaliação registada',                       'Nenhuma avaliação registrada' UNION ALL
  SELECT 'No observations recorded',      'Sin observaciones registradas',             'Keine Beobachtungen aufgezeichnet',                     'Sem observações registadas',                        'Sem observações registradas' UNION ALL
  SELECT 'Basic Assessments',             'Valoraciones Básicas',                      'Grundbewertungen',                                      'Avaliações Básicas',                                'Avaliações Básicas' UNION ALL
  SELECT 'Patient Information',           'Información del Paciente',                  'Patienteninformationen',                                'Informações do Paciente',                           'Informações do Paciente' UNION ALL
  SELECT 'Not available',                 'No disponible',                             'Nicht verfügbar',                                       'Não disponível',                                    'Não disponível' UNION ALL
  SELECT 'years',                         'años',                                      'Jahre',                                                 'anos',                                              'anos' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score','Escala de Glasgow — Puntaje Total',       'Glasgow-Koma-Skala — Gesamtpunktzahl',                  'Escala de Coma de Glasgow — Pontuação Total',        'Escala de Coma de Glasgow — Pontuação Total' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.','No se pudo obtener el PID o Encuentro.','PID oder Begegnung konnte nicht abgerufen werden.',   'Não foi possível obter o PID ou Consulta.',         'Não foi possível obter o PID ou Consulta.' UNION ALL
  SELECT 'Component',                     'Componente',                                'Komponente',                                            'Componente',                                        'Componente' UNION ALL
  SELECT 'Score',                         'Puntaje',                                   'Punktzahl',                                             'Pontuação',                                         'Pontuação' UNION ALL
  SELECT 'Observations',                  'Observaciones',                             'Beobachtungen',                                         'Observações',                                       'Observações' UNION ALL
  -- Injury level / Reference scale (print.php evaluaciones)
  SELECT 'Injury level',                  'Nivel de Lesión',                           'Verletzungsgrad',                                       'Nível de Lesão',                                    'Nível de Lesão' UNION ALL
  SELECT 'Reference scale',               'Escala de Referencia',                      'Referenzskala',                                         'Escala de Referência',                              'Escala de Referência' UNION ALL
  -- Registro VM view/print
  SELECT 'Ventilation Record Detail',     'Detalle de Registro de VM',                 'Beatmungsprotokolldetail',                               'Detalhe do Registo de Ventilação',                  'Detalhe do Registro de Ventilação' UNION ALL
  SELECT 'Ventilation Record List',       'Lista de Registros de VM',                  'Beatmungsprotokollliste',                                'Lista de Registos de Ventilação',                   'Lista de Registros de Ventilação' UNION ALL
  SELECT 'MECHANICAL VENTILATION RECORD', 'REGISTRO DE VENTILACIÓN MECÁNICA',          'BEATMUNGSPROTOKOLL',                                    'REGISTO DE VENTILAÇÃO MECÂNICA',                    'REGISTRO DE VENTILAÇÃO MECÂNICA' UNION ALL
  SELECT 'Mechanical Ventilation Record', 'Registro de Ventilación Mecánica',          'Beatmungsprotokoll',                                    'Registo de Ventilação Mecânica',                    'Registro de Ventilação Mecânica' UNION ALL
  SELECT 'No ventilation records found',  'No se encontraron registros de ventilación','Keine Beatmungsaufzeichnungen gefunden',                 'Nenhum registo de ventilação encontrado',           'Nenhum registro de ventilação encontrado' UNION ALL
  SELECT 'Ventilation Parameters',        'Parámetros de Ventilación',                 'Beatmungsparameter',                                    'Parâmetros de Ventilação',                          'Parâmetros de Ventilação' UNION ALL
  -- Common print strings (standard OpenEMR — upsert is safe)
  SELECT 'Parameter',                     'Parámetro',                                 'Parameter',                                             'Parâmetro',                                         'Parâmetro' UNION ALL
  SELECT 'Mode',                          'Modo',                                      'Modus',                                                 'Modo',                                              'Modo' UNION ALL
  SELECT 'Response',                      'Respuesta',                                 'Antwort',                                               'Resposta',                                          'Resposta' UNION ALL
  SELECT 'Position',                      'Posición',                                  'Position',                                              'Posição',                                           'Posição' UNION ALL
  SELECT 'Item',                          'Ítem',                                      'Element',                                               'Item',                                              'Item' UNION ALL
  SELECT 'Procedure',                     'Procedimiento',                             'Prozedur',                                              'Procedimento',                                      'Procedimento' UNION ALL
  SELECT 'Status',                        'Estado',                                    'Status',                                                'Estado',                                            'Estado' UNION ALL
  SELECT 'Date',                          'Fecha',                                     'Datum',                                                 'Data',                                              'Data' UNION ALL
  SELECT 'Glasgow',                       'Glasgow',                                   'Glasgow',                                               'Glasgow',                                           'Glasgow'
) t ON c.constant_name = t.cn
JOIN lang_languages ll ON ll.lang_id IN (3, 4, 5, 19, 20, 21)
ON DUPLICATE KEY UPDATE definition = VALUES(definition);
#EndIf

-- Register nursing forms in OpenEMR form registry
#IfNotRow registry directory curaciones
INSERT INTO registry (name, state, directory, sql_run, unpackaged, date, priority, category, patient_encounter, aco_spec)
VALUES ('Nursing Wound Care', 1, 'curaciones', 1, 1, NOW(), 0, 'Nursing', 1, 'encounters|notes');
#EndIf
#IfNotRow registry directory aplicaciones
INSERT INTO registry (name, state, directory, sql_run, unpackaged, date, priority, category, patient_encounter, aco_spec)
VALUES ('Nursing Applications', 1, 'aplicaciones', 1, 1, NOW(), 0, 'Nursing', 1, 'encounters|notes');
#EndIf
#IfNotRow registry directory cuidados
INSERT INTO registry (name, state, directory, sql_run, unpackaged, date, priority, category, patient_encounter, aco_spec)
VALUES ('Nursing Care Bundle', 1, 'cuidados', 1, 1, NOW(), 0, 'Nursing', 1, 'encounters|notes');
#EndIf
#IfNotRow registry directory evaluaciones
INSERT INTO registry (name, state, directory, sql_run, unpackaged, date, priority, category, patient_encounter, aco_spec)
VALUES ('Nursing Evaluations', 1, 'evaluaciones', 1, 1, NOW(), 0, 'Nursing', 1, 'encounters|notes');
#EndIf
#IfNotRow registry directory registro_vm
INSERT INTO registry (name, state, directory, sql_run, unpackaged, date, priority, category, patient_encounter, aco_spec)
VALUES ('Mechanical Ventilation Record', 1, 'registro_vm', 1, 1, NOW(), 0, 'Nursing', 1, 'encounters|notes');
#EndIf
