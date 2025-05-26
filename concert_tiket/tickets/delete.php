<?php
include '../includes/db.php';
include '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php', 'ID tiket tidak valid');
}

$id = intval($_GET['id']);

// Cek apakah tiket memiliki transaksi terkait
$stmt_check = $conn->prepare("SELECT COUNT(*) as count FROM transactions WHERE ticket_id = ?");
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$check_result = $stmt_check->get_result();
$has_transactions = $check_result->fetch_assoc()['count'] > 0;
$stmt_check->close();

if ($has_transactions) {
    redirect('index.php', 'Tidak dapat menghapus tiket karena memiliki transaksi terkait');
}

// Ambil path gambar untuk dihapus
$stmt_image = $conn->prepare("SELECT image_path FROM tickets WHERE id = ?");
$stmt_image->bind_param("i", $id);
$stmt_image->execute();
$image_result = $stmt_image->get_result();
$image_path = $image_result->fetch_assoc()['image_path'];
$stmt_image->close();

// Hapus tiket dari database
$sql = "DELETE FROM tickets WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Hapus gambar jika ada
    if (!empty($image_path) && file_exists('../' . $image_path)) {
        unlink('../' . $image_path);
    }
    redirect('index.php', 'Tiket berhasil dihapus');
} else {
    redirect('index.php', 'Gagal menghapus tiket: ' . $conn->error);
}
?>