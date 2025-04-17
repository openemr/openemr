ALTER TABLE form_vitals
  MODIFY `weight` FLOAT(5,2) default 0,
  MODIFY `height` FLOAT(5,2) default 0,
  MODIFY `BMI`    FLOAT(4,1) default 0;

#IfNotTable form_misc_billing_options
CREATE TABLE IF NOT EXISTS `form_misc_billing_options` (
  id                          bigint(20)   NOT NULL auto_increment,
  date                        datetime     default NULL,
  pid                         bigint(20)   default NULL,
  user                        varchar(255) default NULL,
  groupname                   varchar(255) default NULL,
  authorized                  tinyint(4)   default NULL,
  activity                    tinyint(4)   default NULL,
  employment_related          tinyint(1)   default NULL,
  auto_accident               tinyint(1)   default NULL,
  accident_state              varchar(2)   default NULL,
  other_accident              tinyint(1)   default NULL,
  outside_lab                 tinyint(1)   default NULL,
  lab_amount                  decimal(5,2) default NULL,
  is_unable_to_work           tinyint(1)   default NULL,
  off_work_from               date         default NULL,
  off_work_to                 date         default NULL,
  is_hospitalized             tinyint(1)   default NULL,
  hospitalization_date_from   date         default NULL,
  hospitalization_date_to     date         default NULL,
  medicaid_resubmission_code  varchar(10)  default NULL,
  medicaid_original_reference varchar(15)  default NULL,
  prior_auth_number           varchar(20)  default NULL,
  comments                    varchar(255) default NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;
#EndIf

#IfMissingColumn facility facility_npi
ALTER TABLE facility
  ADD `facility_npi` varchar(15)  NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn lists classification
ALTER TABLE lists
  ADD `classification` int(11) NOT NULL DEFAULT 0;
#EndIf

#IfMissingColumn form_football_injury_audit fimatchtype
ALTER TABLE form_football_injury_audit
  ADD `fimatchtype` int(11) NOT NULL DEFAULT 0;
#EndIf

#IfMissingColumn documents docdate
ALTER TABLE documents
  ADD `docdate` date       DEFAULT NULL,
  ADD `list_id` bigint(20) NOT NULL DEFAULT 0;
#EndIf

#IfMissingColumn users streetb
ALTER TABLE users
  ADD streetb  varchar(60) NOT NULL DEFAULT '',
  ADD streetb2 varchar(60) NOT NULL DEFAULT '',
  ADD notes    text        NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn history_data last_retinal
ALTER TABLE history_data
  ADD `last_retinal`    varchar(255) NOT NULL DEFAULT '',
  ADD `last_fluvax`     varchar(255) NOT NULL DEFAULT '',
  ADD `last_pneuvax`    varchar(255) NOT NULL DEFAULT '',
  ADD `last_ldl`        varchar(255) NOT NULL DEFAULT '',
  ADD `last_hemoglobin` varchar(255) NOT NULL DEFAULT '',
  ADD `last_psa`        varchar(255) NOT NULL DEFAULT '';
#EndIf

#IfMissingColumn openemr_postcalendar_events pc_multiple
ALTER TABLE openemr_postcalendar_events
  ADD `pc_multiple` int(10) unsigned NOT NULL DEFAULT 0;
#EndIf

#IfMissingColumn billing ndc_info
ALTER TABLE billing
  ADD `ndc_info` varchar(255) NOT NULL DEFAULT '';
#EndIf

#IfNotTable claims
CREATE TABLE claims (
  patient_id        int(11)      NOT NULL,
  encounter_id      int(11)      NOT NULL,
  version           int unsigned NOT NULL AUTO_INCREMENT,
  payer_id          int(11)      NOT NULL DEFAULT 0,
  status            tinyint(2)   NOT NULL DEFAULT 0,
  payer_type        tinyint(4)   NOT NULL DEFAULT 0,
  bill_process      tinyint(2)   NOT NULL DEFAULT 0,
  bill_time         datetime     DEFAULT NULL,
  process_time      datetime     DEFAULT NULL,
  process_file      varchar(255) NOT NULL DEFAULT '',
  target            varchar(30)  NOT NULL DEFAULT '',
  x12_partner_id    int(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (patient_id, encounter_id, version)
) ENGINE=MyISAM;
INSERT IGNORE INTO claims (
  patient_id, encounter_id, bill_time, payer_id,
  status, payer_type,
  bill_process, process_time, process_file, target,
  x12_partner_id )
  SELECT DISTINCT
  b.pid, b.encounter, b.bill_date, b.payer_id,
  b.billed * 2 + 1, FIND_IN_SET(i.type, 'primary,secondary,tertiary'),
  b.bill_process, b.process_date, b.process_file, b.target,
  b.x12_partner_id
  FROM billing AS b
  LEFT OUTER JOIN insurance_data AS i ON i.pid = b.pid AND i.provider = b.payer_id
  WHERE b.activity > 0 AND b.encounter > 0 AND b.code_type != 'ICD9' AND b.payer_id > 0;
#EndIf

#IfNotColumnType insurance_data date date
ALTER TABLE insurance_data
  MODIFY `date` date NOT NULL DEFAULT '0000-00-00',
  DROP KEY pid_type,
  DROP KEY pid,
  ADD UNIQUE KEY pid_type_date (pid, type, date);
#EndIf

#IfMissingColumn facility service_location
ALTER TABLE facility
  ADD `service_location` tinyint(1) NOT NULL DEFAULT 1;
#EndIf

#IfMissingColumn form_encounter facility_id
ALTER TABLE form_encounter
  ADD facility_id int(11) NOT NULL DEFAULT 0;
UPDATE form_encounter, facility
  SET form_encounter.facility_id = facility.id WHERE
  facility.name = form_encounter.facility;
#EndIf

#IfMissingColumn users facility_id
ALTER TABLE users
  ADD facility_id int(11) NOT NULL DEFAULT 0;
UPDATE users, facility
  SET users.facility_id = facility.id WHERE
  facility.name = users.facility;
#EndIf
