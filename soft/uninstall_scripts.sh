#!/bin/bash
# stop mutator
pkill --full "^tail.*/var/log/exim4/mainlog$"
#stop send.php
pkill --full "^php7\.0.*/etc/sender4you/send\.php$"
#stop distributor.php
pkill --full "^php7\.0.*/etc/sender4you/distributor\.php$"
# clear folder with scripts and logs
rm -rf /etc/sender4you /var/log/sender4you