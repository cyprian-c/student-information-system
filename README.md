Student Information System - Enterprise Grade MVP

A professional PHP-based student management system with secure data storage and fast retrieval.

## Features

- ✅ Secure admin authentication
- ✅ Complete CRUD operations for student records
- ✅ Advanced search and filtering
- ✅ Real-time statistics dashboard
- ✅ Reports and analytics
- ✅ Responsive design (mobile-friendly)
- ✅ CSV export functionality
- ✅ Form validation
- ✅ PDO prepared statements (SQL injection protection)

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PDO MySQL extension

## Installation

1. **Upload files to your server**

```bash
   Upload all files to your web root directory (e.g., public_html/school_admission/)
```

2. **Create database**

```bash
   - Login to phpMyAdmin or MySQL command line
   - Import the file: sql/school_db.sql
```

3. **Configure database connection**

```bash
   Edit config/database.php and update:
   - DB_HOST (usually 'localhost')
   - DB_USER (your database username)
   - DB_PASS (your database password)
   - DB_NAME (school_db)
```

4. **Set permissions**

```bash
   chmod 755 -R school_admission/
```

5. **Access the system**

```
   https://yourdomain.com/school_admission/

   Default login:
   Username: admin
   Password: admin123
```

## File Structure

```
school_admission/
├── config/          # Database configuration
├── assets/          # CSS, JS, images
├── includes/        # Header, footer templates
├── models/          # Database models (Student class)
├── views/           # User interface pages
├── controllers/     # Business logic handlers
├── admin/           # Admin-only pages
├── sql/             # Database schema
└── index.php        # Login page
```

## Security Features

- Password hashing with bcrypt
- PDO prepared statements
- Session-based authentication
- CSRF protection ready
- XSS prevention with htmlspecialchars()
- Input validation and sanitization

## Usage

### Adding Students

1. Login to admin panel
2. Click "New Admission"
3. Fill in all required fields
4. Click "Save Student"

### Managing Students

1. Go to "All Students"
2. Use search/filter options
3. Click edit icon to modify
4. Click delete icon to remove

### Viewing Reports

1. Navigate to "Reports"
2. View statistics and analytics
3. Export data as needed

## Customization

### Adding More Classes

Edit `views/admission-form.php` and add more grade options in the class dropdown.

### Changing Admin Password

Run this SQL query:

```sql
UPDATE admin_users
SET password = '$2y$10$YOUR_NEW_HASHED_PASSWORD'
WHERE username = 'admin';
```

Generate hash in PHP:

```php
echo password_hash('your_new_password', PASSWORD_DEFAULT);
```

### Adding New Fields

1. Update database table in `sql/school_db.sql`
2. Modify `models/Student.php` methods
3. Update forms in `views/admission-form.php`

## Troubleshooting

**Can't connect to database:**

- Check config/database.php credentials
- Verify MySQL service is running
- Check database exists

**Login not working:**

- Clear browser cache
- Check session is enabled in php.ini
- Verify admin_users table has data

**Pages not loading:**

- Check file permissions (755)
- Enable error reporting in PHP
- Check Apache mod_rewrite is enabled

## Performance Optimization

- Database indexed on frequently queried columns
- Prepared statements with PDO
- Minimal external dependencies
- Optimized Bootstrap/CSS delivery

## Support

For issues or questions, check the code comments or database schema.

## License

Free to use for educational and commercial purposes.
