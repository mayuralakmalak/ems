#!/bin/bash
# Verification script for Laravel Scheduler Cron Job

echo "=== Laravel Scheduler Cron Job Verification ==="
echo ""
echo "1. Checking crontab..."
crontab -l | grep "artisan schedule:run" && echo "   ✓ Cron job found" || echo "   ✗ Cron job NOT found"
echo ""

echo "2. Testing scheduler manually..."
cd /opt/lampp/htdocs/ems-laravel
/opt/lampp/bin/php artisan schedule:run
echo ""

echo "3. Checking scheduled commands..."
/opt/lampp/bin/php artisan schedule:list
echo ""

echo "=== Verification Complete ==="
echo ""
echo "Note: The cron job runs every minute and executes:"
echo "  cd /opt/lampp/htdocs/ems-laravel && /opt/lampp/bin/php artisan schedule:run"
echo ""
echo "Payment reminder emails will be sent daily at 9:00 AM for payments due the next day."

