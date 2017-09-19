#!/bin/bash

# check if root running this script
if [ "$EUID" -ne 0 ]; then
    echo "Run script as root"
    exit 1
fi

which add-apt-repository > /dev/null 2>&1
if [ $? -ne 0 ]; then
    apt-get install -y software-properties-common
fi

# remove repositories
add-apt-repository --remove -y ppa:ondrej/php
add-apt-repository --remove -y precise-pgdg
add-apt-repository --remove -y testing

# purge soft
patterns=("erlang" "postgres" "php7" "rabbitmq" "exim" "fail2ban")
for pattern in ${patterns[@]}; do
    apt-get --purge remove -y $(dpkg -l | grep ${pattern} | awk -F ' ' '{print $2}')
done

# remove composer
rm -rf /usr/local/bin/composer