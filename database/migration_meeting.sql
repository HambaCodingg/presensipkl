-- Jalankan sekali di database hosting setelah fitur meeting ditambahkan.
ALTER TABLE tbl_kegiatan
    ADD COLUMN IF NOT EXISTS meeting_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS meeting_title VARCHAR(255) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS tbl_meeting_signals (
    id_signal BIGINT NOT NULL AUTO_INCREMENT,
    room_id INT NOT NULL,
    sender_id VARCHAR(128) NOT NULL,
    recipient_id VARCHAR(128) DEFAULT NULL,
    signal_type VARCHAR(20) NOT NULL,
    payload LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_signal),
    KEY idx_meeting_signal_room (room_id, id_signal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;