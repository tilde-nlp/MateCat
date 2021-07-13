#!/bin/bash
# some parts taken from /home/matecat/cattool/support_scripts/installActivemq.sh
# error handling...
f () {
    errcode=$? # save the exit code as the first thing done in the trap function
    echo "error $errcode"
    echo "the command executing at the time of the error was"
    echo "$BASH_COMMAND"
    echo "on line ${BASH_LINENO[0]}"
    # do some error handling, cleanup, logging, notification
    # $BASH_COMMAND contains the command that was being executed at the time of the trap
    # ${BASH_LINENO[0]} contains the line number in the script of that command
    # exit the script or return to try again, etc.
    exit $errcode  # or use some other value or do return instead
}
trap f ERR

echo "Starting Apache..."
/etc/init.d/apache2 restart

while true; do
#    echo date " => Waiting for an infinite. More or less..."
    sleep 5
done
