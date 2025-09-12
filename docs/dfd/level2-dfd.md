# Level 2 Data Flow Diagrams - TechForum

## Overview
Level 2 DFDs provide detailed breakdowns of complex processes from the Level 1 DFD, showing the internal sub-processes and data flows.

## 🎯 Purpose
- Decompose complex processes into detailed sub-processes
- Show internal data transformations
- Provide implementation-level detail

---

## 📊 Level 2 DFD: Authentication System (Process 1.0)

```mermaid
graph TB
    %% External Entities
    U[👤 User]
    
    %% Sub-processes
    P1_1[🔍 1.1<br/>Validate<br/>Credentials]
    P1_2[👤 1.2<br/>Create New<br/>Account]
    P1_3[🔑 1.3<br/>Session<br/>Management]
    P1_4[🔄 1.4<br/>Password Reset<br/>Processing]
    
    %% Data Stores
    DS1[(D1: Users)]
    DS7[(D7: Sessions)]
    
    %% User interactions
    U ---|"Login Request"| P1_1
    P1_1 ---|"Auth Result"| U
    U ---|"Registration Data"| P1_2
    P1_2 ---|"Account Status"| U
    U ---|"Reset Request"| P1_4
    P1_4 ---|"Reset Status"| U
    
    %% Credential validation flows
    P1_1 ---|"Credential Check"| DS1
    DS1 ---|"User Data"| P1_1
    P1_1 ---|"Valid Login"| P1_3
    P1_3 ---|"Session Created"| P1_1
    
    %% Account creation flows
    P1_2 ---|"New User Data"| DS1
    DS1 ---|"Duplicate Check"| P1_2
    P1_2 ---|"Account Created"| P1_3
    
    %% Session management flows
    P1_3 ---|"Session Data"| DS7
    DS7 ---|"Existing Session"| P1_3
    
    %% Password reset flows
    P1_4 ---|"Reset Request"| DS1
    DS1 ---|"User Validation"| P1_4
    
    %% Styling
    classDef entity fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef subprocess fill:#fff8e1,stroke:#ff8f00,stroke-width:2px
    classDef datastore fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    
    class U entity
    class P1_1,P1_2,P1_3,P1_4 subprocess
    class DS1,DS7 datastore
```

### 🔍 1.1 Validate Credentials
- Receives login credentials (email, password)
- Validates against stored user data
- Implements password hashing verification
- Returns authentication success/failure

### 👤 1.2 Create New Account
- Processes registration data
- Validates username/email uniqueness
- Hashes passwords securely
- Creates new user record

### 🔑 1.3 Session Management
- Creates user sessions on successful login
- Manages session data and timeouts
- Handles session validation for requests
- Implements logout functionality

### 🔄 1.4 Password Reset Processing
- Receives password reset requests
- Validates user existence
- Creates reset tokens/requests
- Coordinates with admin approval system

---

## 📊 Level 2 DFD: Content Management (Process 2.0)

```mermaid
graph TB
    %% External Entities
    U[👤 User]
    
    %% Sub-processes
    P2_1[📝 2.1<br/>Create<br/>Post]
    P2_2[✏️ 2.2<br/>Edit<br/>Post]
    P2_3[🗑️ 2.3<br/>Delete<br/>Content]
    P2_4[💬 2.4<br/>Manage<br/>Replies]
    P2_5[🏷️ 2.5<br/>Category<br/>Assignment]
    P2_6[🔒 2.6<br/>Ownership<br/>Validation]
    
    %% Data Stores
    DS2[(D2: Posts)]
    DS3[(D3: Replies)]
    DS4[(D4: Categories)]
    
    %% User interactions
    U ---|"New Post Data"| P2_1
    P2_1 ---|"Post Created"| U
    U ---|"Edit Request"| P2_2
    P2_2 ---|"Post Updated"| U
    U ---|"Delete Request"| P2_3
    P2_3 ---|"Delete Status"| U
    U ---|"Reply Data"| P2_4
    P2_4 ---|"Reply Created"| U
    
    %% Create post flows
    P2_1 ---|"Category Check"| P2_5
    P2_5 ---|"Valid Category"| P2_1
    P2_5 ---|"Category Data"| DS4
    DS4 ---|"Available Categories"| P2_5
    P2_1 ---|"New Post"| DS2
    
    %% Edit post flows
    P2_2 ---|"Ownership Check"| P2_6
    P2_6 ---|"Permission Status"| P2_2
    P2_6 ---|"User Check"| DS2
    DS2 ---|"Post Owner"| P2_6
    P2_2 ---|"Post Updates"| DS2
    DS2 ---|"Current Post"| P2_2
    
    %% Delete content flows
    P2_3 ---|"Ownership Check"| P2_6
    P2_3 ---|"Soft Delete"| DS2
    P2_3 ---|"Reply Deletion"| DS3
    
    %% Reply management flows
    P2_4 ---|"Reply Data"| DS3
    P2_4 ---|"Post Validation"| DS2
    DS2 ---|"Post Exists"| P2_4
    
    %% Styling
    classDef entity fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef subprocess fill:#fff8e1,stroke:#ff8f00,stroke-width:2px
    classDef datastore fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    
    class U entity
    class P2_1,P2_2,P2_3,P2_4,P2_5,P2_6 subprocess
    class DS2,DS3,DS4 datastore
```

### 📝 2.1 Create Post
- Validates post data (title, body, category)
- Assigns post to user
- Implements content sanitization
- Creates new post record

### ✏️ 2.2 Edit Post
- Validates ownership permissions
- Updates existing post content
- Maintains edit history
- Preserves post metadata

### 🗑️ 2.3 Delete Content
- Implements soft delete functionality
- Validates user permissions
- Marks posts/replies as deleted
- Preserves data for admin review

### 💬 2.4 Manage Replies
- Creates threaded replies to posts
- Validates parent post existence
- Implements reply permissions
- Manages reply hierarchy

### 🏷️ 2.5 Category Assignment
- Validates category selection
- Assigns posts to categories
- Implements category restrictions
- Manages category metadata

### 🔒 2.6 Ownership Validation
- Verifies user ownership of content
- Implements admin override permissions
- Validates edit/delete permissions
- Supports dynamic schema (user_id/author_id)

---

## 📊 Level 2 DFD: Admin Management (Process 5.0)

```mermaid
graph TB
    %% External Entities
    A[🔐 Administrator]
    
    %% Sub-processes
    P5_1[🔑 5.1<br/>Admin<br/>Authentication]
    P5_2[👥 5.2<br/>User<br/>Management]
    P5_3[🛡️ 5.3<br/>Content<br/>Moderation]
    P5_4[🔄 5.4<br/>Password Reset<br/>Approval]
    P5_5[📊 5.5<br/>System<br/>Reports]
    P5_6[💬 5.6<br/>Admin<br/>Messaging]
    
    %% Data Stores
    DS1[(D1: Users)]
    DS2[(D2: Posts)]
    DS5[(D5: Admin Messages)]
    DS6[(D6: Password Requests)]
    
    %% Admin interactions
    A ---|"Token + Credentials"| P5_1
    P5_1 ---|"Admin Access"| A
    A ---|"User Commands"| P5_2
    P5_2 ---|"User Status"| A
    A ---|"Moderation Actions"| P5_3
    P5_3 ---|"Moderation Result"| A
    A ---|"Reset Approval"| P5_4
    P5_4 ---|"Reset Status"| A
    A ---|"Report Request"| P5_5
    P5_5 ---|"System Data"| A
    A ---|"Admin Message"| P5_6
    P5_6 ---|"Message Status"| A
    
    %% Authentication flows
    P5_1 ---|"Token Validation"| P5_1
    P5_1 ---|"Credential Check"| P5_1
    
    %% User management flows
    P5_2 ---|"User Data"| DS1
    DS1 ---|"User Records"| P5_2
    P5_2 ---|"User Updates"| DS1
    
    %% Content moderation flows
    P5_3 ---|"Content Check"| DS2
    DS2 ---|"Post Data"| P5_3
    P5_3 ---|"Moderation Actions"| DS2
    
    %% Password reset flows
    P5_4 ---|"Reset Requests"| DS6
    DS6 ---|"Pending Requests"| P5_4
    P5_4 ---|"Request Updates"| DS6
    P5_4 ---|"Password Updates"| DS1
    
    %% Reporting flows
    P5_5 ---|"Data Queries"| DS1
    P5_5 ---|"Content Queries"| DS2
    DS1 ---|"User Statistics"| P5_5
    DS2 ---|"Content Statistics"| P5_5
    
    %% Messaging flows
    P5_6 ---|"Message Data"| DS5
    DS5 ---|"Message History"| P5_6
    
    %% Styling
    classDef entity fill:#e1f5fe,stroke:#01579b,stroke-width:2px
    classDef subprocess fill:#fff8e1,stroke:#ff8f00,stroke-width:2px
    classDef datastore fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    
    class A entity
    class P5_1,P5_2,P5_3,P5_4,P5_5,P5_6 subprocess
    class DS1,DS2,DS5,DS6 datastore
```

### 🔑 5.1 Admin Authentication
- Implements two-layer security (token + credentials)
- Validates admin tokens
- Verifies admin credentials
- Creates admin sessions

### 👥 5.2 User Management
- Manages user accounts
- Implements user activation/deactivation
- Handles user data updates
- Provides user search and filtering

### 🛡️ 5.3 Content Moderation
- Reviews reported content
- Implements content deletion/restoration
- Manages content flags
- Provides moderation logs

### 🔄 5.4 Password Reset Approval
- Reviews password reset requests
- Approves/rejects reset requests
- Generates temporary passwords
- Notifies users of reset status

### 📊 5.5 System Reports
- Generates user statistics
- Creates content analytics
- Provides system health reports
- Implements data export functionality

### 💬 5.6 Admin Messaging
- Creates admin messages to users
- Manages communication history
- Implements broadcast messaging
- Tracks message delivery status

---

## 🔗 Process Interactions

### Data Flow Coordination
- Authentication status flows to all content processes
- Ownership validation occurs before content modifications
- View tracking integrates with content viewing
- Admin processes have override permissions

### Error Handling
- Invalid credentials trigger error responses
- Permission violations are logged and reported
- Data validation failures provide user feedback
- System errors are captured for admin review

### Security Integration
- All processes validate user sessions
- Admin processes require elevated permissions
- Input sanitization occurs at process boundaries
- Output encoding prevents XSS attacks

---
*These Level 2 DFDs provide detailed implementation guidance for the TechForum system processes.*