// transactions/update_status.php
<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php', 'ID transaksi tidak valid');
}

$id = intval($_GET['id']);
$transaction = $conn->query("SELECT t.*, p.status as payment_status 
                            FROM transactions t
                            JOIN payments p ON t.payment_id = p.id
                            WHERE t.id = $id")->fetch_assoc();

if (!$transaction) {
    redirect('index.php', 'Transaksi tidak ditemukan');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = clean_input($_POST['status']);

    $conn->begin_transaction();

    try {
        // Update transaction status
        $conn->query("UPDATE transactions SET status = '$new_status' WHERE id = $id");

        // Update payment status if needed
        if ($new_status == 'completed' && $transaction['payment_status'] != 'paid') {
            $conn->query("UPDATE payments SET status = 'paid', payment_date = NOW() WHERE id = {$transaction['payment_id']}");
        } elseif ($new_status == 'cancelled') {
            // Return ticket quota
            $conn->query("UPDATE tickets t 
                         JOIN transactions tr ON t.id = tr.ticket_id
                         SET t.available = t.available + tr.quantity
                         WHERE tr.id = $id");

            // Mark payment as failed
            $conn->query("UPDATE payments SET status = 'failed' WHERE id = {$transaction['payment_id']}");
        }

        $conn->commit();
        redirect('index.php', 'Status transaksi berhasil diperbarui');
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal memperbarui status: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Update Status Transaksi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Update Status Transaksi #<?= $id ?></h1>

        <?php if (isset($error))
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="post">
            <div class="form-group">
                <label>Status Saat Ini</label>
                <input type="text" value="<?= ucfirst($transaction['status']) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Status Baru</label>
                <select name="status" required>
                    <option value="pending" <?= $transaction['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $transaction['status'] == 'completed' ? 'selected' : '' ?>>Completed
                    </option>
                    <option value="cancelled" <?= $transaction['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled
                    </option>
                </select>
            </div>

            <button type="submit" name="update_status" class="btn">Update Status</button>
            <a href="index.php" class="btn">Kembali</a>
        </form>
    </div>
</body>

</html>