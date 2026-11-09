# ERP Core Module System - Installation Guide

This guide provides step-by-step instructions for setting up the ERP Core Module System from scratch.

## Prerequisites

- PHP 8.0 or higher
- Composer
- MySQL/MariaDB database server
- Web server (Apache/Nginx)
- Node.js and Yarn (for SASS compilation)

## Installation Steps

### 1. Clone/Copy the ERP Core Project

First, copy or clone the erp-core project to your desired location:

```bash
# If using git
git clone <repository-url> ds-erp-module-system-test

# Or copy the project files to your web server directory
```

### 2. Create Database

Create a new MySQL database with UTF8MB4 collation:

```sql
CREATE DATABASE `ds-erp-module-system-test` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;
```

**Important:** Use the exact collation `utf8mb4_general_ci` for proper character encoding support.

### 3. Configure Environment Variables

Edit the `.env.dev` file and update the `DATABASE_URL` with your database credentials:

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/ds-erp-module-system-test?serverVersion=8.0.28&charset=utf8mb4"
```

**Parameters explained:**
- `root:` - Database username (root in this example)
- `@127.0.0.1:3306` - Database host and port
- `ds-erp-module-system-test` - Database name
- `serverVersion=8.0.28` - MySQL server version
- `charset=utf8mb4` - Character encoding

### 4. Install Dependencies

Run Composer to install PHP dependencies:

```bash
composer install
```

This will install all required Symfony packages and dependencies.

### 5. Generate Database Migrations

Create the initial database migration:

```bash
php bin\console make:migration
```

This command will generate migration files based on your entity definitions.

### 6. Execute Database Migrations

Apply the migrations to create the database schema:

```bash
php bin/console doctrine:migrations:migrate
```

This will create all necessary tables in your database.

### 7. Insert Initial Data

Execute the following SQL queries to create the default admin user and user group:

```sql
-- Create Admin user group
INSERT INTO `user_group` (`id`, `name`, `roles`) 
VALUES (1, 'Admin', '["ROLE_ADMIN", "ROLE_SUPER_ADMIN"]');

-- Create default admin user
INSERT INTO `base_user` (
    `id`, 
    `email`, 
    `roles`, 
    `password`, 
    `is_active`, 
    `auth_code`, 
    `is_enable_two_fa`, 
    `trusted_version`, 
    `is_dark_theme`, 
    `name`, 
    `family`, 
    `is_verified`
) VALUES (
    1, 
    'support@devscale.bg', 
    '[]', 
    '$2y$13$1RFXEmXEEyjYLT4GY2VNeuf/uB6ivcKAtIU0lmAeeaxRbjh17SAd2', 
    1, 
    NULL, 
    0, 
    0, 
    0, 
    'Dev', 
    'Scale', 
    1
);

-- Associate user with admin group
INSERT INTO `base_user_user_group` (`base_user_id`, `user_group_id`) 
VALUES (1, 1);
```

### 8. Build SASS Assets

Compile SASS files to CSS:

```bash
php bin/console sass:build
```

This will generate the compiled CSS files from your SASS sources.

### 9. Access the System

Navigate to your application URL. For example:

```
http://ds-erp-module-system-test.localhost/login
```

### 10. Login

Use the following credentials to access the system:

- **Email:** `support@devscale.bg`
- **Password:** `admin`

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify your database credentials in `.env.dev`
   - Ensure MySQL service is running
   - Check if the database exists and has correct collation

2. **Permission Issues**
   - Ensure the `var/` directory is writable
   - Set proper permissions for cache and logs directories

3. **SASS Build Errors**
   - Verify Node.js and Yarn are installed
   - Check if all frontend dependencies are installed

4. **Migration Errors**
   - Ensure database exists and is accessible
   - Check if previous migrations were applied correctly

### File Permissions

Set proper permissions for Symfony directories:

```bash
# On Linux/Mac
chmod -R 755 var/
chmod -R 755 public/uploads/

# On Windows, ensure the application has write access to these directories
```

## Security Notes

- Change the default admin password after first login
- Configure proper database credentials (avoid using root in production)
- Set up HTTPS in production environment
- Configure proper file permissions

## Next Steps

After successful installation:

1. Configure your web server (Apache/Nginx) to point to the `public/` directory
2. Set up proper environment variables for production
3. Configure email settings for password reset functionality
4. Set up backup procedures for your database
5. Configure logging and monitoring

## Support

For additional support or questions, please refer to:
- Symfony documentation: https://symfony.com/doc/
- Project-specific documentation in the `docs/` directory
- Contact: support@devscale.bg 