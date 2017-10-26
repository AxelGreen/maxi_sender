#!/bin/bash

# output file
OUTPUT="/etc/sender4you/bash/insert"

# list of regex
MAIN_REGEX="^(.{25}) (.{6}-.{6}-.{2}) (\*\*|<=|=>|==) (.*)$"
START_REGEX="P=local.*id=([^@]+)@(.*)$"
SUCCESS_REGEX="T=remote_smtp"

# variables for pause bounces
PAUSE_REGEX=""
PAUSE_UPDATE=0 # timestamp when need to refresh PAUSE_REGEX
CURRENT_TIMESTAMP=0
PAUSE_MATCH=0

# temporary not used
DEFER_REGEX="T=remote_smtp.*defer \((-*[0-9]+)\): (.*)$"
BOUNCE_MAIN_REGEX="T=remote_smtp"
BOUNCE_REGEX=" F=<[^>]+>: (.*)$"

# parts of query
QUERY_START="INSERT INTO public.exim_logs (date, exim_id, action, message_id, host, error, defer) VALUES "
QUERY_END="
	ON CONFLICT (exim_id) DO UPDATE
		SET date = excluded.date,
			action = CASE WHEN (excluded.action = 1 AND public.exim_logs.action != 0) THEN public.exim_logs.action ELSE excluded.action END,
			error = CASE WHEN excluded.action = 1 THEN public.exim_logs.error ELSE excluded.error END,
			defer = CASE WHEN excluded.action != 1 THEN public.exim_logs.defer ELSE excluded.defer END;"
QUERY=""

# holds changeable part of query
DATE=""
EXIM_ID=""
ACTION=""
MESSAGE_ID="NULL"
HOST="NULL"
ERROR="NULL"
DEFER="NULL"

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
		DEFER="NULL"

		# save full log message for further processing
		LOG_MESSAGE="${BASH_REMATCH[4]//\'/\'\'}"

		# make actions based on action flag
		case "${BASH_REMATCH[3]}" in
			# send start
			"<=")
				# exit if log_message not contains id - message_id - without it  and "P=local" this is not our letter
				if ! [[ ${LOG_MESSAGE} =~ $START_REGEX ]]
				then
					continue
				fi
				ACTION="0"
				MESSAGE_ID="'${BASH_REMATCH[1]}'"
				HOST="'${BASH_REMATCH[2]}'"
				;;
			# defer
			"==")
				# exit if log_message not contains "T=remote_smtp defer" - not remote defer
#				if ! [[ ${LOG_MESSAGE} =~ $DEFER_REGEX ]]
#				then
#					continue
#				fi
				ACTION="1"
				DEFER="'$LOG_MESSAGE'"
				;;
			# success delivery
			"=>")
				# exit if log_message not contains T=remote_smtp - another transport was used so we not interested in this log
				if ! [[ ${LOG_MESSAGE} =~ $SUCCESS_REGEX ]]
				then
					continue
				fi
				ACTION="2"
				;;
			# bounce
			"**")
#				# exit if log_message not contains T=remote_smtp - another transport was used so we not interested in this log
#				if ! [[ ${LOG_MESSAGE} =~ $BOUNCE_MAIN_REGEX ]]
#				then
#					continue
#				fi
				# exit if log_message contains F=<>: - local bounce message
#				if [[ ${LOG_MESSAGE} =~ $BOUNCE_REGEX ]]
#				then
#					continue
#				fi
				ACTION="3"
				ERROR="'$LOG_MESSAGE'"

				# check maybe need to update PAUSE_REGEX
				CURRENT_TIMESTAMP="$(date +%s)"
				if [ ${CURRENT_TIMESTAMP} -ge ${PAUSE_UPDATE} ]; then
					# update PAUSE_REGEX
					PAUSE_REGEX="$(wget --quiet -qO- http://api.sender4you.com/maxi/pauseRegex)"
					PAUSE_UPDATE="$(($(date +%s)+3600))"
				fi

				PAUSE_MATCH=0
				# check if PAUSE_REGEX not empty
				if [ ! -z "$PAUSE_REGEX" ]; then

					# check this bounce message
					PAUSE_MATCH=$(grep --ignore-case --count --extended-regexp "$PAUSE_REGEX" <<< ${LOG_MESSAGE})

					if [ ${PAUSE_MATCH} -ge 1 ]; then
						# this bounce is one of pause messages. Call PHP script to put it into memcache
						php7.0 /etc/sender4you/pause.php "$EXIM_ID" "$LOG_MESSAGE"
					fi

				fi

				;;
		esac
		# generate full query
		QUERY="$QUERY_START($DATE, $EXIM_ID, $ACTION, $MESSAGE_ID, $HOST, $ERROR, $DEFER)$QUERY_END"
		# push it to file
		echo "$QUERY" >> "$OUTPUT"
	fi
done