<?php
include '../includes/db.php';
include '../includes/functions.php';

$sql = "SELECT * FROM events ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Event</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Daftar Event</h1>
        <?php display_message(); ?>
        <a href="../index.php" class="btn">Kembali ke Menu Utama</a>
        <a href="create.php" class="btn">Tambah Event</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Tanggal</th>
                    <th>Tempat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['date'])) ?></td>
                        <td><?= htmlspecialchars($row['venue']) ?></td>
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
