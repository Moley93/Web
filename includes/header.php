<?php
// includes/header.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME . ' - UK Hardware Specialists'; ?></title>
    <meta name="description" content="VYLO - Your trusted UK hardware specialist providing next-day delivery on quality electronics and development boards.">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <h1>VYLO</h1>
                        <span class="tagline">UK Hardware Specialists</span>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                        <li><a href="hardware.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'hardware.php' ? 'active' : ''; ?>">Hardware</a></li>
                        <li><a href="firmware.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'firmware.php' ? 'active' : ''; ?>">Firmware</a></li>
                        <li><a href="software.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'software.php' ? 'active' : ''; ?>">Software</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <a href="cart.php" class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo get_cart_count(); ?></span>
                    </a>
                    
                    <?php if (is_logged_in()): ?>
                        <a href="profile.php" class="btn btn-secondary">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="logout.php" class="btn btn-outline">Logout</a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-secondary">Register</a>
                        <a href="login.php" class="btn btn-primary">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">