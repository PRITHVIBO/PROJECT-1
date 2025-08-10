// Profile Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const usernameInput = document.getElementById('username');
    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitButton = document.querySelector('button.primary');

    // Real-time username validation
    if (usernameInput) {
        usernameInput.addEventListener('input', validateUsername);
        usernameInput.addEventListener('blur', validateUsername);
    }

    // Password validation
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            validatePassword(this.value);
            validatePasswordMatch();
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        confirmPasswordInput.addEventListener('blur', validatePasswordMatch);
    }

    // Form submission handling
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    function validateUsername() {
        const username = usernameInput.value.trim();
        const pattern = /^[A-Za-z0-9_-]{3,50}$/;
        
        clearValidationMessage(usernameInput);
        
        if (username === '') {
            setValidationState(usernameInput, 'error', 'Username cannot be empty');
            return false;
        }
        
        if (username.length < 3) {
            setValidationState(usernameInput, 'error', 'Username must be at least 3 characters');
            return false;
        }
        
        if (username.length > 50) {
            setValidationState(usernameInput, 'error', 'Username must be less than 50 characters');
            return false;
        }
        
        if (!pattern.test(username)) {
            setValidationState(usernameInput, 'error', 'Only letters, numbers, underscore, and dash allowed');
            return false;
        }
        
        setValidationState(usernameInput, 'success', 'Username looks good');
        return true;
    }

    function validatePassword(password) {
        if (!password) {
            clearPasswordStrength();
            return;
        }

        const strength = calculatePasswordStrength(password);
        updatePasswordStrength(strength);
        
        if (password.length < 6) {
            setValidationState(newPasswordInput, 'error', 'Password must be at least 6 characters');
            return false;
        }
        
        if (strength.score < 2) {
            setValidationState(newPasswordInput, 'error', 'Password is too weak');
            return false;
        }
        
        setValidationState(newPasswordInput, 'success', 'Password strength is good');
        return true;
    }

    function validatePasswordMatch() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        clearValidationMessage(confirmPasswordInput);
        
        if (!confirmPassword) {
            return;
        }
        
        if (newPassword !== confirmPassword) {
            setValidationState(confirmPasswordInput, 'error', 'Passwords do not match');
            return false;
        }
        
        setValidationState(confirmPasswordInput, 'success', 'Passwords match');
        return true;
    }

    function calculatePasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            numbers: /\d/.test(password),
            symbols: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        // Calculate score
        if (checks.length) score++;
        if (checks.lowercase) score++;
        if (checks.uppercase) score++;
        if (checks.numbers) score++;
        if (checks.symbols) score++;

        let strength = 'weak';
        if (score >= 4) strength = 'strong';
        else if (score >= 3) strength = 'medium';

        return { score, strength, checks };
    }

    function updatePasswordStrength(strengthData) {
        let strengthIndicator = document.querySelector('.password-strength');
        
        if (!strengthIndicator) {
            strengthIndicator = document.createElement('div');
            strengthIndicator.className = 'password-strength';
            strengthIndicator.innerHTML = '<div class="password-strength-bar"></div>';
            newPasswordInput.parentNode.appendChild(strengthIndicator);
        }
        
        strengthIndicator.className = `password-strength ${strengthData.strength}`;
    }

    function clearPasswordStrength() {
        const strengthIndicator = document.querySelector('.password-strength');
        if (strengthIndicator) {
            strengthIndicator.remove();
        }
    }

    function setValidationState(input, state, message) {
        // Remove existing classes
        input.classList.remove('valid', 'invalid');
        
        // Add new class
        if (state === 'success') {
            input.classList.add('valid');
        } else if (state === 'error') {
            input.classList.add('invalid');
        }
        
        // Clear existing validation message
        clearValidationMessage(input);
        
        // Add new validation message
        if (message) {
            const messageEl = document.createElement('div');
            messageEl.className = `validation-message ${state}`;
            messageEl.textContent = message;
            input.parentNode.appendChild(messageEl);
        }
    }

    function clearValidationMessage(input) {
        const existingMessage = input.parentNode.querySelector('.validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }
    }

    function handleFormSubmit(e) {
        let isValid = true;
        
        // Validate username
        if (!validateUsername()) {
            isValid = false;
        }
        
        // Check if password change is being attempted
        const isPasswordChange = currentPasswordInput.value || 
                                newPasswordInput.value || 
                                confirmPasswordInput.value;
        
        if (isPasswordChange) {
            // Validate all password fields are filled
            if (!currentPasswordInput.value) {
                setValidationState(currentPasswordInput, 'error', 'Current password is required');
                isValid = false;
            }
            
            if (!newPasswordInput.value) {
                setValidationState(newPasswordInput, 'error', 'New password is required');
                isValid = false;
            }
            
            if (!confirmPasswordInput.value) {
                setValidationState(confirmPasswordInput, 'error', 'Password confirmation is required');
                isValid = false;
            }
            
            // Validate password strength and match
            if (newPasswordInput.value && !validatePassword(newPasswordInput.value)) {
                isValid = false;
            }
            
            if (!validatePasswordMatch()) {
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            showError('Please fix the validation errors above');
            return;
        }
        
        // Show loading state
        showLoading();
    }

    function showLoading() {
        submitButton.disabled = true;
        submitButton.classList.add('loading');
        submitButton.textContent = 'Saving...';
    }

    function hideLoading() {
        submitButton.disabled = false;
        submitButton.classList.remove('loading');
        submitButton.innerHTML = '<i class="fas fa-save"></i> Save Changes';
    }

    function showError(message) {
        // Remove existing error messages
        const existingErrors = document.querySelectorAll('.js-error');
        existingErrors.forEach(error => error.remove());
        
        // Create new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-list js-error';
        errorDiv.innerHTML = `<strong>Error:</strong> ${message}`;
        
        // Insert after the profile meta section
        const profileMeta = document.querySelector('.profile-meta');
        if (profileMeta) {
            profileMeta.parentNode.insertBefore(errorDiv, profileMeta.nextSibling);
        } else {
            // Fallback: insert at the top of the form wrapper
            const formWrapper = document.querySelector('.profile-wrapper');
            const firstChild = formWrapper.querySelector('h1').nextSibling;
            formWrapper.insertBefore(errorDiv, firstChild);
        }
        
        // Scroll to error
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.success, .error-list:not(.js-error)');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';
            setTimeout(() => {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 500);
        }, 5000);
    });

    // Enhanced form experience
    const inputs = document.querySelectorAll('.form-row input');
    inputs.forEach(input => {
        // Add focus animations
        input.addEventListener('focus', function() {
            this.parentNode.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentNode.classList.remove('focused');
        });
        
        // Auto-save draft (optional - stores in localStorage)
        if (input.type !== 'password') {
            input.addEventListener('input', function() {
                localStorage.setItem(`profile_${input.name}`, input.value);
            });
        }
    });

    // Restore draft values on page load
    inputs.forEach(input => {
        if (input.type !== 'password' && !input.disabled) {
            const draftValue = localStorage.getItem(`profile_${input.name}`);
            if (draftValue && !input.value) {
                input.value = draftValue;
            }
        }
    });

    // Clear draft when form is successfully submitted
    form.addEventListener('submit', function() {
        setTimeout(() => {
            inputs.forEach(input => {
                if (input.type !== 'password') {
                    localStorage.removeItem(`profile_${input.name}`);
                }
            });
        }, 1000);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+S or Cmd+S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
        
        // Escape to clear current field
        if (e.key === 'Escape' && document.activeElement.tagName === 'INPUT') {
            document.activeElement.blur();
        }
    });

    console.log('Profile page JavaScript loaded successfully');

    // Ensure header navigation works properly
    initializeHeaderNavigation();
});

function initializeHeaderNavigation() {
    // Make sure all navigation links are clickable
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.style.pointerEvents = 'auto';
        link.style.cursor = 'pointer';
        
        // Add click event listener as backup
        link.addEventListener('click', function(e) {
            // Allow normal navigation unless there's an issue
            const href = this.getAttribute('href');
            if (href && href !== '#') {
                // Force navigation if needed
                if (e.defaultPrevented) {
                    e.preventDefault();
                    window.location.href = href;
                }
            }
        });
    });

    // Ensure mobile menu works
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    const navOverlay = document.getElementById('navOverlay');
    
    if (menuToggle && mainNav && navOverlay) {
        // Remove any existing listeners and re-add
        menuToggle.replaceWith(menuToggle.cloneNode(true));
        const newMenuToggle = document.getElementById('menuToggle');
        
        function toggleMobileMenu() {
            const isOpen = mainNav.classList.toggle('open');
            navOverlay.classList.toggle('show', isOpen);
            document.body.classList.toggle('nav-open', isOpen);
            newMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
        
        newMenuToggle.addEventListener('click', toggleMobileMenu);
        navOverlay.addEventListener('click', () => {
            if (mainNav.classList.contains('open')) {
                toggleMobileMenu();
            }
        });
        
        // Close on ESC
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mainNav.classList.contains('open')) {
                toggleMobileMenu();
            }
        });
    }
    
    console.log('Header navigation initialized successfully');
}
