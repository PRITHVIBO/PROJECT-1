// Auth page JavaScript functionality
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    if (registerBtn) {
        registerBtn.addEventListener('click', function () {
            if (container) {
                container.classList.add('active');
            } else {
                console.warn('Auth container not found: cannot activate register view');
            }
        });
    }

    if (loginBtn) {
        loginBtn.addEventListener('click', function () {
            if (container) {
                container.classList.remove('active');
            } else {
                console.warn('Auth container not found: cannot activate login view');
            }
        });
    }

    // Forgot password functionality with smooth transitions
    // Accept an optional event to avoid relying on the implicit global `event`
    window.showForgotPassword = function (e) {
        const container = document.getElementById('container');

        // Prevent default for anchor clicks
        if (e && typeof e.preventDefault === 'function') e.preventDefault();

        // Add loading effect (be defensive in case no event/element)
        const forgotLink = (e && (e.currentTarget || e.target)) || document.querySelector('.forgot-password-link');
        const originalText = forgotLink ? forgotLink.innerHTML : null;
        if (forgotLink) {
            forgotLink.innerHTML = '⏳ Loading...';
            forgotLink.style.pointerEvents = 'none';
        }

        // Smooth transition
        setTimeout(() => {
            container.classList.add("forgot");
            if (forgotLink) {
                forgotLink.innerHTML = originalText;
                forgotLink.style.pointerEvents = 'auto';
            }

            // Add subtle animation to the forgot password form
            const forgotForm = document.getElementById('forgotPassword');
            if (forgotForm) {
                forgotForm.style.opacity = '0';
                forgotForm.style.transform = 'scale(0.9)';

                setTimeout(() => {
                    forgotForm.style.transition = 'all 0.5s ease';
                    forgotForm.style.opacity = '1';
                    forgotForm.style.transform = 'scale(1)';
                }, 100);
            }
        }, 200);
    };

    window.hideForgotPassword = function () {
        const container = document.getElementById('container');
        const forgotForm = document.getElementById('forgotPassword');

        // Smooth transition back
        if (forgotForm) {
            forgotForm.style.transition = 'all 0.3s ease';
            forgotForm.style.opacity = '0';
            forgotForm.style.transform = 'scale(0.9)';
        }

        setTimeout(() => {
            container.classList.remove("forgot");
            if (forgotForm) {
                forgotForm.style.transform = 'scale(1)';
            }
        }, 300);
    };

    // Admin access token system
    window.showAdminAccess = function () {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.id = 'adminTokenOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        `;

        // Create modal dialog
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: translateY(20px);
            transition: all 0.3s ease;
        `;

        modal.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 48px; margin-bottom: 15px;">🔒</div>
                <h2 style="color: #2a5298; margin-bottom: 10px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                    Secure Admin Access
                </h2>
                <p style="color: #666; margin-bottom: 25px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px;">
                    Enter the security token to access admin portal
                </p>
                
                <input type="password" id="adminToken" placeholder="Enter security token" style="
                    width: 100%;
                    padding: 14px 16px;
                    border: 2px solid #e1e5e9;
                    border-radius: 8px;
                    font-size: 14px;
                    box-sizing: border-box;
                    margin-bottom: 20px;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    transition: border-color 0.3s ease;
                " onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e1e5e9'">
                
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button onclick="verifyAdminToken(event)" style="
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 12px 24px;
                        border: none;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 600;
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        Access Admin Portal
                    </button>
                    
                    <button onclick="closeAdminModal()" style="
                        background: #f8f9fa;
                        color: #6c757d;
                        padding: 12px 24px;
                        border: 1px solid #ced4da;
                        border-radius: 8px;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 600;
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.backgroundColor='#e9ecef'" onmouseout="this.style.backgroundColor='#f8f9fa'">
                        Cancel
                    </button>
                </div>
                
                <div id="tokenError" style="
                    color: #dc3545;
                    font-size: 13px;
                    margin-top: 15px;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    display: none;
                "></div>
                
                <div style="
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    color: #856404;
                    padding: 12px;
                    border-radius: 8px;
                    margin-top: 20px;
                    font-size: 12px;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                ">
                    <strong>For developers:</strong> Token is defined in admin_access.php file
                </div>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Animate in
        setTimeout(() => {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0)';
        }, 10);

        // Focus on input
        setTimeout(() => {
            document.getElementById('adminToken').focus();
        }, 300);

        // Handle Enter key
        document.getElementById('adminToken').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                verifyAdminToken();
            }
        });
    };

    // Verify admin token function
    window.verifyAdminToken = function (e) {
        const token = document.getElementById('adminToken').value;
        const errorDiv = document.getElementById('tokenError');

        if (!token.trim()) {
            errorDiv.textContent = 'Please enter the security token';
            errorDiv.style.display = 'block';
            return;
        }

        // Show loading
        const submitBtn = (e && (e.currentTarget || e.target)) || document.querySelector('#adminTokenOverlay button');
        const originalText = submitBtn ? submitBtn.textContent : null;
        if (submitBtn) {
            submitBtn.textContent = 'Verifying...';
            submitBtn.disabled = true;
        }

        // Create a form and submit the token to admin_access.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin_access.php';
        form.style.display = 'none';

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'security_token';
        tokenInput.value = token;

        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    };

    // Close admin modal function
    window.closeAdminModal = function () {
        const overlay = document.getElementById('adminTokenOverlay');
        if (overlay) {
            overlay.style.opacity = '0';
            const modal = overlay.querySelector('div');
            modal.style.transform = 'translateY(20px)';

            setTimeout(() => {
                document.body.removeChild(overlay);
            }, 300);
        }
    };

    // Ensure the Forgot Password link always works, even without inline handlers
    const forgotLinkEl = document.querySelector('.forgot-password-link');
    if (forgotLinkEl) {
        forgotLinkEl.addEventListener('click', function (e) {
            // Delegate to the exported handler
            if (typeof window.showForgotPassword === 'function') {
                window.showForgotPassword(e);
            }
        });
    }

    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.msg, .flash-message');
    messages.forEach(msg => {
        setTimeout(() => {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                if (msg.parentNode) {
                    msg.parentNode.removeChild(msg);
                }
            }, 300);
        }, 5000);
    });

    // Add loading animation on form submission
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = 'Processing...';
                submitBtn.disabled = true;
            }
        });
    });
});
