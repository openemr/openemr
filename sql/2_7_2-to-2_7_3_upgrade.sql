ALTER TABLE lists
  ADD begdate    date          DEFAULT NULL,
  ADD enddate    date          DEFAULT NULL,
  ADD occurrence int(11)       DEFAULT 0,
  ADD referredby varchar(255)  DEFAULT NULL,
  ADD extrainfo  varchar(255)  DEFAULT NULL;

ALTER TABLE patient_data
  ADD squad      varchar(32)   NOT NULL DEFAULT '',
  ADD fitness    int(11)       NOT NULL DEFAULT 0;

ALTER TABLE history_data
  CHANGE last_breast_exam        last_breast_exam        varchar(255) DEFAULT '',
  CHANGE last_mammogram          last_mammogram          varchar(255) DEFAULT '',
  CHANGE last_gynocological_exam last_gynocological_exam varchar(255) DEFAULT '',
  CHANGE last_rectal_exam        last_rectal_exam        varchar(255) DEFAULT '',
  CHANGE last_prostate_exam      last_prostate_exam      varchar(255) DEFAULT '',
  CHANGE last_physical_exam      last_physical_exam      varchar(255) DEFAULT '',
  CHANGE last_sigmoidoscopy_colonoscopy last_sigmoidoscopy_colonoscopy varchar(255) DEFAULT '',
  ADD    last_ecg             varchar(255) NOT NULL DEFAULT '',
  ADD    last_cardiac_echo    varchar(255) NOT NULL DEFAULT '',
  -- here is 1 digit for each of the above exams, in order, with values
  -- 0=n/a, 1=normal, 2=abnormal:
  ADD    last_exam_results    varchar(255) NOT NULL DEFAULT '000000000';

UPDATE history_data SET last_breast_exam        = '' WHERE last_breast_exam        = '0000-00-00 00:00:00';
UPDATE history_data SET last_mammogram          = '' WHERE last_mammogram          = '0000-00-00 00:00:00';
UPDATE history_data SET last_gynocological_exam = '' WHERE last_gynocological_exam = '0000-00-00 00:00:00';
UPDATE history_data SET last_rectal_exam        = '' WHERE last_rectal_exam        = '0000-00-00 00:00:00';
UPDATE history_data SET last_prostate_exam      = '' WHERE last_prostate_exam      = '0000-00-00 00:00:00';
UPDATE history_data SET last_physical_exam      = '' WHERE last_physical_exam      = '0000-00-00 00:00:00';
UPDATE history_data SET last_sigmoidoscopy_colonoscopy = '' WHERE last_sigmoidoscopy_colonoscopy = '0000-00-00 00:00:00';

update insurance_numbers
  set provider_number_type = x12_id_type;

ALTER TABLE insurance_numbers
  DROP column x12_id_type,
  ADD rendering_provider_number_type varchar(4) DEFAULT NULL,
  ADD rendering_provider_number varchar(20) DEFAULT NULL;

ALTER TABLE openemr_postcalendar_events
  -- Appointment status is one of the following:
  --  - = not otherwise applicable
  --  * = reminder call completed
  --  + = chart pulled
  --  ? = no show
  --  @ = patient arrived
  --  ~ = arrived late
  --  ! = left without being seen
  --  # = left due to ins/money issue
  --  < = visit in progress
  --  > = checked out
  --  $ = coding complete
  ADD pc_apptstatus char(1) NOT NULL DEFAULT '-';

ALTER TABLE lists
  ADD diagnosis varchar(255) NOT NULL DEFAULT '';

## 
## Table structure for table `batchcom`
## 

CREATE TABLE IF NOT EXISTS `batchcom` (
  `id` bigint(20) NOT NULL auto_increment,
  `patient_id` int(11) NOT NULL default '0',
  `sent_by` bigint(20) NOT NULL default '0',
  `msg_type` varchar(60) NOT NULL default '',
  `msg_subject` varchar(255) NOT NULL default '',
  `msg_text` mediumtext NOT NULL,
  `msg_date_sent` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;

ALTER TABLE billing
  MODIFY `code` varchar(9) DEFAULT NULL;
