CREATE TABLE IF NOT EXISTS form_soccer_injury (
 id          bigint(20)   NOT NULL auto_increment,
 activity    tinyint(1)   NOT NULL DEFAULT 1, -- 0 if deleted

 siinjtime   time         DEFAULT NULL,       -- time-of-day of injury

 -- The enumerations below start at a value of 1 - 0 would indicate
 -- an unknown or nonconformant value.

 -- 1st qtr, 2nd qtr, 3rd qtr, 4th qtr, warmup, extra time, cooldown,
 -- training warmup, training session, training cooldown:
 sigametime  int(11)      NOT NULL DEFAULT 0,

 -- tackling, tackled, collision, kicked, elbow, ball, other contact,
 -- passing, shooting, running, dribbling, heading, jumping, landing, fall,
 -- stretching, twisting/turning, throwing, diving, other non-contact:
 simechanism  int(11)      NOT NULL DEFAULT 0,
 simech_other varchar(255) DEFAULT '',

 -- pitch, training, artificial, allweather, indoor, gym, other:
 sisurface   int(11)      NOT NULL DEFAULT 0,

 -- defender, midfield-offense, midfield-defense, wingback, forward,
 -- striker, goalkeeper, starting-lineup, substitute:
 siposition  int(11)      NOT NULL DEFAULT 0,

 -- molded cleats, detachable cleats, indoor shoes, turf shoes:
 sifootwear  int(11)      NOT NULL DEFAULT 0,

 siequip_1   tinyint(1)   NOT NULL DEFAULT 0, -- shin pads
 siequip_2   tinyint(1)   NOT NULL DEFAULT 0, -- gloves
 siequip_3   tinyint(1)   NOT NULL DEFAULT 0, -- ankle strapping
 siequip_4   tinyint(1)   NOT NULL DEFAULT 0, -- knee strapping
 siequip_5   tinyint(1)   NOT NULL DEFAULT 0, -- bracing
 siequip_6   tinyint(1)   NOT NULL DEFAULT 0, -- synthetic cast

 -- left, right, bilateral, n/a:
 siside      int(11)      NOT NULL DEFAULT 0,

 -- immediately, later, not at all:
 siremoved   int(11)      NOT NULL DEFAULT 0,

 sitreat_1   tinyint(1)   NOT NULL DEFAULT 0, -- hospital a&e dept
 sitreat_2   tinyint(1)   NOT NULL DEFAULT 0, -- general practitioner
 sitreat_3   tinyint(1)   NOT NULL DEFAULT 0, -- physiotherapist
 sitreat_4   tinyint(1)   NOT NULL DEFAULT 0, -- nurse
 sitreat_5   tinyint(1)   NOT NULL DEFAULT 0, -- hospital specialist
 sitreat_6   tinyint(1)   NOT NULL DEFAULT 0, -- osteopath
 sitreat_7   tinyint(1)   NOT NULL DEFAULT 0, -- chiropractor
 sitreat_8   tinyint(1)   NOT NULL DEFAULT 0, -- sports massage therapist
 sitreat_9   tinyint(1)   NOT NULL DEFAULT 0, -- sports physician
 sitreat_10  tinyint(1)   NOT NULL DEFAULT 0, -- other
 sitreat_other varchar(255) DEFAULT '',

 -- if player is unlikely to return to play:
 sinoreturn  tinyint(1)   NOT NULL DEFAULT 0,

 PRIMARY KEY (id)
) TYPE=MyISAM;
