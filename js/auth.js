class AuthManager {
    constructor() {
        this.init();
    }

    init() {
        // Register form handler
        const registerForm = document.getElementById('register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', this.handleRegister.bind(this));
        }

        // Login form handler
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', this.handleLogin.bind(this));
        }

        // Logout button handler
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', this.handleLogout.bind(this));
        }

        // Check if user is logged in
        this.checkAuthState();
    }

    async handleRegister(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const messageEl = document.getElementById('form-message');

        // Client-side validation
        const password = formData.get('password');
        const confirmPassword = formData.get('confirm-password');
        
        if (password !== confirmPassword) {
            showMessage(messageEl, 'Passwords do not match', 'error');
            return;
        }

        if (password.length < 8) {
            showMessage(messageEl, 'Password must be at least 8 characters', 'error');
            return;
        }

        if (!formData.get('terms')) {
            showMessage(messageEl, 'You must agree to the Terms of Service', 'error');
            return;
        }

        try {
            const response = await fetch('/php/register.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showMessage(messageEl, 'Account created successfully! Please log in.', 'success');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else {
                showMessage(messageEl, result.message || 'Registration failed', 'error');
            }
        } catch (error) {
            showMessage(messageEl, 'Network error. Please try again.', 'error');
        }
    }

    async handleLogin(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const messageEl = document.getElementById('form-message');

        try {
            const response = await fetch('/php/login.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Store user data
                localStorage.setItem('user', JSON.stringify(result.user));
                localStorage.setItem('token', result.token);
                
                showMessage(messageEl, 'Login successful!', 'success');
                
                // Redirect to profile or previous page
                const urlParams = new URLSearchParams(window.location.search);
                const redirect = urlParams.get('redirect') || '/profile';
                
                setTimeout(() => {
                    window.location.href = redirect;
                }, 1000);
            } else {
                showMessage(messageEl, result.message || 'Login failed', 'error');
            }
        } catch (error) {
            showMessage(messageEl, 'Network error. Please try again.', 'error');
        }
    }

    handleLogout() {
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        window.location.href = '/';
    }

    checkAuthState() {
        const user = JSON.parse(localStorage.getItem('user') || 'null');
        const authButtons = document.getElementById('auth-buttons');
        const userMenu = document.getElementById('user-menu');
        
        if (user) {
            if (authButtons) authButtons.style.display = 'none';
            if (userMenu) userMenu.style.display = 'flex';
        } else {
            if (authButtons) authButtons.style.display = 'flex';
            if (userMenu) userMenu.style.display = 'none';
        }
    }

    isLoggedIn() {
        return !!localStorage.getItem('user');
    }

    getUser() {
        return JSON.parse(localStorage.getItem('user') || 'null');
    }

    getToken() {
        return localStorage.getItem('token');
    }
}

// Initialize auth manager
const auth = new AuthManager();