<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php', 'ID peserta tidak valid');
}

$id = intval($_GET['id']);

// Cek apakah peserta memiliki transaksi
$stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM transactions WHERE participant_id = ?");
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$check_result = $stmt_check->get_result();
$has_transactions = $check_result->fetch_assoc()['count'] > 0;
$stmt_check->close();

if ($has_transactions) {
    redirect('index.php', 'Tidak dapat menghapus peserta karena memiliki transaksi terkait');
}

$sql = "DELETE FROM participants WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    redirect('index.php', 'Peserta berhasil dihapus');
} else {
    redirect('index.php', 'Gagal menghapus peserta: ' . $conn->error);
}
?>