# Exhibition Management System - Login Credentials & Page Links

## Base URL
```
http://localhost/ems-laravel/public
```

---

## 🔐 LOGIN CREDENTIALS

### 👨‍💼 ADMIN ACCOUNT
```
Email: asadm@alakmalak.com
Password: 123456
Role: Admin
```

### 👤 EXHIBITOR ACCOUNTS (3 Test Users)
```
1. Email: rajesh@techcorp.com
   Password: 123456
   Company: TechCorp Solutions

2. Email: priya@innovate.com
   Password: 123456
   Company: Innovate Industries

3. Email: amit@globaltech.com
   Password: 123456
   Company: Global Tech Solutions
```

---

## 🌐 PUBLIC PAGES (No Login Required)

### Home & Exhibitions
- **Home Page**: `http://localhost/ems-laravel/public/`
- **Exhibitions List**: `http://localhost/ems-laravel/public/exhibitions`
- **Exhibition Details**: `http://localhost/ems-laravel/public/exhibitions/{id}` (e.g., `/exhibitions/2`)

### Authentication
- **Login**: `http://localhost/ems-laravel/public/login`
- **Register**: `http://localhost/ems-laravel/public/register`
- **OTP Login**: `http://localhost/ems-laravel/public/login-otp`

---

## 🔧 ADMIN PANEL (Login Required - Admin Role)

### Dashboard
- **Admin Dashboard**: `http://localhost/ems-laravel/public/admin/dashboard`

### Exhibition Management
- **List Exhibitions**: `http://localhost/ems-laravel/public/admin/exhibitions`
- **Create Exhibition (Step 1)**: `http://localhost/ems-laravel/public/admin/exhibitions/create`
- **View Exhibition**: `http://localhost/ems-laravel/public/admin/exhibitions/{id}` (e.g., `/admin/exhibitions/2`)
- **Edit Exhibition**: `http://localhost/ems-laravel/public/admin/exhibitions/{id}/edit`
- **Step 2 - Floor Plan**: `http://localhost/ems-laravel/public/admin/exhibitions/{id}/step2`
- **Step 3 - Payment Schedule**: `http://localhost/ems-laravel/public/admin/exhibitions/{id}/step3`
- **Step 4 - Badge & Manual**: `http://localhost/ems-laravel/public/admin/exhibitions/{id}/step4`

### Booth Management
- **List Booths**: `http://localhost/ems-laravel/public/admin/exhibitions/{exhibitionId}/booths` (e.g., `/admin/exhibitions/2/booths`)
- **Create Booth**: `http://localhost/ems-laravel/public/admin/exhibitions/{exhibitionId}/booths/create`
- **View Booth**: `http://localhost/ems-laravel/public/admin/exhibitions/{exhibitionId}/booths/{id}`
- **Edit Booth**: `http://localhost/ems-laravel/public/admin/exhibitions/{exhibitionId}/booths/{id}/edit`

### User Management
- **List Users**: `http://localhost/ems-laravel/public/admin/users`
- **Edit User**: `http://localhost/ems-laravel/public/admin/users/{id}/edit`
- **View User**: `http://localhost/ems-laravel/public/admin/users/{id}`

### Booking Management
- **List Bookings**: `http://localhost/ems-laravel/public/admin/bookings`
- **View Booking**: `http://localhost/ems-laravel/public/admin/bookings/{id}`

### Financial Management
- **Financial Overview**: `http://localhost/ems-laravel/public/admin/financial`

### Reports & Analytics
- **Reports Dashboard**: `http://localhost/ems-laravel/public/admin/reports`

---

## 👤 EXHIBITOR PANEL (Login Required - Exhibitor Role)

### Dashboard
- **Exhibitor Dashboard**: `http://localhost/ems-laravel/public/dashboard`

### Exhibition Booking
- **Browse Exhibitions**: `http://localhost/ems-laravel/public/exhibitions`
- **Book Booth**: `http://localhost/ems-laravel/public/exhibitions/{id}` (shows booking form when logged in)
- **View Booking**: `http://localhost/ems-laravel/public/bookings/{id}`

### Payment
- **Make Payment**: `http://localhost/ems-laravel/public/payments/{bookingId}`

### Documents Management
- **List Documents**: `http://localhost/ems-laravel/public/documents`
- **Upload Document**: `http://localhost/ems-laravel/public/documents/create`
- **View Document**: `http://localhost/ems-laravel/public/documents/{id}`
- **Edit Document**: `http://localhost/ems-laravel/public/documents/{id}/edit`

### Badges Management
- **List Badges**: `http://localhost/ems-laravel/public/badges`
- **Create Badge**: `http://localhost/ems-laravel/public/badges/create`
- **View Badge**: `http://localhost/ems-laravel/public/badges/{id}`
- **Edit Badge**: `http://localhost/ems-laravel/public/badges/{id}/edit`
- **Download Badge**: `http://localhost/ems-laravel/public/badges/{id}/download`

### Messages
- **Messages**: `http://localhost/ems-laravel/public/messages`
- **Create Message**: `http://localhost/ems-laravel/public/messages/create`
- **View Message**: `http://localhost/ems-laravel/public/messages/{id}`
- **Edit Message**: `http://localhost/ems-laravel/public/messages/{id}/edit`

### Wallet
- **Wallet Balance**: `http://localhost/ems-laravel/public/wallet`

### Profile
- **Edit Profile**: `http://localhost/ems-laravel/public/profile`

---

## 📋 QUICK ACCESS LINKS

### Admin Quick Links
```
Dashboard:        /admin/dashboard
Exhibitions:      /admin/exhibitions
Users:            /admin/users
Bookings:         /admin/bookings
Financial:        /admin/financial
Reports:          /admin/reports
```

### Exhibitor Quick Links
```
Dashboard:        /dashboard
Browse:           /exhibitions
Documents:        /documents
Badges:           /badges
Messages:         /messages
Wallet:           /wallet
```

---

## 🧪 TEST DATA AVAILABLE

### Test Exhibitions
- India Tech Expo 2024 (ID: 2)
- Global Business Summit 2024 (ID: 3)
- Startup Innovation Fest 2024 (ID: 4)

### Test Booths
- Each exhibition has 19 test booths with varying sizes and prices

### Test Services
- Each exhibition has 10 additional services

---

## 📝 NOTES

1. **All passwords are**: `123456`
2. **Base URL**: Replace `{id}` with actual IDs from database
3. **Admin routes** require Admin or Sub Admin role
4. **Exhibitor routes** require authentication (any logged-in user)
5. **Public routes** are accessible without login

---

## 🔗 DIRECT ACCESS URLS (Replace {id} with actual IDs)

### Admin
- Dashboard: `http://localhost/ems-laravel/public/admin/dashboard`
- Exhibitions: `http://localhost/ems-laravel/public/admin/exhibitions`
- Users: `http://localhost/ems-laravel/public/admin/users`
- Bookings: `http://localhost/ems-laravel/public/admin/bookings`

### Exhibitor
- Dashboard: `http://localhost/ems-laravel/public/dashboard`
- Documents: `http://localhost/ems-laravel/public/documents`
- Badges: `http://localhost/ems-laravel/public/badges`
- Messages: `http://localhost/ems-laravel/public/messages`
- Wallet: `http://localhost/ems-laravel/public/wallet`

### Public
- Home: `http://localhost/ems-laravel/public/`
- Login: `http://localhost/ems-laravel/public/login`
- Register: `http://localhost/ems-laravel/public/register`

