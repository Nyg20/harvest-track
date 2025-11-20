# HarvestTrack - Roles and Features Documentation

## Overview
HarvestTrack implements a comprehensive role-based access control (RBAC) system with three distinct user roles: **Admin**, **Officer**, and **Farmer**. Each role has specific permissions and access to different features.

---

## User Roles

### 1. Admin Role
**Full system access with administrative privileges**

#### Capabilities:
- ✅ Full access to all system features
- ✅ User management (create, update, delete users)
- ✅ View and manage all harvest records
- ✅ Generate comprehensive reports (harvest summary, farmer performance, seasonal analysis)
- ✅ Review and update feedback status
- ✅ Access all farmers' data
- ✅ Delete any harvest records
- ✅ Configure system settings

#### Access to:
- Dashboard (all data)
- Harvest Data (all records)
- Reports (generate and view all)
- Feedback (view all, update status)
- Users Management (admin only)
- Settings

---

### 2. Officer Role (Agricultural Officer)
**Monitoring and reporting capabilities**

#### Capabilities:
- ✅ View all harvest records (read-only)
- ✅ Generate reports and analytics
- ✅ Monitor farmer performance
- ✅ Access seasonal analysis data
- ✅ Submit feedback and suggestions
- ❌ Cannot delete harvest records
- ❌ Cannot manage users
- ❌ Cannot update feedback status

#### Access to:
- Dashboard (all data)
- Harvest Data (view only)
- Reports (generate and view all)
- Feedback (submit only)
- Settings

---

### 3. Farmer Role
**Personal harvest management**

#### Capabilities:
- ✅ Manage own harvest records (create, update, delete)
- ✅ View own harvest history
- ✅ Track personal performance
- ✅ Submit feedback
- ✅ Receive notifications
- ❌ Cannot view other farmers' data
- ❌ Cannot generate reports
- ❌ Cannot manage users
- ❌ Cannot view all harvest records

#### Access to:
- Dashboard (own data only)
- Harvest Data (own records only)
- Reports (view own reports)
- Feedback (submit and view own)
- Settings

---

## Core Features Implementation

### 1. Reports Functionality

#### Report Types:
1. **Harvest Summary**
   - Total quantity by crop type
   - Harvest count per crop
   - Date range filtering
   - User-specific or system-wide

2. **Farmer Performance**
   - Performance metrics by farmer
   - Location-based analysis
   - Harvest count and total quantity
   - Comparative analysis

3. **Seasonal Analysis**
   - Crop performance by season
   - Seasonal trends
   - Crop type distribution

#### Access Control:
- **Admin & Officer**: Can generate all report types
- **Farmer**: Can view reports related to their own data
- Reports are stored in database for historical reference

#### API Endpoints:
- `GET /backend/api.php?action=get_reports` - Retrieve reports
- `POST /backend/api.php?action=generate_report` - Generate new report

---

### 2. Feedback Functionality

#### Features:
- Submit feedback with subject and message
- Track feedback status (pending, reviewed, resolved)
- View feedback history
- Admin can update feedback status

#### Access Control:
- **All Users**: Can submit feedback
- **Admin**: Can view all feedback and update status
- **Officer & Farmer**: Can view only their own feedback

#### Feedback Status:
- `pending` - Newly submitted
- `reviewed` - Admin has reviewed
- `resolved` - Issue/suggestion resolved

#### API Endpoints:
- `GET /backend/api.php?action=get_feedback` - Retrieve feedback
- `POST /backend/api.php?action=add_feedback` - Submit feedback
- `POST /backend/api.php?action=update_feedback_status` - Update status (admin only)

---

### 3. Settings Functionality

#### User Preferences:
1. **Notification Settings**
   - Enable/disable in-app notifications
   - Enable/disable email notifications

2. **Appearance**
   - Theme selection (light, dark, auto)
   - Language preference (English, Swahili, French)

3. **Profile Information**
   - View name, email, role
   - Role-specific privilege information

4. **Security**
   - Change password (placeholder)
   - Logout from all devices

#### API Endpoints:
- `GET /backend/api.php?action=get_settings` - Retrieve user settings
- `POST /backend/api.php?action=update_settings` - Update settings

---

## Database Schema

### Tables:

#### users
```sql
- id (INT, PRIMARY KEY)
- name (VARCHAR)
- email (VARCHAR, UNIQUE)
- password (VARCHAR, hashed)
- role (ENUM: 'admin', 'farmer', 'officer')
- phone (VARCHAR)
- location (VARCHAR)
- status (ENUM: 'active', 'inactive')
- created_at, updated_at (TIMESTAMP)
```

#### reports
```sql
- id (INT, PRIMARY KEY)
- harvest_id (INT, FOREIGN KEY)
- report_type (VARCHAR)
- metrics (JSON)
- generated_by (INT, FOREIGN KEY to users)
- generated_at (TIMESTAMP)
```

#### feedback
```sql
- id (INT, PRIMARY KEY)
- user_id (INT, FOREIGN KEY)
- subject (VARCHAR)
- message (TEXT)
- status (ENUM: 'pending', 'reviewed', 'resolved')
- created_at, updated_at (TIMESTAMP)
```

#### user_settings
```sql
- id (INT, PRIMARY KEY)
- user_id (INT, FOREIGN KEY, UNIQUE)
- notifications_enabled (BOOLEAN)
- email_notifications (BOOLEAN)
- theme (VARCHAR)
- language (VARCHAR)
- created_at, updated_at (TIMESTAMP)
```

---

## Role-Based Access Control Implementation

### Backend (PHP)

#### Session Management Functions:
```php
isLoggedIn()              // Check if user is authenticated
hasRole($role)            // Check specific role
hasAnyRole($roles)        // Check multiple roles
canAccess($resource)      // Check resource access
requireLogin()            // Enforce authentication
requireRole($role)        // Enforce specific role
getCurrentUser()          // Get current user info
```

#### Access Control in API:
- All API endpoints require authentication
- Role checks before sensitive operations
- User-specific data filtering based on role
- Proper error messages for unauthorized access

### Frontend (PHP/JavaScript)

#### Navigation:
- Dynamic menu based on user role
- Admin-only sections hidden from other roles
- Role badge displayed in sidebar

#### Pages:
- `dashboard.php` - Role-based data display
- `harvest-data.php` - CRUD with role restrictions
- `reports.php` - Generate reports (admin/officer only)
- `feedback.php` - Submit and manage feedback
- `settings.php` - User preferences
- `users.php` - User management (admin only)

---

## Default Credentials

### Admin Account:
- Email: `admin@harvesttrack.com`
- Password: `admin123`
- Role: Admin

### Test Accounts:
- Farmer: `john@farm.com` / `admin123`
- Officer: `jane@agri.gov` / `admin123`

---

## Security Features

1. **Password Hashing**: Using PHP `password_hash()` with bcrypt
2. **Session Management**: Secure session configuration
3. **SQL Injection Prevention**: Prepared statements with PDO
4. **XSS Protection**: HTML escaping with `htmlspecialchars()`
5. **CSRF Protection**: (To be implemented)
6. **Role Validation**: Server-side role checks on all operations

---

## API Reference

### Authentication
- `POST /backend/auth.php?action=login` - User login
- `POST /backend/auth.php?action=register` - User registration
- `GET /backend/auth.php?action=logout` - User logout

### Harvest Management
- `GET /backend/api.php?action=get_harvests` - Get harvest records
- `POST /backend/api.php?action=add_harvest` - Add harvest record
- `POST /backend/api.php?action=update_harvest` - Update harvest record
- `POST /backend/api.php?action=delete_harvest` - Delete harvest record

### Reports
- `GET /backend/api.php?action=get_reports` - Get reports list
- `POST /backend/api.php?action=generate_report` - Generate new report

### Feedback
- `GET /backend/api.php?action=get_feedback` - Get feedback list
- `POST /backend/api.php?action=add_feedback` - Submit feedback
- `POST /backend/api.php?action=update_feedback_status` - Update status (admin)

### User Management (Admin Only)
- `GET /backend/api.php?action=get_users` - Get all users
- `POST /backend/api.php?action=add_user` - Create user
- `POST /backend/api.php?action=update_user` - Update user
- `POST /backend/api.php?action=delete_user` - Delete user

### Settings
- `GET /backend/api.php?action=get_settings` - Get user settings
- `POST /backend/api.php?action=update_settings` - Update settings

### Dashboard
- `GET /backend/api.php?action=get_dashboard_data` - Get dashboard metrics

### Notifications
- `GET /backend/api.php?action=get_notifications` - Get notifications
- `POST /backend/api.php?action=mark_notification_read` - Mark as read

---

## File Structure

```
shamba/
├── backend/
│   ├── api.php                 # Main API handler
│   ├── auth.php                # Authentication handler
│   └── config/
│       ├── database.php        # Database connection
│       └── session.php         # Session & role management
├── database/
│   └── schema.sql              # Database schema
├── assets/
│   └── css/
│       └── style.css           # Styles
├── dashboard.php               # Main dashboard
├── harvest-data.php            # Harvest management
├── reports.php                 # Reports page (NEW)
├── feedback.php                # Feedback page (NEW)
├── settings.php                # Settings page (NEW)
├── users.php                   # User management (admin)
├── index.php                   # Login page
├── register.php                # Registration page
└── ROLES_AND_FEATURES.md       # This documentation
```

---

## Installation & Setup

1. **Database Setup**:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. **Configure Database**:
   - Edit `backend/config/database.php`
   - Set database credentials

3. **Set Permissions**:
   ```bash
   chmod 755 backend/
   chmod 644 backend/*.php
   ```

4. **Access Application**:
   - Navigate to your web server URL
   - Login with default admin credentials
   - Create additional users as needed

---

## Future Enhancements

- [ ] Password reset functionality
- [ ] Email notification system
- [ ] Advanced analytics dashboard
- [ ] Export reports to PDF/Excel
- [ ] Multi-language support
- [ ] Mobile responsive improvements
- [ ] Real-time notifications
- [ ] Audit logging
- [ ] Two-factor authentication
- [ ] API rate limiting

---

## Support

For issues or questions, submit feedback through the application or contact the system administrator.

**Version**: 1.0  
**Last Updated**: November 2025
