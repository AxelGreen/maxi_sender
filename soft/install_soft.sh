#!/bin/bash -x

# add repositories
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
version=$(awk '{print $3}' /etc/*-release)
if [ "${version}" == "8" ]; then
    echo "deb http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list
    echo "deb-src http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list
    wget https://www.dotdeb.org/dotdeb.gpg
    apt-key add dotdeb.gpg && rm dotdeb.gpg
else
    add-apt-repository -y ppa:ondrej/php
fi
# tests
#add-apt-repository -y "deb http://deb.debian.org/debian experimental main"
#apt-get -t experimental install libc6=2.25-0experimental3


# start installation
apt-get update
apt-get install -y fail2ban htop
apt-get install -y postgresql-9.6 erlang erlang-nox rabbitmq-server exim4
apt-get install -y --allow-unauthenticated php7.0 php7.0-opcache php7.0-mbstring php7.0-bcmath php7.0-zip php7.0-geoip php7.0-curl php7.0-json php7.0-pgsql php7.0-cli


#apt-get install -y --allow-unauthenticated php7.2 php7.2-opcache php7.2-mbstring php7.2-bcmath php7.2-zip php7.2-geoip php7.2-curl php7.2-json php7.2-pgsql php7.2-cli php7.2-cgi php7.2-xml libsodium18
#apt-get install -y --allow-unauthenticated php-pear
#apt-get install libcurl3-openssl-dev


# composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer