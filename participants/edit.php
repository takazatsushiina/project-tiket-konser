<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php', 'ID peserta tidak valid');
}

$id = intval($_GET['id']);

// Ambil data peserta
$sql = "SELECT * FROM participants WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$participant = $stmt->get_result()->fetch_assoc();

if (!$participant) {
    redirect('index.php', 'Peserta tidak ditemukan');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } else {
        $sql = "UPDATE participants SET name=?, email=?, phone=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $phone, $id);

        if ($stmt->execute()) {
            redirect('index.php', 'Data peserta berhasil diperbarui');
        } else {
            $error = "Gagal memperbarui peserta: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Peserta</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Edit Peserta</h1>

        <?php if (isset($error))
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="post">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?= htmlspecialchars($participant['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($participant['email']) ?>" required>
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($participant['phone']) ?>" required>
            </div>

            <button type="submit" class="btn">Update</button>
            <a href="index.php" class="btn">Kembali</a>
        </form>
    </div>
</body>

</html>