CREATE TABLE IF NOT EXISTS procedure_order (
  `procedure_order_id`     bigint(20)   NOT NULL AUTO_INCREMENT,
  `procedure_type_id`      bigint(20)   NOT NULL            COMMENT 'references procedure_type.procedure_type_id',
  `provider_id`            bigint(20)   NOT NULL DEFAULT 0  COMMENT 'references users.id',
  `patient_id`             bigint(20)   NOT NULL            COMMENT 'references patient_data.pid',
  `encounter_id`           bigint(20)   NOT NULL DEFAULT 0  COMMENT 'references form_encounter.encounter',
  `date_collected`         datetime     DEFAULT NULL        COMMENT 'time specimen collected',
  `date_ordered`           date         DEFAULT NULL,
  `order_priority`         varchar(31)  NOT NULL DEFAULT '',
  `order_status`           varchar(31)  NOT NULL DEFAULT '' COMMENT 'pending,routed,complete,canceled',
  `patient_instructions`   text         NOT NULL DEFAULT '',
  `activity`               tinyint(1)   NOT NULL DEFAULT 1  COMMENT '0 if deleted',
  PRIMARY KEY (`procedure_order_id`)
) ENGINE=MyISAM;

