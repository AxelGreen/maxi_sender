#!/bin/bash

# name of file which contains commands
FILE=$(./file_rotate.sh insert.sql)

# check if file not empty
if [ $(wc -l $FILE | awk '{print $1}') -eq 0 ]; then
	echo "empty file"
	return
fi

echo "not empty"
# TODO: delete
return

# execute file
# TODO: execute file

# delete file
rm $FILE

# call api to tell that this server has something to download to main server
# TODO: call api