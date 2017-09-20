#!/bin/bash
# make local config
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
# add Sender4you ip to ignore list - unlimited login attempts
sed -i "s/^.*ignoreip.*=.*$/ignoreip = 88.99.195.32/" /etc/fail2ban/jail.local
# restart fail2ban
/etc/init.d/fail2ban restart