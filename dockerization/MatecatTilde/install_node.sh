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

# ----- Node.js
echo "Need Root for installing NodeJS"
sh -c 'echo "Got Root!"'

echo "Get Latest v10 Number..."
{
wget --output-document=node-updater.html https://nodejs.org/dist/latest-v10.x/

ARCH=$(uname -m)

if [ $ARCH = x86_64 ]
then
	grep -o '>node-v.*-linux-x64.tar.gz' node-updater.html > node-cache.txt 2>&1

	VER=$(grep -o 'node-v.*-linux-x64.tar.gz' node-cache.txt)
else
	grep -o '>node-v.*-linux-x86.tar.gz' node-updater.html > node-cache.txt 2>&1

	VER=$(grep -o 'node-v.*-linux-x86.tar.gz' node-cache.txt)
fi
rm ./node-cache.txt
rm ./node-updater.html
} # &> /dev/null

echo "Done"

DIR=$( cd "$( dirname $0 )" && pwd )

echo "Downloading latest v10 stable Version $VER..."
{
echo "wget https://nodejs.org/dist/latest-v10.x/$VER -O $DIR/$VER"
wget https://nodejs.org/dist/latest-v10.x/$VER -O $DIR/$VER
} # &> /dev/null

cd /usr/local && tar --strip-components 1 -xzf $DIR/$VER

rm $DIR/$VER

