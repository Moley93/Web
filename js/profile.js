class ProfileManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadProfileData();
        this.setupEventListeners();
    }

    setupEventListeners() {
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
        const user = auth.getUser();
        const token = auth.getToken();

        if (!user || !token) {
            window.location.href = '/login?redirect=/profile';
            return;
        }

        try {
            // Load user profile
            const response = await fetch('/php/get_profile.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            const profileData = await response.json();

            if (profileData.success) {
                this.displayProfileInfo(profileData.user);
                this.loadOrderHistory(token);
                this.loadTrackingInfo(token);
            }
        } catch (error) {
            console.error('Error loading profile:', error);
        }
    }

    displayProfileInfo(user) {
        document.getElementById('user-email').textContent = user.email || 'Not provided';
        document.getElementById('member-since').textContent = formatDate(user.created_at) || 'Unknown';
        document.getElementById('user-name').textContent = 
            [user.first_name, user.last_name].filter(Boolean).join(' ') || 'Not provided';
        document.getElementById('user-company').textContent = user.company || 'Not provided';
    }

    async loadOrderHistory(token) {
        try {
            const response = await fetch('/php/get_orders.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            const ordersData = await response.json();

            if (ordersData.success) {
                this.displayOrders(ordersData.orders);
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            document.getElementById('orders-container').innerHTML = 
                '<p class="error">Error loading orders</p>';
        }
    }

    displayOrders(orders) {
        const container = document.getElementById('orders-container');

        if (orders.length === 0) {
            container.innerHTML = '<p>No orders found</p>';
            return;
        }

        container.innerHTML = orders.map(order => `
            <div class="order-card">
                <div class="order-header">
                    <h4>Order #${order.id}</h4>
                    <span class="order-status ${order.status}">${order.status}</span>
                </div>
                <p>Date: ${formatDate(order.created_at)}</p>
                <p>Total: ${formatCurrency(order.total)}</p>
                <p>Items: ${JSON.parse(order.items).map(item => `${item.name} (${item.quantity})`).join(', ')}</p>
            </div>
        `).join('');
    }

    async loadTrackingInfo(token) {
        try {
            const response = await fetch('/php/get_tracking.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            const trackingData = await response.json();

            if (trackingData.success) {
                this.displayTracking(trackingData.tracking);
            }
        } catch (error) {
            console.error('Error loading tracking:', error);
            document.getElementById('tracking-container').innerHTML = 
                '<p class="error">Error loading tracking information</p>';
        }
    }

    displayTracking(tracking) {
        const container = document.getElementById('tracking-container');

        if (tracking.length === 0) {
            container.innerHTML = '<p>No tracking information available</p>';
            return;
        }

        container.innerHTML = tracking.map(track => `
            <div class="tracking-card">
                <h4>Order #${track.order_id}</h4>
                <p><strong>Carrier:</strong> ${track.carrier}</p>
                <p><strong>Tracking Number:</strong> ${track.tracking_number}</p>
                <p><strong>Status:</strong> ${track.status}</p>
                <p><strong>Updated:</strong> ${formatDate(track.updated_at)}</p>
            </div>
        `).join('');
    }

    openEditModal() {
        const user = auth.getUser();
        const modal = document.getElementById('edit-profile-modal');
        
        if (user) {
            document.getElementById('edit-first-name').value = user.first_name || '';
            document.getElementById('edit-last-name').value = user.last_name || '';
            document.getElementById('edit-company').value = user.company || '';
        }

        modal.style.display = 'flex';
    }

    async handleProfileUpdate(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const token = auth.getToken();

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
                const user = auth.getUser();
                user.first_name = formData.get('first-name');
                user.last_name = formData.get('last-name');
                user.company = formData.get('company');
                localStorage.setItem('user', JSON.stringify(user));

                // Refresh display
                this.displayProfileInfo(user);
                
                // Close modal
                document.getElementById('edit-profile-modal').style.display = 'none';
                
                alert('Profile updated successfully!');
            } else {
                alert('Error updating profile: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            alert('Network error. Please try again.');
        }
    }
}

// Initialize profile manager if on profile page
if (window.location.pathname === '/profile') {
    const profile = new ProfileManager();
}