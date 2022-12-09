DROP VIEW IF EXISTS view_patient;
CREATE VIEW view_patient AS
SELECT 
pd.`pid` AS PID,
pd.`title` AS Title,
pd.`fname` AS First_Name,
pd.`mname` AS Middle_Name,
pd.`lname` AS Last_Name,
pd.`nickname` AS Nickname,
pd.`DOB`,
l1.`title` AS Sex,
l2.`title` AS Race,
l3.`title` AS Ethnicity,
pd.`email` AS Email,
pd.`phone_home` AS Phone_Home,
pd.`phone_biz` AS Phone_Work,
pd.`phone_cell` AS Phone_Cell,
pd.`phone_contact` AS Phone_Emergency,
pd.`emr_contact` AS Emergency_Contact,
l4.`title` AS Emergency_Relation,
l5.`title` AS Patient_Status,
l6.`title` AS Language,
pd.`street` AS Address,
pd.`city` AS City,
pd.`state` AS State,
pd.`postal_code` AS Zip,
l7.`title` AS County
FROM `patient_data` pd
LEFT JOIN `list_options` l1 ON l1.`list_id` = 'sex' AND pd.`sex` = l1.`option_id`
LEFT JOIN `list_options` l2 ON l2.`list_id` = 'race' AND pd.`race` = l2.`option_id`
LEFT JOIN `list_options` l3 ON l3.`list_id` = 'ethnicity' AND pd.`ethnicity` = l3.`option_id`
LEFT JOIN `list_options` l4 ON l4.`list_id` = 'Relationship' AND pd.`emr_relation` = l4.`option_id`
LEFT JOIN `list_options` l5 ON l5.`list_id` = 'Patient_Status' AND pd.`patient_status` = l5.`option_id`
LEFT JOIN `list_options` l6 ON l6.`list_id` = 'language' AND pd.`language` = l6.`option_id`
LEFT JOIN `list_options` l7 ON l7.`list_id` = 'county' AND pd.`county` = l7.`option_id`;