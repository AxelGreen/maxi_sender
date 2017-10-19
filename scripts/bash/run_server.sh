#!/bin/bash

# get number of distributors running
SERVERS_COUNT=$(pgrep --full --count "^php7\.0 --server 0\.0\.0\.0:80 --docroot /etc/sender4you/public/ /etc/sender4you/server\.php$")

# check if server already running
if [ $SERVERS_COUNT -ge 1 ]; then
	exit 1;
fi

nohup php7.0 --server 0.0.0.0:80 --docroot /etc/sender4you/public/ /etc/sender4you/server.php 2>>/var/log/sender4you/server.error.log 1>>/var/log/sender4you/server.access.log &
disown