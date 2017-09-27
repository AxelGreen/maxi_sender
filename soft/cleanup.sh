#!/bin/bash -x
# copy install log
cp /root/maxi_sender/install.log /var/log/sender4you/install.log
# remove all installation files
rm -rf /root/maxi_sender/
# purge git
apt-get purge -y git