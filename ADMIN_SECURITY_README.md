# TechForum Admin Access Security Configuration

## 🔒 Secure Admin Access System

The admin portal is now protected by a two-layer security system with permanent admin credentials:

### Layer 1: Security Token
- **File**: `admin_access.php`
- **Current Token**: `TF_SECURE_2025_ADM1N_G8T3WAY_K3Y`
- **Purpose**: Prevents unauthorized discovery of admin login interface

### Layer 2: Permanent Admin Authentication
- **Username**: `techforum_admin`
- **Password**: `SecureAdmin@2025!`
- **Role**: Superadmin with full permissions
- **Storage**: Hardcoded in PHP constants (no database dependency)

## 📝 User Flow Clarification

### For Regular Users (Forgot Password):
1. Go to Sign In/Sign Up page (`auth.php`)
2. Click "Forgot your password?" link
3. Click "Request Password Reset" button → Goes to `password_reset.php`
4. Submit email for admin review
5. Admin provides new credentials via platform

### For Admin Access:
1. Go to Sign In/Sign Up page (`auth.php`) 
2. Click "🔒 Admin Access" button → Goes to `admin_access.php`
3. Enter security token: `TF_SECURE_2025_ADM1N_G8T3WAY_K3Y`
4. Enter permanent admin credentials:
   - **Username**: `techforum_admin`
   - **Password**: `SecureAdmin@2025!`
5. Access admin dashboard

## 📝 Configuration Instructions

### To Change Admin Credentials:
1. Open `admin_access.php`
2. Find lines: 
   ```php
   define('ADMIN_USERNAME', 'techforum_admin');
   define('ADMIN_PASSWORD', 'SecureAdmin@2025!');
   ```
3. Change the username and password to your preferred values
4. Save and distribute new credentials to authorized admins only

### To Change Security Token:
1. Open `admin_access.php`
2. Find line: `define('ADMIN_ACCESS_TOKEN', 'TF_SECURE_2025_ADM1N_G8T3WAY_K3Y');`
3. Change the token to your own secure value
4. Save and distribute new token to authorized admins only

### Security Features:
- ✅ **Hidden Admin Interface**: Only accessible with security token
- ✅ **Failed Attempt Logging**: Invalid token and login attempts are logged
- ✅ **Session Management**: Token verification stored in session
- ✅ **Permanent Credentials**: No database dependency for admin access
- ✅ **Hardcoded Security**: Admin credentials stored as PHP constants

### Access URLs:
- **User Password Reset**: `password_reset.php` (for regular users who forgot password)
- **Secure Admin Portal**: `admin_access.php` (requires security token for admin access)
- **Old Admin Login**: `admin_login.php` (redirects to secure admin portal)
- **Admin Dashboard**: `admin_dashboard.php` (requires admin login)

### Default Credentials:
- **Admin Username**: `techforum_admin`
- **Admin Password**: `SecureAdmin@2025!`
- **Security Token**: `TF_SECURE_2025_ADM1N_G8T3WAY_K3Y`

## 🚨 Important Security Notes

1. **Change both the security token AND admin credentials immediately** in production
2. **Use HTTPS** for all admin access in production
3. **Regularly rotate** both the security token and admin password
4. **Monitor logs** for failed access attempts
5. **Limit IP access** if possible via server configuration
6. **Keep credentials secure** - they are now hardcoded in the PHP file

## 🔧 Simplified Admin Access

**No signup required!** The admin system now uses permanent credentials:

1. **Security Token**: Protects the admin login interface
2. **Fixed Credentials**: Permanent username and password (no database required)
3. **Direct Access**: Once authenticated, full admin dashboard access

---
**Created**: August 10, 2025
**System**: TechForum Admin Security v2.0
**Status**: Active & Secure 🔒
