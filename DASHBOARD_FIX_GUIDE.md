# 🔧 Dashboard Fix Guide - Data Loading & Charts

## Issues Fixed

### **1. SQL Query Bug** ✅ FIXED
**Problem**: The crop distribution query was being prepared twice, causing SQL errors.

**Location**: `backend/api.php` - `getDashboardData()` function

**Fix Applied**:
```php
// BEFORE (BROKEN):
$query = "SELECT crop_type, SUM(quantity) as total FROM harvests";
// ... prepare and execute ...
$query .= " GROUP BY crop_type";  // ❌ Added AFTER execution!
$stmt = $db->prepare($query);     // ❌ Prepared again!

// AFTER (FIXED):
$query = "SELECT crop_type, SUM(quantity) as total FROM harvests";
if ($currentUser['role'] === 'farmer') {
    $query .= " WHERE user_id = ? GROUP BY crop_type";  // ✅ Added BEFORE execution
} else {
    $query .= " GROUP BY crop_type";
}
$stmt = $db->prepare($query);
$stmt->execute();
```

### **2. Missing GROUP BY in Trends Query** ✅ FIXED
**Problem**: Harvest trends query was missing GROUP BY clause.

**Fix Applied**:
```php
// Added GROUP BY MONTH(harvest_date) to both branches
$query .= " GROUP BY MONTH(harvest_date)";
```

---

## 🚀 Quick Fix Steps

### **Step 1: Clear Browser Cache**
```
Press: Ctrl + Shift + Delete
OR
Hard Refresh: Ctrl + F5
```

### **Step 2: Test the API**
Visit: `http://localhost/shamba/test_dashboard_api.php`

This will:
- ✅ Test database connection
- ✅ Check for harvest data
- ✅ Verify API response
- ✅ Test JavaScript fetch

### **Step 3: Add Sample Data (if needed)**
If you have no harvest records:
```
http://localhost/shamba/add_sample_data.php
```

This will add:
- 12 sample harvest records
- Storage capacity data
- System notifications

### **Step 4: View Dashboard**
```
http://localhost/shamba/dashboard.php
```

---

## 🔍 Troubleshooting

### **Problem: "Loading..." never changes**

**Cause**: JavaScript can't fetch data from API

**Solutions**:
1. Open browser console (F12) and check for errors
2. Verify you're logged in (session active)
3. Check API endpoint manually: `backend/api.php?action=get_dashboard_data`

### **Problem: Charts not displaying**

**Cause**: Chart.js not loaded or no data

**Check**:
1. Chart.js CDN is loaded (check network tab in F12)
2. Canvas elements exist in HTML
3. Data is being returned from API

**Fix**:
```html
<!-- Verify this line is in dashboard.php <head> -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

### **Problem: "Database error" message**

**Cause**: SQL query error or missing tables

**Solutions**:
1. Run database migration:
   ```bash
   mysql -u root -p harvesttrack < database/schema.sql
   ```

2. Check error logs:
   ```bash
   tail -f /var/log/apache2/error.log
   ```

3. Test database connection:
   ```bash
   mysql -u root -p harvesttrack -e "SHOW TABLES;"
   ```

### **Problem: Empty charts with no data**

**Cause**: No harvest records in database

**Solution**: Add sample data
```
http://localhost/shamba/add_sample_data.php
```

---

## 📊 API Response Structure

The dashboard expects this JSON structure:

```json
{
  "success": true,
  "data": {
    "totalHarvests": 1234.5,
    "activeFarmers": 45,
    "cropsInSeason": ["Wheat", "Maize", "Rice"],
    "storageLeft": 65,
    "trends": [
      {"month": 1, "total": 125.5},
      {"month": 2, "total": 89.3}
    ],
    "cropDistribution": [
      {"crop_type": "Wheat", "total": 403.1},
      {"crop_type": "Maize", "total": 293.4}
    ],
    "notifications": [
      {
        "id": 1,
        "type": "system",
        "title": "Welcome",
        "message": "System ready"
      }
    ]
  }
}
```

---

## 🧪 Manual Testing

### **Test 1: Check API Endpoint**
```bash
curl -b cookies.txt http://localhost/shamba/backend/api.php?action=get_dashboard_data
```

### **Test 2: Check Database**
```sql
-- Check harvest count
SELECT COUNT(*) FROM harvests;

-- Check crop distribution
SELECT crop_type, SUM(quantity) as total 
FROM harvests 
GROUP BY crop_type;

-- Check trends
SELECT MONTH(harvest_date) as month, SUM(quantity) as total 
FROM harvests 
WHERE harvest_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY MONTH(harvest_date);
```

### **Test 3: Check JavaScript Console**
1. Open dashboard.php
2. Press F12
3. Go to Console tab
4. Look for errors or API responses

---

## 🎯 Expected Behavior

### **On Page Load**:
1. JavaScript calls `loadDashboardData()`
2. Fetch request to `backend/api.php?action=get_dashboard_data`
3. API returns JSON with data
4. Cards update with numbers
5. Line chart renders with trends
6. Doughnut chart renders with crop distribution
7. Recent updates list populates

### **If No Data**:
- Charts show sample/placeholder data
- Cards show "0" or "Loading..."
- Recent updates show default messages

---

## 📝 Files Modified

| File | Change | Status |
|------|--------|--------|
| `backend/api.php` | Fixed crop distribution query | ✅ Fixed |
| `backend/api.php` | Added GROUP BY to trends query | ✅ Fixed |
| `test_dashboard_api.php` | Created diagnostic tool | ✅ New |
| `add_sample_data.php` | Created data seeding script | ✅ New |

---

## 🔧 Debug Mode

Add this to the top of `backend/api.php` for debugging:

```php
// Enable error reporting (REMOVE IN PRODUCTION!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all queries
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

---

## ✅ Verification Checklist

- [ ] Browser cache cleared
- [ ] Logged in as valid user
- [ ] Database has harvest records
- [ ] API returns success response
- [ ] Chart.js CDN loads successfully
- [ ] No JavaScript errors in console
- [ ] Cards display numbers
- [ ] Line chart renders
- [ ] Doughnut chart renders
- [ ] Recent updates display

---

## 🚨 Common Errors & Solutions

### **Error: "Call to undefined function getCurrentUser()"**
**Solution**: Include session.php
```php
require_once 'config/session.php';
```

### **Error: "Cannot read property 'getContext' of null"**
**Solution**: Canvas element missing or wrong ID
```html
<canvas id="trendsChart"></canvas>
<canvas id="cropChart"></canvas>
```

### **Error: "Uncaught SyntaxError: Unexpected token"**
**Solution**: API returning HTML instead of JSON (check for errors before JSON output)

### **Error: "Failed to fetch"**
**Solution**: CORS issue or wrong URL
- Check URL path is correct
- Ensure session cookies are sent

---

## 📞 Quick Commands

```bash
# Test API
curl http://localhost/shamba/backend/api.php?action=get_dashboard_data

# Check database
mysql -u root -p harvesttrack -e "SELECT COUNT(*) FROM harvests;"

# View error logs
tail -f /var/log/apache2/error.log

# Restart Apache
sudo systemctl restart apache2

# Clear PHP opcache (if enabled)
sudo systemctl restart php-fpm
```

---

## 🎉 Success Indicators

You'll know it's working when:

1. ✅ Dashboard loads without "Loading..." stuck
2. ✅ Numbers appear in all 4 cards
3. ✅ Line chart shows harvest trends
4. ✅ Doughnut chart shows crop distribution
5. ✅ Recent updates list shows notifications
6. ✅ No errors in browser console
7. ✅ API test page shows success

---

## 📚 Additional Resources

- **Test API**: `test_dashboard_api.php`
- **Add Data**: `add_sample_data.php`
- **System Test**: `test_pages.php`
- **Design Preview**: `design_preview.html`

---

**Status**: ✅ Fixed  
**Last Updated**: November 16, 2025  
**Next**: Clear cache and test!
