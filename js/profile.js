class ProfileManager {
    constructor() {
        this.maxRetries = 3;
        this.retryDelay = 1000;
        this.init();
    }

    init() {
        // Add debug logging
        console.log('ProfileManager: Initializing...');
        
        // Check if we're on the profile page
        if (!this.isProfilePage()) {
            console.log('ProfileManager: Not on profile page, skipping initialization');
            return;
        }

        this.loadProfileData();
        this.setupEventListeners();
    }

    isProfilePage() {
        return window.location.pathname === '/profile' || 
               window.location.pathname.includes('profile') ||
               document.getElementById('profile-info');
    }

    setupEventListeners() {
        console.log('ProfileManager: Setting up event listeners...');
        
        // Edit profile modal
        const editProfileBtn = document.getElementById('edit-profile');
        const modal = document.getElementById('edit-profile-modal');
        const closeModal = document.getElementById('close-modal');
        const cancelEdit = document.getElementById('cancel-edit');
        const editForm = document.getElementById('edit-profile-form');

        if (editProfileBtn && modal) {
            editProfileBtn.addEventListener('click', () => {
                this.openEditModal();
            });
        }

        if (closeModal && modal) {
            closeModal.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }

        if (cancelEdit && modal) {
            cancelEdit.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', this.handleProfileUpdate.bind(this));
        }
    }

    async loadProfileData() {
        console.log('ProfileManager: Loading profile data...');
        
        // Check authentication first
        if (!this.checkAuthentication()) {
            console.log('ProfileManager: Authentication check failed');
            return;
        }

        const user = this.getStoredUser();
        const token = this.getStoredToken();

        console.log('ProfileManager: Auth check passed', { 
            hasUser: !!user, 
            hasToken: !!token,
            tokenLength: token ? token.length : 0
        });

        try {
            // Load user profile with retries
            const profileData = await this.fetchWithRetry('/php/get_profile.php', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            console.log('ProfileManager: Profile response received', profileData);

            if (profileData.success) {
                this.displayProfileInfo(profileData.user);
                await this.loadOrderHistory(token);
                await this.loadTrackingInfo(token);
            } else {
                console.error('ProfileManager: Profile load failed', profileData.message);
                this.showError('profile-info', `Error loading profile: ${profileData.message}`);
                
                // If token is invalid, clear auth and redirect
                if (profileData.message.includes('token') || profileData.message.includes('Authorization')) {
                    console.log('ProfileManager: Token appears invalid, clearing auth');
                    this.clearAuthentication();
                    setTimeout(() => {
                        window.location.href = '/login?redirect=/profile&reason=session_expired';
                    }, 2000);
                }
            }
        } catch (error) {
            console.error('ProfileManager: Error loading profile', error);
            this.showError('profile-info', `Network error loading profile: ${error.message}`);
        }
    }

    checkAuthentication() {
        const user = this.getStoredUser();
        const token = this.getStoredToken();

        console.log('ProfileManager: Checking authentication...', {
            hasUser: !!user,
            hasToken: !!token,
            userEmail: user ? user.email : 'none'
        });

        if (!user || !token) {
            console.log('ProfileManager: No user or token found, redirecting to login');
            this.showError('profile-info', 'Please log in to view your profile');
            
            setTimeout(() => {
                window.location.href = '/login?redirect=/profile&reason=not_authenticated';
            }, 2000);
            return false;
        }

        // Check if token is expired (basic check)
        try {
            const payload = this.decodeJWT(token);
            if (payload && payload.exp && payload.exp < Math.floor(Date.now() / 1000)) {
                console.log('ProfileManager: Token expired, clearing auth');
                this.clearAuthentication();
                this.showError('profile-info', 'Your session has expired. Please log in again.');
                
                setTimeout(() => {
                    window.location.href = '/login?redirect=/profile&reason=token_expired';
                }, 2000);
                return false;
            }
        } catch (e) {
            console.warn('ProfileManager: Could not decode JWT for expiry check', e);
        }

        return true;
    }

    decodeJWT(token) {
        try {
            const parts = token.split('.');
            if (parts.length !== 3) return null;
            
            const payload = atob(parts[1]);
            return JSON.parse(payload);
        } catch (e) {
            return null;
        }
    }

    getStoredUser() {
        try {
            return JSON.parse(localStorage.getItem('user') || 'null');
        } catch (e) {
            console.error('ProfileManager: Error parsing stored user', e);
            return null;
        }
    }

    getStoredToken() {
        return localStorage.getItem('token');
    }

    clearAuthentication() {
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        console.log('ProfileManager: Authentication cleared');
    }

    async fetchWithRetry(url, options, retries = this.maxRetries) {
        for (let i = 0; i < retries; i++) {
            try {
                console.log(`ProfileManager: Attempting request to ${url} (attempt ${i + 1}/${retries})`);
                
                const response = await fetch(url, options);
                console.log(`ProfileManager: Response status ${response.status} for ${url}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log(`ProfileManager: Parsed response for ${url}`, data);
                return data;
                
            } catch (error) {
                console.error(`ProfileManager: Request failed (attempt ${i + 1}/${retries})`, error);
                
                if (i === retries - 1) {
                    throw error;
                }
                
                // Wait before retrying
                await new Promise(resolve => setTimeout(resolve, this.retryDelay * (i + 1)));
            }
        }
    }

    displayProfileInfo(user) {
        console.log('ProfileManager: Displaying profile info', user);
        
        const emailEl = document.getElementById('user-email');
        const memberSinceEl = document.getElementById('member-since');
        const nameEl = document.getElementById('user-name');
        const companyEl = document.getElementById('user-company');

        if (emailEl) emailEl.textContent = user.email || 'Not provided';
        if (memberSinceEl) memberSinceEl.textContent = this.formatDate(user.created_at) || 'Unknown';
        if (nameEl) nameEl.textContent = 
            [user.first_name, user.last_name].filter(Boolean).join(' ') || 'Not provided';
        if (companyEl) companyEl.textContent = user.company || 'Not provided';
    }

    async loadOrderHistory(token) {
        try {
            console.log('ProfileManager: Loading order history...');
            
            const ordersData = await this.fetchWithRetry('/php/get_orders.php', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            console.log('ProfileManager: Orders response', ordersData);

            if (ordersData.success) {
                this.displayOrders(ordersData.orders);
            } else {
                this.showError('orders-container', `Error loading orders: ${ordersData.message}`);
            }
        } catch (error) {
            console.error('ProfileManager: Error loading orders', error);
            this.showError('orders-container', `Error loading orders: ${error.message}`);
        }
    }

    displayOrders(orders) {
        const container = document.getElementById('orders-container');
        if (!container) return;

        if (orders.length === 0) {
            container.innerHTML = '<p>No orders found. <a href="/store">Start shopping!</a></p>';
            return;
        }

        container.innerHTML = orders.map(order => `
            <div class="order-card">
                <div class="order-header">
                    <h4>Order #${order.order_number || order.id}</h4>
                    <span class="order-status ${order.status}">${order.status}</span>
                </div>
                <p><strong>Date:</strong> ${this.formatDate(order.created_at)}</p>
                <p><strong>Total:</strong> ${this.formatCurrency(order.total)}</p>
                <p><strong>Items:</strong> ${this.formatOrderItems(order.items)}</p>
            </div>
        `).join('');
    }

    formatOrderItems(itemsJson) {
        try {
            const items = typeof itemsJson === 'string' ? JSON.parse(itemsJson) : itemsJson;
            return items.map(item => `${item.name} (${item.quantity})`).join(', ');
        } catch (e) {
            return 'Error parsing items';
        }
    }

    async loadTrackingInfo(token) {
        try {
            console.log('ProfileManager: Loading tracking info...');
            
            const trackingData = await this.fetchWithRetry('/php/get_tracking.php', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });

            console.log('ProfileManager: Tracking response', trackingData);

            if (trackingData.success) {
                this.displayTracking(trackingData.tracking);
            } else {
                this.showError('tracking-container', `Error loading tracking: ${trackingData.message}`);
            }
        } catch (error) {
            console.error('ProfileManager: Error loading tracking', error);
            this.showError('tracking-container', `Error loading tracking: ${error.message}`);
        }
    }

    displayTracking(tracking) {
        const container = document.getElementById('tracking-container');
        if (!container) return;

        if (tracking.length === 0) {
            container.innerHTML = '<p>No tracking information available</p>';
            return;
        }

        container.innerHTML = tracking.map(track => `
            <div class="tracking-card">
                <h4>Order #${track.order_number || track.order_id}</h4>
                <p><strong>Carrier:</strong> ${track.carrier}</p>
                <p><strong>Tracking Number:</strong> <a href="#" onclick="window.open('https://www.google.com/search?q=${track.carrier}+tracking+${track.tracking_number}', '_blank')">${track.tracking_number}</a></p>
                <p><strong>Status:</strong> ${track.status}</p>
                <p><strong>Updated:</strong> ${this.formatDate(track.updated_at)}</p>
            </div>
        `).join('');
    }

    openEditModal() {
        const user = this.getStoredUser();
        const modal = document.getElementById('edit-profile-modal');
        
        if (user) {
            const firstNameEl = document.getElementById('edit-first-name');
            const lastNameEl = document.getElementById('edit-last-name');
            const companyEl = document.getElementById('edit-company');
            
            if (firstNameEl) firstNameEl.value = user.first_name || '';
            if (lastNameEl) lastNameEl.value = user.last_name || '';
            if (companyEl) companyEl.value = user.company || '';
        }

        if (modal) modal.style.display = 'flex';
    }

    async handleProfileUpdate(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const token = this.getStoredToken();

        try {
            const response = await fetch('/php/update_profile.php', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Update local storage
                const user = this.getStoredUser();
                if (user) {
                    user.first_name = formData.get('first-name');
                    user.last_name = formData.get('last-name');
                    user.company = formData.get('company');
                    localStorage.setItem('user', JSON.stringify(user));
                }

                // Refresh display
                this.displayProfileInfo(user);
                
                // Close modal
                const modal = document.getElementById('edit-profile-modal');
                if (modal) modal.style.display = 'none';
                
                this.showSuccess('Profile updated successfully!');
            } else {
                this.showError(null, 'Error updating profile: ' + result.message);
            }
        } catch (error) {
            console.error('ProfileManager: Error updating profile', error);
            this.showError(null, 'Network error. Please try again.');
        }
    }

    showError(elementId, message) {
        console.error('ProfileManager: Showing error', { elementId, message });
        
        if (elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = `<p class="error" style="color: #ef4444; padding: 1rem; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border: 1px solid #ef4444;">${message}</p>`;
            }
        } else {
            alert(message);
        }
    }

    showSuccess(message) {
        console.log('ProfileManager: Showing success', message);
        
        // Create a temporary success message
        const successDiv = document.createElement('div');
        successDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            z-index: 1000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        `;
        successDiv.textContent = message;
        document.body.appendChild(successDiv);
        
        setTimeout(() => {
            document.body.removeChild(successDiv);
        }, 3000);
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-GB', {
            style: 'currency',
            currency: 'GBP'
        }).format(amount);
    }

    formatDate(dateString) {
        if (!dateString) return 'Unknown';
        return new Intl.DateTimeFormat('en-GB', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(dateString));
    }
}

// Enhanced initialization with error handling
document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('DOM loaded, initializing ProfileManager...');
        window.profileManager = new ProfileManager();
    } catch (error) {
        console.error('Failed to initialize ProfileManager:', error);
    }
});

// Make it globally available for debugging
window.ProfileManager = ProfileManager;