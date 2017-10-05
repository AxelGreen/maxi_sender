#!/bin/bash

SENDERS_COUNT=$(pgrep --full --count "^php7\.0.*/etc/sender4you/send\.php$")
MAX_COUNT=5

# check if enough senders running right now
if [ $SENDERS_COUNT -ge $MAX_COUNT ]; then
	exit 1;
fi
# count number of new senders
NEW_SENDERS=$((MAX_COUNT - SENDERS_COUNT))

for (( i=1; i<=$NEW_SENDERS; i++))
do
	nohup php7.0 /etc/sender4you/send.php 2>&1 1>/dev/null &
	disown
done