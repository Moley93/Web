class VyloAPI {
    constructor() {
        this.baseURL = 'http://localhost:3001/api'; // Update with your backend URL
        this.authToken = localStorage.getItem('authToken');
    }

    // Set auth token
    setAuthToken(token) {
        this.authToken = token;
        localStorage.setItem('authToken', token);
    }

    // Remove auth token
    clearAuthToken() {
        this.authToken = null;
        localStorage.removeItem('authToken');
    }

    // Make authenticated request
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        if (this.authToken && !options.skipAuth) {
            config.headers.Authorization = `Bearer ${this.authToken}`;
        }

        const response = await fetch(url, config);
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'API request failed');
        }

        return response.json();
    }

    // Auth methods
    async register(userData) {
        const response = await this.request('/auth/register', {
            method: 'POST',
            body: JSON.stringify(userData),
            skipAuth: true
        });
        
        if (response.token) {
            this.setAuthToken(response.token);
        }
        
        return response;
    }

    async login(email, password) {
        const response = await this.request('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
            skipAuth: true
        });
        
        if (response.token) {
            this.setAuthToken(response.token);
        }
        
        return response;
    }

    async logout() {
        try {
            await this.request('/auth/logout', { method: 'POST' });
        } finally {
            this.clearAuthToken();
        }
    }

    // Product methods
    async getProducts(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/products?${params}`, { skipAuth: true });
    }

    // Cart methods
    async getCart() {
        return this.request('/orders/cart');
    }

    async addToCart(productId, quantity = 1) {
        return this.request('/orders/cart', {
            method: 'POST',
            body: JSON.stringify({ productId, quantity })
        });
    }

    async updateCartItem(productId, quantity) {
        return this.request('/orders/cart', {
            method: 'PUT',
            body: JSON.stringify({ productId, quantity })
        });
    }

    async removeFromCart(productId) {
        return this.request(`/orders/cart/${productId}`, {
            method: 'DELETE'
        });
    }

    // Order methods
    async createOrder(orderData) {
        return this.request('/orders', {
            method: 'POST',
            body: JSON.stringify(orderData)
        });
    }

    async getOrders() {
        return this.request('/orders');
    }

    async getOrderById(orderId) {
        return this.request(`/orders/${orderId}`);
    }

    // User profile methods
    async getProfile() {
        return this.request('/auth/profile');
    }

    async updateProfile(profileData) {
        return this.request('/auth/profile', {
            method: 'PUT',
            body: JSON.stringify(profileData)
        });
    }
}

// Create global API instance
const vyloAPI = new VyloAPI();