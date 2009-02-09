#IfMissingColumn form_misc_billing_options replacement_claim
ALTER TABLE form_misc_billing_options
  ADD replacement_claim tinyint(1) DEFAULT 0;
#EndIf

#IfMissingColumn insurance_data accept_assignment
ALTER TABLE insurance_data
  ADD accept_assignment varchar(5) NOT NULL DEFAULT 'TRUE';
#EndIf

#IfMissingColumn forms deleted
ALTER TABLE forms 
    ADD deleted TINYINT DEFAULT '0' NOT NULL COMMENT 'flag indicates form has been deleted';
#EndIf

#IfMissingColumn immunizations vis_date
ALTER TABLE immunizations
  ADD `vis_date` date default NULL COMMENT 'Date of VIS Statement',
  ADD `administered_by` VARCHAR( 255 ) default NULL COMMENT 'Alternative to administered_by_id';
#EndIf

#IfNotTable chart_tracker
CREATE TABLE chart_tracker (
  ct_pid            int(11)       NOT NULL,
  ct_when           datetime      NOT NULL,
  ct_userid         bigint(20)    NOT NULL DEFAULT 0,
  ct_location       varchar(31)   NOT NULL DEFAULT '',
  PRIMARY KEY (ct_pid, ct_when)
) ENGINE=MyISAM;
#EndIf

#IfNotRow list_options list_id chartloc
INSERT INTO list_options VALUES ('lists'   ,'chartloc','Chart Storage Locations',1,0,0);
INSERT INTO list_options VALUES ('chartloc','fileroom','File Room'              ,1,0,0);
#EndIf

#IfMissingColumn form_encounter last_level_billed
ALTER TABLE form_encounter
  ADD last_level_billed int           NOT NULL DEFAULT 0 COMMENT '0=none, 1=ins1, 2=ins2, etc',
  ADD last_level_closed int           NOT NULL DEFAULT 0 COMMENT '0=none, 1=ins1, 2=ins2, etc',
  ADD last_stmt_date    date          DEFAULT NULL,
  ADD stmt_count        int           NOT NULL DEFAULT 0;
#EndIf

#IfNotTable ar_session
CREATE TABLE ar_session (
  session_id     int unsigned  NOT NULL AUTO_INCREMENT,
  payer_id       int(11)       NOT NULL            COMMENT '0=pt else references insurance_companies.id',
  user_id        int(11)       NOT NULL            COMMENT 'references users.id for session owner',
  closed         tinyint(1)    NOT NULL DEFAULT 0  COMMENT '0=no, 1=yes',
  reference      varchar(255)  NOT NULL DEFAULT '' COMMENT 'check or EOB number',
  check_date     date          DEFAULT NULL,
  deposit_date   date          DEFAULT NULL,
  pay_total      decimal(12,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (session_id),
  KEY user_closed (user_id, closed),
  KEY deposit_date (deposit_date)
) ENGINE=MyISAM;
#EndIf

#IfNotTable ar_activity
CREATE TABLE ar_activity (
  pid            int(11)       NOT NULL,
  encounter      int(11)       NOT NULL,
  sequence_no    int unsigned  NOT NULL AUTO_INCREMENT,
  code           varchar(9)    NOT NULL            COMMENT 'empty means claim level',
  modifier       varchar(5)    NOT NULL DEFAULT '',
  payer_type     int           NOT NULL            COMMENT '0=pt, 1=ins1, 2=ins2, etc',
  post_time      datetime      NOT NULL,
  post_user      int(11)       NOT NULL            COMMENT 'references users.id',
  session_id     int unsigned  NOT NULL            COMMENT 'references ar_session.session_id',
  memo           varchar(255)  NOT NULL DEFAULT '' COMMENT 'adjustment reasons go here',
  pay_amount     decimal(12,2) NOT NULL DEFAULT 0  COMMENT 'either pay or adj will always be 0',
  adj_amount     decimal(12,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (pid, encounter, sequence_no),
  KEY session_id (session_id)
) ENGINE=MyISAM;
#EndIf

#IfMissingColumn users ssi_relayhealth
ALTER TABLE users
  ADD ssi_relayhealth varchar(64) NULL;
#EndIf
