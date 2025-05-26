<?php
include 'includes/db.php';
include 'includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['participant_id'])) {
    redirect('login.php', 'Silakan login terlebih dahulu');
}

$participant_id = $_SESSION['participant_id'];
$participant_name = $_SESSION['participant_name'];

// Ambil riwayat transaksi
$transactions = $conn->query("
    SELECT t.*, 
           tk.type as ticket_type, tk.price as ticket_price,
           e.name as event_name, e.date as event_date, e.venue,
           py.method as payment_method, py.status as payment_status
    FROM transactions t
    JOIN tickets tk ON t.ticket_id = tk.id
    JOIN events e ON tk.event_id = e.id
    JOIN payments py ON t.payment_id = py.id
    WHERE t.participant_id = $participant_id
    ORDER BY t.id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Peserta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .header {
            background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .user-info {
            text-align: right;
        }

        .logout-btn {
            background: white;
            color: #8B5CF6;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .transaction-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
            margin-bottom: 15px;
        }

        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .transaction-header:hover {
            background: #f0f0f0;
        }

        .transaction-details {
            display: none;
            padding: 15px;
            background: #fafafa;
            border-radius: 5px;
            margin-top: 10px;
        }

        .detail-section {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .detail-section:last-child {
            border-bottom: none;
        }

        .detail-section h3 {
            margin-top: 0;
            color: #8B5CF6;
        }

        .btn {
            background: #8B5CF6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #7C4DFF;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .status-badge.paid {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-badge.pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-badge.failed {
            background: #FEE2E2;
            color: #991B1B;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .transaction-item.active .toggle-icon {
            transform: rotate(180deg);
        }

        .transaction-item.active .transaction-details {
            display: block;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <h1>Konser Musik</h1>
            <div class="user-info">
                <p>Halo, <?php echo htmlspecialchars($participant_name); ?></p>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Riwayat Transaksi Terakhir</h2>
            <?php if ($transactions->num_rows > 0): ?>
                <?php while ($transaction = $transactions->fetch_assoc()): ?>
                    <div class="transaction-item" id="transaction-<?= $transaction['id'] ?>">
                        <div class="transaction-header" onclick="toggleDetails(<?= $transaction['id'] ?>)">
                            <div>
                                <h3><?php echo htmlspecialchars($transaction['event_name']); ?></h3>
                                <p>Tanggal: <?php echo date('d M Y H:i', strtotime($transaction['transaction_date'])); ?></p>
                                <p>Total: Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></p>
                            </div>
                            <div>
                                <span class="status-badge <?= $transaction['payment_status'] ?>">
                                    <?= ucfirst($transaction['payment_status']) ?>
                                </span>
                                <i class="fas fa-chevron-down toggle-icon"></i>
                            </div>
                        </div>

                        <div class="transaction-details">
                            <div class="detail-section">
                                <h3>Informasi Event</h3>
                                <p><strong>Nama Event:</strong> <?= htmlspecialchars($transaction['event_name']) ?></p>
                                <p><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($transaction['event_date'])) ?></p>
                                <p><strong>Tempat:</strong> <?= htmlspecialchars($transaction['venue']) ?></p>
                            </div>

                            <div class="detail-section">
                                <h3>Detail Tiket</h3>
                                <p><strong>Jenis Tiket:</strong> <?= $transaction['ticket_type'] ?></p>
                                <p><strong>Harga Satuan:</strong> Rp <?= number_format($transaction['ticket_price'], 0, ',', '.') ?></p>
                                <p><strong>Jumlah:</strong> <?= $transaction['quantity'] ?></p>
                                <p><strong>Total Harga:</strong> Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></p>
                            </div>

                            <div class="detail-section">
                                <h3>Informasi Pembayaran</h3>
                                <p><strong>Metode:</strong> <?= ucfirst(str_replace('_', ' ', $transaction['payment_method'])) ?></p>
                                <p><strong>Status:</strong>
                                    <span class="status-badge <?= $transaction['payment_status'] ?>">
                                        <?= ucfirst($transaction['payment_status']) ?>
                                    </span>
                                </p>
                            </div>

                            <a href="detail.php?id=<?= $transaction['id'] ?>" class="btn">Lihat Detail Lengkap</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Anda belum memiliki transaksi.</p>
            <?php endif; ?>
        </div>

        <a href="home.php" class="btn">Lihat Event</a>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        function toggleDetails(transactionId) {
            const transactionItem = document.getElementById(`transaction-${transactionId}`);
            transactionItem.classList.toggle('active');
        }
    </script>
</body>

</html>