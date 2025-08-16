// Modern Security Company Website JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initParticles();
    initAnimations();
    initFormHandlers();
    initScrollEffects();
    initModals();
    initTooltips();
    initEmergencyButton();
    
    console.log('Maxman Security Website Loaded Successfully');
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

// Automatic Emergency Button
function initEmergencyButton() {
    const emergencyBtn = document.getElementById('openAlertModal');
    
    if (emergencyBtn) {
        emergencyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            this.innerHTML = '<i class="bi bi-hourglass-split"></i>';
            this.disabled = true;
            
            // Get user's location automatically
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Auto-send emergency alert
                        sendEmergencyAlert(position);
                    },
                    function(error) {
                        // If location fails, still send alert without location
                        sendEmergencyAlert(null);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 60000
                    }
                );
            } else {
                // Geolocation not supported, send alert without location
                sendEmergencyAlert(null);
            }
        });
    }
}

// Send Emergency Alert Function
async function sendEmergencyAlert(position) {
    const emergencyBtn = document.getElementById('openAlertModal');
    
    try {
        // Prepare alert data
        const alertData = new FormData();
        alertData.append('alertMessage', 'EMERGENCY ALERT: User has pressed the emergency button and requires immediate assistance.');
        alertData.append('alertName', 'Emergency User');
        alertData.append('alertPhone', 'Not provided');
        
        if (position) {
            const coords = `${position.coords.latitude},${position.coords.longitude}`;
            alertData.append('alertLocation', coords);
        }
        
        // Send the alert
        const response = await fetch('php/receive_alert.php', {
            method: 'POST',
            body: alertData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success notification
            showNotification('Emergency alert sent successfully! Help is on the way.', 'success');
            
            // Reset button
            emergencyBtn.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
            emergencyBtn.disabled = false;
            
            // Add success animation
            emergencyBtn.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            setTimeout(() => {
                emergencyBtn.style.background = 'var(--gradient-danger)';
            }, 2000);
            
        } else {
            throw new Error(result.message || 'Failed to send alert');
        }
        
    } catch (error) {
        console.error('Emergency alert error:', error);
        showNotification('Failed to send emergency alert. Please try again or call emergency services directly.', 'error');
        
        // Reset button
        emergencyBtn.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
        emergencyBtn.disabled = false;
    }
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
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // All elements excluded from scroll animations for completely static positioning
    // No elements will move during scroll
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
    
    // Security Alert Form (manual form)
    const alertForm = document.getElementById('securityAlertForm');
    if (alertForm) {
        alertForm.addEventListener('submit', handleSecurityAlert);
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
            bootstrap.Modal.getInstance(document.getElementById('requestServiceModal')).hide();
        } else {
            showNotification(result.message || 'An error occurred. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Network error. Please check your connection and try again.', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Handle Security Alert (manual form)
async function handleSecurityAlert(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<span class="loading"></span> Sending Alert...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Emergency alert sent successfully! Help is on the way.', 'success');
            form.reset();
            bootstrap.Modal.getInstance(document.getElementById('securityAlertModal')).hide();
        } else {
            showNotification(result.message || 'Failed to send alert. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Network error. Please try again or call emergency services directly.', 'error');
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
            showNotification('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1000);
        } else {
            showNotification(result.message || 'Invalid credentials. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
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
        console.error('Error:', error);
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
    
    // Initialize location sharing for manual form
    initLocationSharing();
}

// Location sharing functionality (for manual form)
function initLocationSharing() {
    const getLocationBtn = document.getElementById('getLocationBtn');
    const locationInput = document.getElementById('alertLocation');
    const locationStatus = document.getElementById('locationStatus');
    
    if (getLocationBtn) {
        getLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                this.innerHTML = '<span class="loading"></span> Getting location...';
                this.disabled = true;
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const coords = `${position.coords.latitude},${position.coords.longitude}`;
                        locationInput.value = coords;
                        locationStatus.innerHTML = '<span class="text-success">Location shared successfully!</span>';
                        getLocationBtn.innerHTML = 'Location Shared';
                        getLocationBtn.classList.remove('btn-outline-primary');
                        getLocationBtn.classList.add('btn-success');
                    },
                    function(error) {
                        locationStatus.innerHTML = '<span class="text-danger">Unable to get location. Please enter manually.</span>';
                        getLocationBtn.innerHTML = 'Try Again';
                        getLocationBtn.disabled = false;
                    }
                );
            } else {
                locationStatus.innerHTML = '<span class="text-warning">Geolocation not supported by this browser.</span>';
            }
        });
    }
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