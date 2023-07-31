<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

class PrescriptionValidator extends BaseValidator
{
    protected function configureValidator()
    {
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context) {
                $context->required("start_date", "Start Date")->datetime('Y-m-d');
                $context->required("route", 'Route')->string();
                $context->required("provider_id", "Patient Id")->numeric()->callback(function ($value) {
                    // check if patient exist
                    return $this->validateId('id', 'patient_data', $value);
                });

                $context->optional("encounter_uuid", "Encounter")->numeric()->callback(function ($value) {
                    return $this->validateId('id', 'form_encounter', $value);
                });

                $context->optional("drug", "Drug")->string();
                $context->optional("drug_id", "Drug Id")->numeric()->callback(function ($value) {
                    return $this->validateId('drug_id', 'drugs', $value);
                });

                $context->optional("quantity", "Drug Quantity")->string();
                $context->optional("form_id", "Drug Form")->string()->callback(function ($value) {
                    return $this->validateCode($value, 'list_options', 'drug_form');
                });

                $context->optional("route_id", "Drug Route")->string()->callback(function ($value) {
                    return $this->validateCode($value, 'list_options', 'drug_route');
                });

                $context->optional("interval_id", "Drug Interval")->string()->callback(function ($value) {
                    return $this->validateCode($value, 'list_options', 'drug_interval');
                });

                $context->optional("dosage", "Dosage")->string();
                $context->optional("size", "Size")->string();
                $context->optional("refills", "Refills")->string();
                $context->optional("per_refill", "Number of tablets")->string();
                $context->optional("note", "Note")->string();
                $context->optional("medication", "Add to medication list")->bool();
                $context->optional("substitute", "Substitute")->bool();
                $context->optional("rxnorm_drugcode", "rxnorm_drugcode")->bool();
                $context->optional("drug_dosage_instructions", "drug_dosage_instructions")->string();
            }
        );

        // update validations copied from insert
        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function (Validator $context) {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    function ($rules) {
                        foreach ($rules as $key => $chain) {
                            $chain->required(false);
                        }
                    }
                );
                // additional uuid validation
                $context->required("uuid", "Prescription UUID")->callback(function ($value) {
                    return $this->validateId("uuid", "prescriptions", $value, true);
                })->uuid();
            }
        );
    }
}
/** CREATE TABLE `prescriptions` (
 * `id` int(11) NOT NULL AUTO_INCREMENT,
 * `uuid` binary(16) DEFAULT NULL,
 * `patient_id` bigint(20) DEFAULT NULL,
 * `filled_by_id` int(11) DEFAULT NULL,
 * `pharmacy_id` int(11) DEFAULT NULL,
 * `date_added` datetime DEFAULT NULL COMMENT 'Datetime the prescriptions was initially created',
 * `date_modified` datetime DEFAULT NULL COMMENT 'Datetime the prescriptions was last modified',
 * `provider_id` int(11) DEFAULT NULL,
 * `encounter` int(11) DEFAULT NULL,
 * `start_date` date DEFAULT NULL,
 * `drug` varchar(150) DEFAULT NULL,
 * `drug_id` int(11) NOT NULL DEFAULT 0,
 * `rxnorm_drugcode` varchar(25) DEFAULT NULL,
 * `form` int(3) DEFAULT NULL,
 * `dosage` varchar(100) DEFAULT NULL,
 * `quantity` varchar(31) DEFAULT NULL,
 * `size` varchar(25) DEFAULT NULL,
 * `unit` int(11) DEFAULT NULL,
 * `route` varchar(100) DEFAULT NULL COMMENT 'Max size 100 characters is same max as immunizations',
 * `interval` int(11) DEFAULT NULL,
 * `substitute` int(11) DEFAULT NULL,
 * `refills` int(11) DEFAULT NULL,
 * `per_refill` int(11) DEFAULT NULL,
 * `filled_date` date DEFAULT NULL,
 * `medication` int(11) DEFAULT NULL,
 * `note` text DEFAULT NULL,
 * `active` int(11) NOT NULL DEFAULT 1,
 * `datetime` datetime DEFAULT NULL,
 * `user` varchar(50) DEFAULT NULL,
 * `site` varchar(50) DEFAULT NULL,
 * `prescriptionguid` varchar(50) DEFAULT NULL,
 * `erx_source` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0-OpenEMR 1-External',
 * `erx_uploaded` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0-Pending NewCrop upload 1-Uploaded to NewCrop',
 * `drug_info_erx` text DEFAULT NULL,
 * `external_id` varchar(20) DEFAULT NULL,
 * `end_date` date DEFAULT NULL,
 * `indication` text DEFAULT NULL,
 * `prn` varchar(30) DEFAULT NULL,
 * `ntx` int(2) DEFAULT NULL,
 * `rtx` int(2) DEFAULT NULL,
 * `txDate` date NOT NULL,
 * `usage_category` varchar(100) DEFAULT NULL COMMENT 'option_id in list_options.list_id=medication-usage-category',
 * `usage_category_title` varchar(255) NOT NULL COMMENT 'title in list_options.list_id=medication-usage-category',
 * `request_intent` varchar(100) DEFAULT NULL COMMENT 'option_id in list_options.list_id=medication-request-intent',
 * `request_intent_title` varchar(255) NOT NULL COMMENT 'title in list_options.list_id=medication-request-intent',
 * `drug_dosage_instructions` longtext DEFAULT NULL COMMENT 'Medication dosage instructions',
 * `created_by` bigint(20) DEFAULT NULL COMMENT 'users.id the user that first created this record',
 * `updated_by` bigint(20) DEFAULT NULL COMMENT 'users.id the user that last modified this record',
 * PRIMARY KEY (`id`),
 * UNIQUE KEY `uuid` (`uuid`),
 * KEY `patient_id` (`patient_id`)
 * ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
 */
