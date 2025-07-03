
#IfMissingColumn module_faxsms_credentials setup_persist
ALTER TABLE `module_faxsms_credentials` ADD `setup_persist` tinytext;
#Endif

CREATE TABLE IF NOT EXISTS `ringcentral_call_events` (
     id INT AUTO_INCREMENT PRIMARY KEY,
     session_id VARCHAR(255) UNIQUE,
     direction VARCHAR(20),
     status VARCHAR(50),
     from_number VARCHAR(20),
     to_number VARCHAR(20),
     timestamp DATETIME,
     raw_data TEXT,
     INDEX idx_session_id (session_id),
     INDEX idx_timestamp (timestamp)
);

CREATE TABLE IF NOT EXISTS `ringcentral_voicemails` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id VARCHAR(255) UNIQUE,
    from_number VARCHAR(20),
    received_date DATETIME,
    transcription TEXT,
    raw_data TEXT,
    INDEX idx_from_number (from_number)
);
