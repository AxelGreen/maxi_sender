#!/bin/bash

# check if root running this script
if [ "$EUID" -ne 0 ]; then
    echo "Run script as root"
    exit 1
fi

# run deinstallation for all soft - to be sure that all is clear and new one
./soft/uninstall_soft.sh
# run installation
./soft/install_soft.sh

# run all configuration change files
./config/exim.sh
./config/postgres.sh
./config/fail2ban.sh
./config/rabbit.sh

# remove all scripts
./soft/uninstall_scripts.sh
# install php scripts
./soft/install_scripts.sh

# run script to configure some soft and scripts: change configuration file for scripts (set passwords, etc), get dkims and domains.virtual for Exim, create db for Postgres

# update cron
./config/cron.sh