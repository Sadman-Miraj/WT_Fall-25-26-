document.addEventListener('DOMContentLoaded', function() {
    // Cookie notification
    if (!localStorage.getItem('cookie_notice')) {
        showCookieNotice();
    }
    
    // Welcome message animation
    const welcomeMessage = document.querySelector('.welcome-message');
    if (welcomeMessage) {
        welcomeMessage.style.opacity = '0';
        welcomeMessage.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            welcomeMessage.style.transition = 'all 0.5s ease';
            welcomeMessage.style.opacity = '1';
            welcomeMessage.style.transform = 'translateY(0)';
        }, 500);
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Service items hover effect enhancement
    const serviceItems = document.querySelectorAll('.service-item');
    serviceItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Inventory items click effect
    const inventoryItems = document.querySelectorAll('.light');
    inventoryItems.forEach(item => {
        item.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
    });
    
    // Enhanced Dropdown Functionality with 1-second hover
    const dropdown = document.querySelector('.dropdown');
    const dropdownContent = document.querySelector('.dropdown-content');
    
    if (dropdown && dropdownContent) {
        let dropdownTimer;
        let isMouseOverDropdown = false;
        
        // Show dropdown on hover with animation
        dropdown.addEventListener('mouseenter', function() {
            clearTimeout(dropdownTimer);
            isMouseOverDropdown = true;
            showDropdown();
        });
        
        // Hide dropdown with delay when leaving
        dropdown.addEventListener('mouseleave', function(e) {
            // Check if mouse is moving to dropdown content
            const relatedTarget = e.relatedTarget;
            if (relatedTarget && !dropdownContent.contains(relatedTarget)) {
                isMouseOverDropdown = false;
                dropdownTimer = setTimeout(() => {
                    if (!isMouseOverDropdown) {
                        hideDropdown();
                    }
                }, 1000); // Stay for 1 second
            }
        });
        
        // Handle dropdown content hover
        dropdownContent.addEventListener('mouseenter', function() {
            clearTimeout(dropdownTimer);
            isMouseOverDropdown = true;
        });
        
        dropdownContent.addEventListener('mouseleave', function(e) {
            isMouseOverDropdown = false;
            const relatedTarget = e.relatedTarget;
            
            // Check if mouse is moving back to dropdown trigger
            if (relatedTarget && !dropdown.contains(relatedTarget)) {
                dropdownTimer = setTimeout(() => {
                    if (!isMouseOverDropdown) {
                        hideDropdown();
                    }
                }, 300);
            }
        });
        
        // Mobile touch support
        let isMobile = window.innerWidth <= 768 || 'ontouchstart' in window;
        let isDropdownOpen = false;
        
        dropdown.addEventListener('click', function(e) {
            if (isMobile) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!isDropdownOpen) {
                    showDropdown();
                    isDropdownOpen = true;
                } else {
                    hideDropdown();
                    isDropdownOpen = false;
                }
            }
        });
        
        // Close dropdown when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (isMobile && isDropdownOpen && !dropdown.contains(e.target)) {
                hideDropdown();
                isDropdownOpen = false;
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            isMobile = window.innerWidth <= 768 || 'ontouchstart' in window;
            if (!isMobile && dropdownContent.classList.contains('show')) {
                hideDropdown();
                isDropdownOpen = false;
            }
        });
        
        function showDropdown() {
            dropdownContent.style.display = 'block';
            // Force reflow
            dropdownContent.offsetHeight;
            dropdownContent.classList.add('show');
        }
        
        function hideDropdown() {
            dropdownContent.classList.remove('show');
            setTimeout(() => {
                if (!dropdownContent.classList.contains('show')) {
                    dropdownContent.style.display = 'none';
                }
            }, 300);
        }
    }
    
    // Book and Buy button enhancements
    const bookBtn = document.getElementById('book');
    const buyBtn = document.getElementById('buy');
    
    [bookBtn, buyBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(-50%) translateY(-5px) scale(1.05)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(-50%) translateY(0) scale(1)';
            });
        }
    });
    
    // Social media icons hover effect
    const socialIcons = document.querySelectorAll('#social a');
    socialIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) rotate(5deg)';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) rotate(0)';
        });
    });
    
    // Header scroll effect
    const header = document.getElementById('top');
    let lastScroll = 0;
    let isHeaderHidden = false;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll <= 0) {
            header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            return;
        }
        
        if (currentScroll > lastScroll && currentScroll > 100) {
            // Scrolling down
            if (!isHeaderHidden) {
                header.style.transform = 'translateY(-100%)';
                isHeaderHidden = true;
            }
        } else {
            // Scrolling up
            if (isHeaderHidden) {
                header.style.transform = 'translateY(0)';
                header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.15)';
                isHeaderHidden = false;
            }
        }
        
        lastScroll = currentScroll;
    });
    
    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.1
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for browsers without IntersectionObserver
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
    
    // Add intersection observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements for fade-in
    const fadeElements = document.querySelectorAll('.service-item, .light, .fp');
    fadeElements.forEach(el => {
        observer.observe(el);
    });
    
    // Add fade-in animation styles
    const animationStyle = document.createElement('style');
    animationStyle.textContent = `
        .service-item, .light, .fp {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        
        .service-item.fade-in, .light.fade-in, .fp.fade-in {
            opacity: 1;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(animationStyle);
});

// Cookie Notice Function
function showCookieNotice() {
    const notice = document.createElement('div');
    notice.id = 'cookie-notice';
    notice.innerHTML = `
        <div class="cookie-content">
            <p>We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.</p>
            <button id="accept-cookies">Accept</button>
        </div>
    `;
    
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        #cookie-notice {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(44, 62, 80, 0.95);
            color: white;
            padding: 20px;
            z-index: 10000;
            animation: slideUp 0.5s ease;
            backdrop-filter: blur(5px);
        }
        
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .cookie-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .cookie-content p {
            flex: 1;
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
        }
        
        #accept-cookies {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        #accept-cookies:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1f6396 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        @media (max-width: 768px) {
            .cookie-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            #accept-cookies {
                width: 100%;
                max-width: 200px;
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notice);
    
    const acceptButton = document.getElementById('accept-cookies');
    acceptButton.addEventListener('click', function() {
        localStorage.setItem('cookie_notice', 'accepted');
        notice.style.animation = 'slideUp 0.5s ease reverse forwards';
        setTimeout(() => {
            notice.remove();
            style.remove();
        }, 500);
    });
    
    // Auto-hide after 10 seconds
    setTimeout(() => {
        if (notice.parentNode) {
            notice.style.animation = 'slideUp 0.5s ease reverse forwards';
            setTimeout(() => {
                notice.remove();
                style.remove();
            }, 500);
        }
    }, 10000);
}

// Logout confirmation
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('logout-link') || e.target.closest('.logout-link')) {
        e.preventDefault();
        const logoutLink = e.target.closest('.logout-link');
        showLogoutConfirmation(logoutLink.href);
    }
});

function showLogoutConfirmation(logoutUrl) {
    if (document.getElementById('logout-modal')) return;
    
    const modal = document.createElement('div');
    modal.id = 'logout-modal';
    modal.innerHTML = `
        <div class="modal-overlay">
            <div class="modal-content">
                <h3>Confirm Logout</h3>
                <p>Are you sure you want to logout?</p>
                <div class="modal-buttons">
                    <button id="confirm-logout" class="logout-btn">Yes, Logout</button>
                    <button id="cancel-logout" class="cancel-btn">Cancel</button>
                </div>
            </div>
        </div>
    `;
    
    // Add modal styles
    const style = document.createElement('style');
    style.textContent = `
        #logout-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
        }
        
        .modal-overlay {
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            animation: scaleIn 0.3s ease;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .modal-content h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .modal-content p {
            color: #666;
            margin-bottom: 25px;
            font-size: 16px;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
        
        .cancel-btn {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
        }
        
        .cancel-btn:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        @media (max-width: 480px) {
            .modal-buttons {
                flex-direction: column;
            }
        }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(modal);
    
    document.getElementById('confirm-logout').addEventListener('click', function() {
        window.location.href = logoutUrl;
    });
    
    document.getElementById('cancel-logout').addEventListener('click', function() {
        modal.remove();
        style.remove();
    });
    
    // Close modal when clicking outside
    modal.querySelector('.modal-overlay').addEventListener('click', function(e) {
        if (e.target === this) {
            modal.remove();
            style.remove();
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('logout-modal')) {
            modal.remove();
            style.remove();
        }
    });
}

// Keyboard navigation support
document.addEventListener('keydown', function(e) {
    // Close dropdown on Escape key
    const dropdownContent = document.querySelector('.dropdown-content');
    if (e.key === 'Escape' && dropdownContent && dropdownContent.style.display === 'block') {
        dropdownContent.classList.remove('show');
        setTimeout(() => {
            dropdownContent.style.display = 'none';
        }, 300);
    }
});

// Add CSS for dropdown animation
const dropdownStyle = document.createElement('style');
dropdownStyle.textContent = `
    /* Dropdown animation fix */
    .dropdown-content {
        transition: opacity 0.3s ease, transform 0.3s ease !important;
    }
    
    /* Ensure dropdown stays above other elements */
    .dropdown {
        z-index: 1001;
    }
    
    /* Improve touch targets on mobile */
    @media (max-width: 768px) {
        .dropdown-content a {
            padding: 15px 20px;
        }
        
        .user-icon {
            width: 45px;
            height: 45px;
            font-size: 20px;
        }
    }
`;
document.head.appendChild(dropdownStyle);