SET NAMES utf8mb4;

-- ============================================================
-- lista_internados.php — Traducciones
-- lang_id: 3=es-ES  4=es-419  5=de  8=fr  9=fr-CA
--          17=pt-PT  18=pt-BR  23=it
-- Seguro para ejecutar múltiples veces (INSERT IGNORE)
-- ============================================================

-- Inpatient List
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Inpatient List');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Lista de Internados'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Inpatient List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Lista de Internados'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Inpatient List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Stationäre Patientenliste'               FROM `lang_constants` c WHERE c.`constant_name` = 'Inpatient List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Liste des patients hospitalisés'         FROM `lang_constants` c WHERE c.`constant_name` = 'Inpatient List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Liste des patients hospitalisés'         FROM `lang_constants` c WHERE c.`constant_name` = 'Inpatient List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Lista de Internados'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Inpatient List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Lista de Internados'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Inpatient List';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Lista dei Ricoverati'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Inpatient List';

-- Record No.
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Record No.');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Nro. Prontuario'                         FROM `lang_constants` c WHERE c.`constant_name` = 'Record No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Nro. Prontuario'                         FROM `lang_constants` c WHERE c.`constant_name` = 'Record No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Aktenzeichen'                            FROM `lang_constants` c WHERE c.`constant_name` = 'Record No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'N° Dossier'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Record No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'N° Dossier'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Record No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Nº Prontuário'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Record No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Nº Prontuário'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Record No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'N° Cartella'                             FROM `lang_constants` c WHERE c.`constant_name` = 'Record No.';

-- Admission
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Admission');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Ingreso'                                 FROM `lang_constants` c WHERE c.`constant_name` = 'Admission';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Ingreso'                                 FROM `lang_constants` c WHERE c.`constant_name` = 'Admission';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Aufnahme'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Admission';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Admission'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Admission';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Admission'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Admission';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Admissão'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Admission';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Admissão'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Admission';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Ricovero'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Admission';

-- Admission Date
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Admission Date');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Fecha de Ingreso'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Admission Date';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Fecha de Ingreso'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Admission Date';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Aufnahmedatum'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Admission Date';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Date d''admission'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Admission Date';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Date d''admission'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Admission Date';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Data de Admissão'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Admission Date';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Data de Admissão'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Admission Date';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Data di Ricovero'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Admission Date';

-- ID (Patient Record)
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('ID (Patient Record)');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'CI (RG Paciente)'                        FROM `lang_constants` c WHERE c.`constant_name` = 'ID (Patient Record)';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'CI (RG Paciente)'                        FROM `lang_constants` c WHERE c.`constant_name` = 'ID (Patient Record)';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'ID (Patientenakte)'                      FROM `lang_constants` c WHERE c.`constant_name` = 'ID (Patient Record)';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'ID (Dossier Patient)'                    FROM `lang_constants` c WHERE c.`constant_name` = 'ID (Patient Record)';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'ID (Dossier Patient)'                    FROM `lang_constants` c WHERE c.`constant_name` = 'ID (Patient Record)';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'ID (Registo do Paciente)'                FROM `lang_constants` c WHERE c.`constant_name` = 'ID (Patient Record)';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'ID (Registro do Paciente)'               FROM `lang_constants` c WHERE c.`constant_name` = 'ID (Patient Record)';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'ID (Cartella Paziente)'                  FROM `lang_constants` c WHERE c.`constant_name` = 'ID (Patient Record)';

-- Reg. No.
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Reg. No.');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Nro. Registro'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Reg. No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Nro. Registro'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Reg. No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Reg.-Nr.'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Reg. No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'N° Enreg.'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Reg. No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'N° Enreg.'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Reg. No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Nº Registo'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Reg. No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Nº Registro'                             FROM `lang_constants` c WHERE c.`constant_name` = 'Reg. No.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'N° Registro'                             FROM `lang_constants` c WHERE c.`constant_name` = 'Reg. No.';

-- Ward
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Ward');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Sala'                                    FROM `lang_constants` c WHERE c.`constant_name` = 'Ward';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Sala'                                    FROM `lang_constants` c WHERE c.`constant_name` = 'Ward';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Station'                                 FROM `lang_constants` c WHERE c.`constant_name` = 'Ward';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Salle'                                   FROM `lang_constants` c WHERE c.`constant_name` = 'Ward';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Salle'                                   FROM `lang_constants` c WHERE c.`constant_name` = 'Ward';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Enfermaria'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Ward';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Enfermaria'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Ward';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Reparto'                                 FROM `lang_constants` c WHERE c.`constant_name` = 'Ward';

-- Bed
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Bed');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Cama'                                    FROM `lang_constants` c WHERE c.`constant_name` = 'Bed';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Cama'                                    FROM `lang_constants` c WHERE c.`constant_name` = 'Bed';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Bett'                                    FROM `lang_constants` c WHERE c.`constant_name` = 'Bed';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Lit'                                     FROM `lang_constants` c WHERE c.`constant_name` = 'Bed';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Lit'                                     FROM `lang_constants` c WHERE c.`constant_name` = 'Bed';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Cama'                                    FROM `lang_constants` c WHERE c.`constant_name` = 'Bed';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Leito'                                   FROM `lang_constants` c WHERE c.`constant_name` = 'Bed';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Letto'                                   FROM `lang_constants` c WHERE c.`constant_name` = 'Bed';

-- Nursing
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Nursing');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Enfermería'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Enfermería'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Pflege'                                  FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Soins infirmiers'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Soins infirmiers'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Enfermagem'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Enfermagem'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Infermieristica'                         FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing';

-- Nursing Options
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Nursing Options');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Opciones de Enfermería'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Options';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Opciones de Enfermería'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Options';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Pflegeoptionen'                          FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Options';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Options de soins infirmiers'             FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Options';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Options de soins infirmiers'             FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Options';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Opções de Enfermagem'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Options';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Opções de Enfermagem'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Options';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Opzioni Infermieristiche'                FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Options';

-- Discharge patient
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Discharge patient');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Dar de Alta al Paciente'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Discharge patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Dar de Alta al Paciente'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Discharge patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Patient entlassen'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Discharge patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Sortie du patient'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Discharge patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Sortie du patient'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Discharge patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Alta do Paciente'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Discharge patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Alta do Paciente'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Discharge patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Dimissione del Paziente'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Discharge patient';

-- Patient discharged successfully
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Patient discharged successfully');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Alta registrada correctamente'           FROM `lang_constants` c WHERE c.`constant_name` = 'Patient discharged successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Alta registrada correctamente'           FROM `lang_constants` c WHERE c.`constant_name` = 'Patient discharged successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Patient erfolgreich entlassen'           FROM `lang_constants` c WHERE c.`constant_name` = 'Patient discharged successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Patient sorti avec succès'               FROM `lang_constants` c WHERE c.`constant_name` = 'Patient discharged successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Patient sorti avec succès'               FROM `lang_constants` c WHERE c.`constant_name` = 'Patient discharged successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Alta registada com sucesso'              FROM `lang_constants` c WHERE c.`constant_name` = 'Patient discharged successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Alta registrada com sucesso'             FROM `lang_constants` c WHERE c.`constant_name` = 'Patient discharged successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Paziente dimesso con successo'           FROM `lang_constants` c WHERE c.`constant_name` = 'Patient discharged successfully';

-- Patient updated successfully
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Patient updated successfully');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Paciente actualizado correctamente'      FROM `lang_constants` c WHERE c.`constant_name` = 'Patient updated successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Paciente actualizado correctamente'      FROM `lang_constants` c WHERE c.`constant_name` = 'Patient updated successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Patient erfolgreich aktualisiert'        FROM `lang_constants` c WHERE c.`constant_name` = 'Patient updated successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Patient mis à jour avec succès'          FROM `lang_constants` c WHERE c.`constant_name` = 'Patient updated successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Patient mis à jour avec succès'          FROM `lang_constants` c WHERE c.`constant_name` = 'Patient updated successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Paciente atualizado com sucesso'         FROM `lang_constants` c WHERE c.`constant_name` = 'Patient updated successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Paciente atualizado com sucesso'         FROM `lang_constants` c WHERE c.`constant_name` = 'Patient updated successfully';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Paziente aggiornato con successo'        FROM `lang_constants` c WHERE c.`constant_name` = 'Patient updated successfully';

-- Register date of death
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Register date of death');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Registrar fecha de fallecimiento'        FROM `lang_constants` c WHERE c.`constant_name` = 'Register date of death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Registrar fecha de fallecimiento'        FROM `lang_constants` c WHERE c.`constant_name` = 'Register date of death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Sterbedatum erfassen'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Register date of death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Enregistrer la date de décès'            FROM `lang_constants` c WHERE c.`constant_name` = 'Register date of death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Enregistrer la date de décès'            FROM `lang_constants` c WHERE c.`constant_name` = 'Register date of death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Registar data de óbito'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Register date of death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Registrar data de óbito'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Register date of death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Registra data del decesso'               FROM `lang_constants` c WHERE c.`constant_name` = 'Register date of death';

-- Register patient death
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Register patient death');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Registrar fallecimiento del paciente'    FROM `lang_constants` c WHERE c.`constant_name` = 'Register patient death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Registrar fallecimiento del paciente'    FROM `lang_constants` c WHERE c.`constant_name` = 'Register patient death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Patientenableben erfassen'               FROM `lang_constants` c WHERE c.`constant_name` = 'Register patient death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Enregistrer le décès du patient'         FROM `lang_constants` c WHERE c.`constant_name` = 'Register patient death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Enregistrer le décès du patient'         FROM `lang_constants` c WHERE c.`constant_name` = 'Register patient death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Registar óbito do paciente'              FROM `lang_constants` c WHERE c.`constant_name` = 'Register patient death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Registrar óbito do paciente'             FROM `lang_constants` c WHERE c.`constant_name` = 'Register patient death';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Registra il decesso del paziente'        FROM `lang_constants` c WHERE c.`constant_name` = 'Register patient death';

-- Are you sure you want to discharge the patient
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Are you sure you want to discharge the patient');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  '¿Está seguro/a que desea dar el alta al paciente?'              FROM `lang_constants` c WHERE c.`constant_name` = 'Are you sure you want to discharge the patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  '¿Está seguro/a que desea dar el alta al paciente?'              FROM `lang_constants` c WHERE c.`constant_name` = 'Are you sure you want to discharge the patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Sind Sie sicher, dass Sie den Patienten entlassen möchten?'     FROM `lang_constants` c WHERE c.`constant_name` = 'Are you sure you want to discharge the patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Êtes-vous sûr(e) de vouloir sortir le patient ?'                FROM `lang_constants` c WHERE c.`constant_name` = 'Are you sure you want to discharge the patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Êtes-vous sûr(e) de vouloir sortir le patient ?'                FROM `lang_constants` c WHERE c.`constant_name` = 'Are you sure you want to discharge the patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Tem a certeza que deseja dar alta ao paciente?'                  FROM `lang_constants` c WHERE c.`constant_name` = 'Are you sure you want to discharge the patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Tem certeza que deseja dar alta ao paciente?'                    FROM `lang_constants` c WHERE c.`constant_name` = 'Are you sure you want to discharge the patient';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'È sicuro/a di voler dimettere il paziente?'                      FROM `lang_constants` c WHERE c.`constant_name` = 'Are you sure you want to discharge the patient';

-- Coming Soon
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Coming Soon');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Próximamente'                            FROM `lang_constants` c WHERE c.`constant_name` = 'Coming Soon';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Próximamente'                            FROM `lang_constants` c WHERE c.`constant_name` = 'Coming Soon';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Demnächst'                               FROM `lang_constants` c WHERE c.`constant_name` = 'Coming Soon';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Prochainement'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Coming Soon';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Prochainement'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Coming Soon';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Em breve'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Coming Soon';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Em breve'                                FROM `lang_constants` c WHERE c.`constant_name` = 'Coming Soon';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Prossimamente'                           FROM `lang_constants` c WHERE c.`constant_name` = 'Coming Soon';

-- Error: Could not retrieve patient data.
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Error: Could not retrieve patient data.');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Error: No se pudieron obtener los datos del paciente.'            FROM `lang_constants` c WHERE c.`constant_name` = 'Error: Could not retrieve patient data.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Error: No se pudieron obtener los datos del paciente.'            FROM `lang_constants` c WHERE c.`constant_name` = 'Error: Could not retrieve patient data.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Fehler: Patientendaten konnten nicht abgerufen werden.'           FROM `lang_constants` c WHERE c.`constant_name` = 'Error: Could not retrieve patient data.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Erreur : Impossible de récupérer les données du patient.'         FROM `lang_constants` c WHERE c.`constant_name` = 'Error: Could not retrieve patient data.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Erreur : Impossible de récupérer les données du patient.'         FROM `lang_constants` c WHERE c.`constant_name` = 'Error: Could not retrieve patient data.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Erro: Não foi possível obter os dados do paciente.'               FROM `lang_constants` c WHERE c.`constant_name` = 'Error: Could not retrieve patient data.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Erro: Não foi possível obter os dados do paciente.'               FROM `lang_constants` c WHERE c.`constant_name` = 'Error: Could not retrieve patient data.';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Errore: Impossibile recuperare i dati del paziente.'              FROM `lang_constants` c WHERE c.`constant_name` = 'Error: Could not retrieve patient data.';

-- Wound Care
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Wound Care');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Curaciones'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Wound Care';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Curaciones'                              FROM `lang_constants` c WHERE c.`constant_name` = 'Wound Care';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Wundversorgung'                          FROM `lang_constants` c WHERE c.`constant_name` = 'Wound Care';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Soins des plaies'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Wound Care';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Soins des plaies'                        FROM `lang_constants` c WHERE c.`constant_name` = 'Wound Care';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Cuidados de feridas'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Wound Care';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Cuidados de feridas'                     FROM `lang_constants` c WHERE c.`constant_name` = 'Wound Care';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Cura delle ferite'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Wound Care';

-- Nursing Evaluation
INSERT IGNORE INTO `lang_constants` (`constant_name`) VALUES ('Nursing Evaluation');
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Evaluación de Enfermería'                FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Evaluación de Enfermería'                FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Pflegebeurteilung'                       FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Évaluation infirmière'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Évaluation infirmière'                   FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Avaliação de Enfermagem'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Avaliação de Enfermagem'                 FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Evaluation';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Valutazione Infermieristica'             FROM `lang_constants` c WHERE c.`constant_name` = 'Nursing Evaluation';

-- ============================================================
-- Suplemento: constantes existentes con traducciones faltantes
-- ============================================================

-- Confirm (solo tiene lang 4,6,15,35 — faltan 3,5,8,9,17,18,23)
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Confirmar'  FROM `lang_constants` c WHERE c.`constant_name` = 'Confirm';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Bestätigen' FROM `lang_constants` c WHERE c.`constant_name` = 'Confirm';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Confirmer'  FROM `lang_constants` c WHERE c.`constant_name` = 'Confirm';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Confirmer'  FROM `lang_constants` c WHERE c.`constant_name` = 'Confirm';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Confirmar'  FROM `lang_constants` c WHERE c.`constant_name` = 'Confirm';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Confirmar'  FROM `lang_constants` c WHERE c.`constant_name` = 'Confirm';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Conferma'   FROM `lang_constants` c WHERE c.`constant_name` = 'Confirm';

-- Deceased (solo tiene lang 15,35 — faltan 3,4,5,8,9,17,18,23)
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 3,  'Fallecido'  FROM `lang_constants` c WHERE c.`constant_name` = 'Deceased';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 4,  'Fallecido'  FROM `lang_constants` c WHERE c.`constant_name` = 'Deceased';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 5,  'Verstorben' FROM `lang_constants` c WHERE c.`constant_name` = 'Deceased';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 8,  'Décédé'     FROM `lang_constants` c WHERE c.`constant_name` = 'Deceased';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 9,  'Décédé'     FROM `lang_constants` c WHERE c.`constant_name` = 'Deceased';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 17, 'Falecido'   FROM `lang_constants` c WHERE c.`constant_name` = 'Deceased';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 18, 'Falecido'   FROM `lang_constants` c WHERE c.`constant_name` = 'Deceased';
INSERT IGNORE INTO `lang_definitions` (`cons_id`,`lang_id`,`definition`) SELECT c.cons_id, 23, 'Deceduto'   FROM `lang_constants` c WHERE c.`constant_name` = 'Deceased';
