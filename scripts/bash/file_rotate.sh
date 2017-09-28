#!/bin/bash
FILE=$1
NEW_FILE=${1}_$(date +"%Y-%m-%d_%H-%M")
cp $FILE $NEW_FILE
> $FILE
echo $NEW_FILE