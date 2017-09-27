#!/bin/bash
# hide X-PHP-Originating-Script header
sed -i "s/^.*mail.add_x_header.*=.*$/mail.add_x_header = Off/" /etc/php/7.0/cli/php.ini
# hide information about php version
sed -i "s/^.*expose_php.*=.*$/expose_php = Off/" /etc/php/7.0/cli/php.ini
