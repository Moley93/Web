// Profile Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
});

function initializeProfile() {
    checkProfileAuthentication();
    populateUserInfo();
    setupProfileNavigation();
    loadOverviewData();
    setupProfileForms();
    loadAddresses();
    loadWishlist();
}

function checkProfileAuthentication() {
    if (!window.vyloApp.user) {
        window.vyloApp.showNotification('Please log in to access your profile', 'error');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
        return;
    }
}

function populateUserInfo() {
    const user = window.vyloApp.user;
    if (!user) return;

    // Update header info
    updateElement('user-name', `${user.first_name} ${user.last_name}`);
    updateElement('user-email', user.email);
}

function setupProfileNavigation() {
    const navItems = document.querySelectorAll('.profile-nav-item:not(.logout)');
    
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchTab(tabName);
        });
    });

    // Setup logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
}

function switchTab(tabName) {
    // Update navigation
    document.querySelectorAll('.profile-nav-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

    // Update content
    document.querySelectorAll('.profile-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.getElementById(`${tabName}-tab`).classList.add('active');

    // Load tab-specific data
    switch(tabName) {
        case 'overview':
            loadOverviewData();
            break;
        case 'orders':
            loadOrderHistory();
            break;
        case 'addresses':
            loadAddresses();
            break;
        case 'settings':
            populateSettingsForm();
            break;
        case 'wishlist':
            loadWishlist();
            break;
    }
}

function loadOverviewData() {
    // Load recent orders count
    const orders = getOrderHistory();
    updateElement('recent-orders-count', `${orders.length} order${orders.length !== 1 ? 's' : ''}`);

    // Load pending deliveries
    const pendingDeliveries = orders.filter(order => 
        ['processing', 'shipped'].includes(order.status)
    );
    updateElement('pending-deliveries', `${pendingDeliveries.length} item${pendingDeliveries.length !== 1 ? 's' : ''}`);

    // Load wishlist count
    const wishlist = JSON.parse(localStorage.getItem('vylo_wishlist') || '[]');
    updateElement('wishlist-count', `${wishlist.length} item${wishlist.length !== 1 ? 's' : ''}`);

    // Load loyalty points (mock data)
    updateElement('loyalty-points', '1,250 points');

    // Load recent activity
    loadRecentActivity();
}

function loadRecentActivity() {
    const activityContainer = document.getElementById('recent-activity-list');
    if (!activityContainer) return;

    const activities = getRecentActivities();
    
    if (activities.length === 0) {
        activityContainer.innerHTML = '<p>No recent activity</p>';
        return;
    }

    activityContainer.innerHTML = activities.map(activity => `
        <div class="activity-item">
            <div class="activity-icon">
                <i class="${activity.icon}"></i>
            </div>
            <div class="activity-content">
                <p>${activity.description}</p>
                <span class="activity-time">${activity.time}</span>
            </div>
        </div>
    `).join('');
}

function getRecentActivities() {
    // Mock recent activities - in a real app this would come from an API
    return [
        {
            icon: 'fas fa-shopping-cart',
            description: 'Order #VYLO-2025-001 was delivered',
            time: '2 hours ago'
        },
        {
            icon: 'fas fa-heart',
            description: 'Added Intel Core i9-13900K to wishlist',
            time: '1 day ago'
        },
        {
            icon: 'fas fa-truck',
            description: 'Order #VYLO-2025-002 has been shipped',
            time: '2 days ago'
        },
        {
            icon: 'fas fa-user-edit',
            description: 'Updated delivery address',
            time: '3 days ago'
        }
    ];
}

function loadOrderHistory() {
    const ordersContainer = document.getElementById('orders-list');
    if (!ordersContainer) return;

    const orders = getOrderHistory();
    
    if (orders.length === 0) {
        ordersContainer.innerHTML = `
            <div class="empty-orders">
                <i class="fas fa-box-open"></i>
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here</p>
                <a href="hardware.html" class="cta-button">Browse Hardware</a>
            </div>
        `;
        return;
    }

    ordersContainer.innerHTML = orders.map(order => createOrderHTML(order)).join('');
}

function getOrderHistory() {
    // Mock order data - in a real app this would come from an API
    return [
        {
            id: 'VYLO-2025-001',
            date: '2025-01-05',
            status: 'delivered',
            total: 589.99,
            items: [
                { name: 'Intel Core i9-13900K', quantity: 1, price: 589.99 }
            ],
            tracking: 'FDX123456789',
            deliveryDate: '2025-01-06'
        },
        {
            id: 'VYLO-2025-002',
            date: '2025-01-03',
            status: 'shipped',
            total: 299.99,
            items: [
                { name: 'Corsair DDR5-5600 32GB', quantity: 1, price: 299.99 }
            ],
            tracking: 'FDX987654321',
            estimatedDelivery: '2025-01-08'
        }
    ];
}

function createOrderHTML(order) {
    const statusColors = {
        'pending': '#ffa500',
        'processing': '#007acc',
        'shipped': '#0099ff',
        'delivered': '#44ff44',
        'cancelled': '#ff4444'
    };

    return `
        <div class="order-card">
            <div class="order-header">
                <div class="order-number">
                    <h4>Order ${order.id}</h4>
                    <span class="order-date">${new Date(order.date).toLocaleDateString('en-GB')}</span>
                </div>
                <div class="order-status" style="color: ${statusColors[order.status]}">
                    <i class="fas fa-circle"></i>
                    ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                </div>
            </div>
            
            <div class="order-items">
                ${order.items.map(item => `
                    <div class="order-item">
                        <span>${item.name}</span>
                        <span>Qty: ${item.quantity}</span>
                        <span>£${item.price.toFixed(2)}</span>
                    </div>
                `).join('')}
            </div>
            
            <div class="order-footer">
                <div class="order-total">
                    <strong>Total: £${order.total.toFixed(2)}</strong>
                </div>
                <div class="order-actions">
                    ${order.tracking ? `
                        <button class="btn-small" onclick="trackOrder('${order.tracking}')">
                            <i class="fas fa-truck"></i> Track Order
                        </button>
                    ` : ''}
                    <button class="btn-small" onclick="viewOrderDetails('${order.id}')">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    ${order.status === 'delivered' ? `
                        <button class="btn-small" onclick="reorderItems('${order.id}')">
                            <i class="fas fa-redo"></i> Reorder
                        </button>
                    ` : ''}
                </div>
            </div>
            
            ${order.tracking ? `
                <div class="tracking-info">
                    <strong>Tracking:</strong> ${order.tracking}
                    ${order.estimatedDelivery ? `
                        <span>• Est. delivery: ${new Date(order.estimatedDelivery).toLocaleDateString('en-GB')}</span>
                    ` : ''}
                    ${order.deliveryDate ? `
                        <span>• Delivered: ${new Date(order.deliveryDate).toLocaleDateString('en-GB')}</span>
                    ` : ''}
                </div>
            ` : ''}
        </div>
    `;
}

function trackOrder(trackingNumber) {
    // Open FedEx tracking in new window
    const fedexUrl = `https://www.fedex.com/fedextrack/?trknbr=${trackingNumber}`;
    window.open(fedexUrl, '_blank');
}

function viewOrderDetails(orderId) {
    // Show order details modal or redirect to order details page
    window.vyloApp.showNotification(`Viewing details for order ${orderId}`, 'info');
    // In a real app, this would show a detailed order view
}

function reorderItems(orderId) {
    const orders = getOrderHistory();
    const order = orders.find(o => o.id === orderId);
    
    if (order) {
        // Add items to cart
        order.items.forEach(item => {
            window.vyloApp.addToCart({
                id: `reorder-${item.name.toLowerCase().replace(/\s+/g, '-')}`,
                name: item.name,
                price: item.price,
                description: 'Reordered item'
            });
        });
        
        window.vyloApp.showNotification('Items added to cart', 'success');
    }
}

function setupProfileForms() {
    // Personal info form
    const personalForm = document.getElementById('personal-info-form');
    if (personalForm) {
        personalForm.addEventListener('submit', handlePersonalInfoUpdate);
    }

    // Password form
    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', handlePasswordChange);
    }

    // Notifications form
    const notificationsForm = document.getElementById('notifications-form');
    if (notificationsForm) {
        notificationsForm.addEventListener('submit', handleNotificationPreferences);
    }

    // New address form
    const newAddressForm = document.getElementById('new-address-form');
    if (newAddressForm) {
        newAddressForm.addEventListener('submit', handleNewAddress);
    }
}

function populateSettingsForm() {
    const user = window.vyloApp.user;
    if (!user) return;

    // Populate personal info form
    const fieldMappings = {
        'settings_first_name': user.first_name,
        'settings_last_name': user.last_name,
        'settings_email': user.email,
        'settings_phone': user.phone,
        'settings_company': user.company
    };

    Object.entries(fieldMappings).forEach(([fieldId, value]) => {
        const field = document.getElementById(fieldId);
        if (field && value) {
            field.value = value;
        }
    });

    // Load notification preferences
    const preferences = JSON.parse(localStorage.getItem('vylo_notification_preferences') || '{}');
    Object.entries(preferences).forEach(([key, value]) => {
        const checkbox = document.querySelector(`input[name="${key}"]`);
        if (checkbox) {
            checkbox.checked = value;
        }
    });
}

async function handlePersonalInfoUpdate(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const updateData = Object.fromEntries(formData);
    
    try {
        // Update user data locally
        const updatedUser = { ...window.vyloApp.user, ...updateData };
        window.vyloApp.saveUser(updatedUser);
        
        // In a real app, this would also update the backend
        window.vyloApp.showNotification('Profile updated successfully', 'success');
        populateUserInfo();
        
    } catch (error) {
        console.error('Error updating profile:', error);
        window.vyloApp.showNotification('Error updating profile', 'error');
    }
}

async function handlePasswordChange(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const passwordData = Object.fromEntries(formData);
    
    // Validate passwords match
    if (passwordData.new_password !== passwordData.confirm_new_password) {
        window.vyloApp.showNotification('New passwords do not match', 'error');
        return;
    }

    try {
        // In a real app, this would validate current password and update
        window.vyloApp.showNotification('Password updated successfully', 'success');
        event.target.reset();
        
    } catch (error) {
        console.error('Error updating password:', error);
        window.vyloApp.showNotification('Error updating password', 'error');
    }
}

function handleNotificationPreferences(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const preferences = {};
    
    // Get all checkbox values
    const checkboxes = event.target.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        preferences[checkbox.name] = checkbox.checked;
    });
    
    localStorage.setItem('vylo_notification_preferences', JSON.stringify(preferences));
    window.vyloApp.showNotification('Notification preferences saved', 'success');
}

function loadAddresses() {
    const addressesContainer = document.getElementById('addresses-list');
    if (!addressesContainer) return;

    const addresses = getAddresses();
    
    if (addresses.length === 0) {
        addressesContainer.innerHTML = `
            <div class="empty-addresses">
                <i class="fas fa-map-marker-alt"></i>
                <h3>No addresses saved</h3>
                <p>Add a delivery address to speed up checkout</p>
            </div>
        `;
        return;
    }

    addressesContainer.innerHTML = addresses.map(address => createAddressHTML(address)).join('');
}

function getAddresses() {
    // Mock addresses - in a real app this would come from an API
    return JSON.parse(localStorage.getItem('vylo_addresses') || '[]');
}

function createAddressHTML(address) {
    return `
        <div class="address-card ${address.isDefault ? 'default' : ''}">
            <div class="address-header">
                <h4>${address.firstName} ${address.lastName}</h4>
                ${address.isDefault ? '<span class="default-badge">Default</span>' : ''}
            </div>
            
            <div class="address-content">
                ${address.company ? `<p>${address.company}</p>` : ''}
                <p>${address.addressLine1}</p>
                ${address.addressLine2 ? `<p>${address.addressLine2}</p>` : ''}
                <p>${address.city}, ${address.postcode}</p>
                <p>${address.county}</p>
                ${address.phone ? `<p><i class="fas fa-phone"></i> ${address.phone}</p>` : ''}
            </div>
            
            <div class="address-actions">
                <button class="btn-small" onclick="editAddress('${address.id}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                ${!address.isDefault ? `
                    <button class="btn-small" onclick="setDefaultAddress('${address.id}')">
                        <i class="fas fa-star"></i> Set Default
                    </button>
                ` : ''}
                <button class="btn-small remove" onclick="deleteAddress('${address.id}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    `;
}

function showAddAddressForm() {
    const form = document.getElementById('add-address-form');
    if (form) {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    }
}

function hideAddAddressForm() {
    const form = document.getElementById('add-address-form');
    if (form) {
        form.style.display = 'none';
        form.querySelector('form').reset();
    }
}

function handleNewAddress(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const addressData = Object.fromEntries(formData);
    
    const newAddress = {
        id: 'addr-' + Date.now(),
        firstName: addressData.first_name,
        lastName: addressData.last_name,
        company: addressData.company,
        addressLine1: addressData.address_line_1,
        addressLine2: addressData.address_line_2,
        city: addressData.city,
        postcode: addressData.postcode,
        phone: addressData.phone,
        isDefault: addressData.is_default === 'on'
    };
    
    // If this is set as default, remove default from others
    let addresses = getAddresses();
    if (newAddress.isDefault) {
        addresses.forEach(addr => addr.isDefault = false);
    }
    
    addresses.push(newAddress);
    localStorage.setItem('vylo_addresses', JSON.stringify(addresses));
    
    loadAddresses();
    hideAddAddressForm();
    window.vyloApp.showNotification('Address added successfully', 'success');
}

function editAddress(addressId) {
    // Show edit form with address data
    window.vyloApp.showNotification('Edit address functionality would be implemented here', 'info');
}

function setDefaultAddress(addressId) {
    let addresses = getAddresses();
    addresses.forEach(addr => {
        addr.isDefault = addr.id === addressId;
    });
    localStorage.setItem('vylo_addresses', JSON.stringify(addresses));
    loadAddresses();
    window.vyloApp.showNotification('Default address updated', 'success');
}

function deleteAddress(addressId) {
    if (confirm('Are you sure you want to delete this address?')) {
        let addresses = getAddresses();
        addresses = addresses.filter(addr => addr.id !== addressId);
        localStorage.setItem('vylo_addresses', JSON.stringify(addresses));
        loadAddresses();
        window.vyloApp.showNotification('Address deleted', 'info');
    }
}

function loadWishlist() {
    const wishlistContainer = document.getElementById('wishlist-items');
    const emptyWishlist = document.getElementById('empty-wishlist');
    
    if (!wishlistContainer) return;

    const wishlistIds = JSON.parse(localStorage.getItem('vylo_wishlist') || '[]');
    
    if (wishlistIds.length === 0) {
        wishlistContainer.style.display = 'none';
        emptyWishlist.style.display = 'block';
        return;
    }

    wishlistContainer.style.display = 'grid';
    emptyWishlist.style.display = 'none';
    
    // Get product details for wishlist items
    const wishlistItems = wishlistIds.map(id => getMockProductById(id)).filter(Boolean);
    
    wishlistContainer.innerHTML = wishlistItems.map(item => `
        <div class="wishlist-item">
            <div class="item-image">
                <i class="${item.icon}"></i>
            </div>
            <div class="item-info">
                <h4>${item.name}</h4>
                <p class="item-price">£${item.price.toFixed(2)}</p>
            </div>
            <div class="item-actions">
                <button class="btn" onclick="addWishlistToCart('${item.id}')">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
                <button class="btn-small remove" onclick="removeFromWishlist('${item.id}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function getMockProductById(productId) {
    // Mock product data
    const mockProducts = {
        'cpu-001': { id: 'cpu-001', name: 'Intel Core i9-13900K', price: 589.99, icon: 'fas fa-microchip' },
        'cpu-002': { id: 'cpu-002', name: 'AMD Ryzen 9 7900X', price: 449.99, icon: 'fas fa-microchip' },
        'ram-001': { id: 'ram-001', name: 'Corsair DDR5-5600 32GB', price: 299.99, icon: 'fas fa-memory' },
        'ssd-001': { id: 'ssd-001', name: 'Samsung 980 PRO 2TB', price: 199.99, icon: 'fas fa-hdd' }
    };
    
    return mockProducts[productId];
}

function addWishlistToCart(productId) {
    const product = getMockProductById(productId);
    if (product) {
        window.vyloApp.addToCart({
            id: product.id,
            name: product.name,
            price: product.price,
            description: 'From wishlist'
        });
        window.vyloApp.showNotification('Added to cart!', 'success');
    }
}

function removeFromWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('vylo_wishlist') || '[]');
    wishlist = wishlist.filter(id => id !== productId);
    localStorage.setItem('vylo_wishlist', JSON.stringify(wishlist));
    
    loadWishlist();
    loadOverviewData(); // Update overview counts
    window.vyloApp.showNotification('Removed from wishlist', 'info');
}

function handleLogout() {
    if (confirm('Are you sure you want to log out?')) {
        window.vyloApp.logout();
        window.location.href = 'index.html';
    }
}

function updateElement(id, content) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = content;
    }
}

// Make switchTab globally available
window.switchTab = switchTab;

// Export functions for global access
window.profileFunctions = {
    switchTab,
    trackOrder,
    viewOrderDetails,
    reorderItems,
    showAddAddressForm,
    hideAddAddressForm,
    editAddress,
    setDefaultAddress,
    deleteAddress,
    addWishlistToCart,
    removeFromWishlist
};

// Add profile-specific styles
const profileStyles = `
    .profile-container {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 2rem;
        margin-top: 120px;
        padding: 2rem 0;
    }
    
    .profile-sidebar {
        background-color: #1a1a1a;
        border-radius: 10px;
        padding: 2rem;
        height: fit-content;
        position: sticky;
        top: 120px;
    }
    
    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #333;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        background-color: #007acc;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2rem;
        color: white;
    }
    
    .profile-nav {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .profile-nav-item {
        background: none;
        border: none;
        color: #cccccc;
        padding: 1rem;
        text-align: left;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .profile-nav-item:hover,
    .profile-nav-item.active {
        background-color: #007acc;
        color: white;
    }
    
    .profile-nav-item.logout {
        margin-top: 1rem;
        color: #ff4444;
    }
    
    .profile-nav-item.logout:hover {
        background-color: #ff4444;
        color: white;
    }
    
    .profile-content {
        background-color: #1a1a1a;
        border-radius: 10px;
        padding: 2rem;
    }
    
    .profile-tab {
        display: none;
    }
    
    .profile-tab.active {
        display: block;
    }
    
    .overview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .overview-card {
        background-color: #0f0f0f;
        border-radius: 10px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .card-icon {
        font-size: 2rem;
        color: #007acc;
    }
    
    .card-content h4 {
        color: #ffffff;
        margin-bottom: 0.5rem;
    }
    
    .card-content p {
        color: #007acc;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .card-content a {
        color: #cccccc;
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .card-content a:hover {
        color: #007acc;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #333;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        color: #007acc;
        font-size: 1.2rem;
    }
    
    .activity-time {
        color: #999;
        font-size: 0.9rem;
    }
    
    .order-card {
        background-color: #0f0f0f;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #333;
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .order-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: bold;
    }
    
    .order-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn-small {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        background-color: #333;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.3s ease;
    }
    
    .btn-small:hover {
        background-color: #555;
    }
    
    .btn-small.remove {
        background-color: #ff4444;
    }
    
    .btn-small.remove:hover {
        background-color: #ff6666;
    }
    
    .address-card {
        background-color: #0f0f0f;
        border-radius: 10px;
        padding: 1.5rem;
        border: 1px solid #333;
        position: relative;
    }
    
    .address-card.default {
        border-color: #007acc;
    }
    
    .default-badge {
        background-color: #007acc;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .addresses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .settings-sections {
        display: grid;
        gap: 3rem;
    }
    
    .settings-section {
        background-color: #0f0f0f;
        border-radius: 10px;
        padding: 2rem;
    }
    
    .checkbox-group {
        display: grid;
        gap: 1rem;
    }
    
    .checkbox-group label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .wishlist-item {
        background-color: #0f0f0f;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
    }
    
    .wishlist-item .item-image {
        font-size: 3rem;
        color: #007acc;
        margin-bottom: 1rem;
    }
    
    .empty-orders,
    .empty-addresses,
    .empty-wishlist {
        text-align: center;
        padding: 3rem;
        color: #cccccc;
    }
    
    .empty-orders i,
    .empty-addresses i,
    .empty-wishlist i {
        font-size: 3rem;
        color: #666;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .profile-container {
            grid-template-columns: 1fr;
            margin-top: 100px;
        }
        
        .profile-sidebar {
            position: static;
        }
        
        .overview-grid {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .order-actions {
            flex-direction: column;
        }
    }
`;

// Inject profile styles
const styleSheet = document.createElement('style');
styleSheet.textContent = profileStyles;
document.head.appendChild(styleSheet);