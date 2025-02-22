# 🎫 Evently API

REST API for event management and ticket booking system.

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- XAMPP (for local development)

### Installation

1. Clone the repository

```
git clone https://github.com/abdelhadi/evently-api.git
```

2. Navigate to the project directory

```
cd evently-api
```

3. Install PHP dependencies

```
composer install
```

4. Set up environment variables

```
cp .env.example .env
```

5. Update the `.env` file with database and mailing settings:

#### Database settings

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=evently
DB_USERNAME=root
DB_PASSWORD=
```

#### Mailing settings

```
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@evently.com"
MAIL_FROM_NAME="${API_NAME}"
```

7. Generate application key

```
php artisan key:generate
```

8. Run database migrations

```
php artisan migrate
```

9. Start XAMPP and ensure MySQL and Apache are running.

10. Start the development server

```
php artisan serve
```

## 👥 User Types

- 👑 Admin - Full system access and user management
- 📋 Organizer - Can create and manage events, handle check-ins
- 🎟️ Attendee - Can browse events and manage tickets

## 🛣️ API Endpoints

### 🔐 Authentication

- POST `/auth/login` - User login
- POST `/auth/register` - User registration
- POST `/auth/verify-otp` - Verify OTP code
- POST `/auth/resend-otp` - Resend OTP code
- POST `/auth/request-password-reset` - Request password reset
- POST `/auth/reset-password` - Reset password
- POST `/auth/me` - Get authenticated user info (requires auth)
- POST `/auth/logout` - Logout user (requires auth)

### 🎭 Events

- GET `/events` - List all events (public)
- GET `/events/search` - Search events (public)
- GET `/events/{id}` - Get event details (public)
- POST `/events` - Create new event (requires auth)
- PUT `/events/{id}` - Update event (requires auth)
- DELETE `/events/{id}` - Delete event (requires auth)

Event Model:

```json
{
  "title": "string",
  "description": "string",
  "start_date": "datetime",
  "end_date": "datetime",
  "location": "string",
  "max_attendees": "integer|nullable",
  "status": "active|cancelled|completed",
  "image_url": "string|nullable",
  "organizer_id": "integer"
}
```

### 🎟️ Tickets

- GET `/auth/tickets` - List user's tickets (requires auth)
- GET `/events/{eventId}/tickets` - List event tickets (requires auth)
- POST `/events/{eventId}/tickets` - Book ticket for event (requires auth)
- GET `/tickets/{ticketId}` - Get ticket details (requires auth)
- PUT `/tickets/{ticketId}` - Update ticket (requires auth)
- POST `/tickets/{ticketId}/verify` - Verify ticket (requires auth)
- DELETE `/events/{eventId}/tickets` - Cancel ticket (requires auth)
- POST `/check-in/tickets` - Check-in ticket (requires auth)

Ticket Model:

```json
{
  "ticket_number": "string",
  "event_id": "integer",
  "user_id": "integer",
  "status": "active|used|cancelled",
  "check_in_time": "datetime|nullable",
  "qr_code": "string"
}
```

### 👥 Users

- GET `/users` - List users (requires auth)
- GET `/users/{id}` - Get user details (requires auth)
- POST `/users` - Create user (requires auth)
- PUT `/users/{id}` - Update user (requires auth)
- PATCH `/users/{id}` - Partial update user (requires auth)
- DELETE `/users/{id}` - Delete user (requires auth)

### 📁 File Uploads

- POST `/uploads` - Upload file (requires auth)
- GET `/uploads/{id}` - Get upload details (requires auth)
- GET `/uploads/image/{id}` - Get image (public)
- DELETE `/uploads/{id}` - Delete upload (requires auth)
- DELETE `/uploads` - Delete multiple uploads (requires auth)

### 🔒 Authentication

The API uses token-based authentication. Include the token in the Authorization header:

```
Authorization: Bearer <token>
```

### ❌ Error Responses

All endpoints return JSON responses in the following format:

```json
{
  "success": false,
  "errors": ["Error message"]
}
```

### ✅ Success Responses

Successful responses follow this format:

```json
{
  "success": true,
  "message": "message",
  "data": { ... }
}
```


