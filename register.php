<?php
// register.php - User Registration Page
$page_title = "Create Account";
require_once 'includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('profile.php');
}

$errors = [];
$success = false;

if ($_POST) {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address_line1 = sanitize_input($_POST['address_line1'] ?? '');
    $address_line2 = sanitize_input($_POST['address_line2'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $postal_code = sanitize_input($_POST['postal_code'] ?? '');
    $country = sanitize_input($_POST['country'] ?? '');

    // Validation
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($address_line1)) {
        $errors[] = "Address is required";
    }
    
    if (empty($city)) {
        $errors[] = "City is required";
    }
    
    if (empty($postal_code)) {
        $errors[] = "Postal code is required";
    }
    
    if (empty($country)) {
        $errors[] = "Country is required";
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "An account with this email already exists";
            }
        } catch(PDOException $e) {
            $errors[] = "Database error occurred";
        }
    }

    // Create account if no errors
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, first_name, last_name, phone, 
                                 address_line1, address_line2, city, postal_code, country) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $email, $hashed_password, $first_name, $last_name, $phone,
                $address_line1, $address_line2, $city, $postal_code, $country
            ]);
            
            $user_id = $pdo->lastInsertId();
            
            // Send welcome email
            $subject = "Welcome to " . SITE_NAME . "!";
            $message = "
                <h2>Welcome to VYLO, {$first_name}!</h2>
                <p>Thank you for creating an account with us. You're now ready to start shopping for quality hardware with next-day delivery.</p>
                <p>Your account details:</p>
                <ul>
                    <li>Email: {$email}</li>
                    <li>Name: {$first_name} {$last_name}</li>
                </ul>
                <p><a href='" . SITE_URL . "/hardware.php'>Start Shopping</a></p>
                <p>Best regards,<br>The VYLO Team</p>
            ";
            
            send_email($email, $subject, $message);
            
            // Auto login user
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            
            // Transfer cart items if any
            $session_id = session_id();
            $stmt = $pdo->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?");
            $stmt->execute([$user_id, $session_id]);
            
            $success = true;
            
        } catch(PDOException $e) {
            $errors[] = "Failed to create account. Please try again.";
        }
    }
}
?>

<div class="container" style="padding: 2rem 0; max-width: 600px;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Create Your Account</h1>
        <p style="color: var(--text-light); font-size: 1.125rem;">
            Join VYLO and get access to quality hardware with next-day delivery
        </p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <h3 style="margin-bottom: 1rem;">Account Created Successfully!</h3>
            <p>Welcome to VYLO! You have been automatically logged in.</p>
            <div style="margin-top: 1rem;">
                <a href="hardware.php" class="btn btn-primary">Start Shopping</a>
                <a href="profile.php" class="btn btn-outline">View Profile</a>
            </div>
        </div>
    <?php else: ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h4>Please correct the following errors:</h4>
                <ul style="margin-top: 0.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" data-validate style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow);">
            
            <!-- Personal Information -->
            <h3 style="margin-bottom: 1.5rem; color: var(--text-dark); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                Personal Information
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-input" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                <small style="color: var(--text-light);">We'll use this for order updates and account notifications</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-input" 
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                <small style="color: var(--text-light);">Optional - for delivery updates</small>
            </div>

            <!-- Account Security -->
            <h3 style="margin: 2rem 0 1.5rem; color: var(--text-dark); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                Account Security
            </h3>
            
            <div class="form-group">
                <label class="form-label" for="password">Password *</label>
                <input type="password" id="password" name="password" class="form-input" required>
                <small style="color: var(--text-light);">Must be at least 8 characters long</small>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>

            <!-- Address Information -->
            <h3 style="margin: 2rem 0 1.5rem; color: var(--text-dark); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">
                Delivery Address
            </h3>
            
            <div class="form-group">
                <label class="form-label" for="address_line1">Address Line 1 *</label>
                <input type="text" id="address_line1" name="address_line1" class="form-input" 
                       value="<?php echo htmlspecialchars($_POST['address_line1'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="address_line2">Address Line 2</label>
                <input type="text" id="address_line2" name="address_line2" class="form-input" 
                       value="<?php echo htmlspecialchars($_POST['address_line2'] ?? ''); ?>">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="city">City *</label>
                    <input type="text" id="city" name="city" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="postal_code">Postal Code *</label>
                    <input type="text" id="postal_code" name="postal_code" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="country">Country *</label>
                <select id="country" name="country" class="form-select" required>
                    <option value="">Select Country</option>
                    <option value="United Kingdom" <?php echo ($_POST['country'] ?? '') === 'United Kingdom' ? 'selected' : ''; ?>>United Kingdom</option>
                    <option value="Ireland" <?php echo ($_POST['country'] ?? '') === 'Ireland' ? 'selected' : ''; ?>>Ireland</option>
                </select>
                <small style="color: var(--text-light);">We currently deliver to UK and Ireland</small>
            </div>

            <!-- Terms and Submit -->
            <div style="margin: 2rem 0;">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem;">
                    <input type="checkbox" required style="margin-top: 0.25rem;">
                    <span style="font-size: 0.875rem; color: var(--text-light);">
                        I agree to the <a href="terms.php" target="_blank" style="color: var(--primary-color);">Terms of Service</a> 
                        and <a href="privacy.php" target="_blank" style="color: var(--primary-color);">Privacy Policy</a>
                    </span>
                </label>
            </div>
            
            <div style="margin: 2rem 0;">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem;">
                    <input type="checkbox" name="newsletter">
                    <span style="font-size: 0.875rem; color: var(--text-light);">
                        Send me updates about new products and special offers
                    </span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.125rem; padding: 1rem;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 1rem;">
            <p style="color: var(--text-light);">Already have an account?</p>
            <a href="login.php" class="btn btn-outline" style="margin-top: 0.5rem;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </a>
        </div>

    <?php endif; ?>
</div>

<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = calculatePasswordStrength(password);
    // You could add a visual password strength indicator here
});

function calculatePasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return strength;
}

// Confirm password validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>