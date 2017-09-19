#!/bin/bash

function debian_like {
    which add-apt-repository > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        apt-get install -y software-properties-common
    fi

    add-apt-repository --remove -y ppa:ondrej/php
    add-apt-repository --remove -y precise-pgdg
    add-apt-repository --remove -y testing

    local patterns=("erlang" "postgres" "php7" "rabbitmq" "exim" "fail2ban")
    for pattern in ${patterns[@]}; do
        apt-get --purge remove -y $(dpkg -l | grep ${pattern} | awk -F ' ' '{print $2}')
    done
}

function sudo_check {
    if [ "$EUID" -ne 0 ]; then
        echo "Please script as root"
        exit 1
    fi
}

sudo_check
debian_like