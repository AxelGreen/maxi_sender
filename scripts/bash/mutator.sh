#!/bin/bash

# output file
OUTPUT="/etc/sender4you/bash/insert"

# list of regex
MAIN_REGEX="^(.{25}) (.{6}-.{6}-.{2}) (\*\*|<=|=>|==) (.*)$"
START_REGEX=" P=local.*id=([^@]+)@(.*)$"
DEFER_REGEX=" T=remote_smtp defer \((-*[0-9]+)\): (.*)$"
SUCCESS_REGEX=" T=remote_smtp "
BOUNCE_MAIN_REGEX=" T=remote_smtp "
BOUNCE_REGEX=" F=<[^>]+>: (.*)$"

# parts of query
QUERY_START="INSERT INTO public.exim_logs (date, exim_id, action, message_id, host, error) VALUES "
QUERY_END=" ON CONFLICT (exim_id) DO UPDATE SET date = excluded.date, action = excluded.action, error = excluded.error;"
QUERY=""

# holds changeable part of query
DATE=""
EXIM_ID=""
ACTION=""
MESSAGE_ID=""
HOST=""
ERROR=""

# log message
LOG_MESSAGE=""

# holds current line of log file
LINE=""

# create output file
touch "$OUTPUT"
chown postgres:postgres "$OUTPUT"

# start following log file
tail --max-unchanged-stats=5 --pid=$$ --silent --lines=0 --follow=name --sleep-interval=0.1 --retry /var/log/exim4/mainlog | \
while read LINE
do
	# check if line satisfy main regex
	if [[ ${LINE} =~ $MAIN_REGEX ]]
	then

		# set date and exim_id
		DATE="'${BASH_REMATCH[1]}'"
		EXIM_ID="'${BASH_REMATCH[2]}'"

		# clear another variables
		MESSAGE_ID="NULL"
		HOST="NULL"
		ERROR="NULL"

		# save full log message for further processing
		LOG_MESSAGE="${BASH_REMATCH[4]}"

		# make actions based on action flag
		case "${BASH_REMATCH[3]}" in
			"<=")
				# exit if log_message not contains id - message_id - without it this is not our letter
				if ! [[ ${LOG_MESSAGE} =~ $START_REGEX ]]
				then
					continue
				fi
				ACTION="0"
				MESSAGE_ID="'${BASH_REMATCH[1]}'"
				HOST="'${BASH_REMATCH[2]}'"
				;;
			"==")
				# exit if log_message not contains "R=dnslookup T=smtp defer" - not remote defer
				if ! [[ ${LOG_MESSAGE} =~ $DEFER_REGEX ]]
				then
					continue
				fi
				ACTION="1"
				ERROR="'${BASH_REMATCH[1]}:${BASH_REMATCH[2],,}'"
				;;
			"=>")
				# exit if log_message not contains T=remote_smtp - another transport was used so we not interested in this log
				if ! [[ ${LOG_MESSAGE} =~ $SUCCESS_REGEX ]]
				then
					continue
				fi
				ACTION="2"
				;;
			"**")
				# exit if log_message not contains T=remote_smtp - another transport was used so we not interested in this log
				if ! [[ ${LOG_MESSAGE} =~ $BOUNCE_MAIN_REGEX ]]
				then
					continue
				fi
				# exit if log_message contains F=<>: - local bounce message
#				if [[ ${LOG_MESSAGE} =~ $BOUNCE_REGEX ]]
#				then
#					continue
#				fi
				ACTION="3"
				ERROR="'$LOG_MESSAGE'"
				;;
		esac
		# generate full query
		QUERY="$QUERY_START($DATE, $EXIM_ID, $ACTION, $MESSAGE_ID, $HOST, $ERROR)$QUERY_END"
		# push it to file
		echo "$QUERY" >> "$OUTPUT"
	fi
done