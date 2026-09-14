-- Jalankan sekali pada database yang sudah ada.
-- Lokasi ini adalah titik kantor/perusahaan tempat siswa melakukan PKL.
ALTER TABLE tbl_siswa
    ADD COLUMN IF NOT EXISTS pkl_longitude DECIMAL(10,7) NULL AFTER pkl_latitude,
    ADD COLUMN IF NOT EXISTS pkl_radius_meter INT NOT NULL DEFAULT 100 AFTER pkl_longitude;
