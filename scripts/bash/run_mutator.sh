#!/bin/bash

MUTATOR_COUNT=$(pgrep --full --count "^tail.*/var/log/exim4/mainlog$")

# check if mutator already running
if [ $MUTATOR_COUNT -gt 0 ]; then
	exit 1;
fi

# run mutator
nohup /etc/sender4you/bash/mutator.sh 2>>/var/log/sender4you/mutator.log  1>/dev/null &
disown