CREATE TABLE IF NOT EXISTS form_sports_fitness (
 id          bigint(20)   NOT NULL auto_increment,
 activity    tinyint(1)   NOT NULL DEFAULT 1, -- 0 if deleted

 -- external
 height_meters            float(4,2)      NOT NULL DEFAULT 0.00,
 weight_kg                float(4,2)      NOT NULL DEFAULT 0.00,

 -- body composition
 skin_folds_9x            float(4,2)      NOT NULL DEFAULT 0.00,
 skin_folds_5x            float(4,2)      NOT NULL DEFAULT 0.00,
 pct_body_fat             float(4,2)      NOT NULL DEFAULT 0.00,
 method_body_fat          enum('Caliper', 'Electronic', 'Hydrostatic'),

 -- cardiovascular
 pulse                    float(4,2)      NOT NULL DEFAULT 0.00,
 bps                      float(4,2)      NOT NULL DEFAULT 0.00,
 bpd                      float(4,2)      NOT NULL DEFAULT 0.00,

 -- http://www.brianmac.demon.co.uk/beep.htm describes the beep test
 beep_level               int(11)         NOT NULL DEFAULT 0,
 beep_shuttles            int(11)         NOT NULL DEFAULT 0,
 beep_vo2_max             float(4,2)      NOT NULL DEFAULT 0.00,

 -- other tests
 vertical_jump_meters     float(4,2)      NOT NULL DEFAULT 0.00,
 agility_505              float(4,2)      NOT NULL DEFAULT 0.00,
 sit_and_reach_cm         float(4,1)      NOT NULL DEFAULT 0.0,
 other                    text            NOT NULL DEFAULT '',

 PRIMARY KEY (id)
) TYPE=MyISAM;
