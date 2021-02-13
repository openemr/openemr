CREATE TABLE IF NOT EXISTS `form_assessment_form`
(
    `id`                        bigint(20) NOT NULL,
    `date`                      datetime     DEFAULT NULL,
    `pid`                       bigint(20)   DEFAULT '0',
    `user`                      varchar(255) DEFAULT NULL,
    `groupname`                 varchar(255) DEFAULT NULL,
    `authorized`                tinyint(4)   DEFAULT '0',
    `activity`                  tinyint(4)   DEFAULT '0',
    `chief_complaint`           text,
    `history_of_present_illnes` text,
    `review_of_systems`         text,
    `past_medical_history`      text,
    `social_history`            varchar(200) DEFAULT NULL,
    `social_history_pks_day`    float        DEFAULT NULL,
    `social_history_yrs_smkd`   float        DEFAULT NULL,
    `social_history_desc`       text,
    `family_history`            tinyint(4)   DEFAULT '0',
    `family_history_desc`       text,
    `allergies`                 text,
    `current_medications`       text,
    `vital_weight`              varchar(50)  DEFAULT NULL,
    `vital_height`              varchar(50)  DEFAULT NULL,
    `vital_temp`                varchar(50)  DEFAULT NULL,
    `vital_bp1`                 varchar(50)  DEFAULT NULL,
    `vital_bp2`                 varchar(50)  DEFAULT NULL,
    `vital_pulse`               varchar(50)  DEFAULT NULL,
    `vital_rr`                  varchar(50)  DEFAULT NULL,
    `vital_bmi`                 varchar(50)  DEFAULT NULL,
    `vital_sat`                 varchar(50)  DEFAULT NULL,
    `vital_on02`                varchar(50)  DEFAULT NULL,
    `physical_exam_desc`        text,
    `problem_list`              varchar(200) DEFAULT NULL,
    `assessment`                text,
    `plan`                      text,
    `vital_ht`                  varchar(10)  DEFAULT NULL,
    `vital_wt`                  varchar(10)  DEFAULT NULL,
    `vital_tp`                  varchar(10)  DEFAULT NULL,
    `surgical_procedure`        text,
    `in_clinic_tests`           longtext,
    `laborders`                 text,
    `feecode`                   text
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `order_for_test`
(
    `id`        bigint(20) NOT NULL AUTO_INCREMENT,
    `list_type` int(2)       DEFAULT '0',
    `list_name` varchar(255) DEFAULT NULL,
    `comments`  text,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `systems_template`
(
    `template_id`       bigint(20) NOT NULL AUTO_INCREMENT,
    `template_name`     varchar(100) DEFAULT NULL,
    `template_location` varchar(200) DEFAULT NULL,
    `field_description` text,
    PRIMARY KEY (`template_id`)
) ENGINE = InnoDB;
