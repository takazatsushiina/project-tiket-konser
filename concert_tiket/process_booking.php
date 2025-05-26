<?php
include 'includes/db.php';
include 'includes/functions.php';

session_start();
if (!isset($_SESSION['participant_id'])) {
    redirect('login.php', 'Silakan login terlebih dahulu');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $participant_id = $_SESSION['participant_id'];
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
            $ticket = $conn->query("SELECT price, available, event_id FROM tickets WHERE id = $ticket_id FOR UPDATE")->fetch_assoc();
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

            redirect("transaction_detail.php?id=$transaction_id", 'Pembelian tiket berhasil!');
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaksi gagal: " . $e->getMessage();
            redirect("event_detail.php?id=" . $ticket['event_id'], $error);
        }
    }
} else {
    redirect('home.php');
}
?>