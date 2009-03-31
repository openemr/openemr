CREATE TABLE IF NOT EXISTS `form_hpi` (
  `id`                  bigint(20)   NOT NULL auto_increment,
  `date`                datetime     DEFAULT NULL,
  `pid`                 bigint(20)   NOT NULL DEFAULT 0,
  `user`                varchar(255) DEFAULT NULL,
  `groupname`           varchar(255) DEFAULT NULL,
  `authorized`          tinyint(4)   NOT NULL DEFAULT 0,
  `activity`            tinyint(4)   NOT NULL DEFAULT 0,
  `complaint`           varchar(255) NOT NULL DEFAULT '',
  `location`            varchar(255) NOT NULL DEFAULT '',
  `quality`             varchar(255) NOT NULL DEFAULT '',
  `severity`            varchar(255) NOT NULL DEFAULT '',
  `duration`            varchar(255) NOT NULL DEFAULT '',
  `timing`              varchar(255) NOT NULL DEFAULT '',
  `context`             varchar(255) NOT NULL DEFAULT '',
  `factors`             varchar(255) NOT NULL DEFAULT '',
  `signs`               varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) TYPE=InnoDB;