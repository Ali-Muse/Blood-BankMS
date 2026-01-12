# Blood Banking Management System - README

## 📋 Project Overview

A comprehensive Blood Banking Management System with role-based access control for managing blood donations, testing, inventory, and distribution. Built for university final year project.

## 🎯 Features

### Public Pages
- **Landing Page** - Mission, vision, statistics, and call-to-action
- **About Us** - Organization history, objectives, and partners
- **Donation Info** - Eligibility criteria, process, and benefits
- **Donor Registration** - Public donor sign-up with auto-eligibility check
- **Login** - Role-based authentication and dashboard redirection
- **Contact** - Contact form and organization information

### Role-Based Dashboards (7 Roles)
1. **System Administrator** - Full system control
2. **Registration Officer** - Donor management with eligibility auto-check
3. **Laboratory Technologist** - Blood testing with quality gate control
4. **Inventory Manager** - Stock management with FIFO dispatch & auto-expiry alerts
5. **Hospital User** - Blood requests with emergency priority
6. **Partner Organizations** - Campaign management with limited data access
7. **Authority/Supervisory** - National oversight with read-only access

## 🚀 Installation

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8.0)
- Web browser (Chrome, Firefox, or Edge)

### Setup Steps

1. **Install XAMPP**
   - Download from https://www.apachefriends.org/
   - Install and start Apache and MySQL services

2. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create database: `blood banking management system`
   - Import the provided SQL schema (from your database dump)

3. **Deploy Files**
   - Copy the `Blood BankMS` folder to `c:\xampp\htdocs\`
   - Ensure all files are in place

4. **Configure Database**
   - Open `includes/config.php`
   - Verify database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'blood banking management system');
     ```

5. **Access the System**
   - Open browser and navigate to: `http://localhost/Blood%20BankMS/`

## 🔐 Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Red Cross (Partner) | redcross@example.com | password123 |
| Minister of Health (Authority) | minister@example.com | password123 |

**Note:** Create additional users through the System Administrator dashboard after logging in.

## 📁 Project Structure

```
Blood BankMS/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       └── sidebar.js          # Sidebar interactions
├── dashboards/
│   ├── admin/                  # System Administrator dashboard
│   ├── registration/           # Registration Officer dashboard
│   ├── lab/                    # Laboratory Technologist dashboard
│   ├── inventory/              # Inventory Manager dashboard
│   ├── hospital/               # Hospital User dashboard
│   ├── partner/                # Partner Organizations dashboard
│   └── authority/              # Authority/Supervisory dashboard
├── includes/
│   ├── config.php              # Database configuration
│   ├── auth.php                # Authentication functions
│   ├── sidebar.php             # Sidebar component
│   ├── get-notifications.php  # Notification API
│   └── logout.php              # Logout handler
├── index.php                   # Landing page
├── login.php                   # Login page
├── register-donor.php          # Public donor registration
├── donation-info.php           # Donation guidelines
├── about.php                   # About us page
└── contact.php                 # Contact page
```

## 🎨 Design Features

- **Clean & Modern UI** - Professional design suitable for academic presentation
- **Responsive Layout** - Works on desktop, tablet, and mobile
- **Role-Based Sidebars** - Dynamic menu rendering based on user role
- **Emoji Icons** - Clear, universally recognizable icons
- **Color-Coded Stats** - Visual indicators for different metrics
- **Gradient Backgrounds** - Modern aesthetic with blood banking theme

## 🔧 Technologies Used

- **HTML5** - Structure and semantics
- **CSS3** - Styling and responsive design
- **JavaScript** - Client-side interactions
- **PHP 8.0** - Server-side logic
- **MySQL** - Database management
- **XAMPP** - Local development environment

## 📊 Advanced Features by Role

### System Administrator
- ✅ View all regions and users
- ✅ Complete audit trail access
- ✅ System-wide reports

### Registration Officer
- ✅ **Eligibility Auto-Check** - Automatic validation based on age, health, last donation

### Laboratory Technologist
- ✅ **Quality Gate Control** - Blood cannot proceed without lab approval
- ✅ Comprehensive safety testing (HIV, HBV, HCV, Syphilis)

### Inventory Manager
- ✅ **Auto-Expiry Alerts** - Alerts for units expiring within 7 days
- ✅ **FIFO Dispatch** - First In, First Out logic for optimal distribution

### Hospital User
- ✅ **Emergency Request Priority** - Emergency requests bypass normal queue

### Partner Organizations
- ✅ **Limited Data Access** - Access only to non-sensitive, aggregated data

### Authority/Supervisory
- ✅ **Read-Only Access** - Complete visibility without modification rights

## 📈 Database Schema

The system uses the following main tables:
- `users` - System users with role-based access
- `donors` - Registered blood donors
- `blood_units` - Blood collection units
- `lab_tests` - Laboratory test results
- `inventory` - Blood stock management
- `blood_requests` - Hospital blood requests
- `appointments` - Donor appointments
- `notifications` - User notifications
- `audit_logs` - System activity logs

## 🎓 University Project Notes

This system demonstrates:
- **Role-Based Access Control (RBAC)**
- **Database Design & Normalization**
- **PHP Session Management**
- **SQL Injection Prevention**
- **Responsive Web Design**
- **User Experience (UX) Design**
- **Healthcare System Workflow**

## 📝 Documentation

- **Role Access Matrix** - See `role-access-matrix.md` for detailed permissions
- **Implementation Plan** - See `implementation_plan.md` for technical details
- **Task Breakdown** - See `task.md` for development checklist

## 🐛 Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP
- Verify database name matches exactly (case-sensitive)
- Check database credentials in `includes/config.php`

### Page Not Found (404)
- Ensure files are in `c:\xampp\htdocs\Blood BankMS\`
- Check Apache is running in XAMPP
- Verify URL encoding for spaces: `Blood%20BankMS`

### Login Not Working
- Verify users exist in database
- Check password matches (plain text for demo)
- Clear browser cache and cookies

## 👨‍💻 Development

To extend the system:
1. Add new menu items in `includes/sidebar.php`
2. Create corresponding PHP pages in appropriate dashboard folder
3. Update role permissions in `includes/auth.php`
4. Add database tables as needed

## 📄 License

This is a university project for educational purposes.

## 👥 Credits

Developed as a Final Year Project for Blood Banking Management System demonstration.

---

**For questions or support, refer to the documentation files or contact your project supervisor.**
