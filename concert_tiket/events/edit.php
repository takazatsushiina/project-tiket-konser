<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php', 'ID event tidak valid');
}

$id = intval($_GET['id']);

// Ambil data event
$sql = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    redirect('index.php', 'Event tidak ditemukan');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $date = clean_input($_POST['date']);
    $venue = clean_input($_POST['venue']);
    $description = clean_input($_POST['description']);

    $sql = "UPDATE events SET name=?, date=?, venue=?, description=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $date, $venue, $description, $id);

    if ($stmt->execute()) {
        redirect('index.php', 'Event berhasil diperbarui');
    } else {
        $error = "Gagal memperbarui event: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Event</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Edit Event</h1>

        <?php if (isset($error))
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="post">
            <div class="form-group">
                <label>Nama Event</label>
                <input type="text" name="name" value="<?= htmlspecialchars($event['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Tanggal</label>
                <input type="datetime-local" name="date" value="<?= date('Y-m-d\TH:i', strtotime($event['date'])) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Tempat</label>
                <input type="text" name="venue" value="<?= htmlspecialchars($event['venue']) ?>" required>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($event['description']) ?></textarea>
            </div>

            <button type="submit" class="btn">Update</button>
            <a href="index.php" class="btn">Kembali</a>
        </form>
    </div>
</body>

</html>