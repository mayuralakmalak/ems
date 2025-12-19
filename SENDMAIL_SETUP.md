# Sendmail Setup Instructions

## Current Status
✅ Mail driver changed to `sendmail` in `.env`
❌ Sendmail is not installed on your system

## Option 1: Install Sendmail (Linux Mint/Ubuntu)

### Install sendmail:
```bash
sudo apt-get update
sudo apt-get install sendmail
```

### Configure sendmail:
```bash
sudo sendmailconfig
```

This will prompt you to configure sendmail. Choose "Yes" for most options.

### Test sendmail:
```bash
echo "Test email" | sendmail -v your-email@example.com
```

## Option 2: Use Postfix (Alternative - Recommended)

Postfix is easier to configure than sendmail:

### Install Postfix:
```bash
sudo apt-get update
sudo apt-get install postfix
```

During installation, it will ask:
- **General type of mail configuration**: Choose "Internet Site"
- **System mail name**: Enter your domain name (or localhost for testing)

### Configure Postfix:
```bash
sudo dpkg-reconfigure postfix
```

### Update Laravel config:
Your `.env` should have:
```env
MAIL_MAILER=sendmail
MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -bs -i
```

Postfix provides a sendmail-compatible interface, so this should work.

### Test:
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

## Option 3: Use Fake SMTP for Local Development (Easiest)

For local development, you might want to use Mailtrap or MailHog instead:

### Use Mailtrap:
1. Sign up at https://mailtrap.io/
2. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
```

### Use MailHog:
1. Install: `sudo apt-get install mailhog` or download from GitHub
2. Run: `mailhog`
3. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```
4. View emails at: http://localhost:8025

## Important Notes

1. **For Production**: Use a proper email service (SendGrid, Mailgun, AWS SES, etc.)

2. **For Local Development**: Mailtrap or MailHog are recommended

3. **Current Configuration**: Your mail is set to `sendmail` but sendmail is not installed. Emails will fail until you install a mail server.

4. **After installing**: Run `php artisan config:clear`
