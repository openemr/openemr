--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfColumn
--    arguments: table_name colname
--    behavior:  if the table and column exist,  the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the table exists but the column does not,  the block will be executed

--  #IfNotColumnType
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed

--  #IfNotRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a row where colname = value, the block will be executed.

--  #IfNotRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfNotRow3D
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfNotRow4D
--    arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3 AND colname4 = value4, the block will be executed.

--  #IfNotRow2Dx2
--    desc:      This is a very specialized function to allow adding items to the list_options table to avoid both redundant option_id and title in each element.
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  The block will be executed if both statements below are true:
--               1) The table table_name does not have a row where colname = value AND colname2 = value2.
--               2) The table table_name does not have a row where colname = value AND colname3 = value3.

--  #IfRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfRow3D
--        arguments: table_name colname value colname2 value2 colname3 value3
--        behavior:  If the table table_name does have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfIndex
--    desc:      This function is most often used for dropping of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the table and index exist the relevant statements are executed, otherwise not.

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #EndIf
--    all blocks are terminated with a #EndIf statement.

--  #IfNotListReaction
--    Custom function for creating Reaction List

--  #IfNotListOccupation
--    Custom function for creating Occupation List

--  #IfTextNullFixNeeded
--    desc: convert all text fields without default null to have default null.
--    arguments: none

--  #IfTableEngine
--    desc:      Execute SQL if the table has been created with given engine specified.
--    arguments: table_name engine
--    behavior:  Use when engine conversion requires more than one ALTER TABLE

--  #IfInnoDBMigrationNeeded
--    desc: find all MyISAM tables and convert them to InnoDB.
--    arguments: none
--    behavior: can take a long time.

UPDATE `background_services` SET `require_once`='/library/MedEx/MedEx_background.php' WHERE `name`='MedEx';

#IfNotRow2Dx2 list_options list_id proc_type option_id fgp title Custom Favorite Group
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_type','fgp','Custom Favorite Group' ,50,0);
#EndIf

#IfNotRow2Dx2 list_options list_id proc_type option_id for title Custom Favorite Item
INSERT INTO list_options ( list_id, option_id, title, seq, is_default ) VALUES ('proc_type','for','Custom Favorite Item' ,60,0);
#EndIf

#IfNotTable form_eye_base
CREATE TABLE `form_eye_base` (
  `id`         bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Links to forms.form_id',
  `date`       datetime DEFAULT NULL,
  `pid`        bigint(20)   DEFAULT NULL,
  `user`       varchar(255) DEFAULT NULL,
  `groupname`  varchar(255) DEFAULT NULL,
  `authorized` tinyint(4)   DEFAULT NULL,
  `activity`   tinyint(4)   DEFAULT NULL,
  PRIMARY KEY `form_link` (`id`),
  UNIQUE KEY `id_date` (`id`,`date`)
) ENGINE = InnoDB;

CREATE TABLE `form_eye_hpi` (
  `id`          bigint(20) NOT NULL COMMENT 'Links to forms.form_id',
  `pid`         bigint(20)   DEFAULT NULL,
  `CC1`         varchar(255) DEFAULT NULL,
  `HPI1`        text,
  `QUALITY1`    varchar(255) DEFAULT NULL,
  `TIMING1`     varchar(255) DEFAULT NULL,
  `DURATION1`   varchar(255) DEFAULT NULL,
  `CONTEXT1`    varchar(255) DEFAULT NULL,
  `SEVERITY1`   varchar(255) DEFAULT NULL,
  `MODIFY1`     varchar(255) DEFAULT NULL,
  `ASSOCIATED1` varchar(255) DEFAULT NULL,
  `LOCATION1`   varchar(255) DEFAULT NULL,
  `CHRONIC1`    varchar(255) DEFAULT NULL,
  `CHRONIC2`    varchar(255) DEFAULT NULL,
  `CHRONIC3`    varchar(255) DEFAULT NULL,
  `CC2`         text,
  `HPI2`        text,
  `QUALITY2`    text,
  `TIMING2`     text,
  `DURATION2`   text,
  `CONTEXT2`    text,
  `SEVERITY2`   text,
  `MODIFY2`     text,
  `ASSOCIATED2` text,
  `LOCATION2`   text,
  `CC3`         text,
  `HPI3`        text,
  `QUALITY3`    text,
  `TIMING3`     text,
  `DURATION3`   text,
  `CONTEXT3`    text,
  `SEVERITY3`   text,
  `MODIFY3`     text,
  `ASSOCIATED3` text,
  `LOCATION3`   text,
  PRIMARY KEY `hpi_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE TABLE `form_eye_ros` (
  `id`           bigint(20) NOT NULL COMMENT 'Links to forms.form_id',
  `pid`        bigint(20)   DEFAULT NULL,
  `ROSGENERAL`   text,
  `ROSHEENT`     text,
  `ROSCV`        text,
  `ROSPULM`      text,
  `ROSGI`        text,
  `ROSGU`        text,
  `ROSDERM`      text,
  `ROSNEURO`     text,
  `ROSPSYCH`     text,
  `ROSMUSCULO`   text,
  `ROSIMMUNO`    text,
  `ROSENDOCRINE` text,
  `ROSCOMMENTS`  text,
  PRIMARY KEY `ros_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE TABLE `form_eye_vitals` (
  `id`          bigint(20)  NOT NULL COMMENT 'Links to forms.form_id',
  `pid`        bigint(20)   DEFAULT NULL,
  `alert`       char(3)     DEFAULT 'yes',
  `oriented`    char(3)     DEFAULT 'TPP',
  `confused`    char(3)     DEFAULT 'nml',
  `ODIOPAP`     varchar(10) DEFAULT NULL,
  `OSIOPAP`     varchar(10) DEFAULT NULL,
  `ODIOPTPN`    varchar(10) DEFAULT NULL,
  `OSIOPTPN`    varchar(10) DEFAULT NULL,
  `ODIOPFTN`    varchar(10) DEFAULT NULL,
  `OSIOPFTN`    varchar(10) DEFAULT NULL,
  `IOPTIME`     time        NOT NULL,
  `ODIOPPOST`   varchar(10) NOT NULL,
  `OSIOPPOST`   varchar(10) NOT NULL,
  `IOPPOSTTIME` time        DEFAULT NULL,
  `ODIOPTARGET` varchar(10) NOT NULL,
  `OSIOPTARGET` varchar(10) NOT NULL,
  `AMSLEROD`    smallint(1) DEFAULT NULL,
  `AMSLEROS`    smallint(1) DEFAULT NULL,
  `ODVF1`       tinyint(1)  DEFAULT NULL,
  `ODVF2`       tinyint(1)  DEFAULT NULL,
  `ODVF3`       tinyint(1)  DEFAULT NULL,
  `ODVF4`       tinyint(1)  DEFAULT NULL,
  `OSVF1`       tinyint(1)  DEFAULT NULL,
  `OSVF2`       tinyint(1)  DEFAULT NULL,
  `OSVF3`       tinyint(1)  DEFAULT NULL,
  `OSVF4`       tinyint(1)  DEFAULT NULL,
  PRIMARY KEY `vitals_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE TABLE `form_eye_acuity` (
  `id`            bigint(20)  NOT NULL COMMENT 'Links to forms.form_id',
  `pid`           bigint(20)   DEFAULT NULL,
  `SCODVA`        varchar(25)  DEFAULT NULL,
  `SCOSVA`        varchar(25)  DEFAULT NULL,
  `PHODVA`        varchar(25)  DEFAULT NULL,
  `PHOSVA`        varchar(25)  DEFAULT NULL,
  `CTLODVA`       varchar(25)  DEFAULT NULL,
  `CTLOSVA`       varchar(25)  DEFAULT NULL,
  `MRODVA`        varchar(25)  DEFAULT NULL,
  `MROSVA`        varchar(25)  DEFAULT NULL,
  `SCNEARODVA`    varchar(25)  DEFAULT NULL,
  `SCNEAROSVA`    varchar(25)  DEFAULT NULL,
  `MRNEARODVA`    varchar(25)  DEFAULT NULL,
  `MRNEAROSVA`    varchar(25)  DEFAULT NULL,
  `GLAREODVA`     varchar(25)  DEFAULT NULL,
  `GLAREOSVA`     varchar(25)  DEFAULT NULL,
  `GLARECOMMENTS` varchar(255) DEFAULT NULL,
  `ARODVA`        varchar(25)  DEFAULT NULL,
  `AROSVA`        varchar(25)  DEFAULT NULL,
  `CRODVA`        varchar(25)  DEFAULT NULL,
  `CROSVA`        varchar(25)  DEFAULT NULL,
  `CTLODVA1`      varchar(25)  DEFAULT NULL,
  `CTLOSVA1`      varchar(25)  DEFAULT NULL,
  `PAMODVA`       varchar(25)  DEFAULT NULL,
  `PAMOSVA`       varchar(25)  DEFAULT NULL,
  `LIODVA`        varchar(25) NOT NULL,
  `LIOSVA`        varchar(25) NOT NULL,
  `WODVANEAR`     varchar(25)  DEFAULT NULL,
  `OSVANEARCC`    varchar(25)  DEFAULT NULL,
  PRIMARY KEY `acuity_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE TABLE `form_eye_refraction` (
  `id`                bigint(20) NOT NULL COMMENT 'Links to forms.form_id',
  `pid`               bigint(20)   DEFAULT NULL,
  `MRODSPH`           varchar(25)  DEFAULT NULL,
  `MRODCYL`           varchar(25)  DEFAULT NULL,
  `MRODAXIS`          varchar(25)  DEFAULT NULL,
  `MRODPRISM`         varchar(25)  DEFAULT NULL,
  `MRODBASE`          varchar(25)  DEFAULT NULL,
  `MRODADD`           varchar(25)  DEFAULT NULL,
  `MROSSPH`           varchar(25)  DEFAULT NULL,
  `MROSCYL`           varchar(25)  DEFAULT NULL,
  `MROSAXIS`          varchar(25)  DEFAULT NULL,
  `MROSPRISM`         varchar(50)  DEFAULT NULL,
  `MROSBASE`          varchar(50)  DEFAULT NULL,
  `MROSADD`           varchar(25)  DEFAULT NULL,
  `MRODNEARSPHERE`    varchar(25)  DEFAULT NULL,
  `MRODNEARCYL`       varchar(25)  DEFAULT NULL,
  `MRODNEARAXIS`      varchar(25)  DEFAULT NULL,
  `MRODPRISMNEAR`     varchar(50)  DEFAULT NULL,
  `MRODBASENEAR`      varchar(25)  DEFAULT NULL,
  `MROSNEARSHPERE`    varchar(25)  DEFAULT NULL,
  `MROSNEARCYL`       varchar(25)  DEFAULT NULL,
  `MROSNEARAXIS`      varchar(125) DEFAULT NULL,
  `MROSPRISMNEAR`     varchar(50)  DEFAULT NULL,
  `MROSBASENEAR`      varchar(25)  DEFAULT NULL,
  `CRODSPH`           varchar(25)  DEFAULT NULL,
  `CRODCYL`           varchar(25)  DEFAULT NULL,
  `CRODAXIS`          varchar(25)  DEFAULT NULL,
  `CROSSPH`           varchar(25)  DEFAULT NULL,
  `CROSCYL`           varchar(25)  DEFAULT NULL,
  `CROSAXIS`          varchar(25)  DEFAULT NULL,
  `CRCOMMENTS`        varchar(255) DEFAULT NULL,
  `BALANCED`          char(2)    NOT NULL,
  `ARODSPH`           varchar(25)  DEFAULT NULL,
  `ARODCYL`           varchar(25)  DEFAULT NULL,
  `ARODAXIS`          varchar(25)  DEFAULT NULL,
  `AROSSPH`           varchar(25)  DEFAULT NULL,
  `AROSCYL`           varchar(25)  DEFAULT NULL,
  `AROSAXIS`          varchar(25)  DEFAULT NULL,
  `ARODADD`           varchar(25)  DEFAULT NULL,
  `AROSADD`           varchar(25)  DEFAULT NULL,
  `ARNEARODVA`        varchar(25)  DEFAULT NULL,
  `ARNEAROSVA`        varchar(25)  DEFAULT NULL,
  `ARODPRISM`         varchar(50)  DEFAULT NULL,
  `AROSPRISM`         varchar(50)  DEFAULT NULL,
  `CTLODSPH`          varchar(25)  DEFAULT NULL,
  `CTLODCYL`          varchar(25)  DEFAULT NULL,
  `CTLODAXIS`         varchar(25)  DEFAULT NULL,
  `CTLODBC`           varchar(25)  DEFAULT NULL,
  `CTLODDIAM`         varchar(25)  DEFAULT NULL,
  `CTLOSSPH`          varchar(25)  DEFAULT NULL,
  `CTLOSCYL`          varchar(25)  DEFAULT NULL,
  `CTLOSAXIS`         varchar(25)  DEFAULT NULL,
  `CTLOSBC`           varchar(25)  DEFAULT NULL,
  `CTLOSDIAM`         varchar(25)  DEFAULT NULL,
  `CTL_COMMENTS`      text,
  `CTLMANUFACTUREROD` varchar(50)  DEFAULT NULL,
  `CTLSUPPLIEROD`     varchar(50)  DEFAULT NULL,
  `CTLBRANDOD`        varchar(50)  DEFAULT NULL,
  `CTLMANUFACTUREROS` varchar(50)  DEFAULT NULL,
  `CTLSUPPLIEROS`     varchar(50)  DEFAULT NULL,
  `CTLBRANDOS`        varchar(50)  DEFAULT NULL,
  `CTLODADD`          varchar(25)  DEFAULT NULL,
  `CTLOSADD`          varchar(25)  DEFAULT NULL,
  `NVOCHECKED`        varchar(25)  DEFAULT NULL,
  `ADDCHECKED`        varchar(25)  DEFAULT NULL,
  PRIMARY KEY `refraction_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE TABLE `form_eye_biometrics` (
  `id` bigint (20) NOT NULL COMMENT 'Links to forms.form_id',
  `pid`        bigint(20)   DEFAULT NULL,
  `ODK1` varchar (10) DEFAULT NULL,
  `ODK2` varchar (10) DEFAULT NULL,
  `ODK2AXIS` varchar (10) DEFAULT NULL,
  `OSK1` varchar (10) DEFAULT NULL,
  `OSK2` varchar (10) DEFAULT NULL,
  `OSK2AXIS` varchar (10) DEFAULT NULL,
  `ODAXIALLENGTH` varchar (20) DEFAULT NULL,
  `OSAXIALLENGTH` varchar (20) DEFAULT NULL,
  `ODPDMeasured` varchar (20) DEFAULT NULL,
  `OSPDMeasured` varchar (20) DEFAULT NULL,
  `ODACD` varchar (20) DEFAULT NULL,
  `OSACD` varchar (20) DEFAULT NULL,
  `ODW2W` varchar (20) DEFAULT NULL,
  `OSW2W` varchar (20) DEFAULT NULL,
  `ODLT` varchar (20) DEFAULT NULL,
  `OSLT` varchar (20) DEFAULT NULL,
  PRIMARY KEY `biometrics_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE TABLE `form_eye_external` (
  `id`           bigint(20) NOT NULL COMMENT 'Links to forms.form_id',
  `pid`        bigint(20)   DEFAULT NULL,
  `RUL`          text,
  `LUL`          text,
  `RLL`          text,
  `LLL`          text,
  `RBROW`        text,
  `LBROW`        text,
  `RMCT`         text,
  `LMCT`         text,
  `RADNEXA`      text,
  `LADNEXA`      text,
  `RMRD`         varchar(25) DEFAULT NULL,
  `LMRD`         varchar(25) DEFAULT NULL,
  `RLF`          varchar(25) DEFAULT NULL,
  `LLF`          varchar(25) DEFAULT NULL,
  `RVFISSURE`    varchar(25) DEFAULT NULL,
  `LVFISSURE`    varchar(25) DEFAULT NULL,
  `ODHERTEL`     varchar(25) DEFAULT NULL,
  `OSHERTEL`     varchar(25) DEFAULT NULL,
  `HERTELBASE`   varchar(25) DEFAULT NULL,
  `RCAROTID`     text,
  `LCAROTID`     text,
  `RTEMPART`     text,
  `LTEMPART`     text,
  `RCNV`         text,
  `LCNV`         text,
  `RCNVII`       text,
  `LCNVII`       text,
  `EXT_COMMENTS` text,
  PRIMARY KEY `external_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE TABLE `form_eye_antseg` (
  `id`                   bigint(20) NOT NULL COMMENT 'Links to forms.form_id',
  `pid`                  bigint(20)   DEFAULT NULL,
  `ODSCHIRMER1`          varchar(25) DEFAULT NULL,
  `OSSCHIRMER1`          varchar(25) DEFAULT NULL,
  `ODSCHIRMER2`          varchar(25) DEFAULT NULL,
  `OSSCHIRMER2`          varchar(25) DEFAULT NULL,
  `ODTBUT`               varchar(25) DEFAULT NULL,
  `OSTBUT`               varchar(25) DEFAULT NULL,
  `OSCONJ`               varchar(25) DEFAULT NULL,
  `ODCONJ`               text,
  `ODCORNEA`             text,
  `OSCORNEA`             text,
  `ODAC`                 text,
  `OSAC`                 text,
  `ODLENS`               text,
  `OSLENS`               text,
  `ODIRIS`               text,
  `OSIRIS`               text,
  `PUPIL_NORMAL`         varchar(2)  DEFAULT '1',
  `ODPUPILSIZE1`         varchar(25) DEFAULT NULL,
  `ODPUPILSIZE2`         varchar(25) DEFAULT NULL,
  `ODPUPILREACTIVITY`    char(25)    DEFAULT NULL,
  `ODAPD`                varchar(25) DEFAULT NULL,
  `OSPUPILSIZE1`         varchar(25) DEFAULT NULL,
  `OSPUPILSIZE2`         varchar(25) DEFAULT NULL,
  `OSPUPILREACTIVITY`    char(25)    DEFAULT NULL,
  `OSAPD`                varchar(25) DEFAULT NULL,
  `DIMODPUPILSIZE1`      varchar(25) DEFAULT NULL,
  `DIMODPUPILSIZE2`      varchar(25) DEFAULT NULL,
  `DIMODPUPILREACTIVITY` varchar(25) DEFAULT NULL,
  `DIMOSPUPILSIZE1`      varchar(25) DEFAULT NULL,
  `DIMOSPUPILSIZE2`      varchar(25) DEFAULT NULL,
  `DIMOSPUPILREACTIVITY` varchar(25) DEFAULT NULL,
  `PUPIL_COMMENTS`       text,
  `ODKTHICKNESS`         varchar(25) DEFAULT NULL,
  `OSKTHICKNESS`         varchar(25) DEFAULT NULL,
  `ODGONIO`              varchar(25) DEFAULT NULL,
  `OSGONIO`              varchar(25) DEFAULT NULL,
  `ANTSEG_COMMENTS`      text,
  PRIMARY KEY `antseg_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE  TABLE `form_eye_postseg` (
  `id`              bigint(20)  NOT NULL COMMENT 'Links to forms.form_id',
  `pid`             bigint(20)   DEFAULT NULL,
  `ODDISC`          text,
  `OSDISC`          text,
  `ODCUP`           text,
  `OSCUP`           text,
  `ODMACULA`        text,
  `OSMACULA`        text,
  `ODVESSELS`       text,
  `OSVESSELS`       text,
  `ODVITREOUS`      text,
  `OSVITREOUS`      text,
  `ODPERIPH`        text,
  `OSPERIPH`        text,
  `ODCMT`           text,
  `OSCMT`           text,
  `RETINA_COMMENTS` text,
  `DIL_RISKS`       char(2)     NOT NULL DEFAULT 'on',
  `DIL_MEDS`        mediumtext,
  `WETTYPE`         varchar(10) NOT NULL,
  `ATROPINE`        varchar(25) NOT NULL,
  `CYCLOMYDRIL`     varchar(25) NOT NULL,
  `TROPICAMIDE`     varchar(25) NOT NULL,
  `CYCLOGYL`        varchar(25) NOT NULL,
  `NEO25`           varchar(25) NOT NULL,
  PRIMARY KEY `postseg_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
  ) ENGINE = InnoDB;

CREATE  TABLE `form_eye_neuro` (
  `id`         bigint (20) NOT NULL COMMENT 'Links to forms.form_id',
  `pid`        bigint(20)   DEFAULT NULL,
  `ACT`        char (3) NOT NULL DEFAULT 'on',
  `ACT5CCDIST` text,
  `ACT1CCDIST` text,
  `ACT2CCDIST` text,
  `ACT3CCDIST` text,
  `ACT4CCDIST` text,
  `ACT6CCDIST` text,
  `ACT7CCDIST` text,
  `ACT8CCDIST` text,
  `ACT9CCDIST` text,
  `ACT10CCDIST` text,
  `ACT11CCDIST` text,
  `ACT1SCDIST` text,
  `ACT2SCDIST` text,
  `ACT3SCDIST` text,
  `ACT4SCDIST` text,
  `ACT5SCDIST` text,
  `ACT6SCDIST` text,
  `ACT7SCDIST` text,
  `ACT8SCDIST` text,
  `ACT9SCDIST` text,
  `ACT10SCDIST` text,
  `ACT11SCDIST` text,
  `ACT1SCNEAR` text,
  `ACT2SCNEAR` text,
  `ACT3SCNEAR` text,
  `ACT4SCNEAR` text,
  `ACT5CCNEAR` text,
  `ACT6CCNEAR` text,
  `ACT7CCNEAR` text,
  `ACT8CCNEAR` text,
  `ACT9CCNEAR` text,
  `ACT10CCNEAR` text,
  `ACT11CCNEAR` text,
  `ACT5SCNEAR` text,
  `ACT6SCNEAR` text,
  `ACT7SCNEAR` text,
  `ACT8SCNEAR` text,
  `ACT9SCNEAR` text,
  `ACT10SCNEAR` text,
  `ACT11SCNEAR` text,
  `ACT1CCNEAR` text,
  `ACT2CCNEAR` text,
  `ACT3CCNEAR` text,
  `ACT4CCNEAR` text,
  `MOTILITYNORMAL` char (3) NOT NULL DEFAULT 'on',
  `MOTILITY_RS` char (1) DEFAULT '0',
  `MOTILITY_RI` char (1) DEFAULT '0',
  `MOTILITY_RR` char (1) DEFAULT '0',
  `MOTILITY_RL` char (1) DEFAULT '0',
  `MOTILITY_LS` char (1) DEFAULT '0',
  `MOTILITY_LI` char (1) DEFAULT '0',
  `MOTILITY_LR` char (1) DEFAULT '0',
  `MOTILITY_LL` char (1) DEFAULT '0',
  `MOTILITY_RRSO` int (1) DEFAULT NULL,
  `MOTILITY_RLSO` int (1) DEFAULT NULL,
  `MOTILITY_RRIO` int (1) DEFAULT NULL,
  `MOTILITY_RLIO` int (1) DEFAULT NULL,
  `MOTILITY_LRSO` int (1) DEFAULT NULL,
  `MOTILITY_LLSO` int (1) DEFAULT NULL,
  `MOTILITY_LRIO` int (1) DEFAULT NULL,
  `MOTILITY_LLIO` int (1) DEFAULT NULL,
  `NEURO_COMMENTS` text,
  `STEREOPSIS` varchar (25) DEFAULT NULL,
  `ODNPA` text,
  `OSNPA` text,
  `VERTFUSAMPS` text,
  `DIVERGENCEAMPS` text,
  `NPC` varchar (10) DEFAULT NULL,
  `DACCDIST` varchar (20) DEFAULT NULL,
  `DACCNEAR` varchar (20) DEFAULT NULL,
  `CACCDIST` varchar (20) DEFAULT NULL,
  `CACCNEAR` varchar (20) DEFAULT NULL,
  `ODCOLOR` text,
  `OSCOLOR` text,
  `ODCOINS` text,
  `OSCOINS` text,
  `ODREDDESAT` varchar (20) DEFAULT NULL,
  `OSREDDESAT` varchar (20) DEFAULT NULL,
  PRIMARY KEY `neuro_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
) ENGINE = InnoDB;

CREATE  TABLE `form_eye_locking` (
  `id`         bigint(20) NOT NULL COMMENT 'Links to forms.form_id',
  `pid`        bigint(20)   DEFAULT NULL,
  `IMP`        text,
  `PLAN`       text,
  `Resource`   varchar(50)         DEFAULT NULL,
  `Technician` varchar(50)         DEFAULT NULL,
  `LOCKED`     varchar(3)          DEFAULT NULL,
  `LOCKEDDATE` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP
  ON UPDATE CURRENT_TIMESTAMP,
  `LOCKEDBY`   varchar(50)         DEFAULT NULL,
  PRIMARY KEY `locking_link` (`id`),
  UNIQUE KEY `id_pid` (`id`,`pid`)
) ENGINE = InnoDB;

ALTER TABLE `form_eye_mag_orders`
    CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    CHANGE `ORDER_PID` `pid` BIGINT(20) NOT NULL,
    ADD `form_id` bigint(20) NOT NULL AFTER `id`,
    DROP INDEX `VISIT_ID`;
ALTER TABLE `form_eye_mag_orders`
    ADD UNIQUE KEY `VISIT_ID` (`pid`, `ORDER_DETAILS`, `ORDER_DATE_PLACED`);

INSERT into `form_eye_base` (`id`,`date`,`pid`,`user`,`groupname`,`authorized`, `activity`)
  select `id`,`date`,`pid`,`user`,`groupname`,`authorized`, `activity` from `form_eye_mag`;

INSERT INTO `form_eye_hpi` (  `id`,`pid`, `CC1`, `HPI1`, `QUALITY1` , `TIMING1`, `DURATION1`, `CONTEXT1`, `SEVERITY1`, `MODIFY1`, `ASSOCIATED1` , `LOCATION1` , `CHRONIC1` , `CHRONIC2` , `CHRONIC3` , `CC2` , `HPI2` , `QUALITY2` , `TIMING2` , `DURATION2` , `CONTEXT2` , `SEVERITY2` , `MODIFY2` , `ASSOCIATED2` , `LOCATION2` , `CC3` , `HPI3` , `QUALITY3` , `TIMING3` , `DURATION3` , `CONTEXT3` , `SEVERITY3` , `MODIFY3` , `ASSOCIATED3` , `LOCATION3` )
  select `id`,`pid`, `CC1`, `HPI1`, `QUALITY1` , `TIMING1`, `DURATION1`, `CONTEXT1`, `SEVERITY1`, `MODIFY1`, `ASSOCIATED1` , `LOCATION1` , `CHRONIC1` , `CHRONIC2` , `CHRONIC3` , `CC2` , `HPI2` , `QUALITY2` , `TIMING2` , `DURATION2` , `CONTEXT2` , `SEVERITY2` , `MODIFY2` , `ASSOCIATED2` , `LOCATION2` , `CC3` , `HPI3` , `QUALITY3` , `TIMING3` , `DURATION3` ,  `CONTEXT3` , `SEVERITY3` , `MODIFY3` , `ASSOCIATED3` , `LOCATION3`
  from `form_eye_mag`;

INSERT INTO `form_eye_ros` ( `id`,`pid`,`ROSGENERAL`, `ROSHEENT`, `ROSCV`, `ROSPULM`, `ROSGI`, `ROSGU`,`ROSDERM`, `ROSNEURO` , `ROSPSYCH` , `ROSMUSCULO`, `ROSIMMUNO`, `ROSENDOCRINE` )
  select `id`,`pid`,`ROSGENERAL`, `ROSHEENT`, `ROSCV`, `ROSPULM`, `ROSGI`, `ROSGU`,
    `ROSDERM`, `ROSNEURO` , `ROSPSYCH` , `ROSMUSCULO`, `ROSIMMUNO`, `ROSENDOCRINE` from `form_eye_mag`;

INSERT INTO `form_eye_vitals` (`id`,`pid`,`alert`,`oriented`,`confused`,`ODIOPAP`,`OSIOPAP`,`ODIOPTPN`,`OSIOPTPN`,`ODIOPFTN`,`OSIOPFTN`,`IOPTIME`,`ODIOPPOST`,`OSIOPPOST`,`IOPPOSTTIME`,`ODIOPTARGET`,`OSIOPTARGET`,`AMSLEROD`,`AMSLEROS`,`ODVF1`,`ODVF2`,`ODVF3`,`ODVF4`,`OSVF1`,`OSVF2`,`OSVF3`,`OSVF4`)
  SELECT `id`,`pid`,`alert`,`oriented`,`confused`,`ODIOPAP`,`OSIOPAP`,`ODIOPTPN`,`OSIOPTPN`,`ODIOPFTN`,`OSIOPFTN`,`IOPTIME`,`ODIOPPOST`,`OSIOPPOST`,`IOPPOSTTIME`,`ODIOPTARGET`,`OSIOPTARGET`,`AMSLEROD`,`AMSLEROS`,`ODVF1`,`ODVF2`,`ODVF3`,`ODVF4`,`OSVF1`,`OSVF2`,`OSVF3`,`OSVF4`
  FROM `form_eye_mag`;

INSERT INTO `form_eye_acuity` ( `id`,`pid`,`SCODVA`, `SCOSVA`, `PHODVA`, `PHOSVA`, `CTLODVA`, `CTLOSVA`, `MRODVA`, `MROSVA`, `SCNEARODVA`, `SCNEAROSVA`, `MRNEARODVA`, `MRNEAROSVA`, `GLAREODVA`, `GLAREOSVA`, `GLARECOMMENTS`, `ARODVA`, `AROSVA`, `CRODVA`, `CROSVA`, `CTLODVA1`, `CTLOSVA1`, `PAMODVA`, `PAMOSVA`, `LIODVA`, `LIOSVA`)
  SELECT  `id`,`pid`,`SCODVA`, `SCOSVA`, `PHODVA`, `PHOSVA`, `CTLODVA`, `CTLOSVA`, `MRODVA`, `MROSVA`, `SCNEARODVA`, `SCNEAROSVA`, `MRNEARODVA`, `MRNEAROSVA`, `GLAREODVA`, `GLAREOSVA`, `GLARECOMMENTS`, `ARODVA`, `AROSVA`, `CRODVA`, `CROSVA`, `CTLODVA1`, `CTLOSVA1`, `PAMODVA`, `PAMOSVA`, `LIODVA`, `LIOSVA`
  from `form_eye_mag`;

INSERT INTO `form_eye_refraction` (  `id`, `pid`,`MRODSPH`, `MRODCYL`, `MRODAXIS`, `MRODPRISM`, `MRODBASE`, `MRODADD`, `MROSSPH`, `MROSCYL`, `MROSAXIS`, `MROSPRISM`, `MROSBASE`, `MROSADD`, `MRODNEARSPHERE`, `MRODNEARCYL`, `MRODNEARAXIS`, `MRODPRISMNEAR`, `MRODBASENEAR`, `MROSNEARSHPERE`, `MROSNEARCYL`, `MROSNEARAXIS`, `MROSPRISMNEAR`, `MROSBASENEAR`, `CRODSPH`, `CRODCYL`, `CRODAXIS`, `CROSSPH`, `CROSCYL`, `CROSAXIS`, `CRCOMMENTS`, `BALANCED`, `ARODSPH`, `ARODCYL`, `ARODAXIS`, `AROSSPH`, `AROSCYL`, `AROSAXIS`, `ARODADD`, `AROSADD`, `ARNEARODVA`, `ARNEAROSVA`, `ARODPRISM`, `AROSPRISM`, `CTLODSPH`, `CTLODCYL`, `CTLODAXIS`, `CTLODBC`, `CTLODDIAM`, `CTLOSSPH`, `CTLOSCYL`, `CTLOSAXIS`, `CTLOSBC`, `CTLOSDIAM`, `CTL_COMMENTS`, `CTLMANUFACTUREROD`, `CTLSUPPLIEROD`, `CTLBRANDOD`, `CTLMANUFACTUREROS`, `CTLSUPPLIEROS`, `CTLBRANDOS`, `CTLODADD`, `CTLOSADD`, `NVOCHECKED`, `ADDCHECKED`)
  SELECT  `id`, `pid`,`MRODSPH`, `MRODCYL`, `MRODAXIS`, `MRODPRISM`, `MRODBASE`, `MRODADD`, `MROSSPH`, `MROSCYL`, `MROSAXIS`, `MROSPRISM`, `MROSBASE`, `MROSADD`, `MRODNEARSPHERE`, `MRODNEARCYL`, `MRODNEARAXIS`, `MRODPRISMNEAR`, `MRODBASENEAR`, `MROSNEARSHPERE`, `MROSNEARCYL`, `MROSNEARAXIS`, `MROSPRISMNEAR`, `MROSBASENEAR`, `CRODSPH`, `CRODCYL`, `CRODAXIS`, `CROSSPH`, `CROSCYL`, `CROSAXIS`, `CRCOMMENTS`, `BALANCED`, `ARODSPH`, `ARODCYL`, `ARODAXIS`, `AROSSPH`, `AROSCYL`, `AROSAXIS`, `ARODADD`, `AROSADD`, `ARNEARODVA`, `ARNEAROSVA`, `ARODPRISM`, `AROSPRISM`, `CTLODSPH`, `CTLODCYL`, `CTLODAXIS`, `CTLODBC`, `CTLODDIAM`, `CTLOSSPH`, `CTLOSCYL`, `CTLOSAXIS`, `CTLOSBC`, `CTLOSDIAM`, `CTL_COMMENTS`, `CTLMANUFACTUREROD`, `CTLSUPPLIEROD`, `CTLBRANDOD`, `CTLMANUFACTUREROS`, `CTLSUPPLIEROS`, `CTLBRANDOS`, `CTLODADD`, `CTLOSADD`, `NVOCHECKED`, `ADDCHECKED`
  from `form_eye_mag`;

INSERT INTO `form_eye_biometrics` (`id`, `pid`, `ODK1`, `ODK2`, `ODK2AXIS`, `OSK1`, `OSK2`, `OSK2AXIS`, `ODAXIALLENGTH`, `OSAXIALLENGTH`, `ODPDMeasured`, `OSPDMeasured`, `ODACD`, `OSACD`, `ODW2W`, `OSW2W`, `ODLT`, `OSLT`)
  select `id`, `pid`, `ODK1`, `ODK2`, `ODK2AXIS`, `OSK1`, `OSK2`, `OSK2AXIS`, `ODAXIALLENGTH`, `OSAXIALLENGTH`, `ODPDMeasured`, `OSPDMeasured`, `ODACD`, `OSACD`, `ODW2W`, `OSW2W`, `ODLT`, `OSLT`
  from `form_eye_mag`;

INSERT INTO `form_eye_external` (`id`, `pid`, `RUL`, `LUL`, `RLL`, `LLL`, `RBROW`, `LBROW`, `RMCT`, `LMCT`, `RADNEXA`, `LADNEXA`, `RMRD`, `LMRD`, `RLF`, `LLF`, `RVFISSURE`, `LVFISSURE`, `ODHERTEL`, `OSHERTEL`, `HERTELBASE`, `RCAROTID`, `LCAROTID`, `RTEMPART`, `LTEMPART`, `RCNV`, `LCNV`, `RCNVII`, `LCNVII`, `EXT_COMMENTS`)
  SELECT  `id`, `pid`, `RUL`, `LUL`, `RLL`, `LLL`, `RBROW`, `LBROW`, `RMCT`, `LMCT`, `RADNEXA`, `LADNEXA`, `RMRD`, `LMRD`, `RLF`, `LLF`, `RVFISSURE`, `LVFISSURE`, `ODHERTEL`, `OSHERTEL`, `HERTELBASE`, `RCAROTID`, `LCAROTID`, `RTEMPART`, `LTEMPART`, `RCNV`, `LCNV`, `RCNVII`, `LCNVII`, `EXT_COMMENTS`
  from `form_eye_mag`;

INSERT INTO `form_eye_antseg` (`id`, `pid`, `ODSCHIRMER1`, `OSSCHIRMER1`, `ODSCHIRMER2`, `OSSCHIRMER2`, `OSCONJ`, `ODCONJ`, `ODCORNEA`, `OSCORNEA`, `ODAC`, `OSAC`, `ODLENS`, `OSLENS`, `ODIRIS`, `OSIRIS`, `PUPIL_NORMAL`, `ODPUPILSIZE1`, `ODPUPILSIZE2`, `ODPUPILREACTIVITY`, `ODAPD`, `OSPUPILSIZE1`, `OSPUPILSIZE2`, `OSPUPILREACTIVITY`, `OSAPD`, `DIMODPUPILSIZE1`, `DIMODPUPILSIZE2`, `DIMODPUPILREACTIVITY`, `DIMOSPUPILSIZE1`, `DIMOSPUPILSIZE2`, `DIMOSPUPILREACTIVITY`, `PUPIL_COMMENTS`, `ODKTHICKNESS`, `OSKTHICKNESS`, `ODGONIO`, `OSGONIO`, `ANTSEG_COMMENTS`)
  SELECT `id`, `pid`, `ODSCHIRMER1`, `OSSCHRIMER1`, `ODSCHRIMER2`, `OSSCHRIMER2`, `OSCONJ`, `ODCONJ`, `ODCORNEA`, `OSCORNEA`, `ODAC`, `OSAC`, `ODLENS`, `OSLENS`, `ODIRIS`, `OSIRIS`, `PUPIL_NORMAL`, `ODPUPILSIZE1`, `ODPUPILSIZE2`, `ODPUPILREACTIVITY`, `ODAPD`, `OSPUPILSIZE1`, `OSPUPILSIZE2`, `OSPUPILREACTIVITY`, `OSAPD`, `DIMODPUPILSIZE1`, `DIMODPUPILSIZE2`, `DIMODPUPILREACTIVITY`, `DIMOSPUPILSIZE1`, `DIMOSPUPILSIZE2`, `DIMOSPUPILREACTIVITY`, `PUPIL_COMMENTS`, `ODKTHICKNESS`, `OSKTHICKNESS`, `ODGONIO`, `OSGONIO`, `ANTSEG_COMMENTS`
from `form_eye_mag`;

INSERT INTO `form_eye_postseg` (`id`, `pid`, `ODDISC`, `OSDISC`, `ODCUP`, `OSCUP`, `ODMACULA`, `OSMACULA`, `ODVESSELS`, `OSVESSELS`, `ODPERIPH`, `OSPERIPH`, `ODCMT`, `OSCMT`, `RETINA_COMMENTS`, `DIL_RISKS`, `WETTYPE`, `ATROPINE`, `CYCLOMYDRIL`, `TROPICAMIDE`, `CYCLOGYL`, `NEO25`)
  SELECT `id`, `pid`, `ODDISC`, `OSDISC`, `ODCUP`, `OSCUP`, `ODMACULA`, `OSMACULA`, `ODVESSELS`, `OSVESSELS`, `ODPERIPH`, `OSPERIPH`, `ODCMT`, `OSCMT`, `RETINA_COMMENTS`, `DIL_RISKS`, `WETTYPE`, `ATROPINE`, `CYCLOMYDRIL`, `TROPICAMIDE`, `CYCLOGYL`, `NEO25`
  from `form_eye_mag`;

INSERT INTO `form_eye_neuro` (`id`, `pid`, `ACT`, `ACT5CCDIST`, `ACT1CCDIST`, `ACT2CCDIST`, `ACT3CCDIST`, `ACT4CCDIST`, `ACT6CCDIST`, `ACT7CCDIST`, `ACT8CCDIST`, `ACT9CCDIST`, `ACT10CCDIST`, `ACT11CCDIST`, `ACT1SCDIST`, `ACT2SCDIST`, `ACT3SCDIST`, `ACT4SCDIST`, `ACT5SCDIST`, `ACT6SCDIST`, `ACT7SCDIST`, `ACT8SCDIST`, `ACT9SCDIST`, `ACT10SCDIST`, `ACT11SCDIST`, `ACT1SCNEAR`, `ACT2SCNEAR`, `ACT3SCNEAR`, `ACT4SCNEAR`, `ACT5CCNEAR`, `ACT6CCNEAR`, `ACT7CCNEAR`, `ACT8CCNEAR`, `ACT9CCNEAR`, `ACT10CCNEAR`, `ACT11CCNEAR`, `ACT5SCNEAR`, `ACT6SCNEAR`, `ACT7SCNEAR`, `ACT8SCNEAR`, `ACT9SCNEAR`, `ACT10SCNEAR`, `ACT11SCNEAR`, `ACT1CCNEAR`, `ACT2CCNEAR`, `ACT3CCNEAR`, `ACT4CCNEAR`, `MOTILITYNORMAL`, `MOTILITY_RS`, `MOTILITY_RI`, `MOTILITY_RR`, `MOTILITY_RL`, `MOTILITY_LS`, `MOTILITY_LI`, `MOTILITY_LR`, `MOTILITY_LL`, `MOTILITY_RRSO`, `MOTILITY_RLSO`, `MOTILITY_RRIO`, `MOTILITY_RLIO`, `MOTILITY_LRSO`, `MOTILITY_LLSO`, `MOTILITY_LRIO`, `MOTILITY_LLIO`, `NEURO_COMMENTS`, `STEREOPSIS`, `ODNPA`, `OSNPA`, `VERTFUSAMPS`, `DIVERGENCEAMPS`, `NPC`, `DACCDIST`, `DACCNEAR`, `CACCDIST`, `CACCNEAR`, `ODCOLOR`, `OSCOLOR`, `ODCOINS`, `OSCOINS`, `ODREDDESAT`, `OSREDDESAT`)
  SELECT `id`, `pid`, `ACT`, `ACT5CCDIST`, `ACT1CCDIST`, `ACT2CCDIST`, `ACT3CCDIST`, `ACT4CCDIST`, `ACT6CCDIST`, `ACT7CCDIST`, `ACT8CCDIST`, `ACT9CCDIST`, `ACT10CCDIST`, `ACT11CCDIST`, `ACT1SCDIST`, `ACT2SCDIST`, `ACT3SCDIST`, `ACT4SCDIST`, `ACT5SCDIST`, `ACT6SCDIST`, `ACT7SCDIST`, `ACT8SCDIST`, `ACT9SCDIST`, `ACT10SCDIST`, `ACT11SCDIST`, `ACT1SCNEAR`, `ACT2SCNEAR`, `ACT3SCNEAR`, `ACT4SCNEAR`, `ACT5CCNEAR`, `ACT6CCNEAR`, `ACT7CCNEAR`, `ACT8CCNEAR`, `ACT9CCNEAR`, `ACT10CCNEAR`, `ACT11CCNEAR`, `ACT5SCNEAR`, `ACT6SCNEAR`, `ACT7SCNEAR`, `ACT8SCNEAR`, `ACT9SCNEAR`, `ACT10SCNEAR`, `ACT11SCNEAR`, `ACT1CCNEAR`, `ACT2CCNEAR`, `ACT3CCNEAR`, `ACT4CCNEAR`, `MOTILITYNORMAL`, `MOTILITY_RS`, `MOTILITY_RI`, `MOTILITY_RR`, `MOTILITY_RL`, `MOTILITY_LS`, `MOTILITY_LI`, `MOTILITY_LR`, `MOTILITY_LL`, `MOTILITY_RRSO`, `MOTILITY_RLSO`, `MOTILITY_RRIO`, `MOTILITY_RLIO`, `MOTILITY_LRSO`, `MOTILITY_LLSO`, `MOTILITY_LRIO`, `MOTILITY_LLIO`, `NEURO_COMMENTS`, `STEREOPSIS`, `ODNPA`, `OSNPA`, `VERTFUSAMPS`, `DIVERGENCEAMPS`, `NPC`, `DACCDIST`, `DACCNEAR`, `CACCDIST`, `CACCNEAR`, `ODCOLOR`, `OSCOLOR`, `ODCOINS`, `OSCOINS`, `ODREDDESAT`, `OSREDDESAT`
  from `form_eye_mag`;

INSERT INTO `form_eye_locking` (`id`, `pid`, `IMP`, `PLAN`, `Resource`, `Technician`, `LOCKED`, `LOCKEDDATE`, `LOCKEDBY`)
  SELECT `id`, `pid`, `IMP`, `PLAN`, `Resource`, `Technician`, `LOCKED`, `LOCKEDDATE`, `LOCKEDBY` FROM `form_eye_mag`;

DROP TABLE `form_eye_mag`;
#EndIf

#IfMissingColumn lists list_option_id
ALTER TABLE `lists` ADD `list_option_id` VARCHAR (100) DEFAULT NULL COMMENT 'Reference to list_options table';
#EndIf

#IfNotRow2D list_options list_id page_validation option_id messages#new_note
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `notes`, `activity`) VALUES ('page_validation', 'messages#new_note','/interface/main/messages/messages.php',150, '{form_datetime:{futureDate:{message: "Must be future date"}}, reply_to:{presence: {message: "Please choose a patient"}}}', 1);
#EndIf

#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2018-10-01 load_filename 2019-ICD-10-CM-Code-Descriptions.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2018-10-01', '2019-ICD-10-CM-Code-Descriptions.zip', 'b23e0128eb2dce0cb007c31638a8dc00');
#EndIf
#IfNotRow4D supported_external_dataloads load_type ICD10 load_source CMS load_release_date 2018-10-01 load_filename 2019-ICD-10-PCS-Order-File.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('ICD10', 'CMS', '2018-10-01', '2019-ICD-10-PCS-Order-File.zip', 'eb545fe61ada9efad0ad97a669f8671f');
#EndIf

#IfNotTable login_mfa_registrations
CREATE TABLE `login_mfa_registrations` (
  `user_id`         bigint(20)     NOT NULL,
  `name`            varchar(30)    NOT NULL,
  `last_challenge`  datetime       DEFAULT NULL,
  `method`          varchar(31)    NOT NULL COMMENT 'Q&A, U2F, TOTP etc.',
  `var1`            varchar(4096)  NOT NULL DEFAULT '' COMMENT 'Question, U2F registration etc.',
  `var2`            varchar(256)   NOT NULL DEFAULT '' COMMENT 'Answer etc.',
  PRIMARY KEY (`user_id`, `name`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn users_secure last_challenge_response
ALTER TABLE `users_secure` ADD COLUMN `last_challenge_response` datetime DEFAULT NULL;
#EndIf

#IfMissingColumn users_secure login_work_area
ALTER TABLE `users_secure` ADD COLUMN `login_work_area` text;
#EndIf

#IfNotColumnType onsite_messages sender_id VARCHAR(64)
ALTER TABLE `onsite_messages` CHANGE `sender_id` `sender_id` VARCHAR(64) NULL COMMENT 'who sent id';
#EndIf

#IfMissingColumn form_eye_mag_dispense CTLODQUANTITY
ALTER TABLE `form_eye_mag_dispense` ADD COLUMN `CTLODQUANTITY` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn form_eye_mag_dispense CTLOSQUANTITY
ALTER TABLE `form_eye_mag_dispense` ADD COLUMN `CTLOSQUANTITY` varchar(255) DEFAULT NULL;
#EndIf

#IfMissingColumn medex_prefs status
ALTER TABLE `medex_prefs` ADD COLUMN `status` text;
#EndIf

UPDATE `list_options` SET `notes`='{"form_title":{"presence": true}}' WHERE `list_id`='page_validation' AND `option_id`='add_edit_issue#theform';
UPDATE `list_options` SET `notes`='{"pc_catid":{"exclusion": ["_blank"]}}' WHERE `list_id`='page_validation' AND `option_id`='common#new_encounter';
UPDATE `list_options` SET `notes`='{"form_patient":{"presence": {"message": "Patient Name Required"}}}' WHERE `list_id`='page_validation' AND `option_id`='add_edit_event#theform';
UPDATE `list_options` SET `notes`='{"rumple":{"presence": {"message":"Required field missing: Please enter the User Name"}}, "stiltskin":{"presence": {"message":"Please enter the password"}}, "fname":{"presence": {"message":"Required field missing: Please enter the First name"}}, "lname":{"presence": {"message":"Required field missing: Please enter the Last name"}}}' WHERE `list_id`='page_validation' AND `option_id`='usergroup_admin_add#new_user';
UPDATE `list_options` SET `notes`='{"fname":{"presence": {"message":"Required field missing: Please enter the First name"}}, "lname":{"presence": {"message":"Required field missing: Please enter the Last name"}}}' WHERE `list_id`='page_validation' AND `option_id`='user_admin#user_form';
UPDATE `list_options` SET `notes`='{"facility":{"presence": true}, "ncolor":{"presence": true}}' WHERE `list_id`='page_validation' AND `option_id`='facility_admin#facility-form';
UPDATE `list_options` SET `notes`='{"facility":{"presence": true}, "ncolor":{"presence": true}}' WHERE `list_id`='page_validation' AND `option_id`='facilities_add#facility-add';
UPDATE `list_options` SET `notes`='{"group_name":{"presence": true}}' WHERE `list_id`='page_validation' AND `option_id`='therapy_groups_add#addGroup';
UPDATE `list_options` SET `notes`='{"group_name":{"presence": true}}' WHERE `list_id`='page_validation' AND `option_id`='therapy_groups_edit#editGroup';
UPDATE `list_options` SET `notes`='{"participant_name":{"presence": true}, "group_patient_start":{"presence": true}}' WHERE `list_id`='page_validation' AND `option_id`='tg_add#add-participant-form';
UPDATE `list_options` SET `notes`='{"pc_catid":{"exclusion": ["_blank"]}}' WHERE `list_id`='page_validation' AND `option_id`='common#new-encounter-form';
UPDATE `list_options` SET `notes`='{"form_group":{"presence": true}}' WHERE `list_id`='page_validation' AND `option_id`='add_edit_event#theform_groups';
UPDATE `list_options` SET `notes`='{"form_datetime":{"futureDate":{"message": "Must be future date"}}, "reply_to":{"presence": {"message": "Please choose a patient"}}}' WHERE `list_id`='page_validation' AND `option_id`='messages#new_note';

#IfNotTable api_token
CREATE TABLE `api_token` (
    `id`           bigint(20) NOT NULL AUTO_INCREMENT,
    `user_id`      bigint(20) NOT NULL,
    `token`        varchar(256) DEFAULT NULL,
    `expiry`       datetime NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
#EndIf

#IfMissingColumn pnotes update_by
ALTER TABLE `pnotes` ADD `update_by` bigint(20) default NULL;
#EndIf

#IfMissingColumn pnotes update_date
ALTER TABLE `pnotes` ADD `update_date` DATETIME DEFAULT NULL;
#EndIf

#IfNotColumnType onsite_documents full_document MEDIUMBLOB
ALTER TABLE `onsite_documents` CHANGE `full_document` `full_document` MEDIUMBLOB;
#EndIf

#IfMissingColumn facility mail_street
ALTER TABLE `facility` ADD `mail_street` VARCHAR(30) default NULL;
#EndIf

#IfMissingColumn facility mail_street2
ALTER TABLE `facility` ADD `mail_street2` VARCHAR(30) default NULL;
#EndIf

#IfMissingColumn facility mail_city
ALTER TABLE `facility` ADD `mail_city` VARCHAR(50) default NULL;
#EndIf

#IfMissingColumn facility mail_state
ALTER TABLE `facility` ADD `mail_state` VARCHAR(3) default NULL;
#EndIf

#IfMissingColumn facility mail_zip
ALTER TABLE `facility` ADD `mail_zip` VARCHAR(10) default NULL;
#EndIf

#IfMissingColumn facility oid
ALTER TABLE `facility` ADD `oid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'HIEs CCDA and FHIR an OID is required/wanted';
#EndIf

#IfNotTable keys
CREATE TABLE `keys` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB;
#EndIf

#IfNotColumnType amendments pid bigint(20)
ALTER TABLE `amendments`
    MODIFY `pid` bigint(20) NOT NULL COMMENT 'Patient ID from patient_data';
#EndIf

#IfNotColumnType billing pid bigint(20)
ALTER TABLE `billing`
    MODIFY `pid` bigint(20) default NULL;
#EndIf

#IfNotColumnType dated_reminders pid bigint(20)
ALTER TABLE `dated_reminders`
    MODIFY `pid` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType drug_sales pid bigint(20)
ALTER TABLE `drug_sales`
    MODIFY `pid` bigint(20) NOT NULL default '0';
#EndIf

#IfNotColumnType form_ros pid bigint(20)
ALTER TABLE `form_ros`
    MODIFY `pid` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType issue_encounter pid bigint(20)
ALTER TABLE `issue_encounter`
    MODIFY `pid` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType onsite_documents pid bigint(20) unsigned
ALTER TABLE `onsite_documents`
    MODIFY `pid` bigint(20) UNSIGNED default NULL;
#EndIf

#IfNotColumnType patient_access_onsite pid bigint(20)
ALTER TABLE `patient_access_onsite`
    MODIFY `pid` bigint(20);
#EndIf

#IfNotColumnType patient_access_offsite pid bigint(20)
ALTER TABLE `patient_access_offsite`
    MODIFY `pid` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType form_eye_mag_wearing PID bigint(20)
ALTER TABLE `form_eye_mag_wearing`
    MODIFY `PID` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType therapy_groups_participants pid bigint(20)
ALTER TABLE `therapy_groups_participants`
    MODIFY `pid` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType therapy_groups_participant_attendance pid bigint(20)
ALTER TABLE `therapy_groups_participant_attendance`
    MODIFY `pid` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType notification_log pid bigint(20)
ALTER TABLE `notification_log`
    MODIFY `pid` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType documents foreign_id bigint(20)
ALTER TABLE `documents`
    MODIFY `foreign_id` bigint(20) default NULL;
#EndIf

#IfNotColumnType batchcom patient_id bigint(20)
ALTER TABLE `batchcom`
    MODIFY `patient_id` bigint(20) NOT NULL default '0';
#EndIf

#IfNotColumnType claims patient_id bigint(20)
ALTER TABLE `claims`
    MODIFY `patient_id` bigint(20) NOT NULL;
#EndIf

#IfNotColumnType immunizations patient_id bigint(20)
ALTER TABLE `immunizations`
    MODIFY `patient_id` bigint(20) default NULL;
#EndIf

#IfNotColumnType prescriptions patient_id bigint(20)
ALTER TABLE `prescriptions`
    MODIFY `patient_id` bigint(20) default NULL;
#EndIf

#IfNotColumnType ar_session patient_id bigint(20)
ALTER TABLE `ar_session`
    MODIFY `patient_id` bigint(20) NOT NULL;
#EndIf

#IfMissingColumn documents encrypted
ALTER TABLE `documents` ADD `encrypted` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '0->No,1->Yes';
#EndIf

#IfNotRow4D supported_external_dataloads load_type CQM_VALUESET load_source NIH_VSAC load_release_date 2017-09-29 load_filename ep_ec_only_cms_20170929.xml.zip
INSERT INTO `supported_external_dataloads` (`load_type`, `load_source`, `load_release_date`, `load_filename`, `load_checksum`) VALUES ('CQM_VALUESET', 'NIH_VSAC', '2017-09-29','ep_ec_only_cms_20170929.xml.zip','38d2e1a27646f2f09fcc389fd2335c50');
#EndIf

#IfNotColumnType eligibility_verification response_id varchar(32)
ALTER TABLE `eligibility_verification` CHANGE `response_id` `response_id` VARCHAR(32) DEFAULT NULL;
#EndIf

#IfNotTable benefit_eligibility
CREATE TABLE `benefit_eligibility` (
    `response_id` bigint(20) NOT NULL,
    `verification_id` bigint(20) NOT NULL,
    `type` varchar(4) DEFAULT NULL,
    `benefit_type` varchar(255) DEFAULT NULL,
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `coverage_level` varchar(255) DEFAULT NULL,
    `coverage_type` varchar(512) DEFAULT NULL,
    `plan_type` varchar(255) DEFAULT NULL,
    `plan_description` varchar(255) DEFAULT NULL,
    `coverage_period` varchar(255) DEFAULT NULL,
    `amount` decimal(5,2) DEFAULT NULL,
    `percent` decimal(3,2) DEFAULT NULL,
    `network_ind` varchar(2) DEFAULT NULL,
    `message` varchar(512) DEFAULT NULL,
    `response_status` enum('A','D') DEFAULT 'A',
    `response_create_date` date DEFAULT NULL,
    `response_modify_date` date DEFAULT NULL
) ENGINE=InnoDB;
#EndIf

#IfTable eligibility_response
DROP TABLE `eligibility_response`;
#EndIf

#IfTable x12_partners
ALTER TABLE `x12_partners` CHANGE `processing_format` `processing_format` ENUM('standard','medi-cal','cms','proxymed','oa_eligibility','availity_eligibility') DEFAULT NULL;
#EndIf

#IfMissingColumn insurance_companies eligibility_id
ALTER TABLE `insurance_companies` ADD `eligibility_id` VARCHAR(32) DEFAULT NULL;
#EndIf

#IfMissingColumn insurance_companies x12_default_eligibility_id
ALTER TABLE `insurance_companies` ADD `x12_default_eligibility_id` INT(11)  DEFAULT NULL;
#EndIf

#IfMissingColumn users_secure login_fail_counter
ALTER TABLE `users_secure` ADD `login_fail_counter` INT(11) DEFAULT '0';
#EndIf

#IfMissingColumn x12_partners x12_dtp03
ALTER TABLE `x12_partners` ADD `x12_dtp03` CHAR(1) DEFAULT 'A';
#EndIf

#IfMissingColumn procedure_order order_diagnosis
ALTER TABLE `procedure_order` ADD `order_diagnosis` VARCHAR(255) DEFAULT '';
#EndIf

#IfTable erx_drug_paid
DROP TABLE `erx_drug_paid`;
#EndIf

#IfNotTable erx_weno_drugs
CREATE TABLE `erx_weno_drugs` (
  `drug_id` int(11) NOT NULL AUTO_INCREMENT,
  `rxcui_drug_coded` int(11) DEFAULT NULL,
  `generic_rxcui` int(11) DEFAULT NULL,
  `drug_db_code_qualifier` text,
  `full_name` varchar(250) NOT NULL,
  `rxn_dose_form` text,
  `full_generic_name` varchar(250) NOT NULL,
  `brand_name` varchar(250) NOT NULL,
  `display_name` varchar(250) NOT NULL,
  `route` text,
  `new_dose_form` varchar(100) DEFAULT NULL,
  `strength` varchar(15) DEFAULT NULL,
  `supress_for` text,
  `display_name_synonym` text,
  `is_retired` text,
  `sxdg_rxcui` varchar(10) DEFAULT NULL,
  `sxdg_tty` text,
  `sxdg_name` varchar(100) DEFAULT NULL,
  `psn_drugdescription` varchar(100) DEFAULT NULL,
  `ncpdp_quantity_term` text,
  `potency_unit_code` varchar(10) DEFAULT NULL,
  `dea_schedule_no` int(2) DEFAULT NULL,
  `dea_schedule` varchar(7) DEFAULT NULL,
  `ingredients` varchar(100) DEFAULT NULL,
  `drug_interaction` varchar(100) DEFAULT NULL,
  `unit_source_code` varchar(3) DEFAULT NULL,
  `code_list_qualifier` int(3) DEFAULT NULL,
  PRIMARY KEY (`drug_id`)
) ENGINE=InnoDB;
#EndIf

#IfNotWenoRx
#EndIf

#IfTable openemr_postcalendar_limits
DROP TABLE `openemr_postcalendar_limits`;
#EndIf

#IfTable openemr_postcalendar_topics
DROP TABLE `openemr_postcalendar_topics`;
#EndIf

#IfTable openemr_session_info
DROP TABLE `openemr_session_info`;
#EndIf

#IfTable array
DROP TABLE `array`;
#EndIf

#IfTable config
DROP TABLE `config`;
#EndIf

#IfTable config_seq
DROP TABLE `config_seq`;
#EndIf

#IfTable geo_country_reference
DROP TABLE `geo_country_reference`;
#EndIf

#IfTable geo_zone_reference
DROP TABLE `geo_zone_reference`;
#EndIf

#IfMissingColumn form_eye_acuity BINOCVA
ALTER TABLE `form_eye_acuity`  ADD `BINOCVA` varchar(25) DEFAULT NULL;
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id ODVITREOUS_0
UPDATE `list_options` SET `seq`= 1022 WHERE `list_id`='Eye_QP_RETINA_defaults' AND `option_id`='ODPERIPH_0';
UPDATE `list_options` SET `seq`= 1024 WHERE `list_id`='Eye_QP_RETINA_defaults' AND `option_id`='OSPERIPH_0';
UPDATE `list_options` SET `seq`= 1026 WHERE `list_id`='Eye_QP_RETINA_defaults' AND `option_id`='OUPERIPH_0';
UPDATE `list_options` SET `title`= 'clear', `seq` = 505 WHERE `list_id`='Eye_Defaults_for_GENERAL' AND `option_id`='ODPERIPH';
UPDATE `list_options` SET `title`= 'clear', `seq` = 515 WHERE `list_id`='Eye_Defaults_for_GENERAL' AND `option_id`='OSPERIPH';
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'ODVITREOUS_0', 'vit: clear field', 910, 0, 0, 'VITREOUS', '', '', 0, 0, 1, 'OD');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id OSVITREOUS_0
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'OSVITREOUS_0', 'vit: clear field', 920, 0, 0, 'VITREOUS', '', '', 0, 0, 1, 'OS');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id OUVITREOUS_0
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'OUVITREOUS_0', 'vit: clear field', 930, 0, 0, 'VITREOUS', '', '', 0, 0, 1, 'OU');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id ODVITREOUS_float
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'ODVITREOUS_float', 'vit: floater', 940, 0, 0, 'VITREOUS', 'vitreous floater', '', 0, 0, 0, 'OD');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id OSVITREOUS_float
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'OSVITREOUS_float', 'vit: floater', 950, 0, 0, 'VITREOUS', 'vitreous floater', '', 0, 0, 0, 'OS');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id OUVITREOUS_float
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'OUVITREOUS_float', 'vit: floater', 960, 0, 0, 'VITREOUS', 'vitreous floater', '', 0, 0, 0, 'OU');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id ODVITREOUS_pvd
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'ODVITREOUS_pvd', 'vit: PVD', 970, 0, 0, 'VITREOUS', 'PVD', '', 0, 0, 0, 'OD');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id OSVITREOUS_pvd
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'OSVITREOUS_pvd', 'vit: PVD', 980, 0, 0, 'VITREOUS', 'PVD', '', 0, 0, 0, 'OS');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id OUVITREOUS_pvd
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'OUVITREOUS_pvd', 'vit: PVD', 990, 0, 0, 'VITREOUS', 'PVD', '', 0, 0, 0, 'OU');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id ODVITREOUS_vh
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'ODVITREOUS_vh', 'vit: hemorrhage', 1000, 0, 0, 'VITREOUS', 'vitreous hemorrhage', '', 0, 0, 0, 'OD');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id OSVITREOUS_vh
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'OSVITREOUS_vh', 'vit: hemorrhage', 1010, 0, 0, 'VITREOUS', 'vitreous hemorrhage', '', 0, 0, 0, 'OS');
#EndIf

#IfNotRow2D list_options list_id Eye_QP_RETINA_defaults option_id OUVITREOUS_vh
INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`, `toggle_setting_1`, `toggle_setting_2`, `activity`, `subtype`) VALUES
('Eye_QP_RETINA_defaults', 'OUVITREOUS_vh', 'vit: hemorrhage', 1020, 0, 0, 'VITREOUS', 'vitreous hemorrhage', '', 0, 0, 0, 'OU');
#EndIf
