# Level 1 Data Flow Diagram - TechForum

## Overview
The Level 1 DFD breaks down the TechForum system into major processes, showing how data flows between processes and data stores.

## 🎯 Purpose
- Decompose the system into major functional processes
- Show data stores and their interactions
- Identify key data transformations

## 📊 Level 1 DFD

```mermaid
graph TB
    %% External Entities
    U[👤 Registered User]
    G[👥 Guest User]  
    A[🔐 Administrator]
    
    %% Processes
    P1[🔐 1.0<br/>Authentication<br/>System]
    P2[📝 2.0<br/>Content<br/>Management]
    P3[👀 3.0<br/>Content<br/>Viewing]
    P4[⚙️ 4.0<br/>User Profile<br/>Management]
    P5[🛡️ 5.0<br/>Admin<br/>Management]
    P6[📊 6.0<br/>View Tracking<br/>System]
    
    %% Data Stores
    DS1[(D1: Users)]
    DS2[(D2: Posts)]
    DS3[(D3: Replies)]
    DS4[(D4: Categories)]
    DS5[(D5: Admin Messages)]
    DS6[(D6: Password Requests)]
    DS7[(D7: Sessions)]
    
    %% User to Authentication
    U ---|"Login Request"| P1
    P1 ---|"Auth Status"| U
    U ---|"Registration Data"| P1
    P1 ---|"Account Created"| U
    
    %% Guest to Content Viewing
    G ---|"Browse Request"| P3
    P3 ---|"Public Content"| G
    
    %% Authentication to Data Stores
    P1 ---|"User Data"| DS1
    DS1 ---|"Credentials"| P1
    P1 ---|"Session Data"| DS7
    DS7 ---|"Session Info"| P1
    
    %% User to Content Management
    U ---|"New Post"| P2
    U ---|"Edit Request"| P2
    U ---|"Delete Request"| P2
    P2 ---|"Post Status"| U
    
    %% Content Management to Data Stores
    P2 ---|"Post Data"| DS2
    DS2 ---|"Existing Posts"| P2
    P2 ---|"Reply Data"| DS3
    DS3 ---|"Existing Replies"| P2
    P2 ---|"Category Info"| DS4
    DS4 ---|"Available Categories"| P2
    
    %% Content Viewing flows
    P3 ---|"Post Request"| DS2
    DS2 ---|"Post Content"| P3
    P3 ---|"Reply Request"| DS3
    DS3 ---|"Reply Content"| P3
    P3 ---|"View Data"| P6
    
    %% User Profile Management
    U ---|"Profile Update"| P4
    P4 ---|"Updated Profile"| U
    P4 ---|"User Changes"| DS1
    DS1 ---|"Current Profile"| P4
    
    %% Admin Management
    A ---|"Admin Login"| P5
    P5 ---|"Admin Dashboard"| A
    A ---|"User Commands"| P5
    A ---|"Content Moderation"| P5
    P5 ---|"System Reports"| A
    
    %% Admin to Data Stores
    P5 ---|"User Management"| DS1
    DS1 ---|"User Data"| P5
    P5 ---|"Content Moderation"| DS2
    DS2 ---|"All Posts"| P5
    P5 ---|"Admin Messages"| DS5
    DS5 ---|"Message History"| P5
    P5 ---|"Password Resets"| DS6
    DS6 ---|"Reset Requests"| P5
    
    %% View Tracking
    P6 ---|"View Count Updates"| DS2
    DS2 ---|"Current Views"| P6
    P6 ---|"Session Check"| DS7
    DS7 ---|"View History"| P6
    
    %% Inter-process flows
    P1 ---|"Auth Status"| P2
    P1 ---|"Auth Status"| P3
    P1 ---|"Auth Status"| P4
    P1 ---|"Admin Auth"| P5
    P3 ---|"View Event"| P6
    
    %% Styling
    classDef entity fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef process fill:#fff3e0,stroke:#f57c00,stroke-width:2px
    classDef datastore fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    
    class U,G,A entity
    class P1,P2,P3,P4,P5,P6 process
    class DS1,DS2,DS3,DS4,DS5,DS6,DS7 datastore
```

## 🔄 Process Descriptions

### 🔐 1.0 Authentication System
**Inputs:**
- Login credentials from users
- Registration data from new users
- Session validation requests

**Processes:**
- Validate login credentials
- Create new user accounts
- Manage user sessions
- Handle password resets

**Outputs:**
- Authentication status
- User session data
- Account creation confirmation

**Data Stores Used:**
- D1: Users (credential validation)
- D7: Sessions (session management)

### 📝 2.0 Content Management
**Inputs:**
- New post submissions
- Post edit requests
- Post deletion requests
- Reply submissions

**Processes:**
- Create new posts
- Update existing posts
- Soft delete posts/replies
- Validate ownership permissions
- Category assignment

**Outputs:**
- Post creation confirmation
- Edit/delete status
- Content validation results

**Data Stores Used:**
- D2: Posts (post CRUD operations)
- D3: Replies (reply management)
- D4: Categories (category assignment)

### 👀 3.0 Content Viewing
**Inputs:**
- Browse requests from users/guests
- Single post view requests
- Category filter requests

**Processes:**
- Retrieve post listings
- Display single post with replies
- Filter by category
- Format content for display
- Check visibility permissions

**Outputs:**
- Formatted post listings
- Individual post content
- Reply threads
- Public content for guests

**Data Stores Used:**
- D2: Posts (content retrieval)
- D3: Replies (reply retrieval)

### ⚙️ 4.0 User Profile Management
**Inputs:**
- Profile update requests
- Password change requests
- Account information requests

**Processes:**
- Update user profiles
- Change passwords
- Manage account settings
- Validate profile data

**Outputs:**
- Updated profile information
- Change confirmations
- Profile validation results

**Data Stores Used:**
- D1: Users (profile updates)

### 🛡️ 5.0 Admin Management
**Inputs:**
- Admin authentication requests
- User management commands
- Content moderation actions
- Password reset approvals

**Processes:**
- Two-layer admin authentication
- User account management
- Content moderation
- System administration
- Password reset processing

**Outputs:**
- Admin dashboard access
- System reports
- User management results
- Moderation confirmations

**Data Stores Used:**
- D1: Users (user management)
- D2: Posts (content moderation)
- D5: Admin Messages (admin communications)
- D6: Password Requests (reset processing)

### 📊 6.0 View Tracking System
**Inputs:**
- Post view events
- Session information
- View increment requests

**Processes:**
- Track post views
- Prevent view spam
- Update view counters
- Session-based throttling

**Outputs:**
- Updated view counts
- View tracking status

**Data Stores Used:**
- D2: Posts (view count updates)
- D7: Sessions (view throttling)

## 💾 Data Store Descriptions

### D1: Users
- **Contains:** User accounts, credentials, profile information
- **Structure:** id, username, email, password_hash, created_at
- **Access:** Authentication, Profile Management, Admin Management

### D2: Posts
- **Contains:** Forum posts with metadata
- **Structure:** id, user_id/author_id, title, body, category, views, is_deleted, timestamps
- **Access:** Content Management, Content Viewing, View Tracking, Admin Management

### D3: Replies
- **Contains:** Threaded replies to posts
- **Structure:** id, post_id, user_id, body, created_at, is_deleted
- **Access:** Content Management, Content Viewing

### D4: Categories
- **Contains:** Available post categories
- **Structure:** Predefined categories (Soft Skills, Technology, Academics, Sports, Lifestyle)
- **Access:** Content Management

### D5: Admin Messages
- **Contains:** Administrative communications
- **Structure:** id, user_id, message, created_at
- **Access:** Admin Management

### D6: Password Requests
- **Contains:** Password reset requests and status
- **Structure:** id, user_id, user_email, status, admin_response, timestamps
- **Access:** Admin Management

### D7: Sessions
- **Contains:** User session data and view tracking
- **Structure:** Session IDs, user associations, view history
- **Access:** Authentication, View Tracking

---
*This Level 1 DFD shows the major processes and data flows within the TechForum system.*