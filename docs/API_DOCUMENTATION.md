# SportBooking REST API Documentation

## Base URL
```
http://your-domain.com/api/v1
```

## Authentication
The API uses **Laravel Sanctum** for token-based authentication.

### Authentication Flow
1. Register or login to receive an access token
2. Include the token in subsequent requests:
   ```
   Authorization: Bearer YOUR_ACCESS_TOKEN
   ```

---

## Endpoints Overview

### Public Endpoints (No Authentication Required)
- `POST /register` - Register new user
- `POST /login` - Login user
- `GET /lapangan` - List all active fields
- `GET /lapangan/{id}` - Get field details
- `GET /lapangan/{id}/available-slots` - Get available time slots
- `POST /lapangan/{id}/calculate-price` - Calculate booking price

### Protected Endpoints (Require Authentication)
- `POST /logout` - Logout user
- `GET /me` - Get authenticated user
- `GET /profile` - Get user profile
- `PUT /profile` - Update profile
- `POST /profile/change-password` - Change password
- `GET /profile/points` - Get points balance & history
- `GET /bookings` - List user's bookings
- `POST /bookings` - Create new booking
- `GET /bookings/{id}` - Get booking details
- `POST /bookings/{id}/upload-payment` - Upload payment proof
- `POST /bookings/{id}/cancel` - Cancel booking

---

## API Reference

### Authentication

#### Register User
```http
POST /api/v1/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890",
  "address": "Jakarta, Indonesia"
}
```

**Response (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "081234567890",
    "address": "Jakarta, Indonesia",
    "points_balance": 0,
    "created_at": "2025-11-06T15:30:00.000000Z"
  },
  "token": "1|abcdef123456..."
}
```

#### Login
```http
POST /api/v1/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "points_balance": 100
  },
  "token": "2|xyz789..."
}
```

#### Logout
```http
POST /api/v1/logout
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

#### Get Current User
```http
GET /api/v1/me
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "081234567890",
  "address": "Jakarta, Indonesia",
  "points_balance": 100,
  "created_at": "2025-11-06T15:30:00.000000Z"
}
```

---

### Lapangan (Fields)

#### List All Fields
```http
GET /api/v1/lapangan
```

**Query Parameters:**
- `category` (optional) - Filter by category: `Futsal`, `Badminton`, `Basket`, `Volly`, `Tennis`
- `search` (optional) - Search by title
- `sort_by` (optional) - Sort by: `price`
- `sort_direction` (optional) - `asc` or `desc`
- `per_page` (optional) - Items per page (default: 15)

**Example:**
```http
GET /api/v1/lapangan?category=Futsal&sort_by=price&sort_direction=asc
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Lapangan Futsal Premium A",
      "category": "Futsal",
      "description": "<p>Field description...</p>",
      "price": 300000,
      "weekday_price": 250000,
      "weekend_price": 350000,
      "peak_hour_start": "17:00:00",
      "peak_hour_end": "21:00:00",
      "peak_hour_multiplier": 1.5,
      "images": ["lapangan-images/image1.jpg"],
      "status": 1,
      "status_label": "Active",
      "operational_hours": {
        "jam_buka": "06:00",
        "jam_tutup": "23:00"
      },
      "is_maintenance": false,
      "maintenance_info": null
    }
  ],
  "links": {...},
  "meta": {...}
}
```

#### Get Field Details
```http
GET /api/v1/lapangan/{id}
```

**Response (200):**
```json
{
  "id": 1,
  "title": "Lapangan Futsal Premium A",
  "category": "Futsal",
  "price": 300000,
  "weekday_price": 250000,
  "weekend_price": 350000,
  "peak_hour_start": "17:00:00",
  "peak_hour_end": "21:00:00",
  "peak_hour_multiplier": 1.5,
  "images": ["lapangan-images/image1.jpg"],
  "operational_hours": {
    "jam_buka": "06:00",
    "jam_tutup": "23:00"
  }
}
```

#### Get Available Time Slots
```http
GET /api/v1/lapangan/{id}/available-slots?date=2025-11-10
```

**Query Parameters:**
- `date` (required) - Date in format: `YYYY-MM-DD`

**Response (200):**
```json
{
  "date": "2025-11-10",
  "lapangan": {
    "id": 1,
    "title": "Lapangan Futsal Premium A",
    "category": "Futsal"
  },
  "slots": [
    {
      "start": "06:00",
      "end": "07:00",
      "is_available": true,
      "price": 250000,
      "is_weekend": false,
      "is_peak_hour": false,
      "peak_multiplier": 1.0
    },
    {
      "start": "18:00",
      "end": "19:00",
      "is_available": false,
      "price": 375000,
      "is_weekend": false,
      "is_peak_hour": true,
      "peak_multiplier": 1.5
    }
  ]
}
```

#### Calculate Price
```http
POST /api/v1/lapangan/{id}/calculate-price
```

**Request Body:**
```json
{
  "date": "2025-11-15",
  "start_time": "18:00",
  "end_time": "20:00"
}
```

**Response (200):**
```json
{
  "base_price": 350000,
  "duration_hours": 2,
  "peak_multiplier": 1.5,
  "total_price": 1050000,
  "is_weekend": true,
  "is_peak_hour": true,
  "price_breakdown": {
    "base": 700000,
    "peak_additional": 350000
  }
}
```

---

### Bookings

#### List User's Bookings
```http
GET /api/v1/bookings
Authorization: Bearer YOUR_TOKEN
```

**Query Parameters:**
- `status` (optional) - Filter by: `pending`, `confirmed`, `cancelled`, `completed`
- `payment_status` (optional) - Filter by: `unpaid`, `waiting_confirmation`, `paid`, `refunded`
- `filter` (optional) - `upcoming` or `past`
- `per_page` (optional) - Items per page (default: 15)

**Example:**
```http
GET /api/v1/bookings?filter=upcoming&per_page=10
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "lapangan": {
        "id": 1,
        "title": "Lapangan Futsal Premium A",
        "category": "Futsal"
      },
      "tanggal": "2025-11-15",
      "jam_mulai": "18:00:00",
      "jam_selesai": "20:00:00",
      "duration": "2 jam",
      "harga": 1050000,
      "payment_method": "Bank Transfer",
      "payment_status": "waiting_confirmation",
      "payment_status_label": "Menunggu Konfirmasi",
      "status": "pending",
      "status_label": "Pending",
      "created_at": "2025-11-06T15:30:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

#### Create Booking
```http
POST /api/v1/bookings
Authorization: Bearer YOUR_TOKEN
```

**Request Body:**
```json
{
  "lapangan_id": 1,
  "tanggal": "2025-11-15",
  "jam_mulai": "18:00",
  "jam_selesai": "20:00",
  "nama_pemesan": "John Doe",
  "nomor_telepon": "081234567890",
  "email": "john@example.com"
}
```

**Response (201):**
```json
{
  "message": "Booking created successfully",
  "booking": {
    "id": 1,
    "lapangan": {
      "id": 1,
      "title": "Lapangan Futsal Premium A"
    },
    "tanggal": "2025-11-15",
    "jam_mulai": "18:00:00",
    "jam_selesai": "20:00:00",
    "harga": 1050000,
    "status": "pending",
    "payment_status": "unpaid"
  },
  "price_details": {
    "base_price": 350000,
    "duration_hours": 2,
    "peak_multiplier": 1.5,
    "total_price": 1050000,
    "is_weekend": true,
    "is_peak_hour": true
  }
}
```

**Error Response (400):**
```json
{
  "message": "Failed to create booking",
  "error": "Time slot is already booked."
}
```

#### Get Booking Details
```http
GET /api/v1/bookings/{id}
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "id": 1,
  "lapangan": {
    "id": 1,
    "title": "Lapangan Futsal Premium A",
    "category": "Futsal",
    "images": ["lapangan-images/image1.jpg"]
  },
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "tanggal": "2025-11-15",
  "jam_mulai": "18:00:00",
  "jam_selesai": "20:00:00",
  "duration": "2 jam",
  "nama_pemesan": "John Doe",
  "nomor_telepon": "081234567890",
  "email": "john@example.com",
  "harga": 1050000,
  "payment_method": "Bank Transfer",
  "payment_status": "waiting_confirmation",
  "payment_proof": "http://domain.com/storage/payment-proofs/proof.jpg",
  "status": "pending",
  "points_earned": 10500,
  "created_at": "2025-11-06T15:30:00.000000Z"
}
```

#### Upload Payment Proof
```http
POST /api/v1/bookings/{id}/upload-payment
Authorization: Bearer YOUR_TOKEN
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
- `payment_method` (required) - `Bank Transfer`, `QRIS`, or `E-Wallet`
- `payment_proof` (required) - Image file (max 2MB)
- `payment_notes` (optional) - Additional notes

**Response (200):**
```json
{
  "message": "Payment proof uploaded successfully",
  "booking": {
    "id": 1,
    "payment_method": "Bank Transfer",
    "payment_status": "waiting_confirmation",
    "payment_proof": "http://domain.com/storage/payment-proofs/proof.jpg"
  }
}
```

#### Cancel Booking
```http
POST /api/v1/bookings/{id}/cancel
Authorization: Bearer YOUR_TOKEN
```

**Request Body:**
```json
{
  "cancellation_reason": "Cannot make it on that day"
}
```

**Response (200):**
```json
{
  "message": "Booking cancelled successfully",
  "booking": {
    "id": 1,
    "status": "cancelled",
    "cancellation_reason": "Cannot make it on that day",
    "refund_amount": 525000
  },
  "refund_info": {
    "percentage": 50,
    "amount": 525000,
    "hours_until_booking": 12
  }
}
```

**Refund Rules:**
- **H-24 or more**: 100% refund
- **Less than H-24**: 50% refund
- **Past bookings**: Cannot be cancelled

---

### Profile

#### Get Profile
```http
GET /api/v1/profile
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "081234567890",
  "address": "Jakarta, Indonesia",
  "points_balance": 10500,
  "created_at": "2025-11-06T15:30:00.000000Z"
}
```

#### Update Profile
```http
PUT /api/v1/profile
Authorization: Bearer YOUR_TOKEN
```

**Request Body:**
```json
{
  "name": "John Updated",
  "phone": "081234567899",
  "address": "Bandung, Indonesia"
}
```

**Response (200):**
```json
{
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "name": "John Updated",
    "phone": "081234567899",
    "address": "Bandung, Indonesia"
  }
}
```

#### Change Password
```http
POST /api/v1/profile/change-password
Authorization: Bearer YOUR_TOKEN
```

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
  "message": "Password changed successfully. Please login again."
}
```

**Note:** All tokens will be revoked after password change.

#### Get Points Balance & History
```http
GET /api/v1/profile/points
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "balance": 10500,
  "transactions": [
    {
      "id": 1,
      "type": "earned",
      "amount": 10500,
      "description": "Booking #1 - Lapangan Futsal Premium A",
      "created_at": "2025-11-06T15:30:00.000000Z"
    },
    {
      "id": 2,
      "type": "redeemed",
      "amount": -5000,
      "description": "Redeem discount for Booking #2",
      "created_at": "2025-11-05T10:00:00.000000Z"
    }
  ]
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "message": "Resource not found."
}
```

### Server Error (500)
```json
{
  "message": "Server error occurred.",
  "error": "Error details..."
}
```

---

## Response Formats

### Success Response Structure
```json
{
  "message": "Success message",
  "data": {...}
}
```

### Paginated Response Structure
```json
{
  "data": [...],
  "links": {
    "first": "http://domain.com/api/v1/resource?page=1",
    "last": "http://domain.com/api/v1/resource?page=10",
    "prev": null,
    "next": "http://domain.com/api/v1/resource?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

---

## Rate Limiting

The API uses Laravel's default rate limiting:
- **60 requests per minute** for authenticated users
- **10 requests per minute** for guests (public endpoints)

When rate limit is exceeded:
```json
{
  "message": "Too Many Requests"
}
```
**HTTP Status:** 429

---

## Date & Time Formats

- **Dates**: `YYYY-MM-DD` (e.g., `2025-11-15`)
- **Times**: `HH:MM` or `HH:MM:SS` (e.g., `18:00` or `18:00:00`)
- **ISO 8601**: Used in responses (e.g., `2025-11-06T15:30:00.000000Z`)

---

## Testing with cURL

### Register
```bash
curl -X POST http://localhost/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### List Lapangan
```bash
curl -X GET "http://localhost/api/v1/lapangan?category=Futsal" \
  -H "Accept: application/json"
```

### Create Booking (with token)
```bash
curl -X POST http://localhost/api/v1/bookings \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "lapangan_id": 1,
    "tanggal": "2025-11-15",
    "jam_mulai": "18:00",
    "jam_selesai": "20:00"
  }'
```

---

## Postman Collection

Download the Postman collection for easy testing:
[Download SportBooking API.postman_collection.json](#)

**Import Steps:**
1. Open Postman
2. Click "Import"
3. Select the JSON file
4. Collection will include all endpoints with example requests

---

## Changelog

### Version 1.0.0 (2025-11-06)
- Initial API release
- Authentication with Laravel Sanctum
- Lapangan management endpoints
- Booking CRUD operations
- Dynamic pricing support
- Payment proof upload
- Profile management
- Points system integration

---

## Support

For API support, please contact:
- **Email**: support@sportbooking.com
- **Documentation**: https://docs.sportbooking.com

---

**Production Ready** ✅
- Token-based authentication
- Input validation
- Error handling
- Database locking for concurrency
- Dynamic pricing integration
- File upload support
- Pagination support
- Comprehensive error messages
