#!/bin/bash

# connect to memcached
source /etc/sender4you/bash/membash.sh
MCSERVER="localhost"
MCPORT=11211

# get value of active_in_pool
BROKEN=$(mc_get broken)
REG="1"
if [[ ${BROKEN} =~ $REG ]] # broken
then
	exit 1;
fi

nohup php7.0 /etc/sender4you/diagnostics.php 2>&1 1>/dev/null &
disown
