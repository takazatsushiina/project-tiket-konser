<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php', 'ID tiket tidak valid');
}

$id = intval($_GET['id']);

// Ambil data tiket beserta informasi event
$sql = "SELECT t.*, e.name as event_name 
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    redirect('index.php', 'Tiket tidak ditemukan');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = clean_input($_POST['type']);
    $price = floatval($_POST['price']);
    $quota = intval($_POST['quota']);

    // Handle image upload jika diperlukan
    $image_path = $ticket['image_path'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/tickets/';
        $max_file_size = 2 * 1024 * 1024; // 2MB

        if ($_FILES['image']['size'] > $max_file_size) {
            $error = "Ukuran file melebihi batas maksimal 2MB.";
        } else {
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_tmp_name = $_FILES['image']['tmp_name'];
            $file_name_original = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
            $file_name_uniq = uniqid('ticket_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $file_name_uniq;

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_tmp_name);
            finfo_close($finfo);
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($file_ext, $allowed_extensions) && in_array($mime_type, $allowed_mime_types)) {
                if (move_uploaded_file($file_tmp_name, $file_path)) {
                    // Hapus gambar lama jika ada
                    if (!empty($ticket['image_path']) && file_exists('../' . $ticket['image_path'])) {
                        unlink('../' . $ticket['image_path']);
                    }
                    $image_path = 'uploads/tickets/' . $file_name_uniq;
                } else {
                    $error = "Gagal memindahkan file yang diupload.";
                }
            } else {
                $error = "Format file tidak valid.";
            }
        }
    }

    if (!isset($error)) {
        $sql = "UPDATE tickets SET type=?, price=?, quota=?, available=?, image_path=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdiisi", $type, $price, $quota, $quota, $image_path, $id);

        if ($stmt->execute()) {
            redirect('index.php', 'Tiket berhasil diperbarui');
        } else {
            $error = "Gagal memperbarui tiket: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Tiket</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Edit Tiket</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Event</label>
                <input type="text" value="<?php echo htmlspecialchars($ticket['event_name']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Jenis Tiket</label>
                <input type="text" name="type" value="<?php echo htmlspecialchars($ticket['type']); ?>" required>
            </div>

            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="price" min="0" value="<?php echo $ticket['price']; ?>" required>
            </div>

            <div class="form-group">
                <label>Kuota</label>
                <input type="number" name="quota" min="1" value="<?php echo $ticket['quota']; ?>" required>
            </div>

            <div class="form-group">
                <label>Gambar Tiket</label>
                <?php if (!empty($ticket['image_path'])): ?>
                    <img src="../<?php echo $ticket['image_path']; ?>" alt="Gambar Tiket"
                        style="max-width: 200px; display: block; margin-bottom: 10px;">
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
                <small>Format: JPG, PNG, GIF (Max 2MB)</small>
            </div>

            <button type="submit" class="btn">Update</button>
            <a href="index.php" class="btn">Kembali</a>
        </form>
    </div>
</body>

</html>