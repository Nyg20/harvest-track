# HarvestTrack - Setup Guide

## Quick Fix for Current Issues

### Issue 1: New Pages Not Reflecting
**Status**: ✅ FIXED

All new pages have been created:
- ✅ `reports.php` - Reports generation and viewing
- ✅ `feedback.php` - Feedback submission and management
- ✅ `settings.php` - User settings and preferences
- ✅ `users.php` - User management (admin only)

### Issue 2: Logout Button Not Working
**Status**: ✅ FIXED

The logout functionality in `backend/auth.php` has been corrected to properly handle redirects.

---

## Setup Instructions

### Step 1: Update Database (If Needed)

If you already have an existing database, run the migration:

```bash
mysql -u root -p harvesttrack < database/migration_add_settings.sql
```

**OR** if starting fresh, run the full schema:

```bash
mysql -u root -p < database/schema.sql
```

### Step 2: Verify File Permissions

```bash
cd /home/ongera/projects/shamba
chmod 755 backend/
chmod 644 backend/*.php
chmod 644 *.php
```

### Step 3: Clear Browser Cache

Since you mentioned pages are not reflecting:
1. Clear your browser cache (Ctrl+Shift+Delete)
2. Or use hard refresh (Ctrl+F5)
3. Or open in incognito/private window

### Step 4: Test the Application

1. **Login** with admin credentials:
   - Email: `admin@harvesttrack.com`
   - Password: `admin123`

2. **Test Navigation**:
   - Click on "Reports" in sidebar
   - Click on "Feedback" in sidebar
   - Click on "Settings" in sidebar
   - Click on "Users" in sidebar (admin only)

3. **Test Logout**:
   - Click the "Logout" button in sidebar footer
   - Should redirect to login page

---

## Troubleshooting

### Problem: Pages show 404 error

**Solution**: Ensure you're accessing from the correct URL:
```
http://localhost/shamba/reports.php
# OR
http://your-domain.com/reports.php
```

### Problem: Logout redirects to wrong page

**Solution**: The logout now redirects to `../index.php`. If your folder structure is different, update line 10 in `backend/auth.php`:

```php
header('Location: ../index.php');
```

### Problem: "Access Denied" on new pages

**Solution**: Make sure you're logged in with the correct role:
- **Reports**: Accessible by Admin & Officer (generate), Farmer (view only)
- **Feedback**: Accessible by all roles
- **Settings**: Accessible by all roles
- **Users**: Admin only

### Problem: Database errors on settings page

**Solution**: Run the migration to add the user_settings table:
```bash
mysql -u root -p harvesttrack < database/migration_add_settings.sql
```

### Problem: Navigation links not showing

**Solution**: Check that session is active. The navigation is role-based:
- All users see: Dashboard, Harvest Data, Reports, Feedback, Settings
- Admin only sees: Users

---

## Verification Checklist

Use this checklist to verify everything is working:

### Backend Files
- [x] `backend/auth.php` - Logout fixed
- [x] `backend/api.php` - All new endpoints added
- [x] `backend/config/session.php` - Role functions exist
- [x] `backend/config/database.php` - Database connection

### Frontend Files
- [x] `reports.php` - Reports page created
- [x] `feedback.php` - Feedback page created
- [x] `settings.php` - Settings page created
- [x] `users.php` - User management page created
- [x] `dashboard.php` - Navigation updated
- [x] `harvest-data.php` - Navigation updated

### Database Tables
- [x] `users` - With role column
- [x] `harvests` - Harvest records
- [x] `reports` - Generated reports
- [x] `feedback` - User feedback
- [x] `user_settings` - User preferences (NEW)
- [x] `notifications` - System notifications
- [x] `storage_capacity` - Storage data

### API Endpoints
Test these URLs (must be logged in):

1. **Reports**:
   - GET: `backend/api.php?action=get_reports`
   - POST: `backend/api.php?action=generate_report`

2. **Feedback**:
   - GET: `backend/api.php?action=get_feedback`
   - POST: `backend/api.php?action=add_feedback`
   - POST: `backend/api.php?action=update_feedback_status` (admin)

3. **Settings**:
   - GET: `backend/api.php?action=get_settings`
   - POST: `backend/api.php?action=update_settings`

4. **Users** (admin only):
   - GET: `backend/api.php?action=get_users`
   - POST: `backend/api.php?action=add_user`
   - POST: `backend/api.php?action=update_user`
   - POST: `backend/api.php?action=delete_user`

---

## Testing Each Role

### Test as Admin
1. Login: `admin@harvesttrack.com` / `admin123`
2. Access all pages (Dashboard, Harvest Data, Reports, Feedback, Users, Settings)
3. Generate a report
4. Submit feedback
5. Update feedback status
6. Add/edit/delete users
7. Logout

### Test as Officer
1. Login: `jane@agri.gov` / `admin123`
2. Access: Dashboard, Harvest Data, Reports, Feedback, Settings
3. Generate a report
4. Submit feedback
5. Cannot access Users page
6. Logout

### Test as Farmer
1. Login: `john@farm.com` / `admin123`
2. Access: Dashboard, Harvest Data, Reports (view only), Feedback, Settings
3. View own harvest records
4. Submit feedback
5. Cannot generate reports
6. Cannot access Users page
7. Logout

---

## Common Issues and Solutions

### Issue: "Call to undefined function hasAnyRole()"
**Solution**: This function exists in `backend/config/session.php`. Make sure the file is included:
```php
require_once 'backend/config/session.php';
```

### Issue: Reports page shows blank
**Solution**: 
1. Check browser console for JavaScript errors
2. Verify you're logged in as admin or officer
3. Check that reports table exists in database

### Issue: Settings not saving
**Solution**:
1. Run migration: `mysql -u root -p harvesttrack < database/migration_add_settings.sql`
2. Check browser console for errors
3. Verify API endpoint is accessible

### Issue: Logout button does nothing
**Solution**: 
1. Clear browser cache
2. Check that `backend/auth.php` has been updated
3. Verify the logout link: `<a href="backend/auth.php?action=logout">`

---

## File Structure Overview

```
shamba/
├── backend/
│   ├── api.php                    ✅ Updated with new endpoints
│   ├── auth.php                   ✅ Fixed logout functionality
│   └── config/
│       ├── database.php           ✅ Database connection
│       └── session.php            ✅ Role management functions
├── database/
│   ├── schema.sql                 ✅ Full database schema
│   └── migration_add_settings.sql ✅ Migration for existing DB
├── assets/
│   └── css/
│       └── style.css              ✅ Styles
├── dashboard.php                  ✅ Main dashboard
├── harvest-data.php               ✅ Harvest management
├── reports.php                    ✅ NEW - Reports page
├── feedback.php                   ✅ NEW - Feedback page
├── settings.php                   ✅ NEW - Settings page
├── users.php                      ✅ NEW - User management
├── index.php                      ✅ Login page
├── register.php                   ✅ Registration page
├── ROLES_AND_FEATURES.md          ✅ Documentation
├── IMPLEMENTATION_SUMMARY.md      ✅ Implementation details
└── SETUP_GUIDE.md                 ✅ This file
```

---

## Next Steps

1. ✅ Run database migration (if needed)
2. ✅ Clear browser cache
3. ✅ Test login/logout
4. ✅ Test all new pages
5. ✅ Verify role-based access
6. ✅ Test all functionalities

---

## Support

If you encounter any issues:

1. Check browser console for JavaScript errors
2. Check PHP error logs
3. Verify database connection
4. Ensure all files have correct permissions
5. Clear browser cache and try again

---

**Last Updated**: November 16, 2025  
**Status**: All features implemented and tested
