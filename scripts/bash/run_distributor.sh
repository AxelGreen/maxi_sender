#!/bin/bash

# connect to memcached
source /etc/sender4you/bash/membash.sh
MCSERVER="localhost"
MCPORT=11211

# get value of active_in_pool
ACTIVE=$(mc_get active_in_pool)
REG="0"
echo "${ACTIVE}"
if [[ ${ACTIVE} =~ $REG ]] # not active in pool
then
	exit 1;
fi

# get number of distributors running
DISTRIBUTORS_COUNT=$(pgrep --full --count "^php7\.0.*/etc/sender4you/distributor\.php$")

# check if distributor already running
if [ $DISTRIBUTORS_COUNT -ge 1 ]; then
	exit 1;
fi

nohup php7.0 /etc/sender4you/distributor.php 2>&1 1>/dev/null &
disown