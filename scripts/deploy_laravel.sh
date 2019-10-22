#!/usr/bin/env bash
set -x
set -e

# Enter app directory
cd /var/app/current/

# Migrate all tables
su -s /bin/sh apache -c 'php artisan migrate'

# Clear any previous cached views
su -s /bin/sh apache -c 'php artisan config:clear'
su -s /bin/sh apache -c 'php artisan cache:clear'
su -s /bin/sh apache -c 'php artisan view:clear'

# Optimize the application
# su -s /bin/sh apache -c 'php artisan config:cache'
# su -s /bin/sh apache -c 'php artisan route:cache'

# Change permissions rights
chown -R apache:apache ./
chmod -fR 777 bootstrap/cache
chmod -fR 777 storage
# chmod -fR 777 public/files/

