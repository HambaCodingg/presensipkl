-- Jalankan sekali di database hosting untuk fitur Zoom internal.
ALTER TABLE tbl_kegiatan
    DROP COLUMN IF EXISTS meeting_enabled,
    DROP COLUMN IF EXISTS meeting_title;

-- Jika tbl_zoom lama sudah ada dengan id_siswa, jalankan manual sekali:
-- ALTER TABLE tbl_zoom DROP FOREIGN KEY fk_zoom_siswa;
-- ALTER TABLE tbl_zoom DROP COLUMN id_siswa;

CREATE TABLE IF NOT EXISTS tbl_zoom (
    id_zoom INT NOT NULL AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    tanggal DATE NOT NULL,
    waktu_awal TIME NOT NULL,
    waktu_akhir TIME NOT NULL,
    dibuat_oleh INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_zoom),
    KEY idx_zoom_tanggal (tanggal)
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

CREATE TABLE IF NOT EXISTS tbl_meeting_screen_share (
    room_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (room_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tbl_zoom_kehadiran (
    id_kehadiran BIGINT NOT NULL AUTO_INCREMENT,
    id_zoom INT NOT NULL,
    kode_pengguna VARCHAR(4) NOT NULL,
    nama VARCHAR(255) NOT NULL,
    level VARCHAR(50) NOT NULL,
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    left_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id_kehadiran),
    UNIQUE KEY uq_zoom_kehadiran (id_zoom, kode_pengguna),
    KEY idx_zoom_kehadiran_zoom (id_zoom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
