#!/bin/bash
# listed connection from all IPs
sed -i "s/^.*listen_addresses.*$/listen_addresses = '*'/" /etc/postgresql/9.6/main/postgresql.conf
# trust connections from central server. Don't ask password
echo "host    postgres        postgres        88.99.195.32/0  trust" >> /etc/postgresql/9.6/main/pg_hba.conf
# trust connections from localhost. Don't ask password
sed -i "s/^.*host.*all.*all.*127\.0\.0\.1.*md5.*$/host    all             all             127.0.0.1\/32            trust/" /etc/postgresql/9.6/main/pg_hba.conf
# TODO: initialize tables for statistics and maybe something else

# restart postgres to apply changes
/etc/init.d/postgresql restart