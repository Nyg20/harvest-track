# Storage Capacity Percentage Fix - Dynamic Calculation

## Issue
The admin/officer dashboard was using a static `used_capacity` field instead of calculating storage utilization from actual harvest records in the database.

## Changes Made

### 1. Backend API (`backend/api.php`)
**Lines 130-145:** Changed to calculate storage utilization dynamically from harvest records

```php
// OLD: Used static used_capacity field
$query = "SELECT total_capacity, used_capacity FROM storage_capacity LIMIT 1";
$storage = $stmt->fetch();
$storageLeft = round(($storage['used_capacity'] / $storage['total_capacity']) * 100);

// NEW: Calculate from actual harvest records
// Get total capacity from storage_capacity table
$capacityQuery = "SELECT total_capacity FROM storage_capacity LIMIT 1";
$totalCapacity = $capacityData['total_capacity'] ?? 1000; // Default 1000 tons

// Calculate actual used capacity from harvest records
$usedQuery = "SELECT SUM(quantity) as total_used FROM harvests";
$usedCapacity = $usedStmt->fetch()['total_used'] ?? 0;

// Calculate utilization percentage
$storageLeft = $totalCapacity > 0 ? round(($usedCapacity / $totalCapacity) * 100) : 0;
```

### 2. Dashboard Display (`dashboard.php`)
**Line 111:** Updated label to reflect used capacity
```php
// OLD: 'Storage Capacity Left'
// NEW: 'Storage Capacity Used'
```

## How It Works

### For Farmers:
- Shows their **contribution percentage** = (their harvests / total all harvests) × 100
- Example: If farmer has 50 tons out of 250 total = 20%

### For Admin/Officer:
- Shows **storage utilization** = (sum of all harvests / total capacity) × 100
- Dynamically calculated from actual harvest records
- Example: If 250 tons harvested with 1000 tons capacity = 25% utilized

## Result
The percentage now reflects **real-time data** from harvest records:
- Automatically updates when new harvests are added
- No need to manually update `used_capacity` field
- Consistent with how farmer contribution is calculated

## Testing
1. Login as admin or officer
2. Check the dashboard card showing storage capacity
3. Add a new harvest record
4. Refresh dashboard - percentage should update automatically based on total harvests
