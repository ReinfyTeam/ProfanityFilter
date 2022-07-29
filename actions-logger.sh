if [ -e "actions.log" ]; 
then
   rm "actions.log";
fi
echo =================================================================== > "actions.log"
echo As of $(date +"%d-%m-%Y %T.%N") report data from actions > "actions.log"
echo "" > "actions.log"
echo Action Exit Code: $? > "actions.log"
echo Time Success: $(date +%T.%N) > "actions.log"
echo =================================================================== > "actions.log"
echo User Agent Information (Hosted) > "actions.log"
echo Kernel Name: $(uname -s) > "actions.log"
echo Kernel Release: $(uname -r) > "actions.log"
echo Kernel Version: $(uname -v) > "actions.log"
echo Nodename: $(uname -n) > "actions.log"
echo Machine: $(uname -m) > "actions.log"
echo Processor: $(uname -p) > "actions.log"
echo Hardware Platform: $(uname -i) > "actions.log"
echo Operating System: $(uname -o) > "actions.log"
echo =================================================================== > "actions.log"
echo As of $(date +"%d-%m-%Y"), What files are included in actions? > "actions.log"
if ! command -v tree &> /dev/null
then
    echo "tree is not installed. Installing..."
    sudo apt-get install tree
    echo "tree command is installed."
fi
tree --charset X > "actions.log"
