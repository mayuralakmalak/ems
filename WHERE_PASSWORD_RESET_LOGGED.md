# Where Forgot Password Link is Logged

## Flow Overview

1. **User submits forgot password form**
   - File: `resources/views/auth/forgot-password.blade.php`
   - Form submits to: `route('password.email')`

2. **Controller handles the request**
   - File: `app/Http/Controllers/Auth/PasswordResetLinkController.php`
   - Method: `store()`
   - Line 35: `Password::sendResetLink($request->only('email'))`

3. **Laravel sends the email**
   - Laravel's built-in `Password` facade sends the reset email
   - Uses the mail driver configured in `.env` (`MAIL_MAILER=log`)

4. **Email is logged (not sent)**
   - Since `MAIL_MAILER=log`, Laravel automatically logs the email
   - Logging happens in Laravel's core Mail system (vendor code)
   - **Location**: `storage/logs/laravel.log`

## Log File Location

```
storage/logs/laravel.log
```

## How to Find the Reset Link in Logs

### Method 1: Search for reset-password
```bash
tail -1000 storage/logs/laravel.log | grep "reset-password"
```

### Method 2: Extract the full URL
```bash
tail -2000 storage/logs/laravel.log | grep -o "http[^\"]*reset-password[^\"]*" | tail -1
```

### Method 3: Use the helper script
```bash
./get-reset-link.sh
```

## Code Flow

```
User clicks "Forgot Password"
    ↓
forgot-password.blade.php (form submission)
    ↓
PasswordResetLinkController@store() [Line 26-43]
    ↓
Password::sendResetLink() [Line 35]
    ↓
Laravel Mail System (checks MAIL_MAILER in .env)
    ↓
MAIL_MAILER=log → Logs to storage/logs/laravel.log
    ↓
Email content (including reset link) written to log file
```

## Key Files

1. **Controller**: `app/Http/Controllers/Auth/PasswordResetLinkController.php`
   - Line 35: `Password::sendResetLink()` - This triggers the email/logging

2. **Config**: `config/mail.php`
   - Line 17: `'default' => env('MAIL_MAILER', 'log')` - Determines mail driver
   - Line 73-76: Log mailer configuration

3. **Logging Config**: `config/logging.php`
   - Line 70: `'path' => storage_path('logs/laravel.log')` - Log file location

4. **Environment**: `.env`
   - `MAIL_MAILER=log` - Sets mail driver to log

## Important Notes

- The logging happens **automatically** in Laravel's core Mail system
- No custom logging code is needed - it's built into Laravel
- When `MAIL_MAILER=log`, all emails are logged instead of sent
- The reset link is embedded in the email HTML/text content
- Log file: `storage/logs/laravel.log`
