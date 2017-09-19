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

function rhel_like {
    local patterns=("erlang" "postgres" "php7" "rabbitmq" "exim", "fail2ban")
    for pattern in ${patterns[@]}; do
        packages=$(rpm -qa | grep ${pattern})
        if [ -z "${packages}" ]; then
            continue
        fi
        for file in $(rpm -q --configfiles ${packages}); do
            if [ -f ${file} ]; then
                rm -f ${file}
            fi
        done
        rpm -e ${packages} 2>/dev/null
    done
}

function sudo_check {
    if [ "$EUID" -ne 0 ]; then
        echo "Please script as root"
        exit 1
    fi
}

sudo_check

managers=("apt-get" "yum")
for m in ${managers[@]}; do
    which $m > /dev/null 2>&1
    if [ $? -eq 0 ] && [ "$m" == "apt-get" ]; then
        debian_like
        break
    fi
    if [ $? -eq 0 ] && [ "$m" == "yum" ]; then
        rhel_like
        break
    fi
done