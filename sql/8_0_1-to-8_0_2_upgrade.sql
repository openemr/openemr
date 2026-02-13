--
-- Rename the misspelled list_options option_id from 'declne_to_specfy' to 'decline_to_specify',
-- and update any patient_data.race records that reference the old value.
--

#IfColumn patient_data race
UPDATE `patient_data` SET `race` = 'decline_to_specify' WHERE `race` = 'declne_to_specfy';
#EndIf

#IfRow2D list_options list_id race option_id declne_to_specfy
UPDATE `list_options` SET `option_id` = 'decline_to_specify' WHERE `list_id` = 'race' AND `option_id` = 'declne_to_specfy';
#EndIf

#IfRow2D list_options list_id language option_id declne_to_specfy
UPDATE `list_options` SET `option_id` = 'decline_to_specify' WHERE `list_id` = 'language' AND `option_id` = 'declne_to_specfy';
#EndIf

#IfRow2D list_options list_id ethrace option_id declne_to_specfy
UPDATE `list_options` SET `option_id` = 'decline_to_specify' WHERE `list_id` = 'ethrace' AND `option_id` = 'declne_to_specfy';
#EndIf
