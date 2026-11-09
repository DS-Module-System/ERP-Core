# Quick Start Guide

This is a condensed version of the installation process for developers who need to get the ERP Core Module System running quickly.

## Prerequisites Check

```bash
# Check PHP version (requires 8.0+)
php --version

# Check if Composer is installed
composer --version

# Check if Node.js and Yarn are installed
node --version
yarn --version
```

## Quick Installation

### 1. Database Setup
```sql
CREATE DATABASE `ds-erp-module-system-test` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### 2. Environment Configuration
Edit `.env.dev`:
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/ds-erp-module-system-test?serverVersion=8.0.28&charset=utf8mb4"
```

### 3. Install & Setup
```bash
# Install dependencies
composer install

# Create database schema
php bin\console make:migration
php bin/console doctrine:migrations:migrate

# Insert default admin user
php bin/console doctrine:query:sql "INSERT INTO user_group (id, name, roles) VALUES (1, 'Admin', '[\"ROLE_ADMIN\", \"ROLE_SUPER_ADMIN\"]')"
php bin/console doctrine:query:sql "INSERT INTO base_user (id, email, roles, password, is_active, auth_code, is_enable_two_fa, trusted_version, is_dark_theme, name, family, is_verified) VALUES (1, 'support@devscale.bg', '[]', '\$2y\$13\$1RFXEmXEEyjYLT4GY2VNeuf/uB6ivcKAtIU0lmAeeaxRbjh17SAd2', 1, NULL, 0, 0, 0, 'Dev', 'Scale', 1)"
php bin/console doctrine:query:sql "INSERT INTO base_user_user_group (base_user_id, user_group_id) VALUES (1, 1)"

# Build assets
php bin/console sass:build
```

### 4. Access System
- URL: `http://ds-erp-module-system-test.localhost/login`
- Email: `support@devscale.bg`
- Password: `admin`

## Common Commands

```bash
# Clear cache
php bin/console cache:clear

# Update database schema
php bin/console doctrine:schema:update --force

# Create new migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Build SASS
php bin/console sass:build

# Start development server
php bin/console server:start
```

## Troubleshooting Quick Fixes

```bash
# Fix permissions (Linux/Mac)
chmod -R 755 var/
chmod -R 755 public/uploads/

# Clear all caches
php bin/console cache:clear --env=dev
php bin/console cache:clear --env=prod

# Reset database (WARNING: This will delete all data)
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Development Tips

1. **Environment**: Always use `.env.dev` for development
2. **Cache**: Clear cache after configuration changes
3. **Database**: Use migrations for schema changes
4. **Assets**: Rebuild SASS after CSS changes
5. **Logs**: Check `var/log/dev.log` for errors 