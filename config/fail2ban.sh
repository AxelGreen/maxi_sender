#!/bin/bash
# make local config
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
# add Sender4you ip to ignore list - unlimited login attempts
sed -i "s/^.*ignoreip.*=.*$/ignoreip = 88.99.195.32/" /etc/fail2ban/jail.local
# increase bantime - for how long one IP will be blocked - -1 means forever
sed -i "s/^.*bantime.*=.*$/bantime = -1/" /etc/fail2ban/jail.local
# increase findtime - not more then maxretry login for this amount of time - not more then 3 failed attempts in 1 hour
sed -i "s/^.*findtime.*=.*$/findtime = 3600/" /etc/fail2ban/jail.local
# decrease maxretry - amound of failed attempts before ban
sed -i "s/^.*maxretry.*=.*$/maxretry = 3/" /etc/fail2ban/jail.local
# restart fail2ban
/etc/init.d/fail2ban restart