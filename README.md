Student Information System - Enterprise

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

## Security Features

- Password hashing with bcrypt
- PDO prepared statements
- Session-based authentication
- CSRF protection ready
- XSS prevention with htmlspecialchars()
- Input validation and sanitization

## Usage

## Installation

Create an account after paying one time fee to use the service
and you are good to go.

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

For issues or questions reach ocharo.dev@gmail.com

## License

MIT
