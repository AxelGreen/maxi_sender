#!/bin/bash -x
# create folders for soft and logs
mkdir /etc/sender4you /var/log/sender4you
# move scripts to folder
mv ./scripts/php/* /etc/sender4you
# run composer to install all dependencies
composer install --working-dir /etc/sender4you
# copy bash scripts
mv ./scripts/bash /etc/sender4you/