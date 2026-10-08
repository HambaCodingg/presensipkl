<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$level = strtolower($_SESSION['level'] ?? '');
if (!in_array($level, ['admin', 'siswa'], true)) {
    http_response_code(403);
    exit('Akses tidak diizinkan.');
}

$id_siswa = filter_input(INPUT_GET, 'id_siswa', FILTER_VALIDATE_INT);
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
if ($level === 'siswa') {
    $id_siswa = filter_var($_SESSION['id_siswa'] ?? null, FILTER_VALIDATE_INT);
}
$valid_date = static function ($value) {
    if (!is_string($value)) {
        return false;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
};
if (
    !$id_siswa ||
    !$valid_date($tanggal_awal) ||
    !$valid_date($tanggal_akhir) ||
    $tanggal_awal > $tanggal_akhir
) {
    http_response_code(400);
    exit('Siswa dan rentang tanggal yang valid harus dipilih.');
}

include '../../config/database.php';
include '../../config/function.php';

$student_statement = mysqli_prepare(
    $kon,
    'SELECT nama, perusahaan, nis, pembimbing FROM tbl_siswa WHERE id_siswa = ? LIMIT 1'
);
if (!$student_statement) {
    http_response_code(500);
    exit('Data siswa tidak dapat dimuat.');
}
mysqli_stmt_bind_param($student_statement, 'i', $id_siswa);
if (!mysqli_stmt_execute($student_statement)) {
    http_response_code(500);
    exit('Data siswa tidak dapat dimuat.');
}
$student = null;
mysqli_stmt_bind_result($student_statement, $student_name, $student_company, $student_nis, $student_advisor);
if (mysqli_stmt_fetch($student_statement)) {
    $student = [
        'nama' => $student_name,
        'perusahaan' => $student_company,
        'nis' => $student_nis,
        'pembimbing' => $student_advisor
    ];
}
mysqli_stmt_close($student_statement);
if (!$student) {
    http_response_code(404);
    exit('Data siswa tidak ditemukan.');
}

$signature_statement = mysqli_prepare(
    $kon,
    'SELECT nama_user, ttd_user, ttd_pembimbing, ttd_siswa
     FROM tbl_laporan_kegiatan_ttd
     WHERE id_siswa = ? AND tanggal_awal = ? AND tanggal_akhir = ?
     LIMIT 1'
);
if (!$signature_statement) {
    http_response_code(500);
    exit('Data tanda tangan tidak dapat dimuat.');
}
mysqli_stmt_bind_param($signature_statement, 'iss', $id_siswa, $tanggal_awal, $tanggal_akhir);
if (!mysqli_stmt_execute($signature_statement)) {
    http_response_code(500);
    exit('Data tanda tangan tidak dapat dimuat.');
}
$saved_signature = [];
mysqli_stmt_bind_result($signature_statement, $signature_user_name, $signature_user, $signature_advisor, $signature_student);
if (mysqli_stmt_fetch($signature_statement)) {
    $saved_signature = [
        'nama_user' => $signature_user_name,
        'ttd_user' => $signature_user,
        'ttd_pembimbing' => $signature_advisor,
        'ttd_siswa' => $signature_student
    ];
}
mysqli_stmt_close($signature_statement);

$roles = [
    'user' => ['label' => 'User DU/DI', 'name' => trim((string) ($saved_signature['nama_user'] ?? ''))],
    'pembimbing' => ['label' => 'Pembimbing PKL', 'name' => trim((string) ($student['pembimbing'] ?? ''))],
    'siswa' => ['label' => 'Peserta PKL', 'name' => trim((string) ($student['nama'] ?? ''))]
];
$saved_images = [
    'user' => $saved_signature['ttd_user'] ?? null,
    'pembimbing' => $saved_signature['ttd_pembimbing'] ?? null,
    'siswa' => $saved_signature['ttd_siswa'] ?? null
];
$error_message = '';
$csrf_token = $_SESSION['csrf_laporan_kegiatan'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_laporan_kegiatan'] = $csrf_token;
$report_url = 'cetak_kegiatan.php?' . http_build_query([
    'id_siswa' => $id_siswa,
    'tanggal_awal' => $tanggal_awal,
    'tanggal_akhir' => $tanggal_akhir
]);
$signature_directory = __DIR__ . '/ttd_kegiatan/';
$signature_limit = 2 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf_token, (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
    }

    $posted_user_name = $_POST['nama_user'] ?? '';
    if (!is_string($posted_user_name)) {
        $error_message = 'Nama User DU/DI tidak valid.';
    } else {
        $roles['user']['name'] = trim($posted_user_name);
    }
    if ($roles['user']['name'] === '' || strlen($roles['user']['name']) > 255) {
        $error_message = 'Nama User DU/DI wajib diisi dan maksimal 255 karakter.';
    }

    $new_images = [];
    $image_values = $saved_images;
    if ($error_message === '') {
        foreach (['user', 'pembimbing', 'siswa'] as $role) {
            $file_key = 'ttd_' . $role . '_file';
            $data_key = 'ttd_' . $role . '_data';
            $clear_key = 'hapus_ttd_' . $role;
            $uploaded_file = $_FILES[$file_key] ?? null;
            $has_upload = $uploaded_file && $uploaded_file['error'] !== UPLOAD_ERR_NO_FILE;
            $image_data = $_POST[$data_key] ?? '';
            $should_clear = isset($_POST[$clear_key]);

            if (!is_string($image_data)) {
                $error_message = 'Format tanda tangan digital tidak valid.';
                break;
            }
            if ($should_clear && ($has_upload || $image_data !== '')) {
                $error_message = 'Pilih hapus tanda tangan atau berikan tanda tangan baru, bukan keduanya.';
                break;
            }
            if ($should_clear) {
                $image_values[$role] = null;
                continue;
            }

            $bytes = null;
            if ($has_upload) {
                if ($uploaded_file['error'] !== UPLOAD_ERR_OK || $uploaded_file['size'] > $signature_limit) {
                    $error_message = 'Ukuran tanda tangan maksimal 2 MB.';
                    break;
                }
                $bytes = file_get_contents($uploaded_file['tmp_name']);
                if ($bytes === false) {
                    $error_message = 'File tanda tangan tidak dapat dibaca.';
                    break;
                }
            } elseif ($image_data !== '') {
                if (
                    !preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/=]+)$/', $image_data, $matches) ||
                    strlen($matches[1]) > (int) ceil($signature_limit * 4 / 3)
                ) {
                    $error_message = 'Format tanda tangan digital tidak valid.';
                    break;
                }
                $bytes = base64_decode($matches[1], true);
                if ($bytes === false || strlen($bytes) > $signature_limit) {
                    $error_message = 'Ukuran tanda tangan maksimal 2 MB.';
                    break;
                }
            }

            if ($bytes === null) {
                continue;
            }
            $image_info = getimagesizefromstring($bytes);
            if (
                !$image_info ||
                !in_array($image_info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true) ||
                $image_info[0] > 4000 ||
                $image_info[1] > 4000
            ) {
                $error_message = 'Tanda tangan harus berupa gambar PNG atau JPG yang valid.';
                break;
            }
            if (!is_dir($signature_directory) && !mkdir($signature_directory, 0755, true) && !is_dir($signature_directory)) {
                $error_message = 'Folder penyimpanan tanda tangan tidak dapat dibuat.';
                break;
            }

            $extension = $image_info[2] === IMAGETYPE_PNG ? 'png' : 'jpg';
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
            $destination = $signature_directory . $filename;
            if (file_put_contents($destination, $bytes, LOCK_EX) === false) {
                $error_message = 'Tanda tangan tidak dapat disimpan.';
                break;
            }
            $new_images[] = $filename;
            $image_values[$role] = $filename;
        }
    }

    if ($error_message === '') {
        $save_statement = mysqli_prepare(
            $kon,
            'INSERT INTO tbl_laporan_kegiatan_ttd
                (id_siswa, tanggal_awal, tanggal_akhir, nama_user, ttd_user,
                 nama_pembimbing, ttd_pembimbing, nama_siswa, ttd_siswa, updated_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                nama_user = VALUES(nama_user),
                ttd_user = VALUES(ttd_user),
                nama_pembimbing = VALUES(nama_pembimbing),
                ttd_pembimbing = VALUES(ttd_pembimbing),
                nama_siswa = VALUES(nama_siswa),
                ttd_siswa = VALUES(ttd_siswa),
                updated_by = VALUES(updated_by)'
        );
        if (!$save_statement) {
            $error_message = 'Data tanda tangan tidak dapat disimpan.';
        } else {
            $updated_by_value = filter_var($_SESSION['id_pengguna'] ?? null, FILTER_VALIDATE_INT);
            $updated_by = $updated_by_value === false ? null : $updated_by_value;
            $nama_user = $roles['user']['name'];
            $nama_pembimbing = $roles['pembimbing']['name'];
            $nama_siswa = $roles['siswa']['name'];
            mysqli_stmt_bind_param(
                $save_statement,
                'issssssssi',
                $id_siswa,
                $tanggal_awal,
                $tanggal_akhir,
                $nama_user,
                $image_values['user'],
                $nama_pembimbing,
                $image_values['pembimbing'],
                $nama_siswa,
                $image_values['siswa'],
                $updated_by
            );
            if (!mysqli_stmt_execute($save_statement)) {
                $error_message = 'Data tanda tangan gagal disimpan. Periksa koneksi database dan coba lagi.';
            }
            mysqli_stmt_close($save_statement);
        }
    }

    if ($error_message !== '') {
        foreach ($new_images as $filename) {
            $new_file = $signature_directory . $filename;
            if (is_file($new_file)) {
                unlink($new_file);
            }
        }
    } else {
        foreach (['user', 'pembimbing', 'siswa'] as $role) {
            $old_image = $saved_images[$role];
            if ($old_image && $old_image !== $image_values[$role]) {
                $old_file = $signature_directory . basename($old_image);
                if (is_file($old_file)) {
                    unlink($old_file);
                }
            }
        }
        header('Location: ' . $report_url);
        exit;
    }
}

$month_name = static function ($month) {
    return MendapatkanBulan((int) $month);
};
$period_start = new DateTimeImmutable($tanggal_awal);
$period_end = new DateTimeImmutable($tanggal_akhir);
$period_label = $month_name($period_start->format('m')) . ' ' . $period_start->format('Y');
if ($period_start->format('Y-m') !== $period_end->format('Y-m')) {
    $period_label .= ' - ' . $month_name($period_end->format('m')) . ' ' . $period_end->format('Y');
}
$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tanda Tangan Laporan Kegiatan PKL</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f5f9; color: #1e293b; font: 15px/1.5 Arial, sans-serif; }
        main { max-width: 1100px; margin: 32px auto; padding: 0 18px; }
        .card { margin-bottom: 18px; padding: 22px; background: #fff; border: 1px solid #dbe3ef; border-radius: 10px; box-shadow: 0 4px 14px #0f172a0d; }
        h1 { margin: 0 0 8px; font-size: 23px; }
        h2 { margin: 0 0 8px; font-size: 18px; }
        .muted { color: #64748b; }
        .alert { padding: 12px 16px; border-radius: 6px; background: #fee2e2; color: #991b1b; }
        .summary { margin-top: 18px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px 24px; }
        .summary span { display: block; color: #64748b; font-size: 13px; }
        .summary strong { overflow-wrap: anywhere; }
        .signatures { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .signature { padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; }
        label { display: block; margin: 14px 0 6px; font-weight: 600; }
        input[type=text], input[type=file] { width: 100%; padding: 9px; border: 1px solid #cbd5e1; border-radius: 5px; }
        canvas { display: block; width: 100%; height: 125px; margin-top: 10px; background: #fff; border: 1px dashed #94a3b8; border-radius: 5px; touch-action: none; }
        .saved-signature { display: block; width: 100%; height: 70px; object-fit: contain; margin-top: 8px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .check { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 400; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .button { display: inline-block; padding: 10px 16px; border: 0; border-radius: 5px; background: #2563eb; color: #fff; font: inherit; font-weight: 600; text-decoration: none; cursor: pointer; }
        .button.secondary { background: #475569; }
        @media (max-width: 760px) { .signatures { grid-template-columns: 1fr; } .summary { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 440px) { .summary { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <section class="card">
        <h1>Tanda Tangan Laporan Kegiatan PKL</h1>
        <div class="muted">Tanda tangan bisa ditambahkan sebelum mencetak atau sesudah cetakan awal. Nama dan tanda tangan tersimpan per siswa dan periode, sehingga dapat diperbarui saat mencetak ulang.</div>
        <div class="summary">
            <div><span>Nama / NIS</span><strong><?php echo $escape($student['nama']); ?> / <?php echo $escape($student['nis']); ?></strong></div>
            <div><span>Tempat Praktik Kerja Lapangan</span><strong><?php echo $escape($student['perusahaan']); ?></strong></div>
            <div><span>Bulan Pelaksanaan</span><strong><?php echo $escape($period_label); ?></strong></div>
            <div><span>Pembimbing PKL</span><strong><?php echo $escape($roles['pembimbing']['name'] ?: 'Belum diisi pada data siswa'); ?></strong></div>
            <div><span>Periode laporan</span><strong><?php echo $escape($tanggal_awal); ?> s.d. <?php echo $escape($tanggal_akhir); ?></strong></div>
        </div>
    </section>

    <?php if ($error_message !== ''): ?>
        <div class="alert"><?php echo $escape($error_message); ?></div>
    <?php endif; ?>

    <form class="card" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $escape($csrf_token); ?>">
        <div class="signatures">
            <?php foreach ($roles as $role => $details): ?>
                <section class="signature">
                    <h2><?php echo $escape($details['label']); ?></h2>
                    <?php if ($role === 'user'): ?>
                        <label for="nama-user">Nama User DU/DI</label>
                        <input id="nama-user" type="text" name="nama_user" maxlength="255" value="<?php echo $escape($details['name']); ?>" required>
                    <?php else: ?>
                        <div class="muted"><?php echo $escape($details['name'] ?: 'Tambahkan nama pembimbing pada data siswa.'); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($saved_images[$role]) && is_file($signature_directory . basename($saved_images[$role]))): ?>
                        <img class="saved-signature" src="gambar_ttd_kegiatan.php?<?php echo $escape(http_build_query([
                            'id_siswa' => $id_siswa,
                            'tanggal_awal' => $tanggal_awal,
                            'tanggal_akhir' => $tanggal_akhir,
                            'role' => $role
                        ])); ?>" alt="Tanda tangan tersimpan">
                    <?php endif; ?>
                    <label for="ttd-<?php echo $role; ?>-file">Unggah tanda tangan (PNG/JPG, maksimal 2 MB)</label>
                    <input id="ttd-<?php echo $role; ?>-file" type="file" name="ttd_<?php echo $role; ?>_file" accept="image/png,image/jpeg">
                    <div class="muted">Atau bubuhkan tanda tangan pada kotak di bawah.</div>
                    <canvas id="canvas-<?php echo $role; ?>" width="600" height="250" aria-label="Kolom tanda tangan <?php echo $escape($details['label']); ?>"></canvas>
                    <input type="hidden" name="ttd_<?php echo $role; ?>_data" id="data-<?php echo $role; ?>">
                    <label class="check"><input type="checkbox" name="hapus_ttd_<?php echo $role; ?>" value="1"> Hapus tanda tangan tersimpan</label>
                </section>
            <?php endforeach; ?>
        </div>
        <div class="actions" style="margin-top:20px">
            <button class="button" type="submit">Simpan Tanda Tangan &amp; Cetak Laporan</button>
            <a class="button secondary" href="<?php echo $escape($report_url); ?>" target="_blank" rel="noopener">Cetak Tanpa Mengubah Tanda Tangan</a>
        </div>
    </form>
</main>
<script>
    (function() {
        ['user', 'pembimbing', 'siswa'].forEach(function(role) {
            var canvas = document.getElementById('canvas-' + role);
            var context = canvas.getContext('2d');
            var hidden = document.getElementById('data-' + role);
            var clear = document.querySelector('[name="hapus_ttd_' + role + '"]');
            var file = document.getElementById('ttd-' + role + '-file');
            var drawing = false;
            var hasInk = false;
            context.strokeStyle = '#172554';
            context.lineWidth = 4;
            context.lineCap = 'round';
            context.lineJoin = 'round';

            function position(event) {
                var rect = canvas.getBoundingClientRect();
                return {
                    x: (event.clientX - rect.left) * canvas.width / rect.width,
                    y: (event.clientY - rect.top) * canvas.height / rect.height
                };
            }
            canvas.addEventListener('pointerdown', function(event) {
                event.preventDefault();
                drawing = true;
                hasInk = true;
                clear.checked = false;
                canvas.setPointerCapture(event.pointerId);
                var point = position(event);
                context.beginPath();
                context.moveTo(point.x, point.y);
            });
            canvas.addEventListener('pointermove', function(event) {
                if (!drawing) return;
                var point = position(event);
                context.lineTo(point.x, point.y);
                context.stroke();
            });
            function finishDrawing() {
                if (!drawing) return;
                drawing = false;
                hidden.value = hasInk ? canvas.toDataURL('image/png') : '';
            }
            canvas.addEventListener('pointerup', finishDrawing);
            canvas.addEventListener('pointercancel', finishDrawing);
            file.addEventListener('change', function() {
                hidden.value = '';
                clear.checked = false;
            });
            canvas.addEventListener('pointerdown', function() {
                file.value = '';
            });
            clear.addEventListener('change', function() {
                if (clear.checked) {
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    hidden.value = '';
                    file.value = '';
                    hasInk = false;
                }
            });
        });
    })();
</script>
</body>
</html>
