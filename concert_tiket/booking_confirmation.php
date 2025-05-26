<?php
include 'includes/db.php';
include 'includes/functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$transaction_id = intval($_GET['id']);

$sql = "SELECT t.*, p.name as participant_name, p.email, p.phone, 
               tk.type as ticket_type, tk.price as ticket_price,
               e.name as event_name, e.date as event_date, e.venue,
               py.method as payment_method
        FROM transactions t
        JOIN participants p ON t.participant_id = p.id
        JOIN tickets tk ON t.ticket_id = tk.id
        JOIN events e ON tk.event_id = e.id
        JOIN payments py ON t.payment_id = py.id
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Konfirmasi Pembelian</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .confirmation-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.15);
            padding: 30px;
            max-width: 800px;
            margin: 40px auto;
        }

        .confirmation-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .confirmation-header h1 {
            color: #8B5CF6;
            margin-bottom: 10px;
        }

        .confirmation-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .detail-section {
            margin-bottom: 20px;
        }

        .detail-section h3 {
            color: #8B5CF6;
            margin-bottom: 15px;
            border-bottom: 2px solid #F3F4F6;
            padding-bottom: 8px;
        }

        .detail-item {
            margin-bottom: 10px;
            display: flex;
        }

        .detail-label {
            font-weight: 600;
            min-width: 120px;
            color: #6B7280;
        }

        .detail-value {
            color: #374151;
        }

        .success-icon {
            font-size: 4rem;
            color: #10B981;
            margin-bottom: 20px;
        }

        .actions {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="confirmation-card">
            <div class="confirmation-header">
                <div class="success-icon">✓</div>
                <h1>Pembelian Tiket Berhasil!</h1>
                <p>Terima kasih telah melakukan pembelian. Detail transaksi Anda:</p>
            </div>

            <div class="confirmation-details">
                <div>
                    <div class="detail-section">
                        <h3>Informasi Event</h3>
                        <div class="detail-item">
                            <div class="detail-label">Event:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($transaction['event_name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Tanggal:</div>
                            <div class="detail-value">
                                <?php echo date('l, d F Y H:i', strtotime($transaction['event_date'])); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Lokasi:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($transaction['venue']); ?></div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Detail Tiket</h3>
                        <div class="detail-item">
                            <div class="detail-label">Jenis Tiket:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($transaction['ticket_type']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Harga Satuan:</div>
                            <div class="detail-value">Rp
                                <?php echo number_format($transaction['ticket_price'], 0, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Jumlah:</div>
                            <div class="detail-value"><?php echo $transaction['quantity']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Total Harga:</div>
                            <div class="detail-value">Rp
                                <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="detail-section">
                        <h3>Informasi Peserta</h3>
                        <div class="detail-item">
                            <div class="detail-label">Nama:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($transaction['participant_name']); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($transaction['email']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Telepon:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($transaction['phone']); ?></div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Informasi Pembayaran</h3>
                        <div class="detail-item">
                            <div class="detail-label">Metode:</div>
                            <div class="detail-value">
                                <?php echo ucfirst(str_replace('_', ' ', $transaction['payment_method'])); ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">ID Transaksi:</div>
                            <div class="detail-value">#<?php echo $transaction['id']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value">Menunggu Pembayaran</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions">
                <p>Instruksi pembayaran akan dikirim ke email Anda. Silakan cek inbox atau folder spam.</p>
                <a href="index.php" class="btn">Kembali ke Beranda</a>
                <a href="#" class="btn" onclick="window.print()">Cetak Tiket</a>
            </div>
        </div>
    </div>
</body>

</html>