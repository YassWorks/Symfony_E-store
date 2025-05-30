# Intro 

**El Marchi** is A modern e-commerce platform built with **Symfony**, featuring a comprehensive shopping experience with multi-vendor support, cart management, reviews, and administrative tools.

## 🚀 Features

- **Multi-Vendor Platform**: Support for multiple shops and sellers
- **Product Management**: Comprehensive product catalog with images and categories
- **Shopping Cart**: Full cart functionality with session persistence
- **Wishlist**: Save items for later purchase
- **User Reviews**: Customer feedback and rating system
- **Order Management**: Complete order processing workflow
- **Admin Panel**: Administrative tools for platform management
- **Email Notifications**: Automated email system for orders and notifications
- **Authentication**: Secure user registration and login system
- **Responsive Design**: Modern UI with CSS and JavaScript components

## 🏗️ Project Structure

```
src/
├── Admin/          # Administrative functionality
├── Auth/           # Authentication and user management
├── Cart/           # Shopping cart features
├── Core/           # Core application logic
├── MailModule/     # Email handling
├── Order/          # Order processing
├── Product/        # Product management
├── Review/         # Review system
├── Shop/           # Shop/vendor management
├── Shared/         # Shared utilities and services
└── Wishlist/       # Wishlist functionality
```

## 🚀 Start


### Launch with Docker

1. Ensure Docker and Docker Compose are installed.
2. In your project root, create or update the `.env` file with:
   ```env
   # Docker environment (PostgreSQL)
   DATABASE_URL="postgresql://symfony:symfony@database:5432/symfony?serverVersion=16&charset=utf8"
   MAILER_DSN="smtp://mailer:1025"
   ```
3. Build and start the containers:
   ```bash
   docker-compose -f docker-compose.yml down -v
   docker-compose -f docker-compose.yml up -d --build
   ```
4. Access the application at [http://localhost:8080](http://localhost:8080) and Mailpit UI at [http://localhost:8025](http://localhost:8025).

### Launch without Docker

Prerequisites:
- PHP 8.2+
- Composer
- MySQL running on `127.0.0.1:3308`

1. Clone the repository and install dependencies:
   ```bash
   composer install
   ```
2. Update your `.env` file:
   ```env
   # Local environment (MySQL)
   DATABASE_URL="mysql://root:@127.0.0.1:3308/estore"
   MAILER_DSN="smtp://127.0.0.1:1025"
   ```
3. Create the database and run migrations:
   ```bash
   php bin/console doctrine:database:create --if-not-exists
   php bin/console doctrine:migrations:migrate
   ```
4. Start the Symfony local web server:
   ```bash
   symfony server:start
   ```
   Or with PHP’s built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```
5. Open your browser to [http://localhost:8000](http://localhost:8000).

---

Feel free to explore, contribute, and customize as needed!
