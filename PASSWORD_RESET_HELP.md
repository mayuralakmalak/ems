# Password Reset Help Guide

## Current Setup: Mail Driver = 'log'

With the current configuration, **emails are NOT sent**. Instead, password reset links are written to the log file.

## How to Get Your Password Reset Link

### Method 1: Use the Helper Script (Easiest)

```bash
./get-reset-link.sh
```

This will automatically extract the latest password reset link from your logs.

### Method 2: Manual Search in Log File

```bash
# View the last 100 lines and search for reset link
tail -100 storage/logs/laravel.log | grep "reset-password"

# Or search for the full URL
tail -200 storage/logs/laravel.log | grep -o "http[^\"]*reset-password[^\"]*" | tail -1
```

### Method 3: View Full Log File

```bash
# Open the log file
cat storage/logs/laravel.log | grep -A 5 "reset-password"
```

The reset link will look like:
```
http://localhost/ems-laravel/public/reset-password/TOKEN_HERE?email=user@example.com
```

## To Actually Receive Emails

If you want to receive emails instead of checking logs, you need to configure a proper mail service:

### Option 1: Mailtrap (Best for Testing)

1. **Sign up**: https://mailtrap.io/ (Free account)
2. **Get credentials**: Go to Email Testing → Inboxes → SMTP Settings
3. **Update `.env`**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```
4. **Clear config**: `php artisan config:clear`
5. **Test**: Try forgot password again - email will appear in Mailtrap inbox

### Option 2: Gmail SMTP

1. **Enable 2-Step Verification** on your Gmail account
2. **Generate App Password**:
   - Go to Google Account → Security → 2-Step Verification
   - Click "App passwords"
   - Generate password for "Mail"
3. **Update `.env`**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_char_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```
4. **Clear config**: `php artisan config:clear`
5. **Test**: Try forgot password again

### Option 3: Other SMTP Services

You can use any SMTP service:
- **SendGrid**: Free tier available
- **Mailgun**: Free tier available
- **AWS SES**: Pay as you go
- **Postmark**: Free trial

Just update the `.env` file with the appropriate SMTP settings.

## Quick Test Commands

```bash
# Check current mail driver
php artisan tinker --execute="echo config('mail.default');"

# Test email sending (if SMTP configured)
php artisan tinker
# Then in tinker:
Mail::raw('Test email', function($message) {
    $message->to('your-email@example.com')->subject('Test');
});
```

## Troubleshooting

### If reset link doesn't work:
1. **Check token expiry**: Reset links expire after 60 minutes (default)
2. **Check URL format**: Make sure the full URL is copied
3. **Try again**: Request a new reset link

### If email still not received (with SMTP):
1. **Check spam folder**
2. **Verify SMTP credentials** are correct
3. **Check mail logs**: `tail -f storage/logs/laravel.log`
4. **Test SMTP connection**: Use the test command above

## Current Status

- ✅ Password reset functionality: Working
- ✅ Reset link generation: Working
- ⚠️ Email delivery: Using log file (not sending emails)
- 📝 Reset links: Available in `storage/logs/laravel.log`
