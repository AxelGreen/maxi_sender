#!/bin/bash

# name of file which contains commands
FILE=$(./file_rotate.sh insert)

# check if file not empty
if [ $(wc -l $FILE | awk '{print $1}') -eq 0 ]; then
	echo "empty file"
	rm $FILE
	exit 1
fi

echo "not empty"

# execute file
su - postgres -c "psql --file=$FILE --dbname=postgres"

# delete file
rm $FILE

# call api to tell that this server has something to download to main server
wget --quiet http://api.sender4you.com/maxi/statisticsReady