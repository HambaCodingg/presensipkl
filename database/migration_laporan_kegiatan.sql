ALTER TABLE tbl_siswa
    ADD COLUMN pembimbing VARCHAR(255) NULL AFTER jurusan;

ALTER TABLE tbl_kegiatan
    ADD COLUMN tempat VARCHAR(255) NULL AFTER kegiatan;

CREATE TABLE tbl_laporan_kegiatan_ttd (
    id_laporan INT NOT NULL AUTO_INCREMENT,
    id_siswa INT NOT NULL,
    tanggal_awal DATE NOT NULL,
    tanggal_akhir DATE NOT NULL,
    nama_user VARCHAR(255) NOT NULL DEFAULT '',
    ttd_user VARCHAR(255) NULL,
    nama_pembimbing VARCHAR(255) NOT NULL DEFAULT '',
    ttd_pembimbing VARCHAR(255) NULL,
    nama_siswa VARCHAR(255) NOT NULL DEFAULT '',
    ttd_siswa VARCHAR(255) NULL,
    updated_by INT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_laporan),
    UNIQUE KEY uq_laporan_kegiatan_periode (id_siswa, tanggal_awal, tanggal_akhir),
    KEY idx_laporan_kegiatan_siswa (id_siswa),
    CONSTRAINT fk_laporan_kegiatan_ttd_siswa
        FOREIGN KEY (id_siswa) REFERENCES tbl_siswa (id_siswa)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
