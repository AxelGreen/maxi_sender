#!/bin/bash

# stop services
/etc/init.d/exim4 stop
/etc/init.d/fail2ban stop
/etc/init.d/postgresql stop
/etc/init.d/rabbitmq-server stop

which add-apt-repository > /dev/null 2>&1
if [ $? -ne 0 ]; then
    apt-get install -y software-properties-common
fi

# remove repositories
add-apt-repository --remove -y ppa:ondrej/php
add-apt-repository --remove -y precise-pgdg
add-apt-repository --remove -y testing

# purge soft
patterns=("erlang" "postgres" "php7" "rabbitmq" "exim" "fail2ban" "htop" "git")
for pattern in ${patterns[@]}; do
    apt-get --purge remove -y $(dpkg -l | grep ${pattern} | awk -F ' ' '{print $2}')
done
apt-get autoremove

# remove composer
rm -rf /usr/local/bin/composer

# clear crontab
crontab -r