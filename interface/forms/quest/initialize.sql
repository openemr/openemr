--
-- Define procedure used to modify standard tables
--

DROP PROCEDURE IF EXISTS lab_table_update;

DELIMITER $$
CREATE PROCEDURE lab_table_update (
	IN in_database VARCHAR(32),
	IN in_table VARCHAR(32), 
	IN in_column VARCHAR(32), 
	IN in_alter VARCHAR(255)
)
BEGIN
	-- does column exist 
	SET @query = CONCAT("SELECT COUNT(*) INTO @output FROM information_schema.columns WHERE `table_schema` = '",in_database,"' AND `table_name` = '",in_table,"' AND `column_name` = '",in_column,"'");
	PREPARE statement FROM @query;
	EXECUTE statement;
	DEALLOCATE PREPARE statement;
	
	-- create updates
	IF @output = 0 
	THEN SET @update = CONCAT("ALTER TABLE `",in_table,"` ADD `",in_column,"` ",in_alter);
	ELSE SET @update = CONCAT("ALTER TABLE `",in_table,"` MODIFY `",in_column,"` ",in_alter);
	END IF;
	PREPARE statement FROM @update;
	EXECUTE statement;
	DEALLOCATE PREPARE statement;
END
$$

DELIMITER ;

--
-- Update standard tables as required
--

SET @db = database();

-- pocedure_answers
CALL lab_table_update(@db,"procedure_answers","procedure_code","VARCHAR(31)");

-- pocedure_orders
CALL lab_table_update(@db,"procedure_order","lab_id","BIGINT(20) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","procedure_type_id","BIGINT(20) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","date_collected","DATETIME DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","date_pending","DATETIME DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","date_ordered","DATETIME DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","date_transmitted","DATETIME DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","order_priority","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","control_id","VARCHAR(25) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","patient_instructions","TEXT DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","clinical_hx","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","specimen_type","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","specimen_volume","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","specimen_fasting","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","specimen_duration","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","specimen_transport","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","specimen_source","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order","specimen_location","VARCHAR(31) DEFAULT NULL");

-- proceduer_order_code
CALL lab_table_update(@db,"procedure_order_code","procedure_type","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order_code","procedure_name","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order_code","procedure_source","CHAR(1) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order_code","diagnoses","TEXT DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order_code","reflex_code","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order_code","reflex_set","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order_code","reflex_name","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_order_code","labcorp_zseg","VARCHAR(31) DEFAULT NULL");

-- procedure_providers
CALL lab_table_update(@db,"procedure_providers","type","VARCHAR(12) DEFAULT 'INT'");
CALL lab_table_update(@db,"procedure_providers","remote_port","VARCHAR(5) DEFAULT '22'");

-- procedure_questions
CALL lab_table_update(@db,"procedure_questions","section","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_questions","tips","VARCHAR(255) DEFAULT NULL");

-- procedure_report
CALL lab_table_update(@db,"procedure_report","procedure_order_seq","INT(11) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_report","date_report","DATETIME DEFAULT NULL");
CALL lab_table_update(@db,"procedure_report","lab_id","BIGINT(20) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_report","specimen_num","VARCHAR(63) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_report","report_status","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_report","review_status","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_report","report_notes","TEXT DEFAULT NULL");

-- procedure_result
CALL lab_table_update(@db,"procedure_result","result_data_type","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","result_code","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","result_text","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","result_set","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","facility","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","units","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","result","TEXT DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","range","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","abnormal","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","result_status","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_result","comments","TEXT DEFAULT NULL");

-- procedure_type
CALL lab_table_update(@db,"procedure_type","activity","TINYINT(1) DEFAULT 1");
CALL lab_table_update(@db,"procedure_type","transport","VARCHAR(50) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","body_site","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","specimen","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","route_admin","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","laterality","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","description","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","standard_code","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","related_code","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","units","VARCHAR(31) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","range","VARCHAR(255) DEFAULT NULL");
CALL lab_table_update(@db,"procedure_type","notes","TEXT DEFAULT NULL");

--
-- Insert default lists and list values
--

REPLACE INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES  
('lists', 	'Provider_Protocol', 	'Provider Protocol', 	0, 1, 0, '', '', ''),
('lists', 	'Provider_Type', 		'Provider Type', 		0, 1, 0, '', '', ''),
('lists', 	'Lab_Form_Status', 		'Lab Form Status', 		0, 1, 0, '', '', ''),
('lists', 	'Lab_Handling', 		'Lab Handling', 		0, 1, 0, '', '', ''),
('lists', 	'Lab_Notification', 	'Lab Notification', 	0, 1, 0, '', 'Notify <nurse_username> instead of <doc_username>', ''),
('lists', 	'Lab_Diagnosis', 		'Lab Diagnosis', 		0, 1, 0, '', 'Quick List', ''),
('lists', 	'Lab_Category', 		'Lab Category', 		0, 1, 0, '', 'ID=appt code TITLE=orphan results', ''),
('lists', 	'Lab_Race', 			'Lab Race', 			0, 1, 0, '', 'ID=OEMR code TITLE=HL7 code', ''),
('lists', 	'Lab_Ethnicity',		'Lab Ethnicity', 		0, 1, 0, '', 'ID=OEMR code TITLE=HL7 code', ''),
('lists', 	'Procedure_Billing', 	'Procedure Billing', 	0, 1, 0, '', '', '');

REPLACE INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`, `codes`) VALUES  
('proc_res_status', 	'partial',		'Partial', 					70, 1, 0, '', '', ''),
('proc_type', 			'det', 			'Item Details', 			40, 0, 0, '', 'Discription of test', ''),
('proc_type', 			'fav', 			'Favorite', 				50, 0, 0, '', 'Display in favorite tab', ''),
('proc_type', 			'grp', 			'Group Title', 				10, 0, 0, '', '', ''),
('proc_type', 			'ord', 			'Procedure', 				20, 0, 0, '', '', ''),
('proc_type', 			'pro', 			'Profile Panel', 			30, 0, 0, '', 'Panel (multiple tests)', ''),
('proc_type', 			'rec', 			'Recommendation', 			70, 0, 0, '', '', ''),
('proc_type', 			'res', 			'Discrete Result', 			60, 0, 0, '', '', ''),
('Lab_Diagnosis', 		'V70.6', 		'Default', 					0, 0, 0, '', 'ID=code TITLE=tab name NOTES=alternate name', ''),
('Lab_Notification', 	'admin', 		'nurse', 					0, 0, 0, '', 'Admin -> Lab Nurse', ''),
('Lab_Category', 		'12', 			'Lab Results', 				0, 0, 0, '', 'ID=appt code TITLE=orphan results', ''),
('Lab_Handling', 		'follow', 		'Follow-Up Required', 		3, 0, 0, '', '', ''),
('Lab_Handling', 		'note', 		'Notify Physician', 		1, 0, 0, '', '', ''),
('Lab_Handling', 		'pat', 			'Notify Patient', 			2, 0, 0, '', '', ''),
('Provider_Protocol', 	'DL', 			'File Download', 			30, 0, 0, '', '', ''),
('Provider_Protocol', 	'FSC', 			'sFTP Client', 				20, 0, 0, '', '', ''),
('Provider_Protocol', 	'FC2', 			'sFTP Client 2.5.1', 		20, 0, 0, '', '', ''),
('Provider_Protocol', 	'FSS', 			'sFTP Server', 				10, 0, 0, '', '', ''),
('Provider_Protocol', 	'FS2', 			'sFTP Server 2.5.1', 		10, 0, 0, '', '', ''),
('Provider_Protocol', 	'INT', 			'Internal Only', 			90, 0, 0, '', '', ''),
('Provider_Protocol', 	'UL', 			'File Upload', 				40, 0, 0, '', '', ''),
('Provider_Protocol', 	'WS', 			'Web Service', 				50, 0, 0, '', '', ''),
('Provider_Type', 		'internal', 	'Internal', 				10, 0, 0, '', 'In-House Procedures', ''),
('Provider_Type', 		'laboratory', 	'Generic Laboratory', 		20, 0, 0, '', '', ''),
('Provider_Type', 		'quest', 		'Quest Diagnostics', 		20, 0, 0, '', '', ''),
('Provider_Type', 		'labcorp', 		'LabCorp Laboratory',	 	20, 0, 0, '', '', ''),
('Lab_Form_Status', 	'g', 			'Order Processed', 			3, 0, 0, '', 'DO NOT CHANGE', ''),
('Lab_Form_Status', 	'i', 			'Order Incomplete', 		1, 0, 0, '', 'DO NOT CHANGE', ''),
('Lab_Form_Status', 	'n', 			'Results Notification', 	7, 0, 0, '', 'DO NOT CHANGE', ''),
('Lab_Form_Status', 	's', 			'Order Submitted', 			2, 0, 0, '', 'DO NOT CHANGE', ''),
('Lab_Form_Status', 	'v', 			'Results Reviewed', 			6, 0, 0, '', 'DO NOT CHANGE', ''),
('Lab_Form_Status', 	'x', 			'Results Partial', 			4, 0, 0, '', 'DO NOT CHANGE', ''),
('Lab_Form_Status', 	'z', 			'Results Final', 			5, 0, 0, '', 'DO NOT CHANGE', ''),
('Lab_Form_Status', 	'f', 			'Results Abnormal',			6, 0, 0, '', 'DO NOT CHANGE', ''),
('Lab_Race', 			'amer_ind_or_a', 'I', 						1, 0, 0, '', 'ID = OEMR value', ''),
('Lab_Race', 			'asian', 		'A', 						2, 0, 0, '', 'TITLE = HL7 value', ''),
('Lab_Race', 			'black_or_afri_amer', 'B', 					3, 0, 0, '', '', ''),
('Lab_Race', 			'native_hawai_or_pac_island', 'C', 			4, 0, 0, '', '', ''),
('Lab_Race', 			'unknown', 		'X', 						6, 1, 0, '', 'not provided', ''),
('Lab_Race', 			'white', 		'C', 						5, 0, 0, '', '', ''),
('Lab_Ethnicity', 		'hisp_or_latin', 'H', 						1, 0, 0, '', 'ID = OEMR value', ''),
('Lab_Ethnicity', 		'not_hisp_or_latino', 'N', 					2, 0, 0, '', 'TITLE = HL7 value', ''),
('Lab_Ethnicity', 		'unknown', 		'U', 						3, 0, 0, '', 'not provided', ''),
('Procedure_Billing', 	'C', 			'Bill Clinic', 				30, 0, 0, '', '', ''),
('Procedure_Billing', 	'P', 			'Self Pay', 				20, 0, 0, '', '', ''),
('Procedure_Billing', 	'T', 			'Third-Party', 				10, 1, 0, '', '', '');

--
-- Insert Quest data into table `list_options`
--

REPLACE INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES
('lists', 	'Quest_Label_Printers', 	'Quest Label Printers', 	0, 1, 0, '', ''),
('lists', 	'Quest_Site_Identifiers', 	'Quest Site Identifiers', 	0, 1, 0, '', ''),
('lists', 	'Quest_Accounts', 			'Quest Accounts', 			0, 1, 0, '', '');

REPLACE INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `mapping`, `notes`) VALUES
('Quest_Accounts', 			'12345678', 	'General', 			0, 1, 0, '', ''),
('Quest_Label_Printers', 	'127.0.0.2', 	'Nurse Station', 	20, 1, 0, '', ''),
('Quest_Site_Identifiers', 	'3', 			'123456', 			0, 1, 0, '', 'Primary Clinic');
