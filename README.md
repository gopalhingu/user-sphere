# User Sphere - Project Installation Guide

Welcome to the User Sphere project! This guide will walk you through the steps to set up and run the project from GitHub.

## Requirements

Before starting, make sure your system meets the following requirements:

- **PHP** version 8.2+
- **MySQL** version 5.4+
- **Composer** version 2.7+

## Installation Steps

1. **Clone the Repository**

   First, clone the repository from GitHub:

   ```bash
   git clone https://github.com/gopalhingu/user-sphere.git
   cd user-sphere
   ```

2. **Install Dependencies**

   Run the following command to install all the required dependencies:

   ```bash
   composer update
   ```

3. **Database Migration**

   Next, run the database migrations to set up the database schema:

   ```bash
   php artisan migrate
   ```

4. **Seed the Database**

   After migrating the database, seed the database with the necessary data:

   ```bash
   php artisan db:seed
   ```

5. **Generate Swagger Documentation**

   Run the following command to generate the Swagger documentation for your API:

   ```bash
   php artisan l5-swagger:generate
   ```

6. **Generate JWT Secret**

   To generate the JWT secret key, run the following command:

   ```bash
   php artisan jwt:secret
   ```

7. **Access the Application**

   Now that the setup is complete, you can access the application by navigating to your web browser.

---

## Default Credentials

Once you have successfully installed the application, you can log in with the following default credentials:

- **Admin:**
  - **Email:** admin@example.com
  - **Password:** password

- **User:**
  - **Email:** user@example.com
  - **Password:** password

---

## Environment Configuration

Before running the application, make sure to configure your `.env` file with the following variables:

```env
GOOGLE_CLIENT_ID='your-google-client-id'
GOOGLE_CLIENT_SECRET='your-google-client-secret'
GOOGLE_REDIRECT_URI='http://127.0.0.1:8000/auth/google/callback'

JWT_SECRET=your-generated-jwt-secret
L5_SWAGGER_CONST_HOST=http://127.0.0.1:8000
L5_SWAGGER_GENERATE_ALWAYS=true
```

- Replace `'your-google-client-id'` and `'your-google-client-secret'` with your Google OAuth credentials.
- The `JWT_SECRET` can be generated using the command `php artisan jwt:secret`.
- `L5_SWAGGER_CONST_HOST` should point to your application URL.

---

Let me know if you need anything else!
