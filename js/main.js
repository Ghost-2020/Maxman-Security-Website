// Modern Security Company Website JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initParticles();
    initAnimations();
    initFormHandlers();
    initScrollEffects();
    initModals();
    initTooltips();
});

// Particle Background Animation
function initParticles() {
    const particlesContainer = document.createElement('div');
    particlesContainer.className = 'particles-container';
    document.body.appendChild(particlesContainer);
    
    // Create particles
    for (let i = 0; i < 50; i++) {
        createParticle(particlesContainer);
    }
}

function createParticle(container) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    
    // Random size and position
    const size = Math.random() * 4 + 2;
    const x = Math.random() * window.innerWidth;
    const y = Math.random() * window.innerHeight;
    const delay = Math.random() * 6;
    
    particle.style.cssText = `
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        animation-delay: ${delay}s;
        animation-duration: ${6 + Math.random() * 4}s;
    `;
    
    container.appendChild(particle);
}


// Show Notification Function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.toast-notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'toast-notification';
    
    // Set background based on type
    if (type === 'success') {
        notification.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
    } else if (type === 'error') {
        notification.style.background = 'linear-gradient(135deg, #dc3545, #c82333)';
    } else {
        notification.style.background = 'var(--gradient-accent)';
    }
    
    notification.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : type === 'error' ? 'bi-exclamation-circle' : 'bi-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Scroll-triggered animations
function initScrollEffects() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
                // Add staggered animation delay for multiple elements
                const siblings = Array.from(entry.target.parentElement.children);
                const index = siblings.indexOf(entry.target);
                entry.target.style.animationDelay = `${index * 0.1}s`;
            }
        });
    }, observerOptions);
    
    // Observe elements with scroll-animate class
    document.querySelectorAll('.scroll-animate').forEach(el => {
        observer.observe(el);
    });

    // Add navbar scroll effect
    window.addEventListener('scroll', () => {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// Smooth scrolling for navigation links
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Form handling with modern UX
function initFormHandlers() {
    // Service Request Form
    const serviceForm = document.getElementById('serviceRequestForm');
    if (serviceForm) {
        serviceForm.addEventListener('submit', handleServiceRequest);
    }
    
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Newsletter Form
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletter);
    }
    
    // Service type selection
    const serviceTypeSelect = document.getElementById('serviceType');
    const otherServiceInput = document.getElementById('otherService');
    
    if (serviceTypeSelect && otherServiceInput) {
        serviceTypeSelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                otherServiceInput.classList.remove('d-none');
                otherServiceInput.required = true;
            } else {
                otherServiceInput.classList.add('d-none');
                otherServiceInput.required = false;
            }
        });
    }
}

// Handle Service Request
async function handleServiceRequest(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    const messagesDiv = document.getElementById('form-messages');
    
    // Clear previous messages
    if (messagesDiv) {
        messagesDiv.innerHTML = '';
    }
    
    // Show loading state
    submitBtn.innerHTML = '<span class="loading"></span> Processing...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Service request submitted successfully! We\'ll contact you soon.', 'success');
            form.reset();
            // Reset otherService field visibility
            const otherServiceInput = document.getElementById('otherService');
            if (otherServiceInput) {
                otherServiceInput.classList.add('d-none');
                otherServiceInput.required = false;
            }
            bootstrap.Modal.getInstance(document.getElementById('requestServiceModal')).hide();
        } else {
            const errorMessage = result.message || 'An error occurred. Please try again.';
            showNotification(errorMessage, 'error');
            // Also display in form messages div
            if (messagesDiv) {
                messagesDiv.innerHTML = `<div class="alert alert-danger" role="alert">${errorMessage}</div>`;
            }
        }
    } catch (error) {
        // Silent error handling - show user-friendly message
        const errorMessage = 'Network error. Please check your connection and try again.';
        showNotification(errorMessage, 'error');
        if (messagesDiv) {
            messagesDiv.innerHTML = `<div class="alert alert-danger" role="alert">${errorMessage}</div>`;
        }
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}


// Handle Login
async function handleLogin(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<span class="loading"></span> Logging in...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Login successful! Redirecting to dashboard...', 'success');
            // Show dashboard nav link
            const dashboardNav = document.getElementById('dashboardNav');
            const loginNav = document.getElementById('loginNav');
            if (dashboardNav) dashboardNav.classList.remove('d-none');
            if (loginNav) loginNav.style.display = 'none';
            
            setTimeout(() => {
                window.location.href = 'admin-dashboard.php';
            }, 1000);
        } else {
            showNotification(result.message || 'Invalid credentials. Please try again.', 'error');
        }
    } catch (error) {
        // Silent error handling - show user-friendly message
        showNotification('Network error. Please try again.', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Handle Newsletter Subscription
async function handleNewsletter(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<span class="loading"></span>';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Subscribed successfully!', 'success');
            form.reset();
        } else {
            showNotification(result.message || 'Subscription failed.', 'error');
        }
    } catch (error) {
        // Silent error handling - show user-friendly message
        showNotification('Network error. Please try again.', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Initialize modals
function initModals() {
    // Initialize all Bootstrap modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });
}

// Initialize tooltips
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Parallax effect disabled for static positioning
function initParallax() {
    // Parallax effect disabled - hero section will remain static
}

// Typing animation for hero title
function initTypingAnimation() {
    const heroTitle = document.querySelector('.hero-title');
    if (heroTitle) {
        const text = heroTitle.textContent;
        heroTitle.textContent = '';
        
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                heroTitle.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            }
        };
        
        // Start typing animation after a delay
        setTimeout(typeWriter, 500);
    }
}

// Initialize all animations
function initAnimations() {
    initSmoothScrolling();
    initParallax();
    initTypingAnimation();
    
    // Add animation classes to elements (excluding service cards)
    // Service cards will have static positioning for smooth scrolling
}

// Add CSS for scroll animations
const style = document.createElement('style');
style.textContent = `
    .animate-on-scroll {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s ease-out;
    }
    
    .animate-on-scroll.animate-in {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Service cards excluded from scroll animations for smooth scrolling */
    
    /* All elements excluded from scroll animations for completely static positioning */
    .testimonial, .faq-item, .card {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
    }
`;
document.head.appendChild(style);

// Performance optimization: Debounce scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debouncing to scroll events
window.addEventListener('scroll', debounce(function() {
    // Scroll-based animations can be added here
}, 10));

// Add some interactive features
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to service cards
    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add click effects to buttons
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Add ripple animation CSS
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .btn {
        position: relative;
        overflow: hidden;
    }
`;
document.head.appendChild(rippleStyle);

// ===== MOBILE OPTIMIZATION & TOUCH INTERACTIONS =====

// Mobile Floating Action Menu
function initMobileFabMenu() {
    const fabMain = document.getElementById('fabMain');
    const fabMenuItems = document.getElementById('fabMenuItems');
    const fabService = document.getElementById('fabService');
    const fabContact = document.getElementById('fabContact');
    
    if (!fabMain) return;
    
    // Toggle FAB menu
    fabMain.addEventListener('click', function() {
        fabMenuItems.classList.toggle('show');
        this.querySelector('i').classList.toggle('bi-plus');
        this.querySelector('i').classList.toggle('bi-x');
    });
    
    // FAB menu actions
    if (fabService) {
        fabService.addEventListener('click', function() {
            // Open service request modal
            const serviceModal = new bootstrap.Modal(document.getElementById('requestServiceModal'));
            serviceModal.show();
            fabMenuItems.classList.remove('show');
            fabMain.querySelector('i').classList.add('bi-plus');
            fabMain.querySelector('i').classList.remove('bi-x');
        });
    }
    
    if (fabContact) {
        fabContact.addEventListener('click', function() {
            // Scroll to contact section
            const contactSection = document.getElementById('contact');
            if (contactSection) {
                contactSection.scrollIntoView({ behavior: 'smooth' });
            }
            fabMenuItems.classList.remove('show');
            fabMain.querySelector('i').classList.add('bi-plus');
            fabMain.querySelector('i').classList.remove('bi-x');
        });
    }
    
    // Close FAB menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!fabMain.contains(e.target) && !fabMenuItems.contains(e.target)) {
            fabMenuItems.classList.remove('show');
            fabMain.querySelector('i').classList.add('bi-plus');
            fabMain.querySelector('i').classList.remove('bi-x');
        }
    });
}

// Touch Gestures and Swipe Detection
function initTouchGestures() {
    let startX, startY, endX, endY;
    const minSwipeDistance = 50;
    
    // Touch start
    document.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    }, { passive: true });
    
    // Touch end
    document.addEventListener('touchend', function(e) {
        endX = e.changedTouches[0].clientX;
        endY = e.changedTouches[0].clientY;
        
        const deltaX = endX - startX;
        const deltaY = endY - startY;
        
        // Determine swipe direction
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
            if (deltaX > 0) {
                handleSwipeRight();
            } else {
                handleSwipeLeft();
            }
        } else if (Math.abs(deltaY) > Math.abs(deltaX) && Math.abs(deltaY) > minSwipeDistance) {
            if (deltaY > 0) {
                handleSwipeDown();
            } else {
                handleSwipeUp();
            }
        }
    }, { passive: true });
}

// Swipe handlers
function handleSwipeRight() {
    showGestureFeedback('Swiped Right');
    // Could open navigation menu
}

function handleSwipeLeft() {
    showGestureFeedback('Swiped Left');
    // Could close modals or go back
}

function handleSwipeUp() {
    showGestureFeedback('Swiped Up');
    // Could scroll to top or show quick actions
}

function handleSwipeDown() {
    showGestureFeedback('Swiped Down');
    // Could refresh or show notifications
}

// Gesture feedback
function showGestureFeedback(message) {
    const feedback = document.getElementById('gestureFeedback');
    if (feedback) {
        feedback.textContent = message;
        feedback.classList.add('show');
        
        setTimeout(() => {
            feedback.classList.remove('show');
        }, 1000);
    }
}

// Mobile-specific optimizations
function initMobileOptimizations() {
    // Prevent zoom on input focus (iOS)
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            if (window.innerWidth <= 768) {
                this.style.fontSize = '16px';
            }
        });
        
        input.addEventListener('blur', function() {
            if (window.innerWidth <= 768) {
                this.style.fontSize = '';
            }
        });
    });
    
    // Optimize scrolling performance
    if (window.innerWidth <= 768) {
        document.body.style.webkitOverflowScrolling = 'touch';
        
        // Reduce animations on mobile
        const animatedElements = document.querySelectorAll('.service-card, .testimonial, .card');
        animatedElements.forEach(el => {
            el.style.transition = 'none';
        });
    }
    
    // Handle orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(() => {
            // Recalculate positions after orientation change
            window.scrollTo(0, window.scrollY);
        }, 100);
    });
}

// Mobile-specific loading states
function showMobileLoading(element) {
    if (window.innerWidth <= 768) {
        const originalContent = element.innerHTML;
        element.innerHTML = '<div class="loading-spinner"></div>';
        element.disabled = true;
        
        return function() {
            element.innerHTML = originalContent;
            element.disabled = false;
        };
    }
    return function() {};
}

// Enhanced touch interactions for cards
function initTouchInteractions() {
    const touchElements = document.querySelectorAll('.service-card, .testimonial, .card');
    
    touchElements.forEach(element => {
        let touchStartTime;
        let touchEndTime;
        
        element.addEventListener('touchstart', function(e) {
            touchStartTime = new Date().getTime();
            this.style.transform = 'scale(0.98)';
        }, { passive: true });
        
        element.addEventListener('touchend', function(e) {
            touchEndTime = new Date().getTime();
            const touchDuration = touchEndTime - touchStartTime;
            
            this.style.transform = '';
            
            // Long press detection (500ms)
            if (touchDuration > 500) {
                handleLongPress(this);
            }
        }, { passive: true });
        
        element.addEventListener('touchcancel', function(e) {
            this.style.transform = '';
        }, { passive: true });
    });
}

// Long press handler
function handleLongPress(element) {
    // Show context menu or additional options
    showGestureFeedback('Long press detected');
    
    // Example: Show quick actions for the element
    const rect = element.getBoundingClientRect();
    showQuickActions(rect.left + rect.width / 2, rect.top);
}

// Quick actions menu
function showQuickActions(x, y) {
    // Create quick actions menu
    const menu = document.createElement('div');
    menu.className = 'quick-actions-menu';
    menu.style.cssText = `
        position: fixed;
        left: ${x}px;
        top: ${y}px;
        background: var(--gradient-primary);
        border-radius: 12px;
        padding: 8px;
        box-shadow: var(--shadow-heavy);
        z-index: 10000;
        transform: translate(-50%, -50%);
    `;
    
    menu.innerHTML = `
        <button class="quick-action-btn" onclick="shareElement()">
            <i class="bi bi-share"></i> Share
        </button>
        <button class="quick-action-btn" onclick="bookmarkElement()">
            <i class="bi bi-bookmark"></i> Save
        </button>
    `;
    
    document.body.appendChild(menu);
    
    // Remove menu after 3 seconds
    setTimeout(() => {
        menu.remove();
    }, 3000);
    
    // Remove menu on click outside
    document.addEventListener('click', function removeMenu() {
        menu.remove();
        document.removeEventListener('click', removeMenu);
    });
}

// Quick action handlers
function shareElement() {
    if (navigator.share) {
        navigator.share({
            title: 'Maxman Security',
            text: 'Check out this security service!',
            url: window.location.href
        });
    } else {
        showGestureFeedback('Sharing not supported');
    }
}

function bookmarkElement() {
    showGestureFeedback('Saved to bookmarks');
}

// Mobile-specific CSS for quick actions
const quickActionsStyle = document.createElement('style');
quickActionsStyle.textContent = `
    .quick-actions-menu {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .quick-action-btn {
        background: none;
        border: none;
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .quick-action-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .quick-action-btn:active {
        background: rgba(255, 255, 255, 0.2);
    }
`;
document.head.appendChild(quickActionsStyle);

// Initialize mobile features
document.addEventListener('DOMContentLoaded', function() {
    initMobileFabMenu();
    initTouchGestures();
    initMobileOptimizations();
    initTouchInteractions();
    
    // Add mobile-specific event listeners
    if (window.innerWidth <= 768) {
        // Double tap to zoom prevention
        let lastTap = 0;
        document.addEventListener('touchend', function(e) {
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;
            if (tapLength < 500 && tapLength > 0) {
                e.preventDefault();
            }
            lastTap = currentTime;
        });
        
        // Prevent pull-to-refresh on critical elements
        const criticalElements = document.querySelectorAll('.mobile-fab-menu');
        criticalElements.forEach(el => {
            el.addEventListener('touchmove', function(e) {
                e.preventDefault();
            }, { passive: false });
        });
    }
}); 