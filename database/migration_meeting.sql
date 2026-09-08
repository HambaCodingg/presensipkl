-- Jalankan sekali di database hosting untuk fitur Zoom internal.
ALTER TABLE tbl_kegiatan
    DROP COLUMN IF EXISTS meeting_enabled,
    DROP COLUMN IF EXISTS meeting_title;

CREATE TABLE IF NOT EXISTS tbl_zoom (
    id_zoom INT NOT NULL AUTO_INCREMENT,
    id_siswa INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    tanggal DATE NOT NULL,
    waktu_awal TIME NOT NULL,
    waktu_akhir TIME NOT NULL,
    dibuat_oleh INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_zoom),
    KEY idx_zoom_siswa_tanggal (id_siswa, tanggal),
    CONSTRAINT fk_zoom_siswa FOREIGN KEY (id_siswa) REFERENCES tbl_siswa (id_siswa) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
