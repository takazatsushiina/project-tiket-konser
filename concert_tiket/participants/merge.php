// participants/merge.php
<?php
include '../includes/db.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['merge'])) {
    $primary_id = intval($_POST['primary_id']);
    $duplicate_id = intval($_POST['duplicate_id']);

    $conn->begin_transaction();

    try {
        // Update all transactions referencing the duplicate to point to primary
        $conn->query("UPDATE transactions SET participant_id = $primary_id WHERE participant_id = $duplicate_id");

        // Delete the duplicate participant
        $conn->query("DELETE FROM participants WHERE id = $duplicate_id");

        $conn->commit();
        redirect('index.php', 'Peserta berhasil digabungkan');
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal menggabungkan peserta: " . $e->getMessage();
    }
}

// Get duplicate participants (same email or phone)
$duplicates = $conn->query("SELECT p1.id as id1, p1.name as name1, p1.email as email1, p1.phone as phone1,
                                   p2.id as id2, p2.name as name2, p2.email as email2, p2.phone as phone2
                            FROM participants p1
                            JOIN participants p2 ON (p1.email = p2.email OR p1.phone = p2.phone) AND p1.id < p2.id
                            ORDER BY p1.email, p1.phone");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Gabungkan Peserta</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Gabungkan Peserta</h1>

        <?php if (isset($error))
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <h2>Daftar Duplikat</h2>

        <?php if ($duplicates->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Peserta 1</th>
                        <th>Peserta 2</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($dup = $duplicates->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong>ID:</strong> <?= $dup['id1'] ?><br>
                                <strong>Nama:</strong> <?= htmlspecialchars($dup['name1']) ?><br>
                                <strong>Email:</strong> <?= htmlspecialchars($dup['email1']) ?><br>
                                <strong>Telepon:</strong> <?= htmlspecialchars($dup['phone1']) ?>
                            </td>
                            <td>
                                <strong>ID:</strong> <?= $dup['id2'] ?><br>
                                <strong>Nama:</strong> <?= htmlspecialchars($dup['name2']) ?><br>
                                <strong>Email:</strong> <?= htmlspecialchars($dup['email2']) ?><br>
                                <strong>Telepon:</strong> <?= htmlspecialchars($dup['phone2']) ?>
                            </td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="primary_id" value="<?= $dup['id1'] ?>">
                                    <input type="hidden" name="duplicate_id" value="<?= $dup['id2'] ?>">
                                    <button type="submit" name="merge" class="btn">Gabungkan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ditemukan peserta duplikat.</p>
        <?php endif; ?>
    </div>
</body>

</html>