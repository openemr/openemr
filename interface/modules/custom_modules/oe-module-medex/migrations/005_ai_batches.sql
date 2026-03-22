CREATE TABLE IF NOT EXISTS medex_ai_batches (
    id int(11) NOT NULL AUTO_INCREMENT,
    batch_id varchar(50) NOT NULL,
    pc_eid int(11) NOT NULL,
    provider_id int(11) DEFAULT NULL,
    facility_id int(11) DEFAULT NULL,
    event_date date DEFAULT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by int(11) DEFAULT NULL,
    undone_at datetime DEFAULT NULL,
    undone_by int(11) DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_batch_id (batch_id),
    INDEX idx_pc_eid (pc_eid),
    INDEX idx_undone (undone_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
