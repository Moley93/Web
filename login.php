<?php
// login.php - User Login Page
$page_title = "Sign In";
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('profile.php');
}

$errors = [];
$email = '';

if ($_POST) {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // Authenticate user
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

                // Transfer cart items from session to user
                $session_id = session_id();
                $stmt = $pdo->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?");
                $stmt->execute([$user['id'], $session_id]);

                // Set remember me cookie if requested
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                    // You would also store this token in the database for security
                }

                // Redirect to intended page or profile
                $redirect_url = $_GET['redirect'] ?? 'profile.php';
                redirect($redirect_url);
                
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch(PDOException $e) {
            $errors[] = "Login failed. Please try again.";
        }
    }
}
?>

<div class="container" style="padding: 2rem 0; max-width: 500px;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Welcome Back</h1>
        <p style="color: var(--text-light); font-size: 1.125rem;">
            Sign in to your VYLO account
        </p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <h4>Sign In Failed</h4>
            <ul style="margin-top: 0.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" 
          data-validate style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow);">
        
        <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-input" 
                   value="<?php echo htmlspecialchars($email); ?>" 
                   placeholder="your@email.com" required autofocus>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input type="password" id="password" name="password" class="form-input" 
                   placeholder="Enter your password" required>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin: 1.5rem 0;">
            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                <input type="checkbox" name="remember_me">
                <span>Remember me for 30 days</span>
            </label>
            
            <a href="forgot-password.php" style="color: var(--primary-color); font-size: 0.875rem; text-decoration: none;">
                Forgot password?
            </a>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.125rem; padding: 1rem;">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
    </form>

    <div style="text-align: center; margin-top: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 1rem;">
        <p style="color: var(--text-light); margin-bottom: 1rem;">Don't have an account?</p>
        <a href="register.php" class="btn btn-outline">
            <i class="fas fa-user-plus"></i> Create Account
        </a>
    </div>

    <!-- Benefits of Having an Account -->
    <div style="margin-top: 2rem; background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow);">
        <h3 style="margin-bottom: 1.5rem; text-align: center;">Benefits of Your VYLO Account</h3>
        
        <div style="display: grid; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.25rem;">Faster Checkout</h4>
                    <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">Saved addresses and payment preferences</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.25rem;">Order History</h4>
                    <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">Track all your orders and reorder easily</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.25rem;">Delivery Tracking</h4>
                    <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">Real-time FedEx tracking for all orders</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.25rem;">Exclusive Offers</h4>
                    <p style="color: var(--text-light); font-size: 0.875rem; margin: 0;">Member-only discounts and early access</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide password functionality
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('password-toggle');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        passwordInput.type = 'password';
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

// Auto-focus email field if empty
document.addEventListener('DOMContentLoaded', function() {
    const emailField = document.getElementById('email');
    if (!emailField.value) {
        emailField.focus();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>