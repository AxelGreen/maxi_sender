#!/bin/bash

echo Installation start: $(date)

# check if root running this script
if [ "$EUID" -ne 0 ]; then
    echo "Run script as root"
    exit 1
fi

echo Soft installation
# run deinstallation for all soft - to be sure that all is clear and new one
./soft/uninstall_soft.sh
# run installation
./soft/install_soft.sh

echo Configuration
# run all configuration change files
./config/exim.sh
./config/postgres.sh
./config/fail2ban.sh
./config/rabbit.sh
./config/php.sh

echo Scripts
# remove all scripts
./soft/uninstall_scripts.sh
# install php scripts
./soft/install_scripts.sh

echo Configure
# run script to configure some soft and scripts: change configuration file for scripts (set passwords, etc), get dkims and domains.virtual for Exim, create db for Postgres
php7.0 /etc/sender4you/configure.php

echo Cron
# update cron
./config/cron.sh

echo Installation end: $(date)
echo Cleanup files
# cleanup installation files
./soft/cleanup.sh

# call api to tell that this installation complete
wget --quiet --output-document=/dev/null http://api.sender4you.com/maxi/finishInstall