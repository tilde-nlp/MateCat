#!/bin/bash

# echo "Node Linux Installer by www.github.com/taaem"
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

echo "Done"

echo "Installing..."
cd /usr/local && tar --strip-components 1 -xzf $DIR/$VER

rm $DIR/$VER

echo "Finished installing!"

