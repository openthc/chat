#!/bin/bash -x
#
# Start Mattermost Server
#

set -o errexit
set -o nounset

f=$(readlink -f "$0")
d=$(dirname "$f")

cd "$d/mattermost/" || exit 1

action="${1:-start}"
case "$action" in
start)
	echo "START"

	nohup \
		./bin/mattermost \
		server \
		>"./mattermost.log" \
		2>&1 \
		&

	echo "pid:$!"

	;;

stop)
	echo "STOP"

	pid=$(pidof mattermost)
	if [ -n "$pid" ]
	then
		kill $pid
	else
		echo "No PID"
	fi

	;;

esac
