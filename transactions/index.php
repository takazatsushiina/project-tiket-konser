<?php
include '../includes/db.php';
include '../includes/functions.php';

$sql = "SELECT t.id, p.name as participant, e.name as event, tk.type as ticket, 
               t.quantity, t.total_amount, t.status, t.transaction_date
        FROM transactions t
        JOIN participants p ON t.participant_id = p.id
        JOIN tickets tk ON t.ticket_id = tk.id
        JOIN events e ON tk.event_id = e.id
        ORDER BY t.transaction_date DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>
    <title>Daftar Transaksi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Daftar Transaksi</h1>
        <?php display_message(); ?>
        <a href="../index.php" class="btn">Kembali ke Menu Utama</a>
        <!--<a href="create.php" class="btn">Buat Transaksi Baru</a>-->

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Peserta</th>
                    <th>Event</th>
                    <th>Tiket</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['participant']) ?></td>
                        <td><?= htmlspecialchars($row['event']) ?></td>
                        <td><?= htmlspecialchars($row['ticket']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td>Rp <?= number_format($row['total_amount'], 0, ',', '.') ?></td>
                        <td>
                            <span class="status-badge <?= $row['status'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y H:i', strtotime($row['transaction_date'])) ?></td>
                        <td>
                            <a href="detail.php?id=<?= $row['id'] ?>" class="btn">Detail</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>