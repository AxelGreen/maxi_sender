#!/bin/bash
# stop mutator
pkill --full "^tail.*/var/log/exim4/mainlog$"
# clear folder with scripts and logs
rm -rf /etc/sender4you /var/log/sender4you