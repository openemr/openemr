ALTER TABLE form_misc_billing_options
  ADD replacement_claim tinyint(1) DEFAULT 0;

ALTER TABLE insurance_data
  ADD accept_assignment varchar(5) NOT NULL DEFAULT 'TRUE';

CREATE TABLE chart_tracker (
  ct_pid            int(11)       NOT NULL,
  ct_when           datetime      NOT NULL,
  ct_userid         bigint(20)    NOT NULL DEFAULT 0,
  ct_location       varchar(31)   NOT NULL DEFAULT '',
  PRIMARY KEY (ct_pid, ct_when)
) ENGINE=MyISAM;

INSERT INTO list_options VALUES ('lists'   ,'chartloc','Chart Storage Locations',1,0,0);
INSERT INTO list_options VALUES ('chartloc','fileroom','File Room'              ,1,0,0);
