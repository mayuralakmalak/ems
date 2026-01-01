#!/bin/bash
# Fix Laravel storage permissions
# Run this script with: sudo bash fix-permissions.sh

# Get the actual path (resolve symlinks)
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd -P)"
cd "$SCRIPT_DIR"

echo "Fixing Laravel storage permissions..."

# Get the web server user (usually daemon or www-data)
WEB_USER="daemon"
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
fi

# Set ownership to web server user
echo "Setting ownership to $WEB_USER..."
chown -R $WEB_USER:$WEB_USER storage bootstrap/cache

# Set directory permissions
echo "Setting directory permissions..."
find storage bootstrap/cache -type d -exec chmod 775 {} \;

# Set file permissions
echo "Setting file permissions..."
find storage bootstrap/cache -type f -exec chmod 664 {} \;

echo "Permissions fixed successfully!"
echo "If you still have issues, make sure your web server user ($WEB_USER) has write access."
