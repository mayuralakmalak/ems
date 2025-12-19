# Quick Email Setup

## Choose Your Option:

### 🟢 Option 1: Mailtrap (Easiest - For Testing)
**Best for:** Development and testing
**Cost:** FREE
**Setup time:** 2 minutes

1. **Sign up**: https://mailtrap.io/ (free account)
2. **Get credentials**: Email Testing → Inboxes → SMTP Settings → Laravel
3. **Update .env** with these values:
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
4. **Run**: `php artisan config:clear`
5. **Test**: Try forgot password - email appears in Mailtrap inbox!

---

### 🔵 Option 2: Gmail (For Real Emails)
**Best for:** Production or real email delivery
**Cost:** FREE
**Setup time:** 5 minutes

1. **Enable 2-Step Verification** on Gmail
2. **Generate App Password**: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Name it "Laravel"
   - Copy the 16-character password
3. **Update .env**:
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
4. **Run**: `php artisan config:clear`
5. **Test**: Try forgot password - email sent to user's inbox!

---

## After Setup:

1. **Clear config cache**:
```bash
php artisan config:clear
```

2. **Test it**:
```bash
php artisan tinker
```
Then:
```php
Mail::raw('Test', function($m) {
    $m->to('your-email@example.com')->subject('Test');
});
```

3. **Try forgot password** - emails should now be sent!

---

## Need Help?

See `SETUP_EMAIL_SENDING.md` for detailed instructions and troubleshooting.
