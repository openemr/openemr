CREATE TABLE IF NOT EXISTS form_cricket_injury_audit (
 id          bigint(20)   NOT NULL auto_increment,
 activity    tinyint(1)   NOT NULL DEFAULT 1,
 cicounty    int(11)      NOT NULL DEFAULT 0,
 citeam      int(11)      NOT NULL DEFAULT 0,
 ciduration  int(11)      NOT NULL DEFAULT 0,
 cirole      int(11)      NOT NULL DEFAULT 0,
 cimatchtype int(11)      NOT NULL DEFAULT 0,
 cicause     int(11)      NOT NULL DEFAULT 0,
 ciactivity  int(11)      NOT NULL DEFAULT 0,
 cibatside   int(11)      NOT NULL DEFAULT 0,
 cibowlside  int(11)      NOT NULL DEFAULT 0,
 cibowltype  int(11)      NOT NULL DEFAULT 0,
 PRIMARY KEY (id)
) TYPE=MyISAM;
