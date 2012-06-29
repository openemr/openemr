CREATE TABLE IF NOT EXISTS `form_affirm_sheet` (
    /* both extended and encounter forms need a last modified date */
    date datetime default NULL comment 'last modified date',
    /* these fields are common to all encounter forms. */
    id bigint(20) NOT NULL auto_increment,
    pid bigint(20) NOT NULL default 0,
    user varchar(255) default NULL,
    groupname varchar(255) default NULL,
    authorized tinyint(4) default NULL,
    activity tinyint(4) default NULL,
    affirm varchar(255),
    exam_yeast varchar(255),
    exam_gardnerrla varchar(255),
    exam_trichomonas varchar(255),
    lot_number varchar(255),
    exp_date varchar(255),
    PRIMARY KEY (id)
) ENGINE=InnoDB;

