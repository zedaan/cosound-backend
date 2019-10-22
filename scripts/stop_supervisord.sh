#!/usr/bin/env bash
set -x
set -e

# Lets stop supervisord before app version is updated
if [ -x /etc/init.d/supervisord ]; then
    /etc/init.d/supervisord stop
fi

