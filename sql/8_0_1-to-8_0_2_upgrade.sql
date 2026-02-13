--
-- Rename misspelled list_options option_id declne_to_specfy -> decline_to_specify
--

#IfRow2D list_options list_id language option_id declne_to_specfy
UPDATE `list_options` SET `option_id` = 'decline_to_specify' WHERE `option_id` = 'declne_to_specfy';
#EndIf
