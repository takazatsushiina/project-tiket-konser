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
    SELECT t.id, t.quantity, t.total_amount, 
           tk.type as ticket_type, e.name as event_name, e.date as event_date
    FROM transactions t
    JOIN tickets tk ON t.ticket_id = tk.id
    JOIN events e ON tk.event_id = e.id
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
                    <div class="transaction-item">
                        <h3><?php echo htmlspecialchars($transaction['event_name']); ?></h3>
                        <p>Tiket: <?php echo htmlspecialchars($transaction['ticket_type']); ?></p>
                        <p>Jumlah: <?php echo $transaction['quantity']; ?></p>
                        <p>Total: Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></p>
                        <p>Tanggal: <?php echo date('d M Y H:i', strtotime($transaction['created_at'])); ?></p>
                    </div>
                <?php endwhile; ?>
                <a href="transactions.php" class="btn">Lihat Semua Transaksi</a>
            <?php else: ?>
                <p>Anda belum memiliki transaksi.</p>
            <?php endif; ?>
        </div>

        <a href="home.php" class="btn">Lihat Event</a>
    </div>
</body>

</html>