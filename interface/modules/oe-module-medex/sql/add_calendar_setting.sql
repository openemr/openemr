-- Add global setting to enable/disable MedEx calendar

INSERT INTO globals (gl_name, gl_index, gl_value, gl_title, gl_description, gl_section, gl_datatype)
VALUES (
    'medex_calendar_enabled',
    0,
    '0',
    'Enable MedEx Modern Calendar',
    'Replace the default OpenEMR calendar with the modern MedEx calendar interface featuring drag-drop scheduling, AI suggestions, and MedEx communication integration.',
    'MedEx',
    1
) ON DUPLICATE KEY UPDATE
    gl_title = VALUES(gl_title),
    gl_description = VALUES(gl_description);
