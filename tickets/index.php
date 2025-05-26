<?php
include '../includes/db.php';
include '../includes/functions.php';

$sql = "SELECT t.*, e.name as event_name 
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        ORDER BY t.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Tiket</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Daftar Tiket</h1>
        <?php display_message(); ?>
        <a href="../index.php" class="btn">Kembali ke Menu Utama</a>
        <a href="create.php" class="btn">Tambah Tiket</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event</th>
                    <th>Jenis</th>
                    <th>Harga</th>
                    <th>Kuota</th>
                    <th>Tersedia</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['event_name']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><?= number_format($row['price'], 0, ',', '.') ?></td>
                        <td><?= $row['quota'] ?></td>
                        <td><?= $row['available'] ?></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn">Edit</a>
                            <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')"
                                class="btn danger">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>