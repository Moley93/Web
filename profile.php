<?php
// profile.php - User Profile Page
$page_title = "My Account";
require_once 'includes/header.php';

// Require login
if (!is_logged_in()) {
    redirect('login.php?redirect=profile.php');
}

// Get user information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirect('login.php');
    }
} catch(PDOException $e) {
    redirect('login.php');
}

// Get user's orders
$orders = [];
try {
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch(PDOException $e) {
    $orders = [];
}

// Handle form submissions
$errors = [];
$success = [];

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $address_line1 = sanitize_input($_POST['address_line1'] ?? '');
        $address_line2 = sanitize_input($_POST['address_line2'] ?? '');
        $city = sanitize_input($_POST['city'] ?? '');
        $postal_code = sanitize_input($_POST['postal_code'] ?? '');
        $country = sanitize_input($_POST['country'] ?? '');
        
        // Validation
        if (empty($first_name)) $errors[] = "First name is required";
        if (empty($last_name)) $errors[] = "Last name is required";
        if (empty($address_line1)) $errors[] = "Address is required";
        if (empty($city)) $errors[] = "City is required";
        if (empty($postal_code)) $errors[] = "Postal code is required";
        if (empty($country)) $errors[] = "Country is required";
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, phone = ?, address_line1 = ?, 
                        address_line2 = ?, city = ?, postal_code = ?, country = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $first_name, $last_name, $phone, $address_line1, 
                    $address_line2, $city, $postal_code, $country, 
                    $_SESSION['user_id']
                ]);
                
                $success[] = "Profile updated successfully";
                
                // Update session name
                $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                
                // Refresh user data
                $user['first_name'] = $first_name;
                $user['last_name'] = $last_name;
                $user['phone'] = $phone;
                $user['address_line1'] = $address_line1;
                $user['address_line2'] = $address_line2;
                $user['city'] = $city;
                $user['postal_code'] = $postal_code;
                $user['country'] = $country;
                
            } catch(PDOException $e) {
                $errors[] = "Failed to update profile";
            }
        }
    }
    
    elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current_password)) $errors[] = "Current password is required";
        if (empty($new_password)) $errors[] = "New password is required";
        if (strlen($new_password) < 8) $errors[] = "New password must be at least 8 characters";
        if ($new_password !== $confirm_password) $errors[] = "Passwords do not match";
        
        if (empty($errors)) {
            // Verify current password
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "Current password is incorrect";
            } else {
                try {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                    
                    $success[] = "Password changed successfully";
                } catch(PDOException $e) {
                    $errors[] = "Failed to change password";
                }
            }
        }
    }
}

$active_tab = $_GET['tab'] ?? 'overview';
?>

<div class="container" style="padding: 2rem 0;">
    <!-- Page Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">My Account</h1>
        <p style="color: var(--text-light);">
            Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!
        </p>
    </div>

    <!-- Alerts -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin: 0;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <ul style="margin: 0;">
                <?php foreach ($success as $message): ?>
                    <li><?php echo htmlspecialchars($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem; align-items: start;">
        
        <!-- Sidebar Navigation -->
        <div style="background: white; border-radius: 1rem; padding: 1.5rem; box-shadow: var(--shadow);">
            <nav>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="?tab=overview" 
                           class="<?php echo $active_tab === 'overview' ? 'active' : ''; ?>"
                           style="display: block; padding: 0.75rem 1rem; text-decoration: none; color: var(--text-dark); border-radius: 0.5rem; transition: all 0.3s;">
                            <i class="fas fa-tachometer-alt"></i> Overview
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="?tab=orders" 
                           class="<?php echo $active_tab === 'orders' ? 'active' : ''; ?>"
                           style="display: block; padding: 0.75rem 1rem; text-decoration: none; color: var(--text-dark); border-radius: 0.5rem; transition: all 0.3s;">
                            <i class="fas fa-box"></i> Order History
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="?tab=profile" 
                           class="<?php echo $active_tab === 'profile' ? 'active' : ''; ?>"
                           style="display: block; padding: 0.75rem 1rem; text-decoration: none; color: var(--text-dark); border-radius: 0.5rem; transition: all 0.3s;">
                            <i class="fas fa-user"></i> Profile Settings
                        </a>
                    </li>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="?tab=security" 
                           class="<?php echo $active_tab === 'security' ? 'active' : ''; ?>"
                           style="display: block; padding: 0.75rem 1rem; text-decoration: none; color: var(--text-dark); border-radius: 0.5rem; transition: all 0.3s;">
                            <i class="fas fa-shield-alt"></i> Security
                        </a>
                    </li>
                    <li style="border-top: 1px solid var(--border-color); margin-top: 1rem; padding-top: 1rem;">
                        <a href="logout.php" 
                           style="display: block; padding: 0.75rem 1rem; text-decoration: none; color: var(--error-color); border-radius: 0.5rem; transition: all 0.3s;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div>
            <?php if ($active_tab === 'overview'): ?>
                <!-- Account Overview -->
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 2rem;">Account Overview</h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;">
                                <i class="fas fa-box"></i>
                            </div>
                            <h3 style="margin-bottom: 0.25rem;"><?php echo count($orders); ?></h3>
                            <p style="color: var(--text-light); margin: 0;">Total Orders</p>
                        </div>
                        
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 2rem; color: var(--success-color); margin-bottom: 0.5rem;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 style="margin-bottom: 0.25rem;">
                                <?php echo count(array_filter($orders, fn($o) => $o['status'] === 'delivered')); ?>
                            </h3>
                            <p style="color: var(--text-light); margin: 0;">Completed Orders</p>
                        </div>
                        
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
                            <div style="font-size: 2rem; color: var(--warning-color); margin-bottom: 0.5rem;">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <h3 style="margin-bottom: 0.25rem;">
                                <?php echo count(array_filter($orders, fn($o) => in_array($o['status'], ['paid', 'shipped']))); ?>
                            </h3>
                            <p style="color: var(--text-light); margin: 0;">Active Orders</p>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <h3 style="margin-bottom: 1rem;">Recent Orders</h3>
                    <?php if (!empty($orders)): ?>
                        <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <div>
                                    <h4 style="margin-bottom: 0.25rem;">Order <?php echo htmlspecialchars($order['order_number']); ?></h4>
                                    <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                        Placed on <?php echo date('j M Y', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    <p style="margin: 0.5rem 0 0; font-weight: 600;">
                                        <?php echo format_price($order['total_amount']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="?tab=orders" class="btn btn-outline">View All Orders</a>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--text-light); text-align: center; padding: 2rem;">
                            No orders yet. <a href="hardware.php" style="color: var(--primary-color);">Start shopping</a>
                        </p>
                    <?php endif; ?>
                </div>

            <?php elseif ($active_tab === 'orders'): ?>
                <!-- Order History -->
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 2rem;">Order History</h2>
                    
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <div>
                                    <h4 style="margin-bottom: 0.25rem;">Order <?php echo htmlspecialchars($order['order_number']); ?></h4>
                                    <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">
                                        Placed on <?php echo date('j M Y \a\t H:i', strtotime($order['created_at'])); ?> • 
                                        <?php echo $order['item_count']; ?> item<?php echo $order['item_count'] !== 1 ? 's' : ''; ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    <p style="margin: 0.5rem 0 0; font-weight: 600;">
                                        <?php echo format_price($order['total_amount']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                
                                <?php if ($order['tracking_number']): ?>
                                    <a href="<?php echo FEDEX_TRACKING_URL . $order['tracking_number']; ?>" 
                                       target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-truck"></i> Track Package
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] === 'delivered'): ?>
                                    <button class="btn btn-secondary btn-sm" onclick="reorderItems(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-redo"></i> Reorder
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 4rem 0;">
                            <div style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h3 style="margin-bottom: 1rem;">No orders yet</h3>
                            <p style="color: var(--text-light); margin-bottom: 2rem;">
                                Start exploring our hardware collection
                            </p>
                            <a href="hardware.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i> Start Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($active_tab === 'profile'): ?>
                <!-- Profile Settings -->
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 2rem;">Profile Settings</h2>
                    
                    <form method="POST" action="?tab=profile" data-validate>
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label" for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" id="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small style="color: var(--text-light);">Contact support to change your email address</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="address_line1">Address Line 1 *</label>
                            <input type="text" id="address_line1" name="address_line1" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['address_line1']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="address_line2">Address Line 2</label>
                            <input type="text" id="address_line2" name="address_line2" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['address_line2']); ?>">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label" for="city">City *</label>
                                <input type="text" id="city" name="city" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['city']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="postal_code">Postal Code *</label>
                                <input type="text" id="postal_code" name="postal_code" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['postal_code']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="country">Country *</label>
                            <select id="country" name="country" class="form-select" required>
                                <option value="">Select Country</option>
                                <option value="United Kingdom" <?php echo $user['country'] === 'United Kingdom' ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="Ireland" <?php echo $user['country'] === 'Ireland' ? 'selected' : ''; ?>>Ireland</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

            <?php elseif ($active_tab === 'security'): ?>
                <!-- Security Settings -->
                <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: var(--shadow);">
                    <h2 style="margin-bottom: 2rem;">Security Settings</h2>
                    
                    <form method="POST" action="?tab=security" data-validate>
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label class="form-label" for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="new_password">New Password *</label>
                            <input type="password" id="new_password" name="new_password" class="form-input" required>
                            <small style="color: var(--text-light);">Must be at least 8 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.sidebar nav a.active {
    background-color: var(--primary-color);
    color: white;
}

.sidebar nav a:hover {
    background-color: var(--bg-light);
}

.sidebar nav a.active:hover {
    background-color: var(--primary-color);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
</style>

<script>
function reorderItems(orderId) {
    if (confirm('Add all items from this order to your cart?')) {
        fetch('api/reorder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                cartManager.showNotification('Items added to cart!', 'success');
                cartManager.updateCartDisplay();
            } else {
                cartManager.showNotification(result.message || 'Error adding items to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            cartManager.showNotification('Error adding items to cart', 'error');
        });
    }
}

// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>