<!DOCTYPE html>
<html>

<head>
    <title>Tambah Event</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<?php
include '../includes/db.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $date = clean_input($_POST['date']);
    $venue = clean_input($_POST['venue']);
    $description = clean_input($_POST['description']);
    $image_path = '';
    $upload_error_message = null; // Variabel untuk menampung pesan error spesifik unggahan

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/events/';
        $max_file_size = 2 * 1024 * 1024; // 2MB

        // 1. Validasi ukuran file
        if ($_FILES['image']['size'] > $max_file_size) {
            $upload_error_message = "Ukuran file melebihi batas maksimal 2MB.";
        } else {
            // Buat direktori jika belum ada
            if (!file_exists($upload_dir)) {
                // 4. Gunakan izin yang lebih ketat dan 3. Cek hasil mkdir
                if (!mkdir($upload_dir, 0755, true)) {
                    $upload_error_message = "Gagal membuat direktori upload. Periksa izin server.";
                }
            }

            if (!$upload_error_message) { // Lanjutkan jika direktori OK
                $file_tmp_name = $_FILES['image']['tmp_name'];
                $file_name_original = $_FILES['image']['name'];
                $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
                $file_name_uniq = uniqid('event_', true) . '.' . $file_ext;
                $file_path = $upload_dir . $file_name_uniq;

                // Validate image extension and MIME type
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                // 2. Validasi tipe MIME
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp_name);
                finfo_close($finfo);
                $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];

                if (in_array($file_ext, $allowed_extensions) && in_array($mime_type, $allowed_mime_types)) {
                    // 3. Cek hasil move_uploaded_file
                    if (move_uploaded_file($file_tmp_name, $file_path)) {
                        $image_path = 'uploads/events/' . $file_name_uniq;
                    } else {
                        $upload_error_message = "Gagal memindahkan file yang diupload. Periksa izin tulis direktori.";
                    }
                } else {
                    $upload_error_message = "Format file tidak valid atau tipe MIME tidak diizinkan (Ekstensi: $file_ext, MIME: $mime_type).";
                }
            }
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['image']['error'] != UPLOAD_ERR_OK) {
        // 3. Tangani error unggah lainnya
        $upload_error_message = "Terjadi kesalahan saat mengupload file (Error code: " . $_FILES['image']['error'] . ").";
    }

    // 5. Hanya lanjutkan jika tidak ada error unggahan
    if ($upload_error_message) {
        $error = $upload_error_message;
    } else {
        $sql = "INSERT INTO events (name, date, venue, description, image_path) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $date, $venue, $description, $image_path);

        if ($stmt->execute()) {
            redirect('index.php', 'Event berhasil ditambahkan');
        } else {
            $error = "Gagal menambahkan event: " . $conn->error;
        }
    }
}
?>



<body>
    <div class="container">
        <h1>Tambah Event</h1>

        <?php if (isset($error))
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Event</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <input type="datetime-local" name="date" required>
            </div>

            <div class="form-group">
                <label>Tempat</label>
                <input type="text" name="venue" required>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label>Gambar Event</label>
                <input type="file" name="image" accept="image/*">
                <small>Format: JPG, PNG, GIF (Max 2MB)</small>
            </div>

            <button type="submit" class="btn">Simpan</button>
            <a href="index.php" class="btn">Kembali</a>
        </form>
    </div>
</body>

</html>