<?php
include '../includes/db.php';
include '../includes/functions.php';

$sql = "SELECT * FROM participants ORDER BY name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Peserta</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Daftar Peserta</h1>
        <?php display_message(); ?>
        <a href="../index.php" class="btn">Kembali ke Menu Utama</a>


        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
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