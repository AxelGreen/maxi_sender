#!/bin/bash
# main config - internet - send and receive emails throw SMTP
sed -i "s/^.*dc_eximconfig_configtype.*=.*$/dc_eximconfig_configtype='internet'/" /etc/exim4/update-exim4.conf.conf
# folder where all income email will be saved
sed -i "s/^.*dc_localdelivery.*=.*$/dc_localdelivery='mail_spool'/" /etc/exim4/update-exim4.conf.conf
# interfaces (IPs) which Exim will listen to. Empty value means that listen all interfaces
sed -i "s/^.*dc_local_interfaces.*=.*$/dc_local_interfaces=''/" /etc/exim4/update-exim4.conf.conf
# list of domains which Exim will consider as itself (get emails for this domains, not relay them)
sed -i "s/^.*dc_other_hostnames.*=.*$/dc_other_hostnames='\/etc\/exim4\/domains.virtual'/" /etc/exim4/update-exim4.conf.conf
# exim will use split config files
sed -i "s/^.*dc_use_split_config.*=.*$/dc_use_split_config='true'/" /etc/exim4/update-exim4.conf.conf
# set retry config. one minute after error occurred, 6 minutes after error occurred, 31 minute after error occurred
sed -i "s/^.*\*.*\*.*$/* * G,35m,1m,5;/" /etc/exim4/conf.d/retry/30_exim4-config
# change interval between each start of queue runner - process with check maybe need to send some previously queued emails
sed -i "s/^.*QUEUEINTERVAL.*$/QUEUEINTERVAL='5m'/" /etc/default/exim4
# change set of information printed to log
sed -i "s/^.*MAIN_LOG_SELECTOR == .*$/MAIN_LOG_SELECTOR == MAIN_LOG_SELECTOR -all/" /etc/exim4/conf.d/main/90_exim4-config_log_selector
# change time format for log entries - log with timezone
sed -i "/log_timezone/,+1d" /etc/exim4/conf.d/main/90_exim4-config_log_selector
echo "log_timezone = true" >> /etc/exim4/conf.d/main/90_exim4-config_log_selector
# set maximum amount of simultaneously queues running
sed -i "/queue_run_max/,+1d" /etc/exim4/conf.d/main/02_exim4-config_options
echo "queue_run_max = 50" >> /etc/exim4/conf.d/main/02_exim4-config_options
# set maximum amount of simultaneously sending letters
sed -i "/remote_max_parallel/,+1d" /etc/exim4/conf.d/main/02_exim4-config_options
echo "remote_max_parallel = 100" >> /etc/exim4/conf.d/main/02_exim4-config_options
# split spool files to separate directories
sed -i "/split_spool_directory/,+1d" /etc/exim4/conf.d/main/02_exim4-config_options
echo "split_spool_directory = true" >> /etc/exim4/conf.d/main/02_exim4-config_options
# split spool files to separate directories
sed -i "/write_rejectlog/,+1d" /etc/exim4/conf.d/main/02_exim4-config_options
echo "write_rejectlog = false" >> /etc/exim4/conf.d/main/02_exim4-config_options
# ignore bounce messages - newer try to resend them
sed -i "s/^.*MAIN_IGNORE_BOUNCE_ERRORS_AFTER.*=.*$/MAIN_IGNORE_BOUNCE_ERRORS_AFTER = 0m/" /etc/exim4/conf.d/main/02_exim4-config_options
# how long to keep frozen defer messages before send
sed -i "s/^.*MAIN_TIMEOUT_FROZEN_AFTER.*=.*$/MAIN_TIMEOUT_FROZEN_AFTER = 1h/" /etc/exim4/conf.d/main/02_exim4-config_options
# disable sending letters about freezing letters
sed -i -r "s/^(.*freeze_tell.*=.*)$/#\1/" /etc/exim4/conf.d/main/02_exim4-config_options
# stop writing separate logs for each message - files from folder /var/spool/exim_incoming/msglog/
sed -i "/no_message_logs/,+1d" /etc/exim4/conf.d/main/02_exim4-config_options
echo "no_message_logs" >> /etc/exim4/conf.d/main/02_exim4-config_options
# redirect income letters to /dev/null
# address_file transport
sed -i "/^.*file.*=.*$/,+1d" /etc/exim4/conf.d/transport/30_exim4-config_address_file
echo "  file = /dev/null" >> /etc/exim4/conf.d/transport/30_exim4-config_address_file
# mail_spool transport
sed -i "/^.*file.*=.*$/,+1d" /etc/exim4/conf.d/transport/30_exim4-config_mail_spool
echo "  file = /dev/null" >> /etc/exim4/conf.d/transport/30_exim4-config_mail_spool
# rewrite all income letters to mail@${domain} email, where ${domain} - domain from original to_email
echo "* \"\${if ! eq {\$sender_host_address}{}{mail@\${domain}}fail}\"" > /etc/exim4/conf.d/rewrite/10_exim4-config_mail
# disable received header
sed -i "/received_header_text/,+1d" /etc/exim4/conf.d/main/02_exim4-config_options
echo "received_header_text = " >> /etc/exim4/conf.d/main/02_exim4-config_options
# change helo data
sed -i "/REMOTE_SMTP_HELO_DATA/,+1d" /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
echo "REMOTE_SMTP_HELO_DATA = \$sender_address_domain" >> /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
# dkim
# domain
sed -i "/DKIM_DOMAIN/,+1d" /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
echo "DKIM_DOMAIN = \$sender_address_domain" >> /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
# file
sed -i "/DKIM_FILE/,+1d" /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
echo "DKIM_FILE = /etc/exim4/dkim/\$sender_address_domain.pem" >> /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
# private key
sed -i "/DKIM_PRIVATE_KEY/,+1d" /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
echo "DKIM_PRIVATE_KEY = \${if exists{DKIM_FILE}{DKIM_FILE}{0}}" >> /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
# selector
sed -i "/DKIM_SELECTOR/,+1d" /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
echo "DKIM_SELECTOR = default" >> /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
# canon - type of dkim check. if canon set to relaxed - check will ignore whitespaces etc, with simple canon - all characters will be included to check
sed -i "/DKIM_CANON/,+1d" /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
echo "DKIM_CANON = relaxed" >> /etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
# restart exim to apply changes
/etc/init.d/exim4 restart