# HarvestTrack - Web-Based Harvest Tracking System

## Overview
HarvestTrack is a comprehensive web-based system designed to help farmers, administrators, and agricultural officers record, monitor, analyze, and report on harvest data. The system supports real-time data entry, trend analysis, and data-driven agricultural planning.

## Features
- **User Management**: Role-based access control for Farmers, Administrators, and Agricultural Officers
- **Harvest Data Entry**: Comprehensive forms for recording crop harvests with validation
- **Reporting & Analytics**: Visual charts and reports using Chart.js for trend analysis
- **Notification System**: Real-time alerts and system notifications
- **Responsive Design**: Mobile-friendly interface that works on all devices

## Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript, Chart.js
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Development Environment**: XAMPP (Apache, PHP, MySQL)

## Installation Instructions

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- Web browser (Chrome, Firefox, Safari, Edge)

### Step 1: Download and Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP following the installation wizard
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup the Project
1. Copy the HarvestTrack project folder to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\harvesttrack\  (Windows)
   /opt/lampp/htdocs/harvesttrack/  (Linux)
   /Applications/XAMPP/htdocs/harvesttrack/  (macOS)
   ```

### Step 3: Create the Database
1. Open your web browser and go to `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose the file `database/schema.sql` from the project folder
4. Click "Go" to import the database structure and sample data

### Step 4: Configure Database Connection
1. Open `backend/config/database.php`
2. Update the database credentials if needed:
   ```php
   private $host = 'localhost';
   private $db_name = 'harvesttrack';
   private $username = 'root';
   private $password = '';  // Default XAMPP MySQL password is empty
   ```

### Step 5: Access the Application
1. Open your web browser
2. Navigate to `http://localhost/harvesttrack`
3. Use the demo credentials to login:
   - **Admin**: admin@harvesttrack.com / admin123
   - **Farmer**: john@farm.com / admin123
   - **Officer**: jane@agri.gov / admin123

## Project Structure
```
harvesttrack/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       ├── auth.js            # Authentication handling
│       ├── dashboard.js       # Dashboard functionality
│       └── harvest-data.js    # Harvest data management
├── backend/
│   ├── config/
│   │   ├── database.php       # Database connection
│   │   └── session.php        # Session management
│   ├── auth.php               # Authentication API
│   └── api.php                # Main API endpoints
├── database/
│   └── schema.sql             # Database structure and sample data
├── index.php                  # Login page
├── register.php               # Registration page
├── dashboard.php              # Main dashboard
├── harvest-data.php           # Harvest data management
└── README.md                  # This file
```

## User Roles and Permissions

### Farmer
- Record and edit own harvest data
- View personal harvest history and analytics
- Submit feedback and suggestions

### Agricultural Officer
- View all harvest data and analytics
- Access system-wide reports and trends
- Monitor regional harvest performance

### Administrator
- Full system access and user management
- System configuration and maintenance
- Access to all reports and analytics

## Database Schema

### Main Tables
- **users**: User accounts and roles
- **harvests**: Harvest records with crop details
- **reports**: Generated reports and analytics
- **feedback**: User feedback and suggestions
- **notifications**: System notifications and alerts
- **storage_capacity**: Storage facility information

## Security Features
- Password hashing using PHP's `password_hash()`
- Session-based authentication
- SQL injection prevention using prepared statements
- Input validation and sanitization
- Role-based access control

## API Endpoints

### Authentication
- `POST /backend/auth.php?action=login` - User login
- `POST /backend/auth.php?action=register` - User registration
- `GET /backend/auth.php?action=logout` - User logout

### Data Management
- `GET /backend/api.php?action=get_dashboard_data` - Dashboard statistics
- `GET /backend/api.php?action=get_harvests` - Harvest records
- `POST /backend/api.php?action=add_harvest` - Add new harvest
- `POST /backend/api.php?action=update_harvest` - Update harvest
- `POST /backend/api.php?action=delete_harvest` - Delete harvest

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL service is running in XAMPP
   - Check database credentials in `backend/config/database.php`
   - Verify database exists and is properly imported

2. **Login Issues**
   - Clear browser cache and cookies
   - Check if sessions are working (ensure `session.save_path` is writable)
   - Verify user exists in database

3. **Charts Not Loading**
   - Check browser console for JavaScript errors
   - Ensure Chart.js CDN is accessible
   - Verify API endpoints are returning data

4. **Permission Denied Errors**
   - Check file permissions (should be readable by web server)
   - Ensure XAMPP has proper permissions to access project folder

## Development Guidelines

### Adding New Features
1. Follow the MVC pattern established in the codebase
2. Use prepared statements for all database queries
3. Implement proper error handling and validation
4. Maintain responsive design principles
5. Add appropriate user role checks

### Code Style
- Use meaningful variable and function names
- Comment complex logic and database queries
- Follow PHP PSR standards where applicable
- Keep CSS organized and use consistent naming

## Support and Maintenance

### Regular Maintenance Tasks
- Backup database regularly
- Monitor system logs for errors
- Update dependencies and security patches
- Review and clean up old data periodically

### Performance Optimization
- Implement database indexing for large datasets
- Use caching for frequently accessed data
- Optimize images and assets
- Consider pagination for large data tables

## License
This project is developed for educational and agricultural development purposes. Please ensure compliance with local data protection and privacy regulations when deploying in production environments.

## Contributing
To contribute to this project:
1. Fork the repository
2. Create a feature branch
3. Make your changes with proper testing
4. Submit a pull request with detailed description

For questions or support, please contact the development team.
