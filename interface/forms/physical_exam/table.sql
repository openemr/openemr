CREATE TABLE IF NOT EXISTS form_physical_exam (
 forms_id        bigint(20)   NOT NULL,
 line_id         char(8)      NOT NULL,
 wnl             tinyint(1)   NOT NULL DEFAULT 0,
 abn             tinyint(1)   NOT NULL DEFAULT 0,
 diagnosis       varchar(255) NOT NULL DEFAULT '',
 comments        varchar(255) NOT NULL DEFAULT '',
 PRIMARY KEY (forms_id, line_id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS form_physical_exam_diagnoses (
 line_id         char(8)      NOT NULL,
 ordering        int(11)      NOT NULL DEFAULT 0,
 diagnosis       varchar(255) NOT NULL DEFAULT '',
 KEY (line_id, ordering)
) TYPE=MyISAM;
