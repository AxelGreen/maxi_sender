#!/bin/bash -x

function debian_like {
    apt-get update
    which add-apt-repository > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        apt-get install -y software-properties-common
    fi

    # postgresql
    add-apt-repository -y "deb http://apt.postgresql.org/pub/repos/apt/ precise-pgdg main"
    wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -

    # rabbitmq
    wget https://packages.erlang-solutions.com/erlang-solutions_1.0_all.deb
    dpkg -i erlang-solutions_1.0_all.deb && rm erlang-solutions_1.0_all.deb

    echo "deb http://www.rabbitmq.com/debian/ testing main" | tee "/etc/apt/sources.list.d/rabbitmq.list"
    wget -O- https://www.rabbitmq.com/rabbitmq-release-signing-key.asc | apt-key add -

    # for php 7.0
    local version=$(awk '{print $3}' /etc/*-release)
    if [ "${version}" == "8" ]; then
        echo "deb http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list
        echo "deb-src http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list
        wget https://www.dotdeb.org/dotdeb.gpg
        apt-key add dotdeb.gpg && rm dotdeb.gpg
    else
        add-apt-repository -y ppa:ondrej/php
    fi

    apt-get update
    apt-get install -y fail2ban
    apt-get install -y postgresql-9.6 erlang erlang-nox rabbitmq-server exim4
    apt-get install -y --allow-unauthenticated php7.0 php7.0-opcache php7.0-mbstring php7.0-bcmath php7.0-zip php7.0-geoip php7.0-curl php7.0-json php7.0-pgsql php7.0-cli
}

function rhel_like {
    yum update -y
    which wget > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        yum install -y wget
    fi

    local centos6=$(cat /etc/centos-release | grep -i "CentOS release 6")
    if [ -n "${centos6}" ]; then
        # postgresql
        rpm -Uvh https://yum.postgresql.org/9.6/redhat/rhel-6-x86_64/pgdg-redhat96-9.6-3.noarch.rpm

        # rabbitmq
        wget https://www.rabbitmq.com/releases/rabbitmq-server/v3.6.9/rabbitmq-server-3.6.9-1.el6.noarch.rpm -O rabbitmq.rpm

        # php 7
        rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
        wget http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
        rpm -ivh epel-release-6-8.noarch.rpm
        rm -f epel-release-6-8.noarch.rpm
    else
        # postgresql
        rpm -Uvh https://yum.postgresql.org/9.6/redhat/rhel-7-x86_64/pgdg-centos96-9.6-3.noarch.rpm

        # rabbitmq
        wget https://www.rabbitmq.com/releases/rabbitmq-server/v3.6.9/rabbitmq-server-3.6.9-1.el7.noarch.rpm -O rabbitmq.rpm

        # php7
        rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
        rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
    fi

    # erlang
    wget https://packages.erlang-solutions.com/erlang-solutions-1.0-1.noarch.rpm
    rpm -Uvh erlang-solutions-1.0-1.noarch.rpm
    rm -f erlang-solutions-1.0-1.noarch.rpm

    rpm --import https://www.rabbitmq.com/rabbitmq-release-signing-key.asc

    yum update -y
    yum install -y postgresql96-server postgresql96 erlang exim fail2ban rabbitmq-server --skip-broken
    yum install -y php70w php70w-gd php70w-opcache php70w-mbstring php70w-bcmath php70w-common php70w-pgsql php70w-cli
    yum install -y rabbitmq.rpm
    rm -f rabbitmq.rpm
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