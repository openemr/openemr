# alter the patient_data table
alter table patient_data
ADD parent_lname varchar(255),
ADD parent_fname varchar(255),
ADD sparent_lname varchar(255),
ADD sparent_fname varchar(255),
ADD auth_until_date date,
ADD progress_due_date date,
ADD ifsp_review_date date,
ADD annual_ifsp_review_date date,
ADD aging_out_date date,
ADD trans_conf_date date,
ADD patient_type varchar(30),
ADD active varchar(4);