#!/usr/bin/env bash
set -x
set -e

# Enable maintenance mode
if [ -f /var/app/current/artisan ]; then
    su -s /bin/sh apache -c 'php /var/app/current/artisan down'
fi
