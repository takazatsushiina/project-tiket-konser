<?php
include '../includes/db.php';
include '../includes/functions.php';

// Ambil daftar event untuk dropdown
$events = $conn->query("SELECT id, name FROM events ORDER BY date DESC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = intval($_POST['event_id']);
    $type = clean_input($_POST['type']);
    $price = floatval($_POST['price']);
    $quota = intval($_POST['quota']);

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/tickets/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('ticket_', true) . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;

        // Validate image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($file_ext), $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
                $image_path = 'uploads/tickets/' . $file_name;
            }
        }
    }

    $sql = "INSERT INTO tickets (event_id, type, price, quota, available, image_path) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiiis", $event_id, $type, $price, $quota, $quota, $image_path);

    if ($stmt->execute()) {
        redirect('index.php', 'Tiket berhasil ditambahkan');
    } else {
        $error = "Gagal menambahkan tiket: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Tiket</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Tambah Tiket</h1>

        <?php if (isset($error))
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Event</label>
                <select name="event_id" required>
                    <option value="">Pilih Event</option>
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jenis Tiket</label>
                <input type="text" name="type" required>
            </div>

            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="price" min="0" required>
            </div>

            <div class="form-group">
                <label>Kuota</label>
                <input type="number" name="quota" min="1" required>
            </div>

            <div class="form-group">
                <label>Gambar Tiket</label>
                <input type="file" name="image" accept="image/*">
                <small>Format: JPG, PNG, GIF (Max 2MB)</small>
            </div>

            <button type="submit" class="btn">Simpan</button>
            <a href="index.php" class="btn">Kembali</a>
        </form>
    </div>
</body>

</html>