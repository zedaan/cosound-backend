#!/usr/bin/env bash
set -x
set -e

if [ ! -e /usr/local/bin/supervisorctl ]; then
    pip install supervisor
fi

if [ ! -f /etc/init.d/supervisord ]; then
    cat<<'_EOF_' >/etc/init.d/supervisord
#!/bin/bash
#
# supervisord   Startup script for the Supervisor process control system
#
# Author:       Mike McGrath <mmcgrath@redhat.com> (based off yumupdatesd)
#               Jason Koppe <jkoppe@indeed.com> adjusted to read sysconfig,
#                   use supervisord tools to start/stop, conditionally wait
#                   for child processes to shutdown, and startup later
#               Erwan Queffelec <erwan.queffelec@gmail.com>
#                   make script LSB-compliant
#
# chkconfig:    345 83 04
# description: Supervisor is a client/server system that allows \
#   its users to monitor and control a number of processes on \
#   UNIX-like operating systems.
# processname: supervisord
# config: /etc/supervisord.conf
# config: /etc/sysconfig/supervisord
# pidfile: /var/run/supervisord.pid
#
### BEGIN INIT INFO
# Provides: supervisord
# Required-Start: $all
# Required-Stop: $all
# Short-Description: start and stop Supervisor process control system
# Description: Supervisor is a client/server system that allows
#   its users to monitor and control a number of processes on
#   UNIX-like operating systems.
### END INIT INFO

# Source function library
. /etc/rc.d/init.d/functions

# Source system settings
if [ -f /etc/sysconfig/supervisord ]; then
    . /etc/sysconfig/supervisord
fi

# Path to the supervisorctl script, server binary,
# and short-form for messages.
supervisorctl=${SUPERVISORCTL-/usr/bin/supervisorctl}
supervisord=${SUPERVISORD-/usr/bin/supervisord}
prog=supervisord
pidfile=${PIDFILE-/var/run/supervisord.pid}
lockfile=${LOCKFILE-/var/lock/subsys/supervisord}
STOP_TIMEOUT=${STOP_TIMEOUT-60}
OPTIONS="${OPTIONS--c /etc/supervisor.conf}"
RETVAL=0

start() {
    echo -n $"Starting $prog: "
    daemon --pidfile=${pidfile} $supervisord $OPTIONS
    RETVAL=$?
    echo
    if [ $RETVAL -eq 0 ]; then
        touch ${lockfile}
        $supervisorctl $OPTIONS status
    fi
    return $RETVAL
}

stop() {
    echo -n $"Stopping $prog: "
    killproc -p ${pidfile} -d ${STOP_TIMEOUT} $supervisord
    RETVAL=$?
    echo
    [ $RETVAL -eq 0 ] && rm -rf ${lockfile} ${pidfile}
}

reload() {
    echo -n $"Reloading $prog: "
    LSB=1 killproc -p $pidfile $supervisord -HUP
    RETVAL=$?
    echo
    if [ $RETVAL -eq 7 ]; then
        failure $"$prog reload"
    else
        $supervisorctl $OPTIONS status
    fi
}

restart() {
    stop
    start
}

case "$1" in
    start)
        start
        ;;
    stop)
        stop
        ;;
    status)
        status -p ${pidfile} $supervisord
        RETVAL=$?
        [ $RETVAL -eq 0 ] && $supervisorctl $OPTIONS status
        ;;
    restart)
        restart
        ;;
    condrestart|try-restart)
        if status -p ${pidfile} $supervisord >&/dev/null; then
          stop
          start
        fi
        ;;
    force-reload|reload)
        reload
        ;;
    *)
        echo $"Usage: $prog {start|stop|restart|condrestart|try-restart|force-reload|reload}"
        RETVAL=2
esac

exit $RETVAL
_EOF_
    chmod +x /etc/init.d/supervisord
fi

if [ ! -f /etc/sysconfig/supervisord ]; then
    cat<<'_EOF_' >/etc/sysconfig/supervisord
# WARNING: change these wisely! for instance, adding -d, --nodaemon
# here will lead to a very undesirable (blocking) behavior
#OPTIONS="-c /etc/supervisord.conf"
PIDFILE=/var/run/supervisord/supervisord.pid

# Path to the supervisord binary
SUPERVISORD=/usr/local/bin/supervisord

# Path to the supervisorctl binary
SUPERVISORCTL=/usr/local/bin/supervisorctl

# How long should we wait before forcefully killing the supervisord process ?
#STOP_TIMEOUT=60

# Remove this if you manage number of open files in some other fashion
#ulimit -n 96000
_EOF_

mkdir -p /var/run/supervisord/
chown apache:apache /var/run/supervisord/
fi

if [ ! -f /etc/supervisor.conf ]; then  
    cat<<'_EOF_' >/etc/supervisor.conf
[unix_http_server]
file=/tmp/supervisor.sock
chmod=0777

[supervisord]
logfile=/var/app/support/logs/supervisord.log
logfile_maxbytes=0
logfile_backups=0
loglevel=warn
pidfile=/var/run/supervisord/supervisord.pid
nodaemon=false
nocleanup=true
user=apache

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[supervisorctl]
serverurl=unix:///tmp/supervisor.sock
_EOF_
fi

if [ ! -f /etc/supervisor.d/cosound-worker.conf ]; then
    mkdir -p /etc/supervisor.d
    # set maximum number of processes to number of vCPU cores
    vCPUs=$(grep ^processor /proc/cpuinfo | wc -l)
    cat<<_EOF_ >/etc/supervisor.d/cosound-worker.conf
[program:cosound-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/app/current/artisan queue:work database --queue="notification"
autostart=true
autorestart=true
;user=root
numprocs=$vCPUs
redirect_stderr=true
stdout_logfile=/var/app/current/storage/logs/cosound-worker.log
_EOF_
fi

