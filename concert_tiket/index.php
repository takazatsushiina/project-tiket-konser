<?php
include 'includes/db.php';
include 'includes/functions.php';

// Ambil statistik
$events_count = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];
$tickets_count = $conn->query("SELECT COUNT(*) as count FROM tickets")->fetch_assoc()['count'];
$participants_count = $conn->query("SELECT COUNT(*) as count FROM participants")->fetch_assoc()['count'];
$transactions_count = $conn->query("SELECT COUNT(*) as count FROM transactions")->fetch_assoc()['count'];

// Ambil event mendatang
$upcoming_events = $conn->query("SELECT * FROM events WHERE date >= NOW() ORDER BY date LIMIT 5");

// Ambil transaksi terbaru
$recent_transactions = $conn->query("SELECT t.id, p.name as participant, e.name as event, tk.type as ticket, t.quantity, t.total_amount 
                                    FROM transactions t
                                    JOIN participants p ON t.participant_id = p.id
                                    JOIN tickets tk ON t.ticket_id = tk.id
                                    JOIN events e ON tk.event_id = e.id
                                    ORDER BY t.transaction_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard - Sistem Tiket Konser</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Sistem Tiket Konser</h1>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Dashboard</a></li>
                    <li><a href="events/">Event</a></li>
                    <li><a href="tickets/">Tiket</a></li>
                    <li><a href="participants/">Peserta</a></li>
                    <li><a href="transactions/">Transaksi</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <section class="stats">
                <div class="stat-card">
                    <h3>Total Event</h3>
                    <p><?= $events_count ?></p>
                    <a href="events/" class="btn small">Lihat Semua</a>
                </div>
                <div class="stat-card">
                    <h3>Total Tiket</h3>
                    <p><?= $tickets_count ?></p>
                    <a href="tickets/" class="btn small">Lihat Semua</a>
                </div>
                <div class="stat-card">
                    <h3>Total Peserta</h3>
                    <p><?= $participants_count ?></p>
                    <a href="participants/" class="btn small">Lihat Semua</a>
                </div>
                <div class="stat-card">
                    <h3>Total Transaksi</h3>
                    <p><?= $transactions_count ?></p>
                    <a href="transactions/" class="btn small">Lihat Semua</a>
                </div>
            </section>

            <section class="dashboard-section">
                <h2>Event Mendatang</h2>
                <?php if ($upcoming_events->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Event</th>
                                <th>Tanggal</th>
                                <th>Tempat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($event_id = $upcoming_events->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($event_id['name']) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($event_id['date'])) ?></td>
                                    <td><?= htmlspecialchars($event_id['venue']) ?></td>
                                    <td>
                                        <a href="events/edit.php?id=<?= $event_id['id'] ?>" class="btn small">Edit</a>
                                        <a href="tickets/create.php?event_id=<?= $event_id['id'] ?>" class="btn small">Tambah
                                            Tiket</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Tidak ada event mendatang.</p>
                <?php endif; ?>
                <a href="events/create.php" class="btn">Tambah Event Baru</a>
            </section>

            <section class="dashboard-section">
                <h2>Transaksi Terbaru</h2>
                <?php if ($recent_transactions->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Peserta</th>
                                <th>Event</th>
                                <th>Tiket</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $transaction['id'] ?></td>
                                    <td><?= htmlspecialchars($transaction['participant']) ?></td>
                                    <td><?= htmlspecialchars($transaction['event']) ?></td>
                                    <td><?= $transaction['ticket'] ?></td>
                                    <td><?= $transaction['quantity'] ?></td>
                                    <td>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Belum ada transaksi.</p>
                <?php endif; ?>
                <a href="transactions/create.php" class="btn">Buat Transaksi Baru</a>
            </section>
        </main>

        <footer>
            <p>&copy; <?= date('Y') ?> Sistem Tiket Konser</p>
        </footer>
    </div>
</body>

</html>