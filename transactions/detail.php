<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php', 'ID transaksi tidak valid');
}

$id = intval($_GET['id']);

$sql = "SELECT t.*, p.name as participant_name, p.email, p.phone, 
               tk.type as ticket_type, tk.price as ticket_price,
               e.name as event_name, e.date as event_date, e.venue,
               py.method as payment_method, py.status as payment_status
        FROM transactions t
        JOIN participants p ON t.participant_id = p.id
        JOIN tickets tk ON t.ticket_id = tk.id
        JOIN events e ON tk.event_id = e.id
        JOIN payments py ON t.payment_id = py.id
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    redirect('index.php', 'Transaksi tidak ditemukan');
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detail Transaksi #<?= $transaction['id'] ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Detail Transaksi #<?= $transaction['id'] ?></h1>

        <div class="transaction-details">
            <div class="detail-section">
                <h2>Informasi Event</h2>
                <p><strong>Nama Event:</strong> <?= htmlspecialchars($transaction['event_name']) ?></p>
                <p><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($transaction['event_date'])) ?></p>
                <p><strong>Tempat:</strong> <?= htmlspecialchars($transaction['venue']) ?></p>
            </div>

            <div class="detail-section">
                <h2>Informasi Peserta</h2>
                <p><strong>Nama:</strong> <?= htmlspecialchars($transaction['participant_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($transaction['email']) ?></p>
                <p><strong>Telepon:</strong> <?= htmlspecialchars($transaction['phone']) ?></p>
            </div>

            <div class="detail-section">
                <h2>Detail Tiket</h2>
                <p><strong>Jenis Tiket:</strong> <?= $transaction['ticket_type'] ?></p>
                <p><strong>Harga Satuan:</strong> Rp <?= number_format($transaction['ticket_price'], 0, ',', '.') ?></p>
                <p><strong>Jumlah:</strong> <?= $transaction['quantity'] ?></p>
                <p><strong>Total Harga:</strong> Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></p>
            </div>

            <div class="detail-section">
                <h2>Informasi Pembayaran</h2>
                <p><strong>Metode:</strong> <?= ucfirst(str_replace('_', ' ', $transaction['payment_method'])) ?></p>
                <p><strong>Status:</strong>
                    <span class="status-badge <?= $transaction['payment_status'] ?>">
                        <?= ucfirst($transaction['payment_status']) ?>
                    </span>
                </p>
                <p><strong>Tanggal Transaksi:</strong>
                    <?= date('d M Y H:i', strtotime($transaction['transaction_date'])) ?></p>
            </div>
        </div>

        <a href="index.php" class="btn">Kembali ke Daftar</a>
    </div>
</body>

</html>