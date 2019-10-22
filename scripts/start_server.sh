#!/usr/bin/env bash
set -x
set -e

# Disable maintenance mode
if [ -f /var/app/current/artisan ]; then
    su -s /bin/sh apache -c 'php /var/app/current/artisan up'
fi

if [[ -n  "$(pgrep httpd)" ]]; then
    service httpd graceful
else
    service httpd start
fi
