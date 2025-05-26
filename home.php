<?php
session_start();
include 'includes/db.php';
include 'includes/functions.php';

// Ambil semua event mendatang beserta info ketersediaan tiket
$events = $conn->query("SELECT e.*, 
                      (SELECT COUNT(*) FROM tickets t WHERE t.event_id = e.id AND t.available > 0) as available_tickets
                      FROM events e
                      WHERE e.date >= NOW()
                      ORDER BY e.date ASC");
if (!$events) {
    die("Query failed: " . $conn->error);
}

// Cek apakah user sudah login
$logged_in = false;

if (isset($_SESSION['participant_id'])) {
    $logged_in = true;
    $participant_name = $_SESSION['participant_name'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Konser Musik - Beli Tiket Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header {
            background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%);
            color: white;
            padding: 20px 0;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .auth-btn {
            background: white;
            color: #8B5CF6;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background: white;
            color: #8B5CF6;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .event-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.15);
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .event-content {
            padding: 20px;
        }

        .event-date {
            color: #8B5CF6;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .event-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #374151;
        }

        .event-venue {
            color: #6B7280;
            margin-bottom: 15px;
        }

        .ticket-list {
            margin-top: 20px;
            border-top: 1px solid #E5E7EB;
            padding-top: 20px;
        }

        .ticket-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .ticket-type {
            font-weight: 600;
        }

        .ticket-price {
            color: #8B5CF6;
            font-weight: 700;
        }

        .ticket-available {
            color: #6B7280;
            font-size: 0.9rem;
        }

        .buy-btn {
            background: #8B5CF6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .buy-btn:hover {
            background: #7C4DFF;
        }

        .buy-btn:disabled {
            background: #E5E7EB;
            color: #9CA3AF;
            cursor: not-allowed;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .hero {
            background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .login-notice {
            background: #FFF3CD;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .availability-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #10B981;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .sold-out-badge {
            background: #EF4444;
        }

        .no-tickets-notice {
            color: #EF4444;
            font-weight: 600;
            margin-top: 15px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
        }

        .modal-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #374151;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-btn-confirm {
            background: #8B5CF6;
            color: white;
            border: none;
        }

        .modal-btn-cancel {
            background: #E5E7EB;
            color: #374151;
            border: none;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <h1>Konser Musik</h1>
            <?php if ($logged_in): ?>
                <div class="user-info">
                    <span>Halo, <?php echo htmlspecialchars($participant_name); ?></span>
                    <a href="dashboard.php" class="auth-btn">Dashboard</a>
                    <a href="logout.php" class="auth-btn">Logout</a>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="auth-btn">Login</a>
                    <a href="register.php" class="auth-btn">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero">
        <div class="container">
            <h1>Konser Musik Terbaik</h1>
            <p>Beli tiket konser favorit Anda secara online dengan mudah dan aman. Nikmati pengalaman menonton konser
                yang tak terlupakan!</p>
        </div>
    </div>

    <div class="container">
        <?php if (!$logged_in): ?>
            <div class="login-notice">
                Silakan <a href="login.php">login</a> atau <a href="register.php">daftar</a> untuk membeli tiket.
            </div>
        <?php endif; ?>

        <h2>Event Mendatang</h2>

        <?php if ($events->num_rows > 0): ?>
            <div class="events-grid">
                <?php while ($event = $events->fetch_assoc()): ?>
                    <div class="event-card">
                        <?php if ($event['available_tickets'] > 0): ?>
                            <div class="availability-badge">Tiket Tersedia</div>
                        <?php else: ?>
                            <div class="availability-badge sold-out-badge">Habis</div>
                        <?php endif; ?>
                        
                        <?php if (!empty($event['image_path'])): ?>
                            <img src="<?php echo $event['image_path']; ?>"
                                alt="<?php echo htmlspecialchars($event['name']); ?>" class="event-image">
                        <?php else: ?>
                            <img src="assets/images/default-event.jpg" alt="Default Event Image" class="event-image">
                        <?php endif; ?>

                        <div class="event-content">
                            <div class="event-date">
                                <?php echo date('l, d F Y', strtotime($event['date'])); ?>
                            </div>
                            <h3 class="event-title"><?php echo htmlspecialchars($event['name']); ?></h3>
                            <div class="event-venue">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['venue']); ?>
                            </div>

                            <div class="ticket-list">
                                <h4>Daftar Tiket</h4>
                                <?php
                                $event_id = (int)$event['id'];
                                $tickets = $conn->query("SELECT * FROM tickets WHERE event_id = $event_id ORDER BY price");
                                if ($tickets && $tickets->num_rows > 0):
                                    while ($ticket = $tickets->fetch_assoc()): ?>
                                        <div class="ticket-item">
                                            <div>
                                                <div class="ticket-type"><?php echo htmlspecialchars($ticket['type']); ?></div>
                                                <div class="ticket-price">Rp <?php echo number_format($ticket['price'], 0, ',', '.'); ?>
                                                </div>
                                                <div class="ticket-available">Tersedia: <?php echo $ticket['available']; ?></div>
                                            </div>
                                            <?php if ($logged_in): ?>
                                                <form method="post" action="pembelian.php" style="margin:0;" class="purchase-form" data-ticket-id="<?php echo $ticket['id']; ?>">
                                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <input type="hidden" name="payment_method" value="transfer">
                                                    <button type="button" class="buy-btn purchase-btn" <?php echo ($ticket['available'] <= 0) ? 'disabled' : ''; ?>>
                                                        <?php echo ($ticket['available'] <= 0) ? 'Habis' : 'Beli'; ?>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="buy-btn" onclick="alert('Silakan login terlebih dahulu')" <?php echo ($ticket['available'] <= 0) ? 'disabled' : ''; ?>>
                                                    <?php echo ($ticket['available'] <= 0) ? 'Habis' : 'Beli'; ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile;
                                else: ?>
                                    <p class="no-tickets-notice">Belum ada tiket tersedia untuk event ini</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Tidak ada event yang tersedia saat ini.</p>
        <?php endif; ?>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Konfirmasi Pembelian</h3>
            <p>Anda yakin ingin membeli tiket ini?</p>
            
            <div class="form-group">
                <label>Jumlah Tiket</label>
                <input type="number" id="quantityInput" name="quantity" min="1" value="1" required>
            </div>
            
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select id="paymentMethod" name="payment_method" required>
                    <option value="transfer">Transfer Bank</option>
                    <option value="credit_card">Kartu Kredit</option>
                    <option value="cash">Tunai</option>
                    <option value="e_wallet">E-Wallet</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" id="cancelBtn">Batal</button>
                <button class="modal-btn modal-btn-confirm" id="confirmBtn">Ya, Beli Tiket</button>
            </div>
        </div>
    </div>

    <script>
        // Purchase confirmation logic
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('confirmationModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const confirmBtn = document.getElementById('confirmBtn');
            const quantityInput = document.getElementById('quantityInput');
            const paymentMethod = document.getElementById('paymentMethod');
            let currentForm = null;
            
            // Handle purchase button clicks
            document.querySelectorAll('.purchase-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.disabled) return;
                    
                    currentForm = this.closest('.purchase-form');
                    modal.style.display = 'block';
                });
            });
            
            // Handle confirm button
            confirmBtn.addEventListener('click', function() {
                if (currentForm) {
                    // Update form values
                    currentForm.querySelector('input[name="quantity"]').value = quantityInput.value;
                    currentForm.querySelector('input[name="payment_method"]').value = paymentMethod.value;
                    
                    // Submit the form
                    currentForm.submit();
                }
                modal.style.display = 'none';
            });
            
            // Handle cancel button
            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>