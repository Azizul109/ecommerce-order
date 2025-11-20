# E-Commerce Order Management System

A scalable REST API for an order management system with inventory tracking built with Laravel 10+.

## Features

### Product & Inventory Management

-   Product CRUD with variants
-   Real-time inventory tracking
-   Low stock alerts (queue job)
-   Bulk product import via CSV
-   Product search with full-text search

### Order Processing

-   Create orders with multiple items
-   Order status workflow: Pending → Processing → Shipped → Delivered → Cancelled
-   Inventory deduction on order confirmation
-   Order rollback on cancellation (restore inventory)
-   Invoice generation (PDF)
-   Email notifications for order updates

### Authentication & Authorization

-   JWT authentication with refresh tokens
-   Role-based access: Admin, Vendor, Customer
-   Admin: Full access
-   Vendor: Manage own products and orders
-   Customer: Place orders, view order history

## Technical Stack

-   Laravel 10+ (Laravel 12)
-   PHP 8.2+ (PHP 8.4)
-   MySQL/PostgreSQL
-   JWT Authentication
-   Queue System
-   Event System
-   Repository Pattern
-   Service Classes

## Local Setup

### Prerequisites

-   PHP 8.2+
-   Composer
-   MySQL/PostgreSQL
-   Redis (optional, for queues)

### Installation Steps

1. **Clone the repository**
    ```bash
    git clone <repository-url>
    cd ecommerce-order-management
    ```

### Install

-   composer install
-   copy .env.example and paste to .env
-   create database 'ecommerce_order_management'
-   run command 'php artisan migrate:fresh --seed'
-   php artisan serve
-   MySQL/PostgreSQL
-   Redis (php artsan queue:work)
-   Import json postman api collection to test api
-   testing command is 'php artisan test'

### API authentication

-   go to cmd and generate jwt token
-   command is 'php artisan jwt:secret'
-   copy the token and paste it on postman Authorization
-   Choose bearer token and paste the token
-   After register of user 'access token' will be given from postman
-   Use the 'access token' to test other api

### Postman Collection

-   postman collection api is also provided
-   Just import it the check the result