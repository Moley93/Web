<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';

$database = new Database();
$db = $database->getConnection();

// Get statistics
try {
    // Total orders
    $orders_query = "SELECT COUNT(*) as total_orders, SUM(total) as total_revenue FROM orders WHERE status = 'paid'";
    $orders_stmt = $db->prepare($orders_query);
    $orders_stmt->execute();
    $stats = $orders_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Recent orders
    $recent_query = "SELECT o.*, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10";
    $recent_stmt = $db->prepare($recent_query);
    $recent_stmt->execute();
    $recent_orders = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Orders needing tracking
    $tracking_query = "SELECT o.* FROM orders o LEFT JOIN tracking t ON o.id = t.order_id WHERE o.status = 'paid' AND t.id IS NULL";
    $tracking_stmt = $db->prepare($tracking_query);
    $tracking_stmt->execute();
    $orders_needing_tracking = $tracking_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VYLO Admin Dashboard</title>
    <link rel="stylesheet" href="../../style.css">
    <style>
        .admin-header {
            background: var(--bg-secondary);
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-primary);
        }
        .orders-section {
            background: var(--bg-card);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }
        .order-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 100px;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            align-items: center;
        }
        .order-row:last-child {
            border-bottom: none;
        }
        .order-header {
            font-weight: bold;
            background: var(--bg-tertiary);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="admin-nav">
                <h1>VYLO Admin Dashboard</h1>
                <div>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="logout.php" class="btn btn-outline" style="margin-left: 1rem;">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($error)): ?>
            <div class="form-message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_orders'] ?? 0; ?></div>
                <div>Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">£<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
                <div>Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($orders_needing_tracking); ?></div>
                <div>Need Tracking</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($recent_orders); ?></div>
                <div>Recent Orders</div>
            </div>
        </div>

        <div class="orders-section">
            <h2>Orders Needing Tracking</h2>
            <?php if (empty($orders_needing_tracking)): ?>
                <p>All orders have tracking information!</p>
            <?php else: ?>
                <div class="order-row order-header">
                    <div>Order Number</div>
                    <div>Customer</div>
                    <div>Total</div>
                    <div>Action</div>
                </div>
                <?php foreach ($orders_needing_tracking as $order): ?>
                    <div class="order-row">
                        <div><?php echo htmlspecialchars($order['order_number']); ?></div>
                        <div><?php echo htmlspecialchars($order['shipping_info'] ? json_decode($order['shipping_info'], true)['email'] : 'Guest'); ?></div>
                        <div>£<?php echo number_format($order['total'], 2); ?></div>
                        <div>
                            <button class="btn btn-primary" onclick="addTracking(<?php echo $order['id']; ?>)">Add Tracking</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="orders-section">
            <h2>Recent Orders</h2>
            <div class="order-row order-header">
                <div>Order Number</div>
                <div>Customer</div>
                <div>Total</div>
                <div>Status</div>
            </div>
            <?php foreach ($recent_orders as $order): ?>
                <div class="order-row">
                    <div><?php echo htmlspecialchars($order['order_number']); ?></div>
                    <div><?php echo htmlspecialchars($order['email'] ?? 'Guest'); ?></div>
                    <div>£<?php echo number_format($order['total'], 2); ?></div>
                    <div><span class="order-status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Tracking Modal -->
    <div class="modal" id="tracking-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Tracking Information</h3>
                <button class="modal-close" onclick="closeTrackingModal()">&times;</button>
            </div>
            <form id="tracking-form">
                <input type="hidden" id="order-id" name="order_id">
                
                <div class="form-group">
                    <label for="tracking-number">Tracking Number</label>
                    <input type="text" id="tracking-number" name="tracking_number" required>
                </div>
                
                <div class="form-group">
                    <label for="carrier">Carrier</label>
                    <select id="carrier" name="carrier" required>
                        <option value="">Select Carrier</option>
                        <option value="FedEx">FedEx</option>
                        <option value="UPS">UPS</option>
                        <option value="DHL">DHL</option>
                        <option value="Royal Mail">Royal Mail</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="In Transit">In Transit</option>
                        <option value="Delivered">Delivered</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeTrackingModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Tracking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addTracking(orderId) {
            document.getElementById('order-id').value = orderId;
            document.getElementById('tracking-modal').style.display = 'flex';
        }

        function closeTrackingModal() {
            document.getElementById('tracking-modal').style.display = 'none';
            document.getElementById('tracking-form').reset();
        }

        document.getElementById('tracking-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('update_tracking.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Tracking information added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Network error: ' + error.message);
            }
        });
    </script>
</body>
</html>