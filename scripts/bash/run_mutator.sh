#!/bin/bash

MUTATOR_COUNT=${pgrep --full --count "^tail.*/var/log/exim4/mainlog$"}
echo "$MUTATOR_COUNT"
