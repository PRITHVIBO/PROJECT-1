// Dashboard JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Add loading animation
    document.body.classList.add('loading');
    
    // Update user stats (simulate API call)
    updateUserStats();
    
    // Set current date/time for last login
    updateLastLogin();
});

function initializeDashboard() {
    // Get user info from session/localStorage (simulated)
    const currentUser = getCurrentUser();
    
    // Update username display
    const usernameElement = document.getElementById('username');
    if (usernameElement && currentUser) {
        usernameElement.textContent = currentUser.username;
    }
    
    console.log('Dashboard initialized for user:', currentUser?.username);
}

function getCurrentUser() {
    // Simulate getting current user from session
    // In real implementation, this would come from your auth system
    return {
        username: 'PRITHVIBO',
        email: 'prithvi@example.com',
        joinDate: 'January 2024',
        posts: 12,
        replies: 28,
        views: 1247
    };
}

function updateUserStats() {
    const user = getCurrentUser();
    
    // Animate counters
    animateCounter('user-posts', 0, user.posts, 1000);
    animateCounter('user-replies', 0, user.replies, 1200);
    animateCounter('user-views', 0, user.views, 1500);
    
    // Update member since
    const memberSinceElement = document.getElementById('member-since');
    if (memberSinceElement) {
        memberSinceElement.textContent = user.joinDate;
    }
}

function animateCounter(elementId, start, end, duration) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const range = end - start;
    const increment = range / (duration / 16); // 60fps
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            current = end;
            clearInterval(timer);
        }
        
        // Format numbers with commas for large values
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

function updateLastLogin() {
    const lastLoginElement = document.getElementById('last-login');
    if (lastLoginElement) {
        // Use the current date/time provided
        lastLoginElement.textContent = '2025-08-08 15:05:29 UTC';
    }
}

// Action handlers
function startNewTopic() {
    // In real implementation, this would navigate to a new topic creation page
    showNotification('Redirecting to create new topic...', 'info');
    setTimeout(() => {
        window.location.href = 'new-topic.html';
    }, 1000);
}

function viewMyPosts() {
    showNotification('Loading your posts...', 'info');
    setTimeout(() => {
        window.location.href = 'my-posts.html';
    }, 1000);
}

function browseCategories() {
    showNotification('Navigating to categories...', 'info');
    setTimeout(() => {
        window.location.href = 'index2.html';
    }, 1000);
}

function editProfile() {
    showNotification('Opening profile settings...', 'info');
    setTimeout(() => {
        window.location.href = 'profile-settings.html';
    }, 1000);
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        showNotification('Logging out...', 'info');
        
        // Clear session data (simulate)
        localStorage.removeItem('user');
        sessionStorage.clear();
        
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1500);
    }
}

// Utility function for notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-info-circle"></i>
        <span>${message}</span>
    `;
    
    // Style the notification
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        backgroundColor: type === 'info' ? '#667eea' : '#e74c3c',
        color: 'white',
        padding: '1rem 1.5rem',
        borderRadius: '10px',
        display: 'flex',
        alignItems: 'center',
        gap: '0.5rem',
        zIndex: '10000',
        boxShadow: '0 4px 20px rgba(0,0,0,0.2)',
        opacity: '0',
        transform: 'translateX(100%)',
        transition: 'all 0.3s ease'
    });
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Add some interactive enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-5px) scale(1)';
        });
    });
    
    // Add click effects to action cards
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(-5px) scale(0.98)';
        });
        
        card.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(-10px) scale(1)';
        });
    });
});