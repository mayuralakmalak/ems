# Email Verification Setup Guide

## Problem
Verification emails are not being sent because the mail driver is set to 'log', which writes emails to log files instead of sending them.

## Solution Options

### Option 1: Check Log Files (Quick Check)
If you just want to see the verification link, check the log file:

```bash
tail -100 storage/logs/laravel.log | grep -A 20 "verify-email"
```

The verification link will be in the log file. You can copy and paste it to verify your email.

### Option 2: Use Mailtrap (Recommended for Local Development)

Mailtrap is a fake SMTP server for testing emails locally.

1. **Sign up for free at**: https://mailtrap.io/
2. **Get your SMTP credentials** from Mailtrap dashboard
3. **Update your `.env` file**:

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

4. **Clear config cache**:
```bash
php artisan config:clear
```

### Option 3: Use Gmail SMTP (For Testing)

1. **Enable 2-Step Verification** on your Gmail account
2. **Generate an App Password**: 
   - Go to Google Account → Security → 2-Step Verification → App passwords
   - Generate a password for "Mail"
3. **Update your `.env` file**:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

4. **Clear config cache**:
```bash
php artisan config:clear
```

### Option 4: Use MailHog (Local SMTP Server)

1. **Install MailHog**:
```bash
# On macOS
brew install mailhog

# On Linux
wget https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_linux_amd64
chmod +x MailHog_linux_amd64
sudo mv MailHog_linux_amd64 /usr/local/bin/mailhog
```

2. **Start MailHog**:
```bash
mailhog
```

3. **Update your `.env` file**:

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

4. **Access MailHog UI**: http://localhost:8025
5. **Clear config cache**:
```bash
php artisan config:clear
```

### Option 5: Use Sendmail (Linux/Mac)

If you have sendmail configured on your system:

```env
MAIL_MAILER=sendmail
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Current Configuration Check

To check your current mail configuration, run:

```bash
php artisan tinker
```

Then in tinker:
```php
config('mail.default')
config('mail.mailers.smtp')
```

## Testing Email Sending

After configuring, test if emails are being sent:

```bash
php artisan tinker
```

Then:
```php
\Illuminate\Support\Facades\Mail::raw('Test email', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

## Important Notes

1. **Always clear config cache** after changing `.env`:
   ```bash
   php artisan config:clear
   ```

2. **For production**, use a proper email service like:
   - AWS SES
   - SendGrid
   - Mailgun
   - Postmark

3. **Check spam folder** if emails are configured but not received.

4. **Log files location**: `storage/logs/laravel.log` (if using 'log' driver)
