#!/bin/bash -x
# copy install log
cp /root/maxi_sender/install.log /var/log/sender4you/install.log
# remove all installation files
rm -rf /root/maxi_sender/
rm -rf /root/install_run.sh
rm -rf /root/install_run.log
rm -rf /etc/sender4you/bash/crontab_template
# purge git
apt-get purge -y git