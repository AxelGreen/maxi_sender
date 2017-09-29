#!/bin/bash
# drop current crontab
crontab -r
# initialize new crontab from template file
crontab /etc/sender4you/bash/crontab_template
# restart cron to apply changes
/etc/init.d/cron restart