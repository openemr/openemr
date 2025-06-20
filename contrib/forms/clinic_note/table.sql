CREATE TABLE IF NOT EXISTS form_clinic_note (
 id                bigint(20)   NOT NULL auto_increment,
 activity          tinyint(1)   NOT NULL DEFAULT 1,  -- 0 if deleted

 history           text,
 examination       text,
 plan              text,

 followup_required int(11)      NOT NULL DEFAULT 0,  -- radio
 followup_timing   varchar(255) NOT NULL DEFAULT '',

 PRIMARY KEY (id)
) ENGINE=InnoDB;
