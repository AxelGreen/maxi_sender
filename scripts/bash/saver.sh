#!/bin/bash

# name of file which contains commands
FILE=$(/etc/sender4you/bash/file_rotate.sh /etc/sender4you/bash/insert)

# check if file not empty
if [ $(wc -l $FILE | awk '{print $1}') -eq 0 ]; then
	rm $FILE
	exit 1
fi

# execute file
su - postgres -c "psql --file=$FILE --dbname=postgres > /dev/null"

# delete file
rm $FILE

# call api to tell that this server has something to download to main server
wget --quiet --output-document=/dev/null http://api.sender4you.com/maxi/statisticsReady