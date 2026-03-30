SET NAMES utf8mb4;

-- ============================================================
-- Nursing Evaluations Form - table.sql
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

CREATE TABLE IF NOT EXISTS `form_evaluaciones` (
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

-- ============================================================
-- Constantes (strings en inglés — base del sistema de traducción)
-- ============================================================

INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES
  ('Nursing Evaluations'),
  ('New Nursing Evaluation'),
  ('Edit Nursing Evaluation'),
  ('Edit Mode'),
  ('Create Mode'),
  ('Evaluation Detail'),
  ('Nursing Evaluations List'),
  ('Consciousness'),
  ('Muscle Tone'),
  ('Pupils'),
  ('Mucous Membranes'),
  ('Glasgow Coma Scale'),
  ('Glasgow Coma Scale - Detailed'),
  ('Eye Opening'),
  ('Motor Response'),
  ('Verbal Response'),
  ('Spontaneously'),
  ('To auditory stimuli'),
  ('To pain'),
  ('No response'),
  ('Obeys commands'),
  ('Localizes pain'),
  ('Withdrawal'),
  ('Abnormal flexion'),
  ('Abnormal extension'),
  ('Oriented and conversing'),
  ('Disoriented and conversing'),
  ('Inappropriate words'),
  ('Incomprehensible sounds'),
  ('Glasgow Total Score'),
  ('Mild injury'),
  ('Moderate injury'),
  ('Severe injury'),
  ('Mild'),
  ('Moderate'),
  ('Severe'),
  ('Injury'),
  ('Evaluation Time'),
  ('Save Changes'),
  ('Basic Assessments'),
  ('Observations'),
  ('No observations recorded'),
  ('Patient Information'),
  ('Not available'),
  ('years'),
  ('No evaluations recorded'),
  ('Record not found'),
  ('No data found'),
  ('Item'),
  ('Response'),
  ('Record Information'),
  ('Recorded'),
  ('Method not allowed'),
  ('Redirecting...'),
  ('Could not retrieve PID or Encounter.'),
  ('Observations about consciousness...'),
  ('Observations about muscle tone...'),
  ('Observations about pupils...'),
  ('Observations about mucous membranes...'),
  ('Observations about eye opening...'),
  ('Observations about motor response...'),
  ('Observations about verbal response...'),
  ('Error: Missing required parameters (PID or Encounter)'),
  ('Error: Missing required data (PID or Encounter)'),
  ('Error: Record not found or insufficient permissions.'),
  ('Error: Could not update the record. Please try again.'),
  ('Error: Could not save the record. Please try again.'),
  ('VIGIL'),
  ('SOMNOLIENTO'),
  ('ESTUPOROSO'),
  ('COMATOSO'),
  ('FLACIDO'),
  ('ESPASTICO'),
  ('MIDRIASIS'),
  ('MIOSIS'),
  ('SECA'),
  ('HUMEDA'),
  ('PALIDA'),
  ('ICTERICA'),
  ('CIANOSIS'),
  ('NURSING EVALUATIONS RECORD'),
  ('Component'),
  ('Score'),
  ('Glasgow Coma Scale — Total Score'),
  ('Responsible Signature'),
  ('Clarification'),
  ('Error: Missing required parameters.');

-- ============================================================
-- Traducciones por idioma
-- ============================================================

INSERT IGNORE INTO `lang_definitions` (`cons_id`, `lang_id`, `definition`)
SELECT lc.cons_id, t.lang_id, t.definition
FROM `lang_constants` lc
JOIN (

  -- ==================== SPANISH (SPAIN) lang_id=3 ====================
  SELECT 'Nursing Evaluations'            AS cn, 3 AS lang_id, 'Evaluaciones de Enfermería'            AS definition UNION ALL
  SELECT 'New Nursing Evaluation',         3, 'Nueva Evaluación de Enfermería' UNION ALL
  SELECT 'Edit Nursing Evaluation',        3, 'Editar Evaluación de Enfermería' UNION ALL
  SELECT 'Edit Mode',                      3, 'Modo Edición' UNION ALL
  SELECT 'Create Mode',                    3, 'Modo Creación' UNION ALL
  SELECT 'Evaluation Detail',              3, 'Detalle de Evaluación' UNION ALL
  SELECT 'Nursing Evaluations List',       3, 'Lista de Evaluaciones de Enfermería' UNION ALL
  SELECT 'Consciousness',                  3, 'Conciencia' UNION ALL
  SELECT 'Muscle Tone',                    3, 'Tono Muscular' UNION ALL
  SELECT 'Pupils',                         3, 'Pupilas' UNION ALL
  SELECT 'Mucous Membranes',               3, 'Mucosas' UNION ALL
  SELECT 'Glasgow Coma Scale',             3, 'Escala de Glasgow' UNION ALL
  SELECT 'Glasgow Coma Scale - Detailed',  3, 'Escala de Glasgow - Detalle' UNION ALL
  SELECT 'Eye Opening',                    3, 'Apertura Ocular' UNION ALL
  SELECT 'Motor Response',                 3, 'Respuesta Motora' UNION ALL
  SELECT 'Verbal Response',                3, 'Respuesta Verbal' UNION ALL
  SELECT 'Spontaneously',                  3, 'Espontáneamente' UNION ALL
  SELECT 'To auditory stimuli',            3, 'A estímulos auditivos' UNION ALL
  SELECT 'To pain',                        3, 'Al dolor' UNION ALL
  SELECT 'No response',                    3, 'Sin respuesta' UNION ALL
  SELECT 'Obeys commands',                 3, 'Obedece órdenes' UNION ALL
  SELECT 'Localizes pain',                 3, 'Localiza el dolor' UNION ALL
  SELECT 'Withdrawal',                     3, 'Flexión de defensa' UNION ALL
  SELECT 'Abnormal flexion',               3, 'Flexión anormal' UNION ALL
  SELECT 'Abnormal extension',             3, 'Extensión anormal' UNION ALL
  SELECT 'Oriented and conversing',        3, 'Orientado y conversa' UNION ALL
  SELECT 'Disoriented and conversing',     3, 'Desorientado y conversa' UNION ALL
  SELECT 'Inappropriate words',            3, 'Lenguaje inadecuado' UNION ALL
  SELECT 'Incomprehensible sounds',        3, 'Sonidos incomprensibles' UNION ALL
  SELECT 'Glasgow Total Score',            3, 'Puntaje Total Glasgow' UNION ALL
  SELECT 'Mild injury',                    3, 'Lesión leve' UNION ALL
  SELECT 'Moderate injury',                3, 'Lesión moderada' UNION ALL
  SELECT 'Severe injury',                  3, 'Lesión grave' UNION ALL
  SELECT 'Mild',                           3, 'Leve' UNION ALL
  SELECT 'Moderate',                       3, 'Moderado' UNION ALL
  SELECT 'Severe',                         3, 'Grave' UNION ALL
  SELECT 'Injury',                         3, 'Lesión' UNION ALL
  SELECT 'Evaluation Time',                3, 'Hora de Evaluación' UNION ALL
  SELECT 'Save Changes',                   3, 'Guardar Cambios' UNION ALL
  SELECT 'Basic Assessments',              3, 'Valoraciones Básicas' UNION ALL
  SELECT 'Observations',                   3, 'Observaciones' UNION ALL
  SELECT 'No observations recorded',       3, 'Sin observaciones registradas' UNION ALL
  SELECT 'Patient Information',            3, 'Información del Paciente' UNION ALL
  SELECT 'Not available',                  3, 'No disponible' UNION ALL
  SELECT 'years',                          3, 'años' UNION ALL
  SELECT 'No evaluations recorded',        3, 'Sin evaluaciones registradas' UNION ALL
  SELECT 'Record not found',               3, 'Registro no encontrado' UNION ALL
  SELECT 'No data found',                  3, 'No se encontraron datos' UNION ALL
  SELECT 'Item',                           3, 'Ítem' UNION ALL
  SELECT 'Response',                       3, 'Respuesta' UNION ALL
  SELECT 'Record Information',             3, 'Información del Registro' UNION ALL
  SELECT 'Recorded',                       3, 'Registrado' UNION ALL
  SELECT 'Method not allowed',             3, 'Método no permitido' UNION ALL
  SELECT 'Redirecting...',                 3, 'Redirigiendo...' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.',                  3, 'No se pudo obtener el PID o Encuentro.' UNION ALL
  SELECT 'Observations about consciousness...',                   3, 'Observaciones sobre conciencia...' UNION ALL
  SELECT 'Observations about muscle tone...',                     3, 'Observaciones sobre tono muscular...' UNION ALL
  SELECT 'Observations about pupils...',                          3, 'Observaciones sobre pupilas...' UNION ALL
  SELECT 'Observations about mucous membranes...',                3, 'Observaciones sobre mucosas...' UNION ALL
  SELECT 'Observations about eye opening...',                     3, 'Observaciones sobre apertura ocular...' UNION ALL
  SELECT 'Observations about motor response...',                  3, 'Observaciones sobre respuesta motora...' UNION ALL
  SELECT 'Observations about verbal response...',                 3, 'Observaciones sobre respuesta verbal...' UNION ALL
  SELECT 'Error: Missing required parameters (PID or Encounter)', 3, 'Error: Faltan parámetros requeridos (PID o Encuentro)' UNION ALL
  SELECT 'Error: Missing required data (PID or Encounter)',       3, 'Error: Faltan datos requeridos (PID o Encuentro)' UNION ALL
  SELECT 'Error: Record not found or insufficient permissions.',  3, 'Error: Registro no encontrado o permisos insuficientes.' UNION ALL
  SELECT 'Error: Could not update the record. Please try again.', 3, 'Error: No se pudo actualizar el registro. Intente nuevamente.' UNION ALL
  SELECT 'Error: Could not save the record. Please try again.',   3, 'Error: No se pudo guardar el registro. Intente nuevamente.' UNION ALL
  SELECT 'VIGIL',       3, 'VIGIL'       UNION ALL
  SELECT 'SOMNOLIENTO', 3, 'SOMNOLIENTO' UNION ALL
  SELECT 'ESTUPOROSO',  3, 'ESTUPOROSO'  UNION ALL
  SELECT 'COMATOSO',    3, 'COMATOSO'    UNION ALL
  SELECT 'FLACIDO',     3, 'FLÁCIDO'     UNION ALL
  SELECT 'ESPASTICO',   3, 'ESPÁSTICO'   UNION ALL
  SELECT 'MIDRIASIS',   3, 'MIDRIASIS'   UNION ALL
  SELECT 'MIOSIS',      3, 'MIOSIS'      UNION ALL
  SELECT 'SECA',        3, 'SECA'        UNION ALL
  SELECT 'HUMEDA',      3, 'HÚMEDA'      UNION ALL
  SELECT 'PALIDA',      3, 'PÁLIDA'      UNION ALL
  SELECT 'ICTERICA',    3, 'ICTÉRICA'    UNION ALL
  SELECT 'CIANOSIS',    3, 'CIANOSIS'    UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',        3, 'REGISTRO DE EVALUACIONES DE ENFERMERÍA' UNION ALL
  SELECT 'Component',                         3, 'Componente' UNION ALL
  SELECT 'Score',                             3, 'Puntaje' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score',  3, 'Escala de Glasgow — Puntaje Total' UNION ALL
  SELECT 'Responsible Signature',             3, 'Firma del Responsable' UNION ALL
  SELECT 'Clarification',                     3, 'Aclaración' UNION ALL
  SELECT 'Error: Missing required parameters.', 3, 'Error: Faltan parámetros requeridos.' UNION ALL

  -- ==================== SPANISH (LATIN AMERICAN) lang_id=4 ====================
  SELECT 'Nursing Evaluations',            4, 'Evaluaciones de Enfermería' UNION ALL
  SELECT 'New Nursing Evaluation',         4, 'Nueva Evaluación de Enfermería' UNION ALL
  SELECT 'Edit Nursing Evaluation',        4, 'Editar Evaluación de Enfermería' UNION ALL
  SELECT 'Edit Mode',                      4, 'Modo Edición' UNION ALL
  SELECT 'Create Mode',                    4, 'Modo Creación' UNION ALL
  SELECT 'Evaluation Detail',              4, 'Detalle de Evaluación' UNION ALL
  SELECT 'Nursing Evaluations List',       4, 'Lista de Evaluaciones de Enfermería' UNION ALL
  SELECT 'Consciousness',                  4, 'Conciencia' UNION ALL
  SELECT 'Muscle Tone',                    4, 'Tono Muscular' UNION ALL
  SELECT 'Pupils',                         4, 'Pupilas' UNION ALL
  SELECT 'Mucous Membranes',               4, 'Mucosas' UNION ALL
  SELECT 'Glasgow Coma Scale',             4, 'Escala de Glasgow' UNION ALL
  SELECT 'Glasgow Coma Scale - Detailed',  4, 'Escala de Glasgow - Detalle' UNION ALL
  SELECT 'Eye Opening',                    4, 'Apertura Ocular' UNION ALL
  SELECT 'Motor Response',                 4, 'Respuesta Motora' UNION ALL
  SELECT 'Verbal Response',                4, 'Respuesta Verbal' UNION ALL
  SELECT 'Spontaneously',                  4, 'Espontáneamente' UNION ALL
  SELECT 'To auditory stimuli',            4, 'A estímulos auditivos' UNION ALL
  SELECT 'To pain',                        4, 'Al dolor' UNION ALL
  SELECT 'No response',                    4, 'Sin respuesta' UNION ALL
  SELECT 'Obeys commands',                 4, 'Obedece órdenes' UNION ALL
  SELECT 'Localizes pain',                 4, 'Localiza el dolor' UNION ALL
  SELECT 'Withdrawal',                     4, 'Flexión de defensa' UNION ALL
  SELECT 'Abnormal flexion',               4, 'Flexión anormal' UNION ALL
  SELECT 'Abnormal extension',             4, 'Extensión anormal' UNION ALL
  SELECT 'Oriented and conversing',        4, 'Orientado y conversa' UNION ALL
  SELECT 'Disoriented and conversing',     4, 'Desorientado y conversa' UNION ALL
  SELECT 'Inappropriate words',            4, 'Lenguaje inadecuado' UNION ALL
  SELECT 'Incomprehensible sounds',        4, 'Sonidos incomprensibles' UNION ALL
  SELECT 'Glasgow Total Score',            4, 'Puntaje Total Glasgow' UNION ALL
  SELECT 'Mild injury',                    4, 'Lesión leve' UNION ALL
  SELECT 'Moderate injury',                4, 'Lesión moderada' UNION ALL
  SELECT 'Severe injury',                  4, 'Lesión grave' UNION ALL
  SELECT 'Mild',                           4, 'Leve' UNION ALL
  SELECT 'Moderate',                       4, 'Moderado' UNION ALL
  SELECT 'Severe',                         4, 'Grave' UNION ALL
  SELECT 'Injury',                         4, 'Lesión' UNION ALL
  SELECT 'Evaluation Time',                4, 'Hora de Evaluación' UNION ALL
  SELECT 'Save Changes',                   4, 'Guardar Cambios' UNION ALL
  SELECT 'Basic Assessments',              4, 'Valoraciones Básicas' UNION ALL
  SELECT 'Observations',                   4, 'Observaciones' UNION ALL
  SELECT 'No observations recorded',       4, 'Sin observaciones registradas' UNION ALL
  SELECT 'Patient Information',            4, 'Información del Paciente' UNION ALL
  SELECT 'Not available',                  4, 'No disponible' UNION ALL
  SELECT 'years',                          4, 'años' UNION ALL
  SELECT 'No evaluations recorded',        4, 'Sin evaluaciones registradas' UNION ALL
  SELECT 'Record not found',               4, 'Registro no encontrado' UNION ALL
  SELECT 'No data found',                  4, 'No se encontraron datos' UNION ALL
  SELECT 'Item',                           4, 'Ítem' UNION ALL
  SELECT 'Response',                       4, 'Respuesta' UNION ALL
  SELECT 'Record Information',             4, 'Información del Registro' UNION ALL
  SELECT 'Recorded',                       4, 'Registrado' UNION ALL
  SELECT 'Method not allowed',             4, 'Método no permitido' UNION ALL
  SELECT 'Redirecting...',                 4, 'Redirigiendo...' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.',                  4, 'No se pudo obtener el PID o Encuentro.' UNION ALL
  SELECT 'Observations about consciousness...',                   4, 'Observaciones sobre conciencia...' UNION ALL
  SELECT 'Observations about muscle tone...',                     4, 'Observaciones sobre tono muscular...' UNION ALL
  SELECT 'Observations about pupils...',                          4, 'Observaciones sobre pupilas...' UNION ALL
  SELECT 'Observations about mucous membranes...',                4, 'Observaciones sobre mucosas...' UNION ALL
  SELECT 'Observations about eye opening...',                     4, 'Observaciones sobre apertura ocular...' UNION ALL
  SELECT 'Observations about motor response...',                  4, 'Observaciones sobre respuesta motora...' UNION ALL
  SELECT 'Observations about verbal response...',                 4, 'Observaciones sobre respuesta verbal...' UNION ALL
  SELECT 'Error: Missing required parameters (PID or Encounter)', 4, 'Error: Faltan parámetros requeridos (PID o Encuentro)' UNION ALL
  SELECT 'Error: Missing required data (PID or Encounter)',       4, 'Error: Faltan datos requeridos (PID o Encuentro)' UNION ALL
  SELECT 'Error: Record not found or insufficient permissions.',  4, 'Error: Registro no encontrado o permisos insuficientes.' UNION ALL
  SELECT 'Error: Could not update the record. Please try again.', 4, 'Error: No se pudo actualizar el registro. Intente nuevamente.' UNION ALL
  SELECT 'Error: Could not save the record. Please try again.',   4, 'Error: No se pudo guardar el registro. Intente nuevamente.' UNION ALL
  SELECT 'VIGIL',       4, 'VIGIL'       UNION ALL
  SELECT 'SOMNOLIENTO', 4, 'SOMNOLIENTO' UNION ALL
  SELECT 'ESTUPOROSO',  4, 'ESTUPOROSO'  UNION ALL
  SELECT 'COMATOSO',    4, 'COMATOSO'    UNION ALL
  SELECT 'FLACIDO',     4, 'FLÁCIDO'     UNION ALL
  SELECT 'ESPASTICO',   4, 'ESPÁSTICO'   UNION ALL
  SELECT 'MIDRIASIS',   4, 'MIDRIASIS'   UNION ALL
  SELECT 'MIOSIS',      4, 'MIOSIS'      UNION ALL
  SELECT 'SECA',        4, 'SECA'        UNION ALL
  SELECT 'HUMEDA',      4, 'HÚMEDA'      UNION ALL
  SELECT 'PALIDA',      4, 'PÁLIDA'      UNION ALL
  SELECT 'ICTERICA',    4, 'ICTÉRICA'    UNION ALL
  SELECT 'CIANOSIS',    4, 'CIANOSIS'    UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',        4, 'REGISTRO DE EVALUACIONES DE ENFERMERÍA' UNION ALL
  SELECT 'Component',                         4, 'Componente' UNION ALL
  SELECT 'Score',                             4, 'Puntaje' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score',  4, 'Escala de Glasgow — Puntaje Total' UNION ALL
  SELECT 'Responsible Signature',             4, 'Firma del Responsable' UNION ALL
  SELECT 'Clarification',                     4, 'Aclaración' UNION ALL
  SELECT 'Error: Missing required parameters.', 4, 'Error: Faltan parámetros requeridos.' UNION ALL

  -- ==================== GERMAN lang_id=5 ====================
  SELECT 'Nursing Evaluations',            5, 'Pflegebewertungen' UNION ALL
  SELECT 'New Nursing Evaluation',         5, 'Neue Pflegebewertung' UNION ALL
  SELECT 'Edit Nursing Evaluation',        5, 'Pflegebewertung bearbeiten' UNION ALL
  SELECT 'Edit Mode',                      5, 'Bearbeitungsmodus' UNION ALL
  SELECT 'Create Mode',                    5, 'Erstellungsmodus' UNION ALL
  SELECT 'Evaluation Detail',              5, 'Bewertungsdetail' UNION ALL
  SELECT 'Nursing Evaluations List',       5, 'Liste der Pflegebewertungen' UNION ALL
  SELECT 'Consciousness',                  5, 'Bewusstsein' UNION ALL
  SELECT 'Muscle Tone',                    5, 'Muskeltonus' UNION ALL
  SELECT 'Pupils',                         5, 'Pupillen' UNION ALL
  SELECT 'Mucous Membranes',               5, 'Schleimhäute' UNION ALL
  SELECT 'Glasgow Coma Scale',             5, 'Glasgow-Koma-Skala' UNION ALL
  SELECT 'Glasgow Coma Scale - Detailed',  5, 'Glasgow-Skala - Detailliert' UNION ALL
  SELECT 'Eye Opening',                    5, 'Augenöffnung' UNION ALL
  SELECT 'Motor Response',                 5, 'Motorische Reaktion' UNION ALL
  SELECT 'Verbal Response',                5, 'Verbale Reaktion' UNION ALL
  SELECT 'Spontaneously',                  5, 'Spontan' UNION ALL
  SELECT 'To auditory stimuli',            5, 'Auf akustische Reize' UNION ALL
  SELECT 'To pain',                        5, 'Auf Schmerz' UNION ALL
  SELECT 'No response',                    5, 'Keine Reaktion' UNION ALL
  SELECT 'Obeys commands',                 5, 'Befolgt Aufforderungen' UNION ALL
  SELECT 'Localizes pain',                 5, 'Lokalisiert Schmerz' UNION ALL
  SELECT 'Withdrawal',                     5, 'Rückzug' UNION ALL
  SELECT 'Abnormal flexion',               5, 'Abnorme Beugung' UNION ALL
  SELECT 'Abnormal extension',             5, 'Abnorme Streckung' UNION ALL
  SELECT 'Oriented and conversing',        5, 'Orientiert und sprechend' UNION ALL
  SELECT 'Disoriented and conversing',     5, 'Desorientiert und sprechend' UNION ALL
  SELECT 'Inappropriate words',            5, 'Unangemessene Wörter' UNION ALL
  SELECT 'Incomprehensible sounds',        5, 'Unverständliche Laute' UNION ALL
  SELECT 'Glasgow Total Score',            5, 'Glasgow-Gesamtpunktzahl' UNION ALL
  SELECT 'Mild injury',                    5, 'Leichte Verletzung' UNION ALL
  SELECT 'Moderate injury',                5, 'Mittelschwere Verletzung' UNION ALL
  SELECT 'Severe injury',                  5, 'Schwere Verletzung' UNION ALL
  SELECT 'Mild',                           5, 'Leicht' UNION ALL
  SELECT 'Moderate',                       5, 'Mittel' UNION ALL
  SELECT 'Severe',                         5, 'Schwer' UNION ALL
  SELECT 'Injury',                         5, 'Verletzung' UNION ALL
  SELECT 'Evaluation Time',                5, 'Bewertungszeit' UNION ALL
  SELECT 'Save Changes',                   5, 'Änderungen speichern' UNION ALL
  SELECT 'Basic Assessments',              5, 'Grundbewertungen' UNION ALL
  SELECT 'Observations',                   5, 'Beobachtungen' UNION ALL
  SELECT 'No observations recorded',       5, 'Keine Beobachtungen aufgezeichnet' UNION ALL
  SELECT 'Patient Information',            5, 'Patienteninformationen' UNION ALL
  SELECT 'Not available',                  5, 'Nicht verfügbar' UNION ALL
  SELECT 'years',                          5, 'Jahre' UNION ALL
  SELECT 'No evaluations recorded',        5, 'Keine Bewertungen aufgezeichnet' UNION ALL
  SELECT 'Record not found',               5, 'Datensatz nicht gefunden' UNION ALL
  SELECT 'No data found',                  5, 'Keine Daten gefunden' UNION ALL
  SELECT 'Item',                           5, 'Element' UNION ALL
  SELECT 'Response',                       5, 'Reaktion' UNION ALL
  SELECT 'Record Information',             5, 'Datensatzinformationen' UNION ALL
  SELECT 'Recorded',                       5, 'Aufgezeichnet' UNION ALL
  SELECT 'Method not allowed',             5, 'Methode nicht erlaubt' UNION ALL
  SELECT 'Redirecting...',                 5, 'Weiterleitung...' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.',                  5, 'PID oder Begegnung konnte nicht abgerufen werden.' UNION ALL
  SELECT 'Observations about consciousness...',                   5, 'Beobachtungen zum Bewusstsein...' UNION ALL
  SELECT 'Observations about muscle tone...',                     5, 'Beobachtungen zum Muskeltonus...' UNION ALL
  SELECT 'Observations about pupils...',                          5, 'Beobachtungen zu den Pupillen...' UNION ALL
  SELECT 'Observations about mucous membranes...',                5, 'Beobachtungen zu den Schleimhäuten...' UNION ALL
  SELECT 'Observations about eye opening...',                     5, 'Beobachtungen zur Augenöffnung...' UNION ALL
  SELECT 'Observations about motor response...',                  5, 'Beobachtungen zur motorischen Reaktion...' UNION ALL
  SELECT 'Observations about verbal response...',                 5, 'Beobachtungen zur verbalen Reaktion...' UNION ALL
  SELECT 'Error: Missing required parameters (PID or Encounter)', 5, 'Fehler: Erforderliche Parameter fehlen (PID oder Begegnung)' UNION ALL
  SELECT 'Error: Missing required data (PID or Encounter)',       5, 'Fehler: Erforderliche Daten fehlen (PID oder Begegnung)' UNION ALL
  SELECT 'Error: Record not found or insufficient permissions.',  5, 'Fehler: Datensatz nicht gefunden oder unzureichende Berechtigungen.' UNION ALL
  SELECT 'Error: Could not update the record. Please try again.', 5, 'Fehler: Datensatz konnte nicht aktualisiert werden. Bitte erneut versuchen.' UNION ALL
  SELECT 'Error: Could not save the record. Please try again.',   5, 'Fehler: Datensatz konnte nicht gespeichert werden. Bitte erneut versuchen.' UNION ALL
  SELECT 'VIGIL',       5, 'WACH'        UNION ALL
  SELECT 'SOMNOLIENTO', 5, 'SCHLÄFRIG'   UNION ALL
  SELECT 'ESTUPOROSO',  5, 'STUPORÖS'    UNION ALL
  SELECT 'COMATOSO',    5, 'KOMATÖS'     UNION ALL
  SELECT 'FLACIDO',     5, 'SCHLAFF'     UNION ALL
  SELECT 'ESPASTICO',   5, 'SPASTISCH'   UNION ALL
  SELECT 'MIDRIASIS',   5, 'MYDRIASIS'   UNION ALL
  SELECT 'MIOSIS',      5, 'MIOSIS'      UNION ALL
  SELECT 'SECA',        5, 'TROCKEN'     UNION ALL
  SELECT 'HUMEDA',      5, 'FEUCHT'      UNION ALL
  SELECT 'PALIDA',      5, 'BLASS'       UNION ALL
  SELECT 'ICTERICA',    5, 'IKTERISCH'   UNION ALL
  SELECT 'CIANOSIS',    5, 'ZYANOSE'     UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',        5, 'PFLEGEBEURTEILUNGSPROTOKOLL' UNION ALL
  SELECT 'Component',                         5, 'Komponente' UNION ALL
  SELECT 'Score',                             5, 'Punktzahl' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score',  5, 'Glasgow-Koma-Skala — Gesamtpunktzahl' UNION ALL
  SELECT 'Responsible Signature',             5, 'Unterschrift des Verantwortlichen' UNION ALL
  SELECT 'Clarification',                     5, 'Klarstellung' UNION ALL
  SELECT 'Error: Missing required parameters.', 5, 'Fehler: Erforderliche Parameter fehlen.' UNION ALL

  -- ==================== FRENCH (STANDARD) lang_id=8 ====================
  SELECT 'Nursing Evaluations',            8, 'Évaluations Infirmières' UNION ALL
  SELECT 'New Nursing Evaluation',         8, 'Nouvelle Évaluation Infirmière' UNION ALL
  SELECT 'Edit Nursing Evaluation',        8, 'Modifier Évaluation Infirmière' UNION ALL
  SELECT 'Edit Mode',                      8, 'Mode Édition' UNION ALL
  SELECT 'Create Mode',                    8, 'Mode Création' UNION ALL
  SELECT 'Evaluation Detail',              8, 'Détail de l''Évaluation' UNION ALL
  SELECT 'Nursing Evaluations List',       8, 'Liste des Évaluations Infirmières' UNION ALL
  SELECT 'Consciousness',                  8, 'Conscience' UNION ALL
  SELECT 'Muscle Tone',                    8, 'Tonus Musculaire' UNION ALL
  SELECT 'Pupils',                         8, 'Pupilles' UNION ALL
  SELECT 'Mucous Membranes',               8, 'Muqueuses' UNION ALL
  SELECT 'Glasgow Coma Scale',             8, 'Échelle de Coma de Glasgow' UNION ALL
  SELECT 'Glasgow Coma Scale - Detailed',  8, 'Échelle de Glasgow - Détaillée' UNION ALL
  SELECT 'Eye Opening',                    8, 'Ouverture des Yeux' UNION ALL
  SELECT 'Motor Response',                 8, 'Réponse Motrice' UNION ALL
  SELECT 'Verbal Response',                8, 'Réponse Verbale' UNION ALL
  SELECT 'Spontaneously',                  8, 'Spontanément' UNION ALL
  SELECT 'To auditory stimuli',            8, 'Aux stimuli auditifs' UNION ALL
  SELECT 'To pain',                        8, 'À la douleur' UNION ALL
  SELECT 'No response',                    8, 'Aucune réponse' UNION ALL
  SELECT 'Obeys commands',                 8, 'Obéit aux ordres' UNION ALL
  SELECT 'Localizes pain',                 8, 'Localise la douleur' UNION ALL
  SELECT 'Withdrawal',                     8, 'Retrait' UNION ALL
  SELECT 'Abnormal flexion',               8, 'Flexion anormale' UNION ALL
  SELECT 'Abnormal extension',             8, 'Extension anormale' UNION ALL
  SELECT 'Oriented and conversing',        8, 'Orienté et conversa' UNION ALL
  SELECT 'Disoriented and conversing',     8, 'Désorienté et conversa' UNION ALL
  SELECT 'Inappropriate words',            8, 'Mots inappropriés' UNION ALL
  SELECT 'Incomprehensible sounds',        8, 'Sons incompréhensibles' UNION ALL
  SELECT 'Glasgow Total Score',            8, 'Score Total de Glasgow' UNION ALL
  SELECT 'Mild injury',                    8, 'Blessure légère' UNION ALL
  SELECT 'Moderate injury',                8, 'Blessure modérée' UNION ALL
  SELECT 'Severe injury',                  8, 'Blessure grave' UNION ALL
  SELECT 'Mild',                           8, 'Léger' UNION ALL
  SELECT 'Moderate',                       8, 'Modéré' UNION ALL
  SELECT 'Severe',                         8, 'Grave' UNION ALL
  SELECT 'Injury',                         8, 'Blessure' UNION ALL
  SELECT 'Evaluation Time',                8, 'Heure d''Évaluation' UNION ALL
  SELECT 'Save Changes',                   8, 'Enregistrer les modifications' UNION ALL
  SELECT 'Basic Assessments',              8, 'Évaluations de Base' UNION ALL
  SELECT 'Observations',                   8, 'Observations' UNION ALL
  SELECT 'No observations recorded',       8, 'Aucune observation enregistrée' UNION ALL
  SELECT 'Patient Information',            8, 'Informations Patient' UNION ALL
  SELECT 'Not available',                  8, 'Non disponible' UNION ALL
  SELECT 'years',                          8, 'ans' UNION ALL
  SELECT 'No evaluations recorded',        8, 'Aucune évaluation enregistrée' UNION ALL
  SELECT 'Record not found',               8, 'Enregistrement non trouvé' UNION ALL
  SELECT 'No data found',                  8, 'Aucune donnée trouvée' UNION ALL
  SELECT 'Item',                           8, 'Élément' UNION ALL
  SELECT 'Response',                       8, 'Réponse' UNION ALL
  SELECT 'Record Information',             8, 'Informations Enregistrement' UNION ALL
  SELECT 'Recorded',                       8, 'Enregistré' UNION ALL
  SELECT 'Method not allowed',             8, 'Méthode non autorisée' UNION ALL
  SELECT 'Redirecting...',                 8, 'Redirection...' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.',                  8, 'Impossible de récupérer le PID ou la Consultation.' UNION ALL
  SELECT 'Observations about consciousness...',                   8, 'Observations sur la conscience...' UNION ALL
  SELECT 'Observations about muscle tone...',                     8, 'Observations sur le tonus musculaire...' UNION ALL
  SELECT 'Observations about pupils...',                          8, 'Observations sur les pupilles...' UNION ALL
  SELECT 'Observations about mucous membranes...',                8, 'Observations sur les muqueuses...' UNION ALL
  SELECT 'Observations about eye opening...',                     8, 'Observations sur l''ouverture des yeux...' UNION ALL
  SELECT 'Observations about motor response...',                  8, 'Observations sur la réponse motrice...' UNION ALL
  SELECT 'Observations about verbal response...',                 8, 'Observations sur la réponse verbale...' UNION ALL
  SELECT 'Error: Missing required parameters (PID or Encounter)', 8, 'Erreur: Paramètres requis manquants (PID ou Consultation)' UNION ALL
  SELECT 'Error: Missing required data (PID or Encounter)',       8, 'Erreur: Données requises manquantes (PID ou Consultation)' UNION ALL
  SELECT 'Error: Record not found or insufficient permissions.',  8, 'Erreur: Enregistrement non trouvé ou permissions insuffisantes.' UNION ALL
  SELECT 'Error: Could not update the record. Please try again.', 8, 'Erreur: Impossible de mettre à jour l''enregistrement. Veuillez réessayer.' UNION ALL
  SELECT 'Error: Could not save the record. Please try again.',   8, 'Erreur: Impossible de sauvegarder l''enregistrement. Veuillez réessayer.' UNION ALL
  SELECT 'VIGIL',       8, 'ÉVEILLÉ'     UNION ALL
  SELECT 'SOMNOLIENTO', 8, 'SOMNOLENT'   UNION ALL
  SELECT 'ESTUPOROSO',  8, 'STUPOREUX'   UNION ALL
  SELECT 'COMATOSO',    8, 'COMATEUX'    UNION ALL
  SELECT 'FLACIDO',     8, 'FLASQUE'     UNION ALL
  SELECT 'ESPASTICO',   8, 'SPASTIQUE'   UNION ALL
  SELECT 'MIDRIASIS',   8, 'MYDRIASE'    UNION ALL
  SELECT 'MIOSIS',      8, 'MYOSE'       UNION ALL
  SELECT 'SECA',        8, 'SÈCHE'       UNION ALL
  SELECT 'HUMEDA',      8, 'HUMIDE'      UNION ALL
  SELECT 'PALIDA',      8, 'PÂLE'        UNION ALL
  SELECT 'ICTERICA',    8, 'ICTÉRIQUE'   UNION ALL
  SELECT 'CIANOSIS',    8, 'CYANOSE'     UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',        8, 'DOSSIER D\'ÉVALUATIONS INFIRMIÈRES' UNION ALL
  SELECT 'Component',                         8, 'Composante' UNION ALL
  SELECT 'Score',                             8, 'Score' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score',  8, 'Échelle de Glasgow — Score Total' UNION ALL
  SELECT 'Responsible Signature',             8, 'Signature du Responsable' UNION ALL
  SELECT 'Clarification',                     8, 'Clarification' UNION ALL
  SELECT 'Error: Missing required parameters.', 8, 'Erreur: Paramètres requis manquants.' UNION ALL

  -- ==================== FRENCH (CANADIAN) lang_id=9 ====================
  SELECT 'Nursing Evaluations',            9, 'Évaluations Infirmières' UNION ALL
  SELECT 'New Nursing Evaluation',         9, 'Nouvelle Évaluation Infirmière' UNION ALL
  SELECT 'Edit Nursing Evaluation',        9, 'Modifier Évaluation Infirmière' UNION ALL
  SELECT 'Edit Mode',                      9, 'Mode Édition' UNION ALL
  SELECT 'Create Mode',                    9, 'Mode Création' UNION ALL
  SELECT 'Evaluation Detail',              9, 'Détail de l''Évaluation' UNION ALL
  SELECT 'Nursing Evaluations List',       9, 'Liste des Évaluations Infirmières' UNION ALL
  SELECT 'Consciousness',                  9, 'Conscience' UNION ALL
  SELECT 'Muscle Tone',                    9, 'Tonus Musculaire' UNION ALL
  SELECT 'Pupils',                         9, 'Pupilles' UNION ALL
  SELECT 'Mucous Membranes',               9, 'Muqueuses' UNION ALL
  SELECT 'Glasgow Coma Scale',             9, 'Échelle de Coma de Glasgow' UNION ALL
  SELECT 'Glasgow Coma Scale - Detailed',  9, 'Échelle de Glasgow - Détaillée' UNION ALL
  SELECT 'Eye Opening',                    9, 'Ouverture des Yeux' UNION ALL
  SELECT 'Motor Response',                 9, 'Réponse Motrice' UNION ALL
  SELECT 'Verbal Response',                9, 'Réponse Verbale' UNION ALL
  SELECT 'Spontaneously',                  9, 'Spontanément' UNION ALL
  SELECT 'To auditory stimuli',            9, 'Aux stimuli auditifs' UNION ALL
  SELECT 'To pain',                        9, 'À la douleur' UNION ALL
  SELECT 'No response',                    9, 'Aucune réponse' UNION ALL
  SELECT 'Obeys commands',                 9, 'Obéit aux ordres' UNION ALL
  SELECT 'Localizes pain',                 9, 'Localise la douleur' UNION ALL
  SELECT 'Withdrawal',                     9, 'Retrait' UNION ALL
  SELECT 'Abnormal flexion',               9, 'Flexion anormale' UNION ALL
  SELECT 'Abnormal extension',             9, 'Extension anormale' UNION ALL
  SELECT 'Oriented and conversing',        9, 'Orienté et conversa' UNION ALL
  SELECT 'Disoriented and conversing',     9, 'Désorienté et conversa' UNION ALL
  SELECT 'Inappropriate words',            9, 'Mots inappropriés' UNION ALL
  SELECT 'Incomprehensible sounds',        9, 'Sons incompréhensibles' UNION ALL
  SELECT 'Glasgow Total Score',            9, 'Score Total de Glasgow' UNION ALL
  SELECT 'Mild injury',                    9, 'Blessure légère' UNION ALL
  SELECT 'Moderate injury',                9, 'Blessure modérée' UNION ALL
  SELECT 'Severe injury',                  9, 'Blessure grave' UNION ALL
  SELECT 'Mild',                           9, 'Léger' UNION ALL
  SELECT 'Moderate',                       9, 'Modéré' UNION ALL
  SELECT 'Severe',                         9, 'Grave' UNION ALL
  SELECT 'Injury',                         9, 'Blessure' UNION ALL
  SELECT 'Evaluation Time',                9, 'Heure d''Évaluation' UNION ALL
  SELECT 'Save Changes',                   9, 'Sauvegarder les modifications' UNION ALL
  SELECT 'Basic Assessments',              9, 'Évaluations de Base' UNION ALL
  SELECT 'Observations',                   9, 'Observations' UNION ALL
  SELECT 'No observations recorded',       9, 'Aucune observation enregistrée' UNION ALL
  SELECT 'Patient Information',            9, 'Informations Patient' UNION ALL
  SELECT 'Not available',                  9, 'Non disponible' UNION ALL
  SELECT 'years',                          9, 'ans' UNION ALL
  SELECT 'No evaluations recorded',        9, 'Aucune évaluation enregistrée' UNION ALL
  SELECT 'Record not found',               9, 'Enregistrement non trouvé' UNION ALL
  SELECT 'No data found',                  9, 'Aucune donnée trouvée' UNION ALL
  SELECT 'Item',                           9, 'Élément' UNION ALL
  SELECT 'Response',                       9, 'Réponse' UNION ALL
  SELECT 'Record Information',             9, 'Informations Enregistrement' UNION ALL
  SELECT 'Recorded',                       9, 'Enregistré' UNION ALL
  SELECT 'Method not allowed',             9, 'Méthode non autorisée' UNION ALL
  SELECT 'Redirecting...',                 9, 'Redirection...' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.',                  9, 'Impossible de récupérer le PID ou la Consultation.' UNION ALL
  SELECT 'Observations about consciousness...',                   9, 'Observations sur la conscience...' UNION ALL
  SELECT 'Observations about muscle tone...',                     9, 'Observations sur le tonus musculaire...' UNION ALL
  SELECT 'Observations about pupils...',                          9, 'Observations sur les pupilles...' UNION ALL
  SELECT 'Observations about mucous membranes...',                9, 'Observations sur les muqueuses...' UNION ALL
  SELECT 'Observations about eye opening...',                     9, 'Observations sur l''ouverture des yeux...' UNION ALL
  SELECT 'Observations about motor response...',                  9, 'Observations sur la réponse motrice...' UNION ALL
  SELECT 'Observations about verbal response...',                 9, 'Observations sur la réponse verbale...' UNION ALL
  SELECT 'Error: Missing required parameters (PID or Encounter)', 9, 'Erreur: Paramètres requis manquants (PID ou Consultation)' UNION ALL
  SELECT 'Error: Missing required data (PID or Encounter)',       9, 'Erreur: Données requises manquantes (PID ou Consultation)' UNION ALL
  SELECT 'Error: Record not found or insufficient permissions.',  9, 'Erreur: Enregistrement non trouvé ou permissions insuffisantes.' UNION ALL
  SELECT 'Error: Could not update the record. Please try again.', 9, 'Erreur: Impossible de mettre à jour l''enregistrement. Veuillez réessayer.' UNION ALL
  SELECT 'Error: Could not save the record. Please try again.',   9, 'Erreur: Impossible de sauvegarder l''enregistrement. Veuillez réessayer.' UNION ALL
  SELECT 'VIGIL',       9, 'ÉVEILLÉ'     UNION ALL
  SELECT 'SOMNOLIENTO', 9, 'SOMNOLENT'   UNION ALL
  SELECT 'ESTUPOROSO',  9, 'STUPOREUX'   UNION ALL
  SELECT 'COMATOSO',    9, 'COMATEUX'    UNION ALL
  SELECT 'FLACIDO',     9, 'FLASQUE'     UNION ALL
  SELECT 'ESPASTICO',   9, 'SPASTIQUE'   UNION ALL
  SELECT 'MIDRIASIS',   9, 'MYDRIASE'    UNION ALL
  SELECT 'MIOSIS',      9, 'MYOSE'       UNION ALL
  SELECT 'SECA',        9, 'SÈCHE'       UNION ALL
  SELECT 'HUMEDA',      9, 'HUMIDE'      UNION ALL
  SELECT 'PALIDA',      9, 'PÂLE'        UNION ALL
  SELECT 'ICTERICA',    9, 'ICTÉRIQUE'   UNION ALL
  SELECT 'CIANOSIS',    9, 'CYANOSE'     UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',        9, 'DOSSIER D\'ÉVALUATIONS INFIRMIÈRES' UNION ALL
  SELECT 'Component',                         9, 'Composante' UNION ALL
  SELECT 'Score',                             9, 'Score' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score',  9, 'Échelle de Glasgow — Score Total' UNION ALL
  SELECT 'Responsible Signature',             9, 'Signature du Responsable' UNION ALL
  SELECT 'Clarification',                     9, 'Clarification' UNION ALL
  SELECT 'Error: Missing required parameters.', 9, 'Erreur: Paramètres requis manquants.' UNION ALL

  -- ==================== PORTUGUESE (EUROPEAN) lang_id=17 ====================
  SELECT 'Nursing Evaluations',            17, 'Avaliações de Enfermagem' UNION ALL
  SELECT 'New Nursing Evaluation',         17, 'Nova Avaliação de Enfermagem' UNION ALL
  SELECT 'Edit Nursing Evaluation',        17, 'Editar Avaliação de Enfermagem' UNION ALL
  SELECT 'Edit Mode',                      17, 'Modo de Edição' UNION ALL
  SELECT 'Create Mode',                    17, 'Modo de Criação' UNION ALL
  SELECT 'Evaluation Detail',              17, 'Detalhe da Avaliação' UNION ALL
  SELECT 'Nursing Evaluations List',       17, 'Lista de Avaliações de Enfermagem' UNION ALL
  SELECT 'Consciousness',                  17, 'Consciência' UNION ALL
  SELECT 'Muscle Tone',                    17, 'Tónus Muscular' UNION ALL
  SELECT 'Pupils',                         17, 'Pupilas' UNION ALL
  SELECT 'Mucous Membranes',               17, 'Mucosas' UNION ALL
  SELECT 'Glasgow Coma Scale',             17, 'Escala de Coma de Glasgow' UNION ALL
  SELECT 'Glasgow Coma Scale - Detailed',  17, 'Escala de Glasgow - Detalhada' UNION ALL
  SELECT 'Eye Opening',                    17, 'Abertura Ocular' UNION ALL
  SELECT 'Motor Response',                 17, 'Resposta Motora' UNION ALL
  SELECT 'Verbal Response',                17, 'Resposta Verbal' UNION ALL
  SELECT 'Spontaneously',                  17, 'Espontaneamente' UNION ALL
  SELECT 'To auditory stimuli',            17, 'A estímulos auditivos' UNION ALL
  SELECT 'To pain',                        17, 'À dor' UNION ALL
  SELECT 'No response',                    17, 'Sem resposta' UNION ALL
  SELECT 'Obeys commands',                 17, 'Obedece comandos' UNION ALL
  SELECT 'Localizes pain',                 17, 'Localiza a dor' UNION ALL
  SELECT 'Withdrawal',                     17, 'Retirada' UNION ALL
  SELECT 'Abnormal flexion',               17, 'Flexão anormal' UNION ALL
  SELECT 'Abnormal extension',             17, 'Extensão anormal' UNION ALL
  SELECT 'Oriented and conversing',        17, 'Orientado e conversando' UNION ALL
  SELECT 'Disoriented and conversing',     17, 'Desorientado e conversando' UNION ALL
  SELECT 'Inappropriate words',            17, 'Palavras inadequadas' UNION ALL
  SELECT 'Incomprehensible sounds',        17, 'Sons incompreensíveis' UNION ALL
  SELECT 'Glasgow Total Score',            17, 'Pontuação Total de Glasgow' UNION ALL
  SELECT 'Mild injury',                    17, 'Lesão leve' UNION ALL
  SELECT 'Moderate injury',                17, 'Lesão moderada' UNION ALL
  SELECT 'Severe injury',                  17, 'Lesão grave' UNION ALL
  SELECT 'Mild',                           17, 'Leve' UNION ALL
  SELECT 'Moderate',                       17, 'Moderado' UNION ALL
  SELECT 'Severe',                         17, 'Grave' UNION ALL
  SELECT 'Injury',                         17, 'Lesão' UNION ALL
  SELECT 'Evaluation Time',                17, 'Hora da Avaliação' UNION ALL
  SELECT 'Save Changes',                   17, 'Guardar Alterações' UNION ALL
  SELECT 'Basic Assessments',              17, 'Avaliações Básicas' UNION ALL
  SELECT 'Observations',                   17, 'Observações' UNION ALL
  SELECT 'No observations recorded',       17, 'Sem observações registadas' UNION ALL
  SELECT 'Patient Information',            17, 'Informações do Paciente' UNION ALL
  SELECT 'Not available',                  17, 'Não disponível' UNION ALL
  SELECT 'years',                          17, 'anos' UNION ALL
  SELECT 'No evaluations recorded',        17, 'Sem avaliações registadas' UNION ALL
  SELECT 'Record not found',               17, 'Registo não encontrado' UNION ALL
  SELECT 'No data found',                  17, 'Nenhum dado encontrado' UNION ALL
  SELECT 'Item',                           17, 'Item' UNION ALL
  SELECT 'Response',                       17, 'Resposta' UNION ALL
  SELECT 'Record Information',             17, 'Informações do Registo' UNION ALL
  SELECT 'Recorded',                       17, 'Registado' UNION ALL
  SELECT 'Method not allowed',             17, 'Método não permitido' UNION ALL
  SELECT 'Redirecting...',                 17, 'A redirecionar...' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.',                  17, 'Não foi possível obter o PID ou Encontro.' UNION ALL
  SELECT 'Observations about consciousness...',                   17, 'Observações sobre consciência...' UNION ALL
  SELECT 'Observations about muscle tone...',                     17, 'Observações sobre tónus muscular...' UNION ALL
  SELECT 'Observations about pupils...',                          17, 'Observações sobre pupilas...' UNION ALL
  SELECT 'Observations about mucous membranes...',                17, 'Observações sobre mucosas...' UNION ALL
  SELECT 'Observations about eye opening...',                     17, 'Observações sobre abertura ocular...' UNION ALL
  SELECT 'Observations about motor response...',                  17, 'Observações sobre resposta motora...' UNION ALL
  SELECT 'Observations about verbal response...',                 17, 'Observações sobre resposta verbal...' UNION ALL
  SELECT 'Error: Missing required parameters (PID or Encounter)', 17, 'Erro: Parâmetros obrigatórios em falta (PID ou Encontro)' UNION ALL
  SELECT 'Error: Missing required data (PID or Encounter)',       17, 'Erro: Dados obrigatórios em falta (PID ou Encontro)' UNION ALL
  SELECT 'Error: Record not found or insufficient permissions.',  17, 'Erro: Registo não encontrado ou permissões insuficientes.' UNION ALL
  SELECT 'Error: Could not update the record. Please try again.', 17, 'Erro: Não foi possível atualizar o registo. Tente novamente.' UNION ALL
  SELECT 'Error: Could not save the record. Please try again.',   17, 'Erro: Não foi possível guardar o registo. Tente novamente.' UNION ALL
  SELECT 'VIGIL',       17, 'VIGIL'       UNION ALL
  SELECT 'SOMNOLIENTO', 17, 'SONOLENTO'   UNION ALL
  SELECT 'ESTUPOROSO',  17, 'ESTUPOROSO'  UNION ALL
  SELECT 'COMATOSO',    17, 'COMATOSO'    UNION ALL
  SELECT 'FLACIDO',     17, 'FLÁCIDO'     UNION ALL
  SELECT 'ESPASTICO',   17, 'ESPÁSTICO'   UNION ALL
  SELECT 'MIDRIASIS',   17, 'MIDRÍASE'    UNION ALL
  SELECT 'MIOSIS',      17, 'MIOSE'       UNION ALL
  SELECT 'SECA',        17, 'SECA'        UNION ALL
  SELECT 'HUMEDA',      17, 'HÚMIDA'      UNION ALL
  SELECT 'PALIDA',      17, 'PÁLIDA'      UNION ALL
  SELECT 'ICTERICA',    17, 'ICTÉRICA'    UNION ALL
  SELECT 'CIANOSIS',    17, 'CIANOSE'     UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',        17, 'REGISTO DE AVALIAÇÕES DE ENFERMAGEM' UNION ALL
  SELECT 'Component',                         17, 'Componente' UNION ALL
  SELECT 'Score',                             17, 'Pontuação' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score',  17, 'Escala de Glasgow — Pontuação Total' UNION ALL
  SELECT 'Responsible Signature',             17, 'Assinatura do Responsável' UNION ALL
  SELECT 'Clarification',                     17, 'Esclarecimento' UNION ALL
  SELECT 'Error: Missing required parameters.', 17, 'Erro: Parâmetros obrigatórios em falta.' UNION ALL

  -- ==================== PORTUGUESE (BRAZILIAN) lang_id=18 ====================
  SELECT 'Nursing Evaluations',            18, 'Avaliações de Enfermagem' UNION ALL
  SELECT 'New Nursing Evaluation',         18, 'Nova Avaliação de Enfermagem' UNION ALL
  SELECT 'Edit Nursing Evaluation',        18, 'Editar Avaliação de Enfermagem' UNION ALL
  SELECT 'Edit Mode',                      18, 'Modo Edição' UNION ALL
  SELECT 'Create Mode',                    18, 'Modo Criação' UNION ALL
  SELECT 'Evaluation Detail',              18, 'Detalhe da Avaliação' UNION ALL
  SELECT 'Nursing Evaluations List',       18, 'Lista de Avaliações de Enfermagem' UNION ALL
  SELECT 'Consciousness',                  18, 'Consciência' UNION ALL
  SELECT 'Muscle Tone',                    18, 'Tônus Muscular' UNION ALL
  SELECT 'Pupils',                         18, 'Pupilas' UNION ALL
  SELECT 'Mucous Membranes',               18, 'Mucosas' UNION ALL
  SELECT 'Glasgow Coma Scale',             18, 'Escala de Coma de Glasgow' UNION ALL
  SELECT 'Glasgow Coma Scale - Detailed',  18, 'Escala de Glasgow - Detalhado' UNION ALL
  SELECT 'Eye Opening',                    18, 'Abertura Ocular' UNION ALL
  SELECT 'Motor Response',                 18, 'Resposta Motora' UNION ALL
  SELECT 'Verbal Response',                18, 'Resposta Verbal' UNION ALL
  SELECT 'Spontaneously',                  18, 'Espontaneamente' UNION ALL
  SELECT 'To auditory stimuli',            18, 'A estímulos auditivos' UNION ALL
  SELECT 'To pain',                        18, 'À dor' UNION ALL
  SELECT 'No response',                    18, 'Sem resposta' UNION ALL
  SELECT 'Obeys commands',                 18, 'Obedece comandos' UNION ALL
  SELECT 'Localizes pain',                 18, 'Localiza a dor' UNION ALL
  SELECT 'Withdrawal',                     18, 'Retirada' UNION ALL
  SELECT 'Abnormal flexion',               18, 'Flexão anormal' UNION ALL
  SELECT 'Abnormal extension',             18, 'Extensão anormal' UNION ALL
  SELECT 'Oriented and conversing',        18, 'Orientado e conversando' UNION ALL
  SELECT 'Disoriented and conversing',     18, 'Desorientado e conversando' UNION ALL
  SELECT 'Inappropriate words',            18, 'Palavras inadequadas' UNION ALL
  SELECT 'Incomprehensible sounds',        18, 'Sons incompreensíveis' UNION ALL
  SELECT 'Glasgow Total Score',            18, 'Pontuação Total de Glasgow' UNION ALL
  SELECT 'Mild injury',                    18, 'Lesão leve' UNION ALL
  SELECT 'Moderate injury',                18, 'Lesão moderada' UNION ALL
  SELECT 'Severe injury',                  18, 'Lesão grave' UNION ALL
  SELECT 'Mild',                           18, 'Leve' UNION ALL
  SELECT 'Moderate',                       18, 'Moderado' UNION ALL
  SELECT 'Severe',                         18, 'Grave' UNION ALL
  SELECT 'Injury',                         18, 'Lesão' UNION ALL
  SELECT 'Evaluation Time',                18, 'Hora da Avaliação' UNION ALL
  SELECT 'Save Changes',                   18, 'Salvar Alterações' UNION ALL
  SELECT 'Basic Assessments',              18, 'Avaliações Básicas' UNION ALL
  SELECT 'Observations',                   18, 'Observações' UNION ALL
  SELECT 'No observations recorded',       18, 'Sem observações registradas' UNION ALL
  SELECT 'Patient Information',            18, 'Informações do Paciente' UNION ALL
  SELECT 'Not available',                  18, 'Não disponível' UNION ALL
  SELECT 'years',                          18, 'anos' UNION ALL
  SELECT 'No evaluations recorded',        18, 'Sem avaliações registradas' UNION ALL
  SELECT 'Record not found',               18, 'Registro não encontrado' UNION ALL
  SELECT 'No data found',                  18, 'Nenhum dado encontrado' UNION ALL
  SELECT 'Item',                           18, 'Item' UNION ALL
  SELECT 'Response',                       18, 'Resposta' UNION ALL
  SELECT 'Record Information',             18, 'Informações do Registro' UNION ALL
  SELECT 'Recorded',                       18, 'Registrado' UNION ALL
  SELECT 'Method not allowed',             18, 'Método não permitido' UNION ALL
  SELECT 'Redirecting...',                 18, 'Redirecionando...' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.',                  18, 'Não foi possível obter o PID ou Encontro.' UNION ALL
  SELECT 'Observations about consciousness...',                   18, 'Observações sobre consciência...' UNION ALL
  SELECT 'Observations about muscle tone...',                     18, 'Observações sobre tônus muscular...' UNION ALL
  SELECT 'Observations about pupils...',                          18, 'Observações sobre pupilas...' UNION ALL
  SELECT 'Observations about mucous membranes...',                18, 'Observações sobre mucosas...' UNION ALL
  SELECT 'Observations about eye opening...',                     18, 'Observações sobre abertura ocular...' UNION ALL
  SELECT 'Observations about motor response...',                  18, 'Observações sobre resposta motora...' UNION ALL
  SELECT 'Observations about verbal response...',                 18, 'Observações sobre resposta verbal...' UNION ALL
  SELECT 'Error: Missing required parameters (PID or Encounter)', 18, 'Erro: Parâmetros obrigatórios ausentes (PID ou Encontro)' UNION ALL
  SELECT 'Error: Missing required data (PID or Encounter)',       18, 'Erro: Dados obrigatórios ausentes (PID ou Encontro)' UNION ALL
  SELECT 'Error: Record not found or insufficient permissions.',  18, 'Erro: Registro não encontrado ou permissões insuficientes.' UNION ALL
  SELECT 'Error: Could not update the record. Please try again.', 18, 'Erro: Não foi possível atualizar o registro. Tente novamente.' UNION ALL
  SELECT 'Error: Could not save the record. Please try again.',   18, 'Erro: Não foi possível salvar o registro. Tente novamente.' UNION ALL
  SELECT 'VIGIL',       18, 'VIGIL'       UNION ALL
  SELECT 'SOMNOLIENTO', 18, 'SONOLENTO'   UNION ALL
  SELECT 'ESTUPOROSO',  18, 'ESTUPOROSO'  UNION ALL
  SELECT 'COMATOSO',    18, 'COMATOSO'    UNION ALL
  SELECT 'FLACIDO',     18, 'FLÁCIDO'     UNION ALL
  SELECT 'ESPASTICO',   18, 'ESPÁSTICO'   UNION ALL
  SELECT 'MIDRIASIS',   18, 'MIDRÍASE'    UNION ALL
  SELECT 'MIOSIS',      18, 'MIOSE'       UNION ALL
  SELECT 'SECA',        18, 'SECA'        UNION ALL
  SELECT 'HUMEDA',      18, 'ÚMIDA'       UNION ALL
  SELECT 'PALIDA',      18, 'PÁLIDA'      UNION ALL
  SELECT 'ICTERICA',    18, 'ICTÉRICA'    UNION ALL
  SELECT 'CIANOSIS',    18, 'CIANOSE'     UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',        18, 'REGISTRO DE AVALIAÇÕES DE ENFERMAGEM' UNION ALL
  SELECT 'Component',                         18, 'Componente' UNION ALL
  SELECT 'Score',                             18, 'Pontuação' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score',  18, 'Escala de Glasgow — Pontuação Total' UNION ALL
  SELECT 'Responsible Signature',             18, 'Assinatura do Responsável' UNION ALL
  SELECT 'Clarification',                     18, 'Esclarecimento' UNION ALL
  SELECT 'Error: Missing required parameters.', 18, 'Erro: Parâmetros obrigatórios ausentes.' UNION ALL

  -- ==================== ITALIAN lang_id=23 ====================
  SELECT 'Nursing Evaluations',            23, 'Valutazioni Infermieristiche' UNION ALL
  SELECT 'New Nursing Evaluation',         23, 'Nuova Valutazione Infermieristica' UNION ALL
  SELECT 'Edit Nursing Evaluation',        23, 'Modifica Valutazione Infermieristica' UNION ALL
  SELECT 'Edit Mode',                      23, 'Modalità Modifica' UNION ALL
  SELECT 'Create Mode',                    23, 'Modalità Creazione' UNION ALL
  SELECT 'Evaluation Detail',              23, 'Dettaglio Valutazione' UNION ALL
  SELECT 'Nursing Evaluations List',       23, 'Elenco Valutazioni Infermieristiche' UNION ALL
  SELECT 'Consciousness',                  23, 'Coscienza' UNION ALL
  SELECT 'Muscle Tone',                    23, 'Tono Muscolare' UNION ALL
  SELECT 'Pupils',                         23, 'Pupille' UNION ALL
  SELECT 'Mucous Membranes',               23, 'Mucose' UNION ALL
  SELECT 'Glasgow Coma Scale',             23, 'Scala del Coma di Glasgow' UNION ALL
  SELECT 'Glasgow Coma Scale - Detailed',  23, 'Scala di Glasgow - Dettagliata' UNION ALL
  SELECT 'Eye Opening',                    23, 'Apertura degli Occhi' UNION ALL
  SELECT 'Motor Response',                 23, 'Risposta Motoria' UNION ALL
  SELECT 'Verbal Response',                23, 'Risposta Verbale' UNION ALL
  SELECT 'Spontaneously',                  23, 'Spontaneamente' UNION ALL
  SELECT 'To auditory stimuli',            23, 'A stimoli uditivi' UNION ALL
  SELECT 'To pain',                        23, 'Al dolore' UNION ALL
  SELECT 'No response',                    23, 'Nessuna risposta' UNION ALL
  SELECT 'Obeys commands',                 23, 'Obbedisce ai comandi' UNION ALL
  SELECT 'Localizes pain',                 23, 'Localizza il dolore' UNION ALL
  SELECT 'Withdrawal',                     23, 'Retrazione' UNION ALL
  SELECT 'Abnormal flexion',               23, 'Flessione anormale' UNION ALL
  SELECT 'Abnormal extension',             23, 'Estensione anormale' UNION ALL
  SELECT 'Oriented and conversing',        23, 'Orientato e conversa' UNION ALL
  SELECT 'Disoriented and conversing',     23, 'Disorientato e conversa' UNION ALL
  SELECT 'Inappropriate words',            23, 'Parole inappropriate' UNION ALL
  SELECT 'Incomprehensible sounds',        23, 'Suoni incomprensibili' UNION ALL
  SELECT 'Glasgow Total Score',            23, 'Punteggio Totale Glasgow' UNION ALL
  SELECT 'Mild injury',                    23, 'Lesione lieve' UNION ALL
  SELECT 'Moderate injury',                23, 'Lesione moderata' UNION ALL
  SELECT 'Severe injury',                  23, 'Lesione grave' UNION ALL
  SELECT 'Mild',                           23, 'Lieve' UNION ALL
  SELECT 'Moderate',                       23, 'Moderato' UNION ALL
  SELECT 'Severe',                         23, 'Grave' UNION ALL
  SELECT 'Injury',                         23, 'Lesione' UNION ALL
  SELECT 'Evaluation Time',                23, 'Ora di Valutazione' UNION ALL
  SELECT 'Save Changes',                   23, 'Salva Modifiche' UNION ALL
  SELECT 'Basic Assessments',              23, 'Valutazioni di Base' UNION ALL
  SELECT 'Observations',                   23, 'Osservazioni' UNION ALL
  SELECT 'No observations recorded',       23, 'Nessuna osservazione registrata' UNION ALL
  SELECT 'Patient Information',            23, 'Informazioni Paziente' UNION ALL
  SELECT 'Not available',                  23, 'Non disponibile' UNION ALL
  SELECT 'years',                          23, 'anni' UNION ALL
  SELECT 'No evaluations recorded',        23, 'Nessuna valutazione registrata' UNION ALL
  SELECT 'Record not found',               23, 'Record non trovato' UNION ALL
  SELECT 'No data found',                  23, 'Nessun dato trovato' UNION ALL
  SELECT 'Item',                           23, 'Voce' UNION ALL
  SELECT 'Response',                       23, 'Risposta' UNION ALL
  SELECT 'Record Information',             23, 'Informazioni Registro' UNION ALL
  SELECT 'Recorded',                       23, 'Registrato' UNION ALL
  SELECT 'Method not allowed',             23, 'Metodo non consentito' UNION ALL
  SELECT 'Redirecting...',                 23, 'Reindirizzamento...' UNION ALL
  SELECT 'Could not retrieve PID or Encounter.',                  23, 'Impossibile recuperare PID o Incontro.' UNION ALL
  SELECT 'Observations about consciousness...',                   23, 'Osservazioni sulla coscienza...' UNION ALL
  SELECT 'Observations about muscle tone...',                     23, 'Osservazioni sul tono muscolare...' UNION ALL
  SELECT 'Observations about pupils...',                          23, 'Osservazioni sulle pupille...' UNION ALL
  SELECT 'Observations about mucous membranes...',                23, 'Osservazioni sulle mucose...' UNION ALL
  SELECT 'Observations about eye opening...',                     23, 'Osservazioni sull''apertura degli occhi...' UNION ALL
  SELECT 'Observations about motor response...',                  23, 'Osservazioni sulla risposta motoria...' UNION ALL
  SELECT 'Observations about verbal response...',                 23, 'Osservazioni sulla risposta verbale...' UNION ALL
  SELECT 'Error: Missing required parameters (PID or Encounter)', 23, 'Errore: Parametri richiesti mancanti (PID o Incontro)' UNION ALL
  SELECT 'Error: Missing required data (PID or Encounter)',       23, 'Errore: Dati richiesti mancanti (PID o Incontro)' UNION ALL
  SELECT 'Error: Record not found or insufficient permissions.',  23, 'Errore: Record non trovato o permessi insufficienti.' UNION ALL
  SELECT 'Error: Could not update the record. Please try again.', 23, 'Errore: Impossibile aggiornare il record. Riprovare.' UNION ALL
  SELECT 'Error: Could not save the record. Please try again.',   23, 'Errore: Impossibile salvare il record. Riprovare.' UNION ALL
  SELECT 'VIGIL',       23, 'VIGILE'      UNION ALL
  SELECT 'SOMNOLIENTO', 23, 'SONNOLENTO'  UNION ALL
  SELECT 'ESTUPOROSO',  23, 'STUPOROSO'   UNION ALL
  SELECT 'COMATOSO',    23, 'COMATOSO'    UNION ALL
  SELECT 'FLACIDO',     23, 'FLACCIDO'    UNION ALL
  SELECT 'ESPASTICO',   23, 'SPASTICO'    UNION ALL
  SELECT 'MIDRIASIS',   23, 'MIDRIASI'    UNION ALL
  SELECT 'MIOSIS',      23, 'MIOSI'       UNION ALL
  SELECT 'SECA',        23, 'SECCA'       UNION ALL
  SELECT 'HUMEDA',      23, 'UMIDA'       UNION ALL
  SELECT 'PALIDA',      23, 'PALLIDA'     UNION ALL
  SELECT 'ICTERICA',    23, 'ITTERICA'    UNION ALL
  SELECT 'CIANOSIS',    23, 'CIANOSI'     UNION ALL
  SELECT 'NURSING EVALUATIONS RECORD',        23, 'REGISTRO DELLE VALUTAZIONI INFERMIERISTICHE' UNION ALL
  SELECT 'Component',                         23, 'Componente' UNION ALL
  SELECT 'Score',                             23, 'Punteggio' UNION ALL
  SELECT 'Glasgow Coma Scale — Total Score',  23, 'Scala di Glasgow — Punteggio Totale' UNION ALL
  SELECT 'Responsible Signature',             23, 'Firma del Responsabile' UNION ALL
  SELECT 'Clarification',                     23, 'Chiarimento' UNION ALL
  SELECT 'Error: Missing required parameters.', 23, 'Errore: Parametri obbligatori mancanti.'

) AS t ON lc.constant_name = t.cn;
