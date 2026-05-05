-- MedEx TeleHealth LBF Form Creation
-- Delete existing if present (for clean re-runs)
DELETE FROM layout_options WHERE form_id = 'LBFtelehealth';
DELETE FROM layout_group_properties WHERE grp_form_id = 'LBFtelehealth';

-- Insert form header in layout_group_properties
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_mapping, grp_seq, grp_activity, grp_aco_spec, grp_save_close, grp_init_open) 
VALUES ('LBFtelehealth', '', 'TeleHealth Visit', 'Clinical', 50, 1, 'encounters|notes', 1, 1);

-- Insert the main group (Provider Dashboard)
INSERT INTO layout_group_properties (grp_form_id, grp_group_id, grp_title, grp_seq, grp_activity, grp_init_open) 
VALUES ('LBFtelehealth', '1', 'Provider Dashboard', 1, 1, 1);

-- Insert form fields
-- Field 1: Header (Static Text)
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description, fld_rows) 
VALUES ('LBFtelehealth', 'th_header', '1', '', 1, 31, 1, 0, 0, '', 1, 3, '<div class="alert alert-info"><i class="fa fa-video"></i> <strong>MedEx TeleHealth</strong><br>Launch telehealth sessions with your patients.</div>', '["EP","E"]', 'TeleHealth header', 0);

-- Field 2: Launch Button (Static Text with link)
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description, fld_rows) 
VALUES ('LBFtelehealth', 'th_launch', '1', 'Video Session', 10, 31, 1, 0, 0, '', 1, 3, '<a href="https://medexbank.com/cart/upload/index.php?route=information/TM" class="btn btn-primary" target="_blank"><i class="fa fa-video"></i> Launch TeleMedEx</a>', '["E"]', 'Launch video session', 0);

-- Field 3: Off-site Provider Access
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description, fld_rows) 
VALUES ('LBFtelehealth', 'th_offsite', '1', 'Off-site Access', 20, 31, 1, 0, 0, '', 1, 3, '<code>https://medexbank.com/login.php</code><br><small>Provider login from outside the office</small>', '["E"]', 'Off-site provider URL', 0);

-- Field 4: Patient Waiting Room
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description, fld_rows) 
VALUES ('LBFtelehealth', 'th_waitroom', '1', 'Patient Waiting Room', 30, 31, 1, 0, 0, '', 1, 3, '<code>https://dsdbox.com/rmag</code><br><small>Share with patients to join waiting room</small>', '["E"]', 'Patient waiting room URL', 0);

-- Field 5: Session Notes (Textarea)
INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description, fld_rows) 
VALUES ('LBFtelehealth', 'th_notes', '1', 'Session Notes', 40, 3, 1, 60, 2000, '', 1, 3, '', '', 'Notes from the session', 4);

-- Field 6: Session Status (List box)
-- First ensure the list exists
INSERT IGNORE INTO list_options (list_id, option_id, title, seq, is_default, activity) VALUES ('lists', 'telehealth_status', 'TeleHealth Status', 0, 0, 1);
INSERT IGNORE INTO list_options (list_id, option_id, title, seq, is_default, activity) VALUES ('telehealth_status', 'scheduled', 'Scheduled', 10, 0, 1);
INSERT IGNORE INTO list_options (list_id, option_id, title, seq, is_default, activity) VALUES ('telehealth_status', 'waiting', 'Patient Waiting', 20, 0, 1);
INSERT IGNORE INTO list_options (list_id, option_id, title, seq, is_default, activity) VALUES ('telehealth_status', 'in_session', 'In Session', 30, 0, 1);
INSERT IGNORE INTO list_options (list_id, option_id, title, seq, is_default, activity) VALUES ('telehealth_status', 'completed', 'Completed', 40, 1, 1);
INSERT IGNORE INTO list_options (list_id, option_id, title, seq, is_default, activity) VALUES ('telehealth_status', 'no_show', 'No Show', 50, 0, 1);
INSERT IGNORE INTO list_options (list_id, option_id, title, seq, is_default, activity) VALUES ('telehealth_status', 'cancelled', 'Cancelled', 60, 0, 1);

INSERT INTO layout_options (form_id, field_id, group_id, title, seq, data_type, uor, fld_length, max_length, list_id, titlecols, datacols, default_value, edit_options, description, fld_rows) 
VALUES ('LBFtelehealth', 'th_status', '1', 'Session Status', 50, 1, 1, 0, 0, 'telehealth_status', 1, 1, 'scheduled', '', 'Session status', 0);
