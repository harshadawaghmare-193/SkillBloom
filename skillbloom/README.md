# 🌱 SkillBloom — Online Course Platform

## Tech Stack
PHP · MySQL · HTML5 · CSS3 · JavaScript (Vanilla)

## Quick Setup

### 1. Database
```sql
-- Import in phpMyAdmin or MySQL CLI:
source database.sql;
```

### 2. Configure
Edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_URL', 'http://localhost/skillbloom');
```

### 3. Place Files
Copy the `skillbloom` folder to your WAMP/XAMPP `www` or `htdocs` directory.

### 4. Access
| URL | Page |
|-----|------|
| `localhost/skillbloom/user/login.php` | Student Login |
| `localhost/skillbloom/user/register.php` | Student Register |
| `localhost/skillbloom/admin/login.php` | Admin Login |

### Default Admin Credentials
- **Email:** admin@skillbloom.com
- **Password:** password

---

## File Structure
```
skillbloom/
├── config.php               ← Main config + DB + helpers
├── database.sql             ← Full DB schema + seed data
├── assets/
│   ├── css/style.css        ← Full design system
│   └── js/main.js           ← Interactivity
├── includes/
│   ├── navbar.php
│   ├── sidebar.php
│   ├── admin_nav.php
│   └── footer.php
├── user/                    ← Student Panel (13 pages)
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── courses.php
│   ├── course-details.php
│   ├── my-courses.php
│   ├── watch-course.php
│   ├── checkout.php
│   ├── payment-status.php
│   ├── my-certificates.php
│   ├── certificate.php
│   └── profile.php
└── admin/                   ← Admin Panel (8 pages)
    ├── login.php
    ├── logout.php
    ├── dashboard.php
    ├── manage-users.php
    ├── manage-courses.php
    ├── manage-videos.php
    ├── manage-categories.php
    ├── manage-payments.php
    └── manage-certificates.php
```

## Features
✅ Full Authentication (Student + Admin)
✅ Course Browsing with Filters & Search
✅ Course Details with Curriculum Preview
✅ Video Player (YouTube + direct video)
✅ Enrollment & Progress Tracking
✅ Payment Simulation (Card/UPI/NetBanking)
✅ Auto Certificate Generation on Completion
✅ Printable/Downloadable Certificates
✅ Complete Admin CRUD (modal-based, same page)
✅ Form Validation (client + server side)
✅ Responsive Design (mobile friendly)
✅ Light color scheme (blue, white, black)
