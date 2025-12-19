#!/bin/bash
# Helper script to extract password reset link from Laravel logs

LOG_FILE="storage/logs/laravel.log"

if [ ! -f "$LOG_FILE" ]; then
    echo "Log file not found: $LOG_FILE"
    exit 1
fi

echo "Searching for password reset links in logs..."
echo "=============================================="
echo ""

# Search for reset-password links
grep -o "reset-password/[^\" ]*" "$LOG_FILE" | tail -1 | while read -r link; do
    if [ ! -z "$link" ]; then
        # Extract the full URL
        full_url=$(grep -o "http[^\" ]*$link[^\" ]*" "$LOG_FILE" | tail -1)
        if [ ! -z "$full_url" ]; then
            echo "Password Reset Link Found:"
            echo "$full_url"
            echo ""
            echo "Copy this link and open it in your browser to reset your password."
        else
            # If full URL not found, construct it
            app_url=$(php artisan tinker --execute="echo config('app.url');" 2>/dev/null)
            echo "Password Reset Link Found:"
            echo "$app_url/$link"
            echo ""
            echo "Copy this link and open it in your browser to reset your password."
        fi
    fi
done

# If no link found, show recent log entries
if ! grep -q "reset-password" "$LOG_FILE" 2>/dev/null; then
    echo "No password reset link found in recent logs."
    echo ""
    echo "Recent log entries:"
    tail -20 "$LOG_FILE" | grep -i "password\|reset\|mail" || tail -10 "$LOG_FILE"
fi
