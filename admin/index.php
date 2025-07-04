<?php
// admin/index.php - Admin Panel
session_start();
require_once '../config.php';

// Simple admin authentication - you should implement proper admin user system
$admin_password = 'admin123'; // Change this!
$is_admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];

// Handle admin login
if (isset($_POST['admin_login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $is_admin_logged_in = true;
    } else {
        $login_error = 'Invalid password';
    }
}

// Handle admin logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    $is_admin_logged_in = false;
}

// Handle order updates
if ($is_admin_logged_in && $_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_order') {
        $order_id = (int)($_POST['order_id'] ?? 0);
        $status = sanitize_input($_POST['status'] ?? '');
        $tracking_number = sanitize_input($_POST['tracking_number'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?");
            $stmt->execute([$status, $tracking_number, $order_id]);
            $success_message = 'Order updated successfully';
        } catch(PDOException $e) {
            $error_message = 'Failed to update order';
        }
    }
}

// Get statistics
$stats = [];
if ($is_admin_logged_in) {
    try {
        // Total orders
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $stats['total_orders'] = $stmt->fetch()['total'];
        
        // Pending orders
        $stmt = $pdo->query("SELECT COUNT(*) as pending FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $stmt->fetch()['pending'];
        
        // Total revenue
        $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'completed'");
        $stats['total_revenue'] = $stmt->fetch()['revenue'] ?? 0;
        
        // Recent orders
        $stmt = $pdo->query("
            SELECT o.*, u.email, u.first_name, u.last_name
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 20
        ");
        $recent_orders = $stmt->fetchAll();
        
    } catch(PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VYLO Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            background: #2563eb;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .login-form {
            max-width: 400px;
            margin: 4rem auto;
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .orders-table {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table th {
            background: #f8fafc;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-paid { background: #dbeafe; color: #1e40af; }
        .status-shipped { background: #d1fae5; color: #065f46; }
        .status-delivered { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }
        
        .logout-link {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .logout-link:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <?php if (!$is_admin_logged_in): ?>
        <!-- Login Form -->
        <div class="login-form">
            <h2 style="text-align: center; margin-bottom: 2rem;">VYLO Admin Login</h2>
            
            <?php if (isset($login_error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Admin Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" name="admin_login" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    
    <?php else: ?>
        <!-- Admin Dashboard -->
        <div class="header">
            <div class="container">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1>
                        <i class="fas fa-tachometer-alt"></i> VYLO Admin Panel
                    </h1>
                    <a href="?logout=1" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Alerts -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #2563eb;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_orders'] ?? 0; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['pending_orders'] ?? 0; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #10b981;">
                        <i class="fas fa-pound-sign"></i>
                    </div>
                    <div class="stat-value"><?php echo format_price($stats['total_revenue'] ?? 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #ef4444;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">
                        <?php 
                        try {
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                            echo $stmt->fetch()['count'];
                        } catch(PDOException $e) {
                            echo '0';
                        }
                        ?>
                    </div>
                    <div class="stat-label">Customers</div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="orders-table">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0;">
                    <h2>Recent Orders</h2>
                </div>
                
                <?php if (!empty($recent_orders)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    <?php if ($order['tracking_number']): ?>
                                        <br><small style="color: #64748b;">Track: <?php echo htmlspecialchars($order['tracking_number']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($order['email']): ?>
                                        <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                                        <small style="color: #64748b;"><?php echo htmlspecialchars($order['email']); ?></small>
                                    <?php else: ?>
                                        Guest Order
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo format_price($order['total_amount']); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('j M Y, H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <button onclick="editOrder(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>', '<?php echo htmlspecialchars($order['tracking_number']); ?>')" 
                                            class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 2rem; text-align: center; color: #64748b;">
                        No orders found
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: 2rem; text-align: center;">
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> View Website
                </a>
                <a href="../hardware.php" class="btn btn-success">
                    <i class="fas fa-shopping-bag"></i> View Store
                </a>
            </div>
        </div>

        <!-- Edit Order Modal -->
        <div id="editOrderModal" class="modal">
            <div class="modal-content">
                <h3 style="margin-bottom: 1.5rem;">Edit Order</h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="update_order">
                    <input type="hidden" id="editOrderId" name="order_id">
                    
                    <div class="form-group">
                        <label class="form-label">Order Status</label>
                        <select id="editStatus" name="status" class="form-input">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tracking Number</label>
                        <input type="text" id="editTracking" name="tracking_number" class="form-input" 
                               placeholder="Enter FedEx tracking number">
                    </div>
                    
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function editOrder(orderId, status, tracking) {
            document.getElementById('editOrderId').value = orderId;
            document.getElementById('editStatus').value = status;
            document.getElementById('editTracking').value = tracking;
            document.getElementById('editOrderModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editOrderModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('editOrderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Auto-refresh page every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>