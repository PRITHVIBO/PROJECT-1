# Visual Diagrams - TechForum

## Overview
This document contains additional visual diagrams and flowcharts to complement the main documentation, providing alternative views and detailed process flows.

## 🎯 Purpose
- Provide comprehensive visual representations
- Support different learning styles
- Enhance understanding through multiple diagram types
- Serve as quick reference for developers

---

## 🔄 System Data Flow Overview

```mermaid
flowchart TB
    subgraph "External Users"
        GUEST[👥 Guest Users]
        USER[👤 Registered Users]
        ADMIN[🔐 Administrators]
    end
    
    subgraph "Web Interface"
        HOME[🏠 Homepage]
        AUTH[🔐 Auth Portal]
        POSTS[📝 Posts Interface]
        PROFILE[👤 Profile Page]
        ADMIN_PANEL[⚙️ Admin Panel]
    end
    
    subgraph "Core Systems"
        AUTH_SYS[🔒 Authentication System]
        CONTENT_SYS[📚 Content Management]
        USER_SYS[👥 User Management]
        ADMIN_SYS[👑 Admin System]
        VIEW_SYS[👁️ View Tracking]
    end
    
    subgraph "Data Storage"
        USER_DB[(👤 Users)]
        POST_DB[(📝 Posts)]
        REPLY_DB[(💬 Replies)]
        SESSION_DB[(🔑 Sessions)]
        ADMIN_DB[(⚙️ Admin Data)]
    end
    
    %% User flows
    GUEST --> HOME
    GUEST --> AUTH
    USER --> HOME
    USER --> POSTS
    USER --> PROFILE
    ADMIN --> ADMIN_PANEL
    
    %% Interface to system flows
    HOME --> VIEW_SYS
    HOME --> CONTENT_SYS
    AUTH --> AUTH_SYS
    POSTS --> CONTENT_SYS
    PROFILE --> USER_SYS
    ADMIN_PANEL --> ADMIN_SYS
    
    %% System to database flows
    AUTH_SYS --> USER_DB
    AUTH_SYS --> SESSION_DB
    CONTENT_SYS --> POST_DB
    CONTENT_SYS --> REPLY_DB
    USER_SYS --> USER_DB
    ADMIN_SYS --> USER_DB
    ADMIN_SYS --> POST_DB
    ADMIN_SYS --> ADMIN_DB
    VIEW_SYS --> POST_DB
    VIEW_SYS --> SESSION_DB
    
    %% Styling
    classDef user fill:#e3f2fd,stroke:#1976d2,stroke-width:2px
    classDef interface fill:#fff3e0,stroke:#f57c00,stroke-width:2px
    classDef system fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
    classDef database fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    
    class GUEST,USER,ADMIN user
    class HOME,AUTH,POSTS,PROFILE,ADMIN_PANEL interface
    class AUTH_SYS,CONTENT_SYS,USER_SYS,ADMIN_SYS,VIEW_SYS system
    class USER_DB,POST_DB,REPLY_DB,SESSION_DB,ADMIN_DB database
```

---

## 🔐 Authentication Flow Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant B as Browser
    participant S as Server
    participant DB as Database
    participant SESS as Session Store
    
    Note over U,SESS: User Registration Flow
    U->>B: Fill registration form
    B->>S: POST /auth/register.php
    S->>S: Validate input data
    S->>DB: Check username/email uniqueness
    DB-->>S: Return check results
    alt Data is valid and unique
        S->>S: Hash password
        S->>DB: INSERT new user
        DB-->>S: Confirm user created
        S->>SESS: Create user session
        S-->>B: Redirect to dashboard
        B-->>U: Show dashboard
    else Validation fails
        S-->>B: Return error message
        B-->>U: Show validation errors
    end
    
    Note over U,SESS: User Login Flow
    U->>B: Enter login credentials
    B->>S: POST /auth/login.php
    S->>DB: SELECT user by email
    DB-->>S: Return user data
    S->>S: Verify password hash
    alt Credentials valid
        S->>SESS: Create user session
        S-->>B: Redirect to dashboard
        B-->>U: Show dashboard
    else Invalid credentials
        S-->>B: Return error message
        B-->>U: Show login error
    end
    
    Note over U,SESS: Admin Authentication
    U->>B: Click Admin Access
    B->>S: GET /admin_access.php
    S-->>B: Show token form
    U->>B: Enter security token
    B->>S: POST token validation
    S->>S: Validate token
    alt Token valid
        S-->>B: Show credential form
        U->>B: Enter admin credentials
        B->>S: POST admin credentials
        S->>S: Validate admin credentials
        alt Admin credentials valid
            S->>SESS: Create admin session
            S-->>B: Redirect to admin dashboard
            B-->>U: Show admin interface
        else Invalid admin credentials
            S-->>B: Return credential error
            B-->>U: Show login error
        end
    else Token invalid
        S-->>B: Return access denied
        B-->>U: Show access denied
    end
```

---

## 📝 Content Management Flow

```mermaid
flowchart TD
    START([User wants to create content]) --> AUTH_CHECK{User authenticated?}
    
    AUTH_CHECK -->|No| LOGIN[Redirect to login]
    LOGIN --> AUTH_CHECK
    
    AUTH_CHECK -->|Yes| CONTENT_TYPE{Content type?}
    
    CONTENT_TYPE -->|New Post| NEW_POST[📝 Create Post Form]
    CONTENT_TYPE -->|Reply| REPLY_FORM[💬 Reply Form]
    CONTENT_TYPE -->|Edit| EDIT_CHECK{User owns content?}
    
    NEW_POST --> VALIDATE_POST[Validate post data]
    VALIDATE_POST --> POST_VALID{Valid?}
    POST_VALID -->|No| SHOW_POST_ERRORS[Show validation errors]
    SHOW_POST_ERRORS --> NEW_POST
    POST_VALID -->|Yes| SAVE_POST[Save post to database]
    SAVE_POST --> INCREMENT_USER_POSTS[Update user post count]
    INCREMENT_USER_POSTS --> REDIRECT_TO_POST[Redirect to new post]
    
    REPLY_FORM --> VALIDATE_REPLY[Validate reply data]
    VALIDATE_REPLY --> REPLY_VALID{Valid?}
    REPLY_VALID -->|No| SHOW_REPLY_ERRORS[Show validation errors]
    SHOW_REPLY_ERRORS --> REPLY_FORM
    REPLY_VALID -->|Yes| SAVE_REPLY[Save reply to database]
    SAVE_REPLY --> UPDATE_POST_STATS[Update post reply count]
    UPDATE_POST_STATS --> REFRESH_POST[Refresh post page]
    
    EDIT_CHECK -->|No| ACCESS_DENIED[Show access denied]
    EDIT_CHECK -->|Yes| EDIT_FORM[📝 Edit Form]
    EDIT_FORM --> VALIDATE_EDIT[Validate edited data]
    VALIDATE_EDIT --> EDIT_VALID{Valid?}
    EDIT_VALID -->|No| SHOW_EDIT_ERRORS[Show validation errors]
    SHOW_EDIT_ERRORS --> EDIT_FORM
    EDIT_VALID -->|Yes| UPDATE_CONTENT[Update content in database]
    UPDATE_CONTENT --> UPDATE_TIMESTAMP[Update modification timestamp]
    UPDATE_TIMESTAMP --> REDIRECT_TO_UPDATED[Redirect to updated content]
    
    REDIRECT_TO_POST --> END([End])
    REFRESH_POST --> END
    REDIRECT_TO_UPDATED --> END
    ACCESS_DENIED --> END
```

---

## 👁️ View Tracking System

```mermaid
graph TB
    subgraph "View Request Flow"
        USER_CLICK[👤 User clicks post link] --> LOAD_POST[📄 Load post page]
        LOAD_POST --> CHECK_SESSION[🔍 Check user session]
        CHECK_SESSION --> VIEW_LOGIC[👁️ View tracking logic]
    end
    
    subgraph "View Tracking Logic"
        VIEW_LOGIC --> SESSION_EXISTS{Session exists?}
        SESSION_EXISTS -->|No| CREATE_SESSION[🔑 Create new session]
        SESSION_EXISTS -->|Yes| CHECK_PREVIOUS[🔍 Check previous view]
        CREATE_SESSION --> CHECK_PREVIOUS
        
        CHECK_PREVIOUS --> VIEWED_BEFORE{Viewed before in session?}
        VIEWED_BEFORE -->|Yes| NO_INCREMENT[❌ Don't increment view]
        VIEWED_BEFORE -->|No| INCREMENT_VIEW[➕ Increment view counter]
        
        INCREMENT_VIEW --> RECORD_VIEW[📝 Record view in tracking table]
        RECORD_VIEW --> UPDATE_POST[📊 Update post view count]
    end
    
    subgraph "Database Operations"
        UPDATE_POST --> POST_TABLE[(📝 Posts Table)]
        RECORD_VIEW --> VIEW_TABLE[(👁️ View Tracking Table)]
        CHECK_PREVIOUS --> VIEW_TABLE
    end
    
    subgraph "Response Generation"
        NO_INCREMENT --> DISPLAY_POST[📺 Display post content]
        UPDATE_POST --> DISPLAY_POST
        DISPLAY_POST --> SHOW_METRICS[📊 Show view count and stats]
        SHOW_METRICS --> RENDER_REPLIES[💬 Render replies]
        RENDER_REPLIES --> COMPLETE[✅ Page complete]
    end
    
    classDef process fill:#fff3e0,stroke:#f57c00,stroke-width:2px
    classDef decision fill:#e3f2fd,stroke:#1976d2,stroke-width:2px
    classDef database fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    classDef result fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
    
    class USER_CLICK,LOAD_POST,CHECK_SESSION,VIEW_LOGIC,CREATE_SESSION,CHECK_PREVIOUS,INCREMENT_VIEW,RECORD_VIEW,UPDATE_POST process
    class SESSION_EXISTS,VIEWED_BEFORE decision
    class POST_TABLE,VIEW_TABLE database
    class NO_INCREMENT,DISPLAY_POST,SHOW_METRICS,RENDER_REPLIES,COMPLETE result
```

---

## 🛡️ Admin Security Architecture

```mermaid
graph TB
    subgraph "Admin Access Layers"
        ADMIN_REQ[🔐 Admin Access Request] --> LAYER1[🎫 Layer 1: Security Token]
        LAYER1 --> TOKEN_VALID{Token Valid?}
        TOKEN_VALID -->|No| ACCESS_DENIED[❌ Access Denied]
        TOKEN_VALID -->|Yes| LAYER2[🔑 Layer 2: Admin Credentials]
        
        LAYER2 --> CRED_VALID{Credentials Valid?}
        CRED_VALID -->|No| LOGIN_FAILED[❌ Login Failed]
        CRED_VALID -->|Yes| ADMIN_SESSION[👑 Create Admin Session]
    end
    
    subgraph "Admin Operations"
        ADMIN_SESSION --> ADMIN_DASHBOARD[⚙️ Admin Dashboard]
        ADMIN_DASHBOARD --> OPERATION_TYPE{Operation Type}
        
        OPERATION_TYPE --> USER_MGMT[👥 User Management]
        OPERATION_TYPE --> CONTENT_MOD[🛡️ Content Moderation]
        OPERATION_TYPE --> PASSWORD_RESET[🔄 Password Reset Approval]
        OPERATION_TYPE --> SYSTEM_MONITOR[📊 System Monitoring]
    end
    
    subgraph "Permission Validation"
        USER_MGMT --> CHECK_USER_PERM[🔍 Check User Management Permission]
        CONTENT_MOD --> CHECK_MOD_PERM[🔍 Check Moderation Permission]
        PASSWORD_RESET --> CHECK_RESET_PERM[🔍 Check Reset Permission]
        SYSTEM_MONITOR --> CHECK_MONITOR_PERM[🔍 Check Monitoring Permission]
        
        CHECK_USER_PERM --> EXECUTE_USER_OP[✅ Execute User Operation]
        CHECK_MOD_PERM --> EXECUTE_MOD_OP[✅ Execute Moderation]
        CHECK_RESET_PERM --> EXECUTE_RESET_OP[✅ Execute Reset Operation]
        CHECK_MONITOR_PERM --> EXECUTE_MONITOR_OP[✅ Execute Monitoring]
    end
    
    subgraph "Audit and Logging"
        EXECUTE_USER_OP --> LOG_USER_ACTION[📝 Log User Action]
        EXECUTE_MOD_OP --> LOG_MOD_ACTION[📝 Log Moderation Action]
        EXECUTE_RESET_OP --> LOG_RESET_ACTION[📝 Log Reset Action]
        EXECUTE_MONITOR_OP --> LOG_MONITOR_ACTION[📝 Log Monitoring Action]
        
        LOG_USER_ACTION --> AUDIT_TRAIL[(📋 Audit Trail)]
        LOG_MOD_ACTION --> AUDIT_TRAIL
        LOG_RESET_ACTION --> AUDIT_TRAIL
        LOG_MONITOR_ACTION --> AUDIT_TRAIL
    end
    
    ACCESS_DENIED --> SECURITY_LOG[⚠️ Log Security Incident]
    LOGIN_FAILED --> SECURITY_LOG
    SECURITY_LOG --> ALERT_SYSTEM[🚨 Security Alert System]
    
    classDef security fill:#ffebee,stroke:#c62828,stroke-width:2px
    classDef admin fill:#fff3e0,stroke:#ff8f00,stroke-width:2px
    classDef operation fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
    classDef audit fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    
    class ADMIN_REQ,LAYER1,LAYER2,TOKEN_VALID,CRED_VALID security
    class ADMIN_SESSION,ADMIN_DASHBOARD,OPERATION_TYPE admin
    class USER_MGMT,CONTENT_MOD,PASSWORD_RESET,SYSTEM_MONITOR,CHECK_USER_PERM,CHECK_MOD_PERM,CHECK_RESET_PERM,CHECK_MONITOR_PERM operation
    class LOG_USER_ACTION,LOG_MOD_ACTION,LOG_RESET_ACTION,LOG_MONITOR_ACTION,AUDIT_TRAIL audit
```

---

## 🔄 Database Schema Relationships

```mermaid
erDiagram
    USERS {
        int id PK
        varchar username UK
        varchar email UK
        varchar password_hash
        timestamp created_at
        timestamp updated_at
    }
    
    POSTS {
        int id PK
        int user_id FK
        varchar title
        text body
        varchar category
        int views
        boolean is_deleted
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    REPLIES {
        int id PK
        int post_id FK
        int user_id FK
        text body
        boolean is_deleted
        timestamp created_at
        timestamp deleted_at
    }
    
    SESSIONS {
        varchar session_id PK
        int user_id FK
        text session_data
        timestamp last_activity
        timestamp expires_at
    }
    
    VIEW_TRACKING {
        int id PK
        int post_id FK
        varchar session_id FK
        varchar ip_address
        timestamp viewed_at
    }
    
    ADMIN_MESSAGES {
        int id PK
        int user_id FK
        text message
        timestamp created_at
    }
    
    PASSWORD_REQUESTS {
        int id PK
        int user_id FK
        varchar user_email
        varchar status
        int admin_id FK
        text admin_response
        varchar new_password
        timestamp created_at
        timestamp response_at
    }
    
    %% Relationships
    USERS ||--o{ POSTS : creates
    USERS ||--o{ REPLIES : writes
    USERS ||--o{ SESSIONS : has
    USERS ||--o{ ADMIN_MESSAGES : receives
    USERS ||--o{ PASSWORD_REQUESTS : submits
    
    POSTS ||--o{ REPLIES : contains
    POSTS ||--o{ VIEW_TRACKING : tracks
    
    SESSIONS ||--o{ VIEW_TRACKING : generates
    
    %% Self-referencing for admin operations
    USERS ||--o{ PASSWORD_REQUESTS : processes_as_admin
```

---

## 🎨 User Interface Component Structure

```mermaid
graph TB
    subgraph "Layout Components"
        HEADER[📋 Header Component]
        MAIN[📄 Main Content Area]
        SIDEBAR[📊 Sidebar Component]
        FOOTER[📑 Footer Component]
    end
    
    subgraph "Header Elements"
        HEADER --> LOGO[🏠 Site Logo]
        HEADER --> NAV[🧭 Navigation Menu]
        HEADER --> USER_INFO[👤 User Info]
        HEADER --> AUTH_BUTTONS[🔐 Auth Buttons]
    end
    
    subgraph "Main Content Types"
        MAIN --> POST_LIST[📝 Post Listing]
        MAIN --> POST_DETAIL[📄 Post Detail]
        MAIN --> FORM_CONTAINER[📝 Form Container]
        MAIN --> DASHBOARD[⚙️ Dashboard]
        MAIN --> PROFILE[👤 Profile Page]
    end
    
    subgraph "Form Components"
        FORM_CONTAINER --> AUTH_FORM[🔐 Authentication Form]
        FORM_CONTAINER --> POST_FORM[📝 Post Creation Form]
        FORM_CONTAINER --> REPLY_FORM[💬 Reply Form]
        FORM_CONTAINER --> PROFILE_FORM[👤 Profile Edit Form]
    end
    
    subgraph "Interactive Elements"
        POST_LIST --> POST_CARD[📇 Post Card]
        POST_DETAIL --> REPLY_THREAD[💬 Reply Thread]
        POST_CARD --> VOTE_BUTTONS[👍 Vote Buttons]
        POST_CARD --> SHARE_BUTTONS[📤 Share Buttons]
        REPLY_THREAD --> REPLY_ITEM[💬 Reply Item]
    end
    
    subgraph "Sidebar Elements"
        SIDEBAR --> CATEGORIES[🏷️ Category Filter]
        SIDEBAR --> POPULAR_POSTS[🔥 Popular Posts]
        SIDEBAR --> USER_STATS[📊 User Statistics]
        SIDEBAR --> RECENT_ACTIVITY[⚡ Recent Activity]
    end
    
    classDef layout fill:#e3f2fd,stroke:#1976d2,stroke-width:2px
    classDef content fill:#fff3e0,stroke:#f57c00,stroke-width:2px
    classDef interactive fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
    classDef sidebar fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    
    class HEADER,MAIN,SIDEBAR,FOOTER layout
    class POST_LIST,POST_DETAIL,FORM_CONTAINER,DASHBOARD,PROFILE content
    class POST_CARD,REPLY_THREAD,VOTE_BUTTONS,SHARE_BUTTONS,REPLY_ITEM interactive
    class CATEGORIES,POPULAR_POSTS,USER_STATS,RECENT_ACTIVITY sidebar
```

---

## 📱 Mobile-First Responsive Design

```mermaid
graph LR
    subgraph "Breakpoint Strategy"
        MOBILE[📱 Mobile First<br/>320px+]
        TABLET[📲 Tablet<br/>768px+]
        DESKTOP[💻 Desktop<br/>1024px+]
        LARGE[🖥️ Large Screen<br/>1440px+]
    end
    
    subgraph "Layout Adaptations"
        MOBILE --> STACK[📚 Stacked Layout]
        TABLET --> SIDEBAR_TOGGLE[🔀 Collapsible Sidebar]
        DESKTOP --> GRID[⚏ Grid Layout]
        LARGE --> WIDE_GRID[⚏ Wide Grid with Margins]
    end
    
    subgraph "Navigation Patterns"
        STACK --> HAMBURGER[🍔 Hamburger Menu]
        SIDEBAR_TOGGLE --> TAB_NAV[📑 Tab Navigation]
        GRID --> HORIZONTAL_NAV[➡️ Horizontal Navigation]
        WIDE_GRID --> MEGA_MENU[📋 Mega Menu]
    end
    
    subgraph "Content Optimization"
        HAMBURGER --> TOUCH_TARGETS[👆 Touch-Friendly Targets]
        TAB_NAV --> SWIPE_GESTURES[👈 Swipe Gestures]
        HORIZONTAL_NAV --> HOVER_EFFECTS[🖱️ Hover Effects]
        MEGA_MENU --> KEYBOARD_NAV[⌨️ Keyboard Navigation]
    end
    
    classDef mobile fill:#e3f2fd,stroke:#1976d2,stroke-width:2px
    classDef tablet fill:#fff3e0,stroke:#f57c00,stroke-width:2px
    classDef desktop fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
    classDef large fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    
    class MOBILE,STACK,HAMBURGER,TOUCH_TARGETS mobile
    class TABLET,SIDEBAR_TOGGLE,TAB_NAV,SWIPE_GESTURES tablet
    class DESKTOP,GRID,HORIZONTAL_NAV,HOVER_EFFECTS desktop
    class LARGE,WIDE_GRID,MEGA_MENU,KEYBOARD_NAV large
```

---

## 🚀 Performance Optimization Strategy

```mermaid
graph TB
    subgraph "Frontend Optimization"
        CSS_MIN[📦 CSS Minification] --> SPRITE[🖼️ Image Sprites]
        JS_MIN[📦 JavaScript Minification] --> LAZY_LOAD[⏳ Lazy Loading]
        SPRITE --> WEBP[🖼️ WebP Images]
        LAZY_LOAD --> CRITICAL_CSS[⚡ Critical CSS]
    end
    
    subgraph "Backend Optimization"
        QUERY_OPT[🔍 Query Optimization] --> INDEX_OPT[📊 Index Optimization]
        INDEX_OPT --> CACHE_LAYER[💾 Caching Layer]
        CACHE_LAYER --> SESSION_OPT[🔑 Session Optimization]
    end
    
    subgraph "Database Performance"
        CONN_POOL[🔌 Connection Pooling] --> PREPARED_STMT[📝 Prepared Statements]
        PREPARED_STMT --> BATCH_OPS[📦 Batch Operations]
        BATCH_OPS --> PARTITION[🗂️ Table Partitioning]
    end
    
    subgraph "Caching Strategy"
        BROWSER_CACHE[🌐 Browser Caching] --> CDN_CACHE[☁️ CDN Caching]
        CDN_CACHE --> SERVER_CACHE[🖥️ Server-side Caching]
        SERVER_CACHE --> DB_CACHE[🗄️ Database Query Cache]
    end
    
    subgraph "Monitoring and Analytics"
        PERF_MONITOR[📊 Performance Monitoring] --> BOTTLENECK[🔍 Bottleneck Detection]
        BOTTLENECK --> AUTO_SCALE[📈 Auto-scaling]
        AUTO_SCALE --> ALERT_SYSTEM[🚨 Alert System]
    end
    
    CRITICAL_CSS --> CACHE_LAYER
    SESSION_OPT --> CONN_POOL
    PARTITION --> BROWSER_CACHE
    DB_CACHE --> PERF_MONITOR
    
    classDef frontend fill:#e3f2fd,stroke:#1976d2,stroke-width:2px
    classDef backend fill:#fff3e0,stroke:#f57c00,stroke-width:2px
    classDef database fill:#f3e5f5,stroke:#7b1fa2,stroke-width:2px
    classDef caching fill:#e8f5e8,stroke:#2e7d32,stroke-width:2px
    classDef monitoring fill:#ffebee,stroke:#c62828,stroke-width:2px
    
    class CSS_MIN,JS_MIN,SPRITE,LAZY_LOAD,WEBP,CRITICAL_CSS frontend
    class QUERY_OPT,INDEX_OPT,CACHE_LAYER,SESSION_OPT backend
    class CONN_POOL,PREPARED_STMT,BATCH_OPS,PARTITION database
    class BROWSER_CACHE,CDN_CACHE,SERVER_CACHE,DB_CACHE caching
    class PERF_MONITOR,BOTTLENECK,AUTO_SCALE,ALERT_SYSTEM monitoring
```

---

*These visual diagrams provide comprehensive views of the TechForum system from multiple perspectives, supporting both technical implementation and system understanding.*