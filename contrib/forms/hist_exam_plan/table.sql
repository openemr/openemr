CREATE TABLE IF NOT EXISTS form_hist_exam_plan (
 id          bigint(20)   NOT NULL auto_increment,
 activity    tinyint(1)   NOT NULL DEFAULT 1, -- 0 if deleted
 history     text         NOT NULL DEFAULT '',
 examination text         NOT NULL DEFAULT '',
 plan        text         NOT NULL DEFAULT '',
 PRIMARY KEY (id)
) TYPE=MyISAM;
