<?php
include '../includes/db.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    // In participants/create.php, before inserting new participant
    $check = $conn->query("SELECT id FROM participants WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $error = "Email sudah terdaftar";
        // Optionally, you can fetch the existing participant and show their details
        $existing = $check->fetch_assoc();
        // Then suggest to use existing record or create new with different email
    }
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } else {
        $sql = "INSERT INTO participants (name, email, phone) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $phone);

        if ($stmt->execute()) {
            redirect('index.php', 'Peserta berhasil ditambahkan');
        } else {
            $error = "Gagal menambahkan peserta: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Peserta</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Tambah Peserta</h1>

        <?php if (isset($error))
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="post">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="tel" name="phone" required>
            </div>

            <button type="submit" class="btn">Simpan</button>
            <a href="index.php" class="btn">Kembali</a>
        </form>
    </div>
</body>

</html>