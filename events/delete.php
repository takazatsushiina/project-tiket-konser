<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php', 'ID event tidak valid');
}

$id = intval($_GET['id']);

// Cek apakah event memiliki tiket terkait
$stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM tickets WHERE event_id = ?");
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$check_result = $stmt_check->get_result();
$has_tickets = $check_result->fetch_assoc()['count'] > 0;
$stmt_check->close();

if ($has_tickets) {
    redirect('index.php', 'Tidak dapat menghapus event karena memiliki tiket terkait');
}

$sql = "DELETE FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    redirect('index.php', 'Event berhasil dihapus');
} else {
    redirect('index.php', 'Gagal menghapus event: ' . $conn->error);
}
?>