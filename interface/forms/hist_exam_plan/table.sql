CREATE TABLE IF NOT EXISTS form_hist_exam_plan (
 id          bigint(20)   NOT NULL auto_increment,
 activity    tinyint(1)   NOT NULL DEFAULT 1, -- 0 if deleted
 history     text,
 examination text,
 plan        text,
 PRIMARY KEY (id)
) ENGINE=InnoDB;
