# Setup Email Sending - Quick Guide

## Option 1: Mailtrap (Recommended for Testing - FREE)

### Step 1: Sign Up
1. Go to https://mailtrap.io/
2. Sign up for a free account
3. Verify your email

### Step 2: Get SMTP Credentials
1. Login to Mailtrap
2. Go to **Email Testing** → **Inboxes**
3. Click on **SMTP Settings**
4. Select **Laravel** from the dropdown
5. Copy the credentials shown

### Step 3: Update .env File
Replace the mail settings in your `.env` file with:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username_here
MAIL_PASSWORD=your_mailtrap_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 4: Clear Config
```bash
php artisan config:clear
```

### Step 5: Test
Try forgot password again - email will appear in your Mailtrap inbox!

---

## Option 2: Gmail SMTP (For Real Emails)

### Step 1: Enable 2-Step Verification
1. Go to https://myaccount.google.com/
2. Click **Security** → **2-Step Verification**
3. Enable it (you'll need your phone)

### Step 2: Generate App Password
1. Go to https://myaccount.google.com/apppasswords
2. Select **Mail** and **Other (Custom name)**
3. Enter "Laravel App" as name
4. Click **Generate**
5. Copy the 16-character password (no spaces)

### Step 3: Update .env File
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_char_app_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 4: Clear Config
```bash
php artisan config:clear
```

### Step 5: Test
Try forgot password - email will be sent to the user's inbox!

---

## Option 3: Other SMTP Services

### SendGrid (Free tier: 100 emails/day)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
```

### Mailgun (Free tier: 5,000 emails/month)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_username
MAIL_PASSWORD=your_mailgun_password
MAIL_ENCRYPTION=tls
```

---

## Quick Test Command

After configuring, test email sending:

```bash
php artisan tinker
```

Then in tinker:
```php
\Illuminate\Support\Facades\Mail::raw('Test email', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

---

## Troubleshooting

### If emails still not sending:
1. **Check credentials** - Make sure username/password are correct
2. **Check port** - Usually 587 for TLS, 465 for SSL
3. **Check encryption** - Use `tls` for port 587, `ssl` for port 465
4. **Check logs** - `tail -f storage/logs/laravel.log`
5. **Verify .env** - Make sure no quotes around values (except for MAIL_FROM_ADDRESS)

### Common Errors:
- **"Connection refused"** - Wrong host or port
- **"Authentication failed"** - Wrong username/password
- **"Connection timeout"** - Firewall blocking port 587/465

---

## Current Status Check

```bash
# Check current mail driver
php artisan tinker --execute="echo config('mail.default');"

# Check SMTP settings
php artisan tinker --execute="echo config('mail.mailers.smtp.host');"
```
