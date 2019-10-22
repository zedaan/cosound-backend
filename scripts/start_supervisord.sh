#!/usr/bin/env bash
set -x
set -e

# Start supervisord again after deploy
if [ -x /etc/init.d/supervisord ]; then
    /etc/init.d/supervisord start
fi

