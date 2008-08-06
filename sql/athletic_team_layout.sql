INSERT INTO layout_options VALUES ('DEM','fitness'  ,'5Stats'  ,'Fitness to Play',10, 1,1, 0, 0,'fitness',1,1,'','','Fitness Level');
INSERT INTO layout_options VALUES ('DEM','userdate1','5Stats'  ,'Return to Play' ,11, 4,1,10,10,''       ,1,1,'','','Return to Play Date');

INSERT INTO layout_options VALUES ('HIS',''         ,'1General','Allergies'      , 3,24,1, 0, 0,''       ,1,1,'','' ,'List of allergies');

INSERT INTO list_options VALUES ('fitness','1','Full Play'          , 1,1,0);
INSERT INTO list_options VALUES ('fitness','2','Full Training'      , 2,0,0);
INSERT INTO list_options VALUES ('fitness','3','Restricted Training', 3,0,0);
INSERT INTO list_options VALUES ('fitness','4','Injured Out'        , 4,0,0);
INSERT INTO list_options VALUES ('fitness','5','Rehabilitation'     , 5,0,0);
INSERT INTO list_options VALUES ('fitness','6','Illness'            , 6,0,0);
INSERT INTO list_options VALUES ('fitness','7','International Duty' , 7,0,0);

INSERT INTO list_options VALUES ('lists','fitness','Sports Fitness Levels',51,0,0);

ALTER TABLE patient_data
  ADD `userdate1` date DEFAULT NULL;

CREATE TABLE IF NOT EXISTS lists_football_injury (
  id          bigint(20)   NOT NULL,
  fiinjmin    int(11)      NOT NULL DEFAULT 0,
  fiinjtime   int(11)      NOT NULL DEFAULT 0,
  fimatchtype int(11)      NOT NULL DEFAULT 0,
  fimech_tackling    tinyint(1)   NOT NULL DEFAULT 0,
  fimech_tackled     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_collision   tinyint(1)   NOT NULL DEFAULT 0,
  fimech_kicked      tinyint(1)   NOT NULL DEFAULT 0,
  fimech_elbow       tinyint(1)   NOT NULL DEFAULT 0,
  fimech_othercon    varchar(255) NOT NULL DEFAULT '',
  fimech_nofoul      tinyint(1)   NOT NULL DEFAULT 0,
  fimech_oppfoul     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_ownfoul     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_yellow      tinyint(1)   NOT NULL DEFAULT 0,
  fimech_red         tinyint(1)   NOT NULL DEFAULT 0,
  fimech_passing     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_shooting    tinyint(1)   NOT NULL DEFAULT 0,
  fimech_running     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_dribbling   tinyint(1)   NOT NULL DEFAULT 0,
  fimech_heading     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_jumping     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_landing     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_fall        tinyint(1)   NOT NULL DEFAULT 0,
  fimech_stretching  tinyint(1)   NOT NULL DEFAULT 0,
  fimech_turning     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_throwing    tinyint(1)   NOT NULL DEFAULT 0,
  fimech_diving      tinyint(1)   NOT NULL DEFAULT 0,
  fimech_overuse     tinyint(1)   NOT NULL DEFAULT 0,
  fimech_othernon    varchar(255) NOT NULL DEFAULT '',
  fisurface   int(11)      NOT NULL DEFAULT 0,
  fiposition  int(11)      NOT NULL DEFAULT 0,
  fifootwear  int(11)      NOT NULL DEFAULT 0,
  fiside      int(11)      NOT NULL DEFAULT 0,
  firemoved   int(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) TYPE=MyISAM;
