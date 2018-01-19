CREATE TABLE IF NOT EXISTS form_body_composition (
 id          bigint(20)   NOT NULL auto_increment,
 activity    tinyint(1)   NOT NULL DEFAULT 1, -- 0 if deleted

 -- external
 body_type                enum('Standard', 'Athletic'),
 height                   float(6,2)      NOT NULL DEFAULT 0.00,
 weight                   float(6,2)      NOT NULL DEFAULT 0.00,

 -- body composition
 bmi                      float(6,2)      NOT NULL DEFAULT 0.00,
 bmr                      float(6,2)      NOT NULL DEFAULT 0.00,
 impedance                float(6,2)      NOT NULL DEFAULT 0.00,
 fat_pct                  float(6,2)      NOT NULL DEFAULT 0.00,
 fat_mass                 float(6,2)      NOT NULL DEFAULT 0.00,
 bps                      float(6,2)      NOT NULL DEFAULT 0.00,
 bpd                      float(6,2)      NOT NULL DEFAULT 0.00,
 ffm                      float(6,2)      NOT NULL DEFAULT 0.00,
 tbw                      float(6,2)      NOT NULL DEFAULT 0.00,

 -- other
 other                    text,

 PRIMARY KEY (id)
) ENGINE=InnoDB;
