<?php
include '../includes/db.php';
include '../includes/functions.php';

// Ambil data peserta dan tiket
$participants = $conn->query("SELECT id, name FROM participants ORDER BY name");
$tickets = $conn->query("SELECT t.id, t.type, t.price, t.available, e.name as event_name 
                         FROM tickets t
                         JOIN events e ON t.event_id = e.id
                         WHERE t.available > 0
                         ORDER BY e.date DESC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $participant_id = intval($_POST['participant_id']);
    $ticket_id = intval($_POST['ticket_id']);
    $quantity = intval($_POST['quantity']);
    $payment_method = clean_input($_POST['payment_method']);

    // Validasi quantity
    if ($quantity <= 0) {
        $error = "Jumlah tiket harus lebih dari 0";
    } else {
        // Mulai transaksi
        $conn->begin_transaction();

        try {
            // Dapatkan harga tiket
            $ticket = $conn->query("SELECT price, available FROM tickets WHERE id = $ticket_id FOR UPDATE")->fetch_assoc();
            $total_amount = $ticket['price'] * $quantity;

            // Cek ketersediaan
            if ($ticket['available'] < $quantity) {
                throw new Exception("Kuota tiket tidak mencukupi");
            }

            // Buat pembayaran
            $stmt = $conn->prepare("INSERT INTO payments (method, amount, status) VALUES (?, ?, 'paid')");
            $stmt->bind_param("sd", $payment_method, $total_amount);
            $stmt->execute();
            $payment_id = $conn->insert_id;

            // Buat transaksi
            $stmt = $conn->prepare("INSERT INTO transactions (participant_id, ticket_id, payment_id, quantity, total_amount, status) VALUES (?, ?, ?, ?, ?, 'completed')");
            $stmt->bind_param("iiiid", $participant_id, $ticket_id, $payment_id, $quantity, $total_amount);
            $stmt->execute();
            $transaction_id = $conn->insert_id;

            // Kurangi kuota tiket
            $stmt = $conn->prepare("UPDATE tickets SET available = available - ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $ticket_id);
            $stmt->execute();

            // Commit transaksi
            $conn->commit();

            redirect("detail.php?id=$transaction_id", 'Transaksi berhasil dibuat');
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaksi gagal: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Buat Transaksi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Buat Transaksi</h1>

        <?php if (isset($error))
            echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="post">
            <div class="form-group">
                <label>Peserta</label>
                <select name="participant_id" required>
                    <option value="">Pilih Peserta</option>
                    <?php while ($participant = $participants->fetch_assoc()): ?>
                        <option value="<?= $participant['id'] ?>"><?= htmlspecialchars($participant['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tiket</label>
                <select name="ticket_id" required>
                    <option value="">Pilih Tiket</option>
                    <?php while ($ticket = $tickets->fetch_assoc()): ?>
                        <option value="<?= $ticket['id'] ?>" data-price="<?= $ticket['price'] ?>"
                            data-available="<?= $ticket['available'] ?>">
                            <?= htmlspecialchars($ticket['event_name']) ?> - <?= $ticket['type'] ?> (Rp
                            <?= number_format($ticket['price'], 0, ',', '.') ?>, Tersedia: <?= $ticket['available'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jumlah</label>
                <input type="number" name="quantity" min="1" value="1" required>
            </div>

            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select name="payment_method" required>
                    <option value="transfer">Transfer Bank</option>
                    <option value="credit_card">Kartu Kredit</option>
                    <option value="cash">Tunai</option>
                    <option value="e_wallet">E-Wallet</option>
                </select>
            </div>

            <div class="form-group">
                <label>Total Harga</label>
                <div id="total-amount">Rp 0</div>
            </div>

            <button type="submit" class="btn">Buat Transaksi</button>
            <a href="index.php" class="btn">Kembali</a>
        </form>
    </div>

    <script>
        // Hitung total harga secara real-time
        document.querySelector('select[name="ticket_id"]').addEventListener('change', updateTotal);
        document.querySelector('input[name="quantity"]').addEventListener('input', updateTotal);

        function updateTotal() {
            const ticketSelect = document.querySelector('select[name="ticket_id"]');
            const quantityInput = document.querySelector('input[name="quantity"]');
            const totalAmountDiv = document.getElementById('total-amount');

            if (ticketSelect.value && quantityInput.value) {
                const price = ticketSelect.options[ticketSelect.selectedIndex].dataset.price;
                const quantity = quantityInput.value;
                const total = price * quantity;

                totalAmountDiv.textContent = 'Rp ' + total.toLocaleString('id-ID');
            } else {
                totalAmountDiv.textContent = 'Rp 0';
            }
        }
    </script>
</body>

</html>