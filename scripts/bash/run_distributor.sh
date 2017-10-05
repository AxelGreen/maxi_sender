#!/bin/bash

SENDERS_COUNT=$(pgrep --full --count "^php7\.0.*/etc/sender4you/distributor\.php$")
MAX_COUNT=1

# check if mutator already running
if [ $SENDERS_COUNT -ge 1 ]; then
	exit 1;
fi

nohup php7.0 /etc/sender4you/distributor.php 2>&1 1>/dev/null &
disown