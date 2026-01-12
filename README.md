# PHP_Laravel12_SafeBox

Laravel SafeBox is a simple and secure Laravel 12 application that allows authenticated users to store sensitive information such as notes or secrets in an encrypted format. Each user can access only their own stored data.

This project is ideal for beginners and interview preparation, focusing on Laravel fundamentals such as authentication, Blade templates, encryption, routing, middleware, and database relationships.

---

## Project Features

User registration and login using Laravel Breeze

Authenticated user access only

Secure storage of secret information

Data encryption using Laravel Crypt

User-wise data separation

Add, view, and delete SafeBox entries

Simple Blade-based user interface

---

## Technology Stack

PHP 8+

Laravel 12

Laravel Breeze (Authentication)

MySQL

Blade Templates

Bootstrap 5

---

## Project Installation Steps

### Step 1: Create Laravel Project

composer create-project laravel/laravel laravel-safebox

cd laravel-safebox

---

### Step 2: Environment Setup

Create a database named:

laravel_safebox

Update the .env file:

DB_DATABASE=laravel_safebox

DB_USERNAME=root

DB_PASSWORD=

Run migrations:

php artisan migrate

---

### Step 3: Install Laravel Breeze

composer require laravel/breeze --dev

php artisan breeze:install

npm install

npm run dev

php artisan migrate

Laravel Breeze provides authentication features such as login, register, logout, and route protection using middleware.

---

## Database Structure

### safe_boxes Table

id : Primary key

user_id : Linked to users table

title : Title of the secret

secret : Encrypted secret data

timestamps : Created and updated time

---

## Application Flow

User registers or logs in

User is redirected to the SafeBox page

User adds secret data with a title

Secret is encrypted before saving to the database

Only the logged-in user can view or delete their own data

---

## Routes Used

/dashboard redirects to /safebox for authenticated users

/safebox displays user SafeBox entries

POST /safebox stores encrypted secret data

DELETE /safebox/{id} deletes user-owned secret

All routes are protected using authentication middleware

---

## Security Implementation

Authentication middleware protects all SafeBox routes

User ID based filtering ensures data isolation

Secrets are encrypted using Laravel Crypt

CSRF protection is applied to all forms

---

## Blade Structure

resources/views

layouts/app.blade.php

safebox/index.blade.php

---

## Controller Logic Summary

Fetch SafeBox data based on logged-in user

Encrypt secret data before storing

Decrypt secret data before displaying

Allow deletion only for records owned by the logged-in user

---

## Interview Explanation

Laravel SafeBox is a secure Laravel application where authenticated users can store sensitive information in encrypted form. It uses Laravel Breeze for authentication, Blade templates for UI, middleware for route protection, and Laravel Crypt for secure data storage. Each user can access only their own data, ensuring privacy and security.

---

## Future Enhancements

Hide and show secret option

Password-type input for secrets

Copy to clipboard functionality

UI and UX improvements

REST API version of SafeBox

Role-based access control

---

## Author

Mihir Mehta

PHP Laravel Developer

