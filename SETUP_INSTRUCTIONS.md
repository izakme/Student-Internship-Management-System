# Student Internship Management System - Setup Instructions

## Prerequisites

- Apache Web Server (XAMPP/LAMPP)
- PHP 7.4+
- MySQL 5.7+
- PDO PHP Extension

## Installation Steps

### 1. Create Database

Execute the SQL schema to create the database and tables:

```bash
mysql -u root -p < /opt/lampp/htdocs/S-I-M-S/database/schema.sql
```

Or manually:

- Open phpMyAdmin (http://localhost/phpmyadmin)
- Create new database: `internship_db`
- Import the `database/schema.sql` file

### 2. Configure Database Connection

The system uses PDO for database connections. Verify the settings in:

- **File**: `backend/config/database.php`
- **Default Settings**:
  - Host: `localhost`
  - Database: `internship_db`
  - Username: `root`
  - Password: `` (empty)

Update these if your MySQL setup is different.

### 3. Access the System

- **Landing Page**: `http://localhost/S-I-M-S/frontend/index.php`
- **Login**: `http://localhost/S-I-M-S/frontend/authentication/login.php`
- **Register**: `http://localhost/S-I-M-S/frontend/authentication/register.php`

### 4. Test User Accounts

Create test accounts through the registration page with these roles:

- `student`
- `company`
- `admin`

## System Architecture

### Backend Classes (`backend/classes/`)

- **User.php** - User management & authentication
- **Auth.php** - Login/authentication logic
- **Student.php** - Student profile management
- **Company.php** - Company profile management
- **Internship.php** - Internship CRUD operations
- **Application.php** - Application management
- **Report.php** - Report generation

### Frontend Modules

#### Public Pages

- `frontend/index.php` - Landing page
- `frontend/authentication/login.php` - Login
- `frontend/authentication/register.php` - Registration

#### Student Pages (`frontend/student/`)

- `dashboard.php` - Student dashboard with statistics
- `internships.php` - View available internships
- `search.php` - Search & apply for internships
- `applications.php` - Track applications
- `profile.php` - Manage student profile

#### Company Pages (`frontend/company/`)

- `dashboard.php` - Company dashboard
- `internships.php` - Post & manage internships
- `applications.php` - Review student applications
- `profile.php` - Manage company profile

#### Admin Pages (`frontend/admin/`)

- `dashboard.php` - Admin dashboard with system statistics
- `users.php` - Manage all users
- `students.php` - View registered students
- `companies.php` - Manage companies
- `internships.php` - Manage all internships
- `applications.php` - Review and update application status
- `reports.php` - Generate system reports

## Features

### Student Features

✓ Register and create account
✓ Update profile (registration no, course, year, phone)
✓ View all available internships
✓ Search internships by title, company, or description
✓ Apply for internships (prevents duplicate applications)
✓ Track application status
✓ Dashboard with statistics

### Company Features

✓ Register and create account
✓ Manage company profile
✓ Post internship opportunities
✓ Delete internship listings
✓ Review student applications
✓ Update application status (Pending/Accepted/Rejected)
✓ Dashboard with statistics

### Admin Features

✓ Manage all users (view, delete)
✓ View all students
✓ Manage companies
✓ Manage internships
✓ Review and update application status
✓ Generate reports:

- Applications Report
- Internships Report
- Students Report
  ✓ System dashboard with key statistics

## Security Features

✓ Password hashing (PASSWORD_BCRYPT)
✓ Session-based authentication
✓ Prepared statements (PDO)
✓ Role-based access control
✓ Input validation and sanitization
✓ CSRF protection (session-based)

## Database Schema

### Tables

**users**

- user_id (Primary Key)
- full_name
- email (Unique)
- password (hashed)
- role (student/company/admin)
- created_at, updated_at

**students**

- student_id (Primary Key)
- user_id (Foreign Key)
- registration_no
- course
- year_of_study
- phone

**companies**

- company_id (Primary Key)
- user_id (Foreign Key)
- company_name
- location
- phone

**internships**

- internship_id (Primary Key)
- company_id (Foreign Key)
- title
- description
- requirements
- deadline
- created_at, updated_at

**applications**

- application_id (Primary Key)
- student_id (Foreign Key)
- internship_id (Foreign Key)
- status (Pending/Accepted/Rejected)
- application_date
- updated_at
- Unique constraint on (student_id, internship_id)

**reports**

- report_id (Primary Key)
- report_name
- report_type
- generated_by (Foreign Key to users)
- generated_date
- report_data (JSON format)

## File Structure

```
S-I-M-S/
├── backend/
│   ├── classes/
│   │   ├── User.php
│   │   ├── Auth.php
│   │   ├── Student.php
│   │   ├── Company.php
│   │   ├── Internship.php
│   │   ├── Application.php
│   │   └── Report.php
│   └── config/
│       └── database.php
├── database/
│   └── schema.sql
└── frontend/
    ├── index.php
    ├── assets/
    │   └── css/
    │       └── style.css
    ├── authentication/
    │   ├── login.php
    │   ├── register.php
    │   └── logout.php
    ├── student/
    │   ├── dashboard.php
    │   ├── internships.php
    │   ├── search.php
    │   ├── applications.php
    │   └── profile.php
    ├── company/
    │   ├── dashboard.php
    │   ├── internships.php
    │   ├── applications.php
    │   └── profile.php
    ├── admin/
    │   ├── dashboard.php
    │   ├── users.php
    │   ├── students.php
    │   ├── companies.php
    │   ├── internships.php
    │   ├── applications.php
    │   └── reports.php
    └── layouts/
        ├── header.php
        ├── sidebar.php
        └── footer.php
```

## Troubleshooting

### Database Connection Error

- Verify MySQL is running
- Check database.php configuration
- Ensure database `internship_db` exists

### Session Issues

- Ensure `session_start()` is called before headers
- Check PHP session settings in php.ini

### Permission Denied

- Verify role before accessing protected pages
- Check session variables are set correctly

## Future Enhancements

- Email notifications for application updates
- Student resume upload
- Advanced filtering and sorting
- Dashboard charts and analytics
- Bulk operations
- API development
- Mobile app
- AWS deployment

---

**System Author**: ISAACK CHANGAWA
**Last Updated**: 2024
