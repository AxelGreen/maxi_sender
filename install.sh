#!/bin/bash
# run deinstallation for all soft - to be sure that all is clear and new one
./soft/uninstall_soft.sh
# run installation
./soft/install_soft.sh
# run all configuration change files
./config/exim.sh
./config/postgres.sh
./config/fail2ban.sh
./config/rabbit.sh
# install php scripts
./soft/uninstall_scripts.sh
./soft/install_scripts.sh
# update cron
./config/cron.sh