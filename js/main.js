document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle with improved functionality
    const burgerMenu = document.getElementById('burger-menu');
    const navMenu = document.getElementById('nav-menu');
    const navContainer = document.querySelector('.nav-container');
    
    // Create and append overlay for mobile menu
    const overlay = document.createElement('div');
    overlay.className = 'nav-menu-overlay';
    overlay.id = 'nav-menu-overlay';
    document.body.appendChild(overlay);
    
    if (burgerMenu && navMenu) {
        // Toggle mobile menu
        burgerMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMobileMenu();
        });
        
        // Close mobile menu when clicking overlay
        overlay.addEventListener('click', function() {
            closeMobileMenu();
        });
        
        // Close mobile menu when clicking nav links
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeMobileMenu();
            });
        });
        
        // Close mobile menu when window is resized to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!navContainer.contains(e.target) && navMenu.classList.contains('active')) {
                    closeMobileMenu();
                }
            }
        });
        
        // Handle escape key to close mobile menu
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });
    }
    
    function toggleMobileMenu() {
        const isActive = navMenu.classList.contains('active');
        if (isActive) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    }
    
    function openMobileMenu() {
        navMenu.classList.add('active');
        burgerMenu.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scroll
    }
    
    function closeMobileMenu() {
        navMenu.classList.remove('active');
        burgerMenu.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = ''; // Restore background scroll
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Close mobile menu if open
                if (window.innerWidth <= 768) {
                    closeMobileMenu();
                }
            }
        });
    });

    // Image gallery functionality
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-image');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            if (mainImage) {
                mainImage.src = this.src;
                mainImage.alt = this.alt;
                
                // Add visual feedback
                thumbnails.forEach(t => t.style.opacity = '0.7');
                this.style.opacity = '1';
            }
        });
    });

    // Initialize auth state
    checkAuthState();
    
    // Handle navbar scroll effect with performance optimization
    let lastScrollY = window.scrollY;
    let ticking = false;
    const navbar = document.querySelector('.navbar');
    
    function updateNavbar() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            navbar.style.background = 'rgba(10, 10, 11, 0.98)';
            navbar.style.backdropFilter = 'blur(12px)';
        } else {
            navbar.style.background = 'rgba(10, 10, 11, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
        }
        
        lastScrollY = currentScrollY;
        ticking = false;
    }
    
    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateNavbar);
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', requestTick);
    
    // Initialize touch events for better mobile interaction
    if ('ontouchstart' in window) {
        // Add touch-friendly interactions
        const touchElements = document.querySelectorAll('.btn, .nav-action, .product-card');
        
        touchElements.forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            element.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
    }
    
    // Improved focus management for accessibility
    const focusableElements = document.querySelectorAll(
        'a[href], button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
    );
    
    // Handle focus trap for mobile menu
    function trapFocus(element) {
        const focusableChildren = element.querySelectorAll(
            'a[href], button, input, textarea, select, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstFocusable = focusableChildren[0];
        const lastFocusable = focusableChildren[focusableChildren.length - 1];
        
        element.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                if (e.shiftKey && document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                } else if (!e.shiftKey && document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        });
    }
    
    // Apply focus trap to mobile menu when active
    const navMenuObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                if (navMenu.classList.contains('active')) {
                    trapFocus(navMenu);
                    // Focus first link in mobile menu
                    const firstLink = navMenu.querySelector('.nav-link');
                    if (firstLink) firstLink.focus();
                }
            }
        });
    });
    
    if (navMenu) {
        navMenuObserver.observe(navMenu, { attributes: true });
    }
    
    // Performance optimization: Debounce resize events
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Handle any resize-specific functionality
            if (window.innerWidth > 768) {
                closeMobileMenu();
            }
        }, 250);
    });
    
    // Preload critical images on mobile
    if (window.innerWidth <= 768) {
        const criticalImages = document.querySelectorAll('img[data-src]');
        criticalImages.forEach(img => {
            img.src = img.dataset.src;
        });
    }
    
    // Add loading states for better UX
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.textContent = 'Loading...';
                submitBtn.disabled = true;
            }
        });
    });
});

function checkAuthState() {
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

function showMessage(element, message, type = 'success') {
    if (!element) return;
    
    element.textContent = message;
    element.className = `form-message ${type}`;
    element.style.display = 'block';
    
    // Auto-hide message after 5 seconds
    setTimeout(() => {
        element.style.display = 'none';
    }, 5000);
    
    // Add animation
    element.style.opacity = '0';
    element.style.transform = 'translateY(-10px)';
    
    requestAnimationFrame(() => {
        element.style.transition = 'all 0.3s ease';
        element.style.opacity = '1';
        element.style.transform = 'translateY(0)';
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
    }).format(amount);
}

function formatDate(dateString) {
    return new Intl.DateTimeFormat('en-GB', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(dateString));
}

// Utility function for debouncing
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Utility function for throttling
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Add error boundary for better error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    
    // Show user-friendly error message for critical errors
    if (e.error && e.error.message) {
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-toast';
        errorMessage.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--error);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            z-index: 1001;
            max-width: 300px;
            animation: slideIn 0.3s ease;
        `;
        errorMessage.textContent = 'Something went wrong. Please refresh the page.';
        
        document.body.appendChild(errorMessage);
        
        setTimeout(() => {
            document.body.removeChild(errorMessage);
        }, 5000);
    }
});

// Add CSS for error toast animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);