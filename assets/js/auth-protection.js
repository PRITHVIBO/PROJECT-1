// Authentication protection for other pages
// Add this script to index1.html, index2.html, index3.html

document.addEventListener('DOMContentLoaded', function() {
    // Check if user is authenticated
    const isAuthenticated = checkAuthStatus();
    
    if (!isAuthenticated) {
        // Show interaction blocker for strangers
        showInteractionBlocker();
        
        // Disable interactive elements
        disableInteractiveElements();
    }
});

function checkAuthStatus() {
    // Check for session/auth token
    // In real implementation, this would check your auth system
    const user = localStorage.getItem('user') || sessionStorage.getItem('user');
    return !!user;
}

function showInteractionBlocker() {
    // Create overlay for non-authenticated users
    const blocker = document.createElement('div');
    blocker.className = 'interaction-blocker';
    blocker.innerHTML = `
        <div class="blocker-content">
            <div class="blocker-message">
                <i class="fas fa-lock"></i>
                <h3>Join the Conversation!</h3>
                <p>You must create an account or sign in to interact with this forum.</p>
                <div class="blocker-actions">
                    <a href="index.html" class="btn-signin">Sign In</a>
                    <a href="index.html" class="btn-signup">Sign Up</a>
                </div>
            </div>
        </div>
    `;
    
    // Add styles
    const style = document.createElement('style');
    style.textContent = `
        .interaction-blocker {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
        }
        
        .blocker-content {
            background: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            max-width: 400px;
            margin: 0 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .blocker-message i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .blocker-message h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .blocker-message p {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .blocker-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn-signin, .btn-signup {
            padding: 0.8rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-signin {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-signup {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-signin:hover, .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(blocker);
}

function disableInteractiveElements() {
    // Disable reply buttons, forms, etc.
    const interactiveElements = [
        'button[type="submit"]',
        'textarea',
        '.reply-btn',
        '.add-message-btn',
        'input[type="text"]'
    ];
    
    interactiveElements.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            el.disabled = true;
            el.style.opacity = '0.5';
            el.style.cursor = 'not-allowed';
        });
    });
}