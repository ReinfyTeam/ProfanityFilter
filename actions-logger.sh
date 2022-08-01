#!/bin/env bash

log="logs/actions_$(date +%d_%m_%Y_%T).log"

if [[ ! -e "logs/" ]]; then
    mkdir $dir
fi

echo "===================================================================" >> $log
echo "As of $(date +%d-%m-%Y %T.%N) report data from actions" >> $log
echo "" > "actions.log"
echo "Action Exit Code: $?" >> $log
echo "Time Success: $(date +%T.%N)" >> $log
echo "=================================================================== >> $log
echo "Github User Agent Information [Hosted] >> $log
echo "Kernel Name: $(uname -s)" >> $log
echo "Kernel Release: $(uname -r)" >> "actions.log"
echo "Kernel Version: $(uname -v)" >> $log
echo "Nodename: $(uname -n)" >> $log
echo "Machine: $(uname -m)" >> $log
echo "Processor: $(uname -p)" >> $log
echo "Hardware Platform: $(uname -i)" >> $log
echo "Operating System: $(uname -o)" >> $log
echo "===================================================================" >> $log
