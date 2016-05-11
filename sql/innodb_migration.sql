/**
 * Convert existing tables to InnoDB
 * Author:  Oleg Sverdlov, Amiel Elboim
 * Created: May 5, 2016
 * 
 *
 */
/*
ar_activity                     	ERROR 1075 (42000): Incorrect table definition; there can be only one auto column and it must be defined as a key
claims                          	ERROR 1075 (42000): Incorrect table definition; there can be only one auto column and it must be defined as a key
procedure_answers               	ERROR 1075 (42000): Incorrect table definition; there can be only one auto column and it must be defined as a key
procedure_order_code            	ERROR 1075 (42000): Incorrect table definition; there can be only one auto column and it must be defined as a key

batchcom                          ERROR 1067 (42000): Invalid default value for 'msg_date_sent'
drugs                           	ERROR 1067 (42000): Invalid default value for 'last_notify'
drug_inventory                  	ERROR 1067 (42000): Invalid default value for 'last_notify'
insurance_data                  	ERROR 1067 (42000): Invalid default value for 'date
openemr_postcalendar_events     	ERROR 1067 (42000): Invalid default value for 'pc_eventDate'
*/
set sql_mode='NO_ENGINE_SUBSTITUTION';

-- The following tables contain a DATE field with default value '0000-00-00'
ALTER TABLE `batchcom` ENGINE="InnoDB";
ALTER TABLE `drugs` ENGINE="InnoDB";
ALTER TABLE `drug_inventory` ENGINE="InnoDB";
ALTER TABLE `insurance_data` ENGINE="InnoDB";
ALTER TABLE `openemr_postcalendar_events` ENGINE="InnoDB";

-- The following tables require special treatment
ALTER TABLE `claims` MODIFY `version` int(10) UNSIGNED NOT NULL COMMENT 'Claim version, incremented in code';
ALTER TABLE `claims` ENGINE="InnoDB";
ALTER TABLE `procedure_answers` MODIFY `answer_seq` int(11) NOT NULL COMMENT 'Procedure_answers answer_seq, incremented in code';
ALTER TABLE `procedure_answers` ENGINE="InnoDB";
ALTER TABLE `procedure_order_code` MODIFY `procedure_order_seq` int(11) NOT NULL COMMENT 'Procedure_order_code procedure_order_seq, incremented in code';
ALTER TABLE `procedure_order_code` ENGINE="InnoDB";
ALTER TABLE `ar_activity` MODIFY `sequence_no` int(10) UNSIGNED NOT NULL COMMENT 'Ar_activity sequence_no, incremented in code';
ALTER TABLE `ar_activity` ENGINE="InnoDB";

-- The rest can be converted without problems.
ALTER TABLE `addresses` ENGINE="InnoDB";
ALTER TABLE `amc_misc_data` ENGINE="InnoDB";                   	
ALTER TABLE `amendments` ENGINE="InnoDB";                      	
ALTER TABLE `amendments_history` ENGINE="InnoDB";              	
ALTER TABLE `array` ENGINE="InnoDB";                      	
# ALTER TABLE `ar_activity` ENGINE="InnoDB";                     
ALTER TABLE `ar_session` ENGINE="InnoDB";                      	
ALTER TABLE `audit_details` ENGINE="InnoDB";                   	
ALTER TABLE `audit_master` ENGINE="InnoDB";                    	
ALTER TABLE `background_services` ENGINE="InnoDB";             	
# ALTER TABLE `batchcom` ENGINE="InnoDB";
ALTER TABLE `billing` ENGINE="InnoDB";
ALTER TABLE `categories` ENGINE="InnoDB";                      	
ALTER TABLE `categories_seq` ENGINE="InnoDB";                  	
ALTER TABLE `categories_to_documents` ENGINE="InnoDB";         	
ALTER TABLE `chart_tracker` ENGINE="InnoDB";                   	
# ALTER TABLE `claims` ENGINE="InnoDB";                          	
ALTER TABLE `clinical_plans` ENGINE="InnoDB";                  	
ALTER TABLE `clinical_plans_rules` ENGINE="InnoDB";            	
ALTER TABLE `clinical_rules` ENGINE="InnoDB";                  	
ALTER TABLE `clinical_rules_log` ENGINE="InnoDB";              	
ALTER TABLE `codes` ENGINE="InnoDB";                           	
ALTER TABLE `code_types` ENGINE="InnoDB";                      	
ALTER TABLE `config` ENGINE="InnoDB";                          	
ALTER TABLE `config_seq` ENGINE="InnoDB";                      	
ALTER TABLE `dated_reminders` ENGINE="InnoDB";                 	
ALTER TABLE `dated_reminders_link` ENGINE="InnoDB";            	
ALTER TABLE `direct_message_log` ENGINE="InnoDB";              	
ALTER TABLE `documents` ENGINE="InnoDB";                       	
ALTER TABLE `documents_legal_categories` ENGINE="InnoDB";      	
ALTER TABLE `documents_legal_detail` ENGINE="InnoDB";          	
ALTER TABLE `documents_legal_master` ENGINE="InnoDB";          	
# ALTER TABLE `drugs` ENGINE="InnoDB";                           	
# ALTER TABLE `drug_inventory` ENGINE="InnoDB";                  	
ALTER TABLE `drug_sales` ENGINE="InnoDB";                      	
ALTER TABLE `drug_templates` ENGINE="InnoDB";                  	
ALTER TABLE `eligibility_response` ENGINE="InnoDB";            	
ALTER TABLE `eligibility_verification` ENGINE="InnoDB";        	
ALTER TABLE `employer_data` ENGINE="InnoDB";                   	
ALTER TABLE `enc_category_map` ENGINE="InnoDB";                	
ALTER TABLE `extended_log` ENGINE="InnoDB";                    	
ALTER TABLE `facility` ENGINE="InnoDB";                        	
ALTER TABLE `facility_user_ids` ENGINE="InnoDB";               	
ALTER TABLE `fee_sheet_options` ENGINE="InnoDB";               	
ALTER TABLE `forms` ENGINE="InnoDB";                           	
ALTER TABLE `form_dictation` ENGINE="InnoDB";                  	
ALTER TABLE `form_encounter` ENGINE="InnoDB";                  	
ALTER TABLE `form_misc_billing_options` ENGINE="InnoDB";       	
ALTER TABLE `form_reviewofs` ENGINE="InnoDB";                  	
ALTER TABLE `form_ros` ENGINE="InnoDB";                        	
ALTER TABLE `form_soap` ENGINE="InnoDB";                       	
ALTER TABLE `form_vitals` ENGINE="InnoDB";                     	
ALTER TABLE `geo_country_reference` ENGINE="InnoDB";           	
ALTER TABLE `geo_zone_reference` ENGINE="InnoDB";              	
ALTER TABLE `globals` ENGINE="InnoDB";                         	
ALTER TABLE `gprelations` ENGINE="InnoDB";                     	
ALTER TABLE `groups` ENGINE="InnoDB";                          	
ALTER TABLE `history_data` ENGINE="InnoDB";                    	
ALTER TABLE `icd10_dx_order_code` ENGINE="InnoDB";             	
ALTER TABLE `icd10_gem_dx_10_9` ENGINE="InnoDB";               	
ALTER TABLE `icd10_gem_dx_9_10` ENGINE="InnoDB";               	
ALTER TABLE `icd10_gem_pcs_10_9` ENGINE="InnoDB";              	
ALTER TABLE `icd10_gem_pcs_9_10` ENGINE="InnoDB";              	
ALTER TABLE `icd10_pcs_order_code` ENGINE="InnoDB";            	
ALTER TABLE `icd10_reimbr_dx_9_10` ENGINE="InnoDB";            	
ALTER TABLE `icd10_reimbr_pcs_9_10` ENGINE="InnoDB";           	
ALTER TABLE `icd9_dx_code` ENGINE="InnoDB";                    	
ALTER TABLE `icd9_dx_long_code` ENGINE="InnoDB";               	
ALTER TABLE `icd9_sg_code` ENGINE="InnoDB";                    	
ALTER TABLE `icd9_sg_long_code` ENGINE="InnoDB";               	
ALTER TABLE `immunizations` ENGINE="InnoDB";                   	
ALTER TABLE `insurance_companies` ENGINE="InnoDB";             	
# ALTER TABLE `insurance_data` ENGINE="InnoDB";                  	
ALTER TABLE `insurance_numbers` ENGINE="InnoDB";               	
ALTER TABLE `integration_mapping` ENGINE="InnoDB";             	
ALTER TABLE `issue_encounter` ENGINE="InnoDB";                 	
ALTER TABLE `issue_types` ENGINE="InnoDB";                     	
ALTER TABLE `lang_constants` ENGINE="InnoDB";                  	
ALTER TABLE `lang_custom` ENGINE="InnoDB";                     	
ALTER TABLE `lang_definitions` ENGINE="InnoDB";                	
ALTER TABLE `lang_languages` ENGINE="InnoDB";                  	
ALTER TABLE `layout_options` ENGINE="InnoDB";                  	
ALTER TABLE `lbf_data` ENGINE="InnoDB";                        	
ALTER TABLE `lbt_data` ENGINE="InnoDB";                        	
ALTER TABLE `lists` ENGINE="InnoDB";                           	
ALTER TABLE `lists_touch` ENGINE="InnoDB";                     	
ALTER TABLE `list_options` ENGINE="InnoDB";                    	
ALTER TABLE `log` ENGINE="InnoDB";                             	
ALTER TABLE `notes` ENGINE="InnoDB";                           	
ALTER TABLE `onotes` ENGINE="InnoDB";                          	
ALTER TABLE `openemr_modules` ENGINE="InnoDB";                 	
ALTER TABLE `openemr_module_vars` ENGINE="InnoDB";             	
ALTER TABLE `openemr_postcalendar_categories` ENGINE="InnoDB"; 	
# ALTER TABLE `openemr_postcalendar_events` ENGINE="InnoDB";     	
ALTER TABLE `openemr_postcalendar_limits` ENGINE="InnoDB";     	
ALTER TABLE `openemr_postcalendar_topics` ENGINE="InnoDB";     	
ALTER TABLE `openemr_session_info` ENGINE="InnoDB";            	
ALTER TABLE `patient_access_offsite` ENGINE="InnoDB";          	
ALTER TABLE `patient_access_onsite` ENGINE="InnoDB";           	
ALTER TABLE `patient_data` ENGINE="InnoDB";                    	
ALTER TABLE `patient_reminders` ENGINE="InnoDB";               	
ALTER TABLE `patient_tracker` ENGINE="InnoDB";                 	
ALTER TABLE `patient_tracker_element` ENGINE="InnoDB";         	
ALTER TABLE `payments` ENGINE="InnoDB";                        	
ALTER TABLE `pharmacies` ENGINE="InnoDB";                      	
ALTER TABLE `phone_numbers` ENGINE="InnoDB";                   	
ALTER TABLE `pma_bookmark` ENGINE="InnoDB";                    	
ALTER TABLE `pma_column_info` ENGINE="InnoDB";                 	
ALTER TABLE `pma_history` ENGINE="InnoDB";                     	
ALTER TABLE `pma_pdf_pages` ENGINE="InnoDB";                   	
ALTER TABLE `pma_relation` ENGINE="InnoDB";                    	
ALTER TABLE `pma_table_coords` ENGINE="InnoDB";                	
ALTER TABLE `pma_table_info` ENGINE="InnoDB";                  	
ALTER TABLE `pnotes` ENGINE="InnoDB";                          	
ALTER TABLE `prescriptions` ENGINE="InnoDB";                   	
ALTER TABLE `prices` ENGINE="InnoDB";                          	
# ALTER TABLE `procedure_answers` ENGINE="InnoDB";               	
ALTER TABLE `procedure_order` ENGINE="InnoDB";                 	
# ALTER TABLE `procedure_order_code` ENGINE="InnoDB";            	
ALTER TABLE `procedure_providers` ENGINE="InnoDB";             	
ALTER TABLE `procedure_questions` ENGINE="InnoDB";             	
ALTER TABLE `procedure_report` ENGINE="InnoDB";                	
ALTER TABLE `procedure_result` ENGINE="InnoDB";                	
ALTER TABLE `procedure_type` ENGINE="InnoDB";                  	
ALTER TABLE `product_warehouse` ENGINE="InnoDB";               	
ALTER TABLE `registry` ENGINE="InnoDB";                        	
ALTER TABLE `report_itemized` ENGINE="InnoDB";                 	
ALTER TABLE `report_results` ENGINE="InnoDB";                  	
ALTER TABLE `rule_action` ENGINE="InnoDB";                     	
ALTER TABLE `rule_action_item` ENGINE="InnoDB";                	
ALTER TABLE `rule_filter` ENGINE="InnoDB";                     	
ALTER TABLE `rule_patient_data` ENGINE="InnoDB";               	
ALTER TABLE `rule_reminder` ENGINE="InnoDB";                   	
ALTER TABLE `rule_target` ENGINE="InnoDB";                     	
ALTER TABLE `sequences` ENGINE="InnoDB";                       	
ALTER TABLE `standardized_tables_track` ENGINE="InnoDB";       	
ALTER TABLE `supported_external_dataloads` ENGINE="InnoDB";    	
ALTER TABLE `syndromic_surveillance` ENGINE="InnoDB";          	
ALTER TABLE `template_users` ENGINE="InnoDB";                  	
ALTER TABLE `transactions` ENGINE="InnoDB";                    	
ALTER TABLE `users` ENGINE="InnoDB";                           	
ALTER TABLE `user_settings` ENGINE="InnoDB";                   	
ALTER TABLE `version` ENGINE="InnoDB";                         	
ALTER TABLE `x12_partners` ENGINE="InnoDB";                    	

--
