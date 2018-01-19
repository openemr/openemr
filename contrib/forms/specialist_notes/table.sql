CREATE TABLE IF NOT EXISTS form_specialist_notes (
 id                bigint(20)   NOT NULL auto_increment,
 activity          tinyint(1)   NOT NULL DEFAULT 1,  -- 0 if deleted

 notes             text,

 followup_required tinyint(1)   NOT NULL DEFAULT 0,  -- checkbox
 followup_timing   varchar(255) NOT NULL DEFAULT '',
 followup_location varchar(255) NOT NULL DEFAULT '',

 PRIMARY KEY (id)
) ENGINE=InnoDB;
