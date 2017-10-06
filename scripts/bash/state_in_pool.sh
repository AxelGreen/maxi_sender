#!/bin/bash
# initialize state in pool
ACTIVE=0
# check input args. If first parameter equal to "1" - change active to 1
if [ "$1" = "1" ]; then
	ACTIVE=1
fi

echo "$ACTIVE"

source /etc/sender4you/bash/membash.sh
MCSERVER="localhost"
MCPORT=11211

mc_set active_in_pool 0 "$ACTIVE"