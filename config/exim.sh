#!/bin/bash
# main config - internet - send and receive emails throw SMTP
sed -i "s/^.*dc_eximconfig_configtype.*=.*$/dc_eximconfig_configtype='internet'/" /etc/exim4/update-exim4.conf.conf
# folder where all income email will be saved
sed -i "s/^.*dc_localdelivery.*=.*$/dc_localdelivery='\/var\/mail'/" /etc/exim4/update-exim4.conf.conf
# interfaces (IPs) which Exim will listen to. Empty value means that listen all interfaces
sed -i "s/^.*dc_local_interfaces.*=.*$/dc_local_interfaces=''/" /etc/exim4/update-exim4.conf.conf
# TODO: create /etc/exim4/domains.virtual file which contains all domains connected to this VPS (one per line)
# list of domains which Exim will consider as itself (get emails for this domains, not relay them)
sed -i "s/^.*dc_other_hostnames.*=.*$/dc_other_hostnames='\/etc\/exim4\/domains\.virtual'/" /etc/exim4/update-exim4.conf.conf
# exim will use split config files
sed -i "s/^.*dc_use_split_config.*=.*$/dc_use_split_config='true'/" /etc/exim4/update-exim4.conf.conf
# set retry config
sed "s/^.*\*.*\*.*$/vdsfsd/" /etc/exim4/conf.d/retry/30_exim4-config
