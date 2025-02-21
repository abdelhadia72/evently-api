# 🎫 Evently API

REST API for event management and ticket booking system.

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- MySQL/PostgreSQL
- Node.js & npm (for frontend)

### Installation

1. Clone the repository

```
git clone https://github.com/yourusername/evently-api.git
```

2. Navigate to the project directory

```
cd evently-api
```

3. Install PHP dependencies

```
composer install
```

4. Install Node.js dependencies

```
npm install
```

5. Set up environment variables

```
cp .env.example .env
```

6. Generate application key

```
php artisan key:generate
```

7. Run database migrations

```
php artisan migrate
```

8. Start the development server

```
php artisan serve
```

## 👥 User Types

- 👑 Admin - Full system access and user management
- 📋 Organizer - Can create and manage events, handle check-ins
- 🎟️ Attendee - Can browse events and manage tickets

## 🛣️ API Endpoints

### 🔐 Authentication

- POST `/api/auth/login` - User login
- POST `/api/auth/register` - User registration
- POST `/api/auth/verify-otp` - Verify OTP code
- POST `/api/auth/resend-otp` - Resend OTP code
- POST `/api/auth/request-password-reset` - Request password reset
- POST `/api/auth/reset-password` - Reset password
- POST `/api/auth/me` - Get authenticated user info (requires auth)
- POST `/api/auth/logout` - Logout user (requires auth)

### 🎭 Events

- GET `/api/events` - List all events (public)
- GET `/api/events/search` - Search events (public)
- GET `/api/events/{id}` - Get event details (public)
- POST `/api/events` - Create new event (requires auth)
- PUT `/api/events/{id}` - Update event (requires auth)
- DELETE `/api/events/{id}` - Delete event (requires auth)

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

- GET `/api/auth/tickets` - List user's tickets (requires auth)
- GET `/api/events/{eventId}/tickets` - List event tickets (requires auth)
- POST `/api/events/{eventId}/tickets` - Book ticket for event (requires auth)
- GET `/api/tickets/{ticketId}` - Get ticket details (requires auth)
- PUT `/api/tickets/{ticketId}` - Update ticket (requires auth)
- POST `/api/tickets/{ticketId}/verify` - Verify ticket (requires auth)
- DELETE `/api/events/{eventId}/tickets` - Cancel ticket (requires auth)
- POST `/api/check-in/tickets` - Check-in ticket (requires auth)

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

- GET `/api/users` - List users (requires auth)

- GET `/api/users/{id}` - Get user details (requires auth)
- POST `/api/users` - Create user (requires auth)
- PUT `/api/users/{id}` - Update user (requires auth)
- PATCH `/api/users/{id}` - Partial update user (requires auth)
- DELETE `/api/users/{id}` - Delete user (requires auth)

### 📁 File Uploads

- POST `/api/uploads` - Upload file (requires auth)
- GET `/api/uploads/{id}` - Get upload details (requires auth)
- GET `/api/uploads/image/{id}` - Get image (public)
- DELETE `/api/uploads/{id}` - Delete upload (requires auth)
- DELETE `/api/uploads` - Delete multiple uploads (requires auth)

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
  "message: message,
  "data": { ... }
}

```
