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

MYSQL_ROOT_PWD="root"
SERVERNAME="local.matecat.com"
RELATIVE_HOST_NAME="http://local.matecat.com/"
JWT_KEY=""
JWT_KEY_KEYCLOAK=""
JWT_ISSUER_KEYCLOAK=""
AUTH_REDIRECT="https://hugo.lv/lv/Account/Login?ReturnUrl="
STORAGE_DIR="/var/www/matecat/storage/"
BRANCH="dockerization"
MT_BASE_URL=""
MT_CLIENT_ID=""
MT_APP_ID=""
TM_BASE_URL=""
TOKEN_REFRESH_URL=""
TERM_BASE_URL=""
SYNONYM_BASE_URL=""
FILE_CONVERTER_BASE_URL="http://hugodevcode.tilde.lv:5000/"
DEV_MODE=true

apt-get update
# ----- Apache2
apt-get install -y apache2
# remove mod_version https://stackoverflow.com/a/38818529
a2enmod rewrite filter deflate headers expires proxy_http.load
apache2ctl restart

# ----- PHP7.0
apt-add-repository ppa:ondrej/php -y
apt-get install -y php7.0 php7.0-mysql libapache2-mod-php7.0 php7.0-curl php7.0-json php7.0-xml php7.0-mcrypt php7.0-mbstring php7.0-zip php-xdebug wget mysql-server
apt-get install -y screen

# ----- Node.js
#apt-get install -y curl
#curl -sL https://deb.nodesource.com/setup_6.x | -E bash -
#apt-get install -y nodejs
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

sed -i 's/short_open_tag = .*/short_open_tag = On/g' /etc/php/7.0/cli/php.ini
sed -i 's/memory_limit = .*/memory_limit = 1024M/g' /etc/php/7.0/cli/php.ini
apache2ctl restart

apt-get -y install git

# delete contents. Git cant clone in to non-empty directory on repeated installs
cd /var/www

pwd
ls -la
git clone https://github.com/tilde-nlp/MateCat.git matecat
cd matecat
git fetch --all
git checkout $BRANCH

# Apache matecat vhost
cp INSTALL/matecat-vhost.conf.sample /etc/apache2/sites-available/matecat-vhost.conf
sed -i 's|@@@path@@@|/var/www/matecat/public|g' /etc/apache2/sites-available/matecat-vhost.conf
sed -i 's|#ServerName www.example.com|ServerName DONT-USE-ME-DOT-COM|g' /etc/apache2/sites-enabled/000-default.conf
a2ensite matecat-vhost.conf
a2dissite 000-default
apache2ctl restart

# download composer installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
# install composer
php composer-setup.php
# remove composer installer
php -r "unlink('composer-setup.php');"
# install matecat prereqs
php composer.phar install
cp inc/config.ini.sample inc/config.ini
sed -i "s|@@@relative_host_name@@@|$RELATIVE_HOST_NAME|g" inc/config.ini
sed -i "s|@@@jwt_key@@@|$JWT_KEY|g" inc/config.ini
sed -i "s|@@@jwt_key_keycloak@@@|$JWT_KEY_KEYCLOAK|g" inc/config.ini
sed -i "s|@@@jwt_issuer_keycloak@@@|$JWT_ISSUER_KEYCLOAK|g" inc/config.ini
sed -i "s|@@@auth_redirect@@@|$AUTH_REDIRECT|g" inc/config.ini
sed -i "s|@@@storage_dir@@@|$STORAGE_DIR|g" inc/config.ini
sed -i "s|@@@mt_base_url@@@|$MT_BASE_URL|g" inc/config.ini
sed -i "s|@@@mt_client_id@@@|$MT_CLIENT_ID|g" inc/config.ini
sed -i "s|@@@mt_app_id@@@|$MT_APP_ID|g" inc/config.ini
sed -i "s|@@@tm_base_url@@@|$TM_BASE_URL|g" inc/config.ini
sed -i "s|@@@token_refresh_url@@@|$TOKEN_REFRESH_URL|g" inc/config.ini
sed -i "s|@@@term_base_url@@@|$TERM_BASE_URL|g" inc/config.ini
sed -i "s|@@@file_converter_base_url@@@|$FILE_CONVERTER_BASE_URL|g" inc/config.ini
sed -i "s|@@@synonym_base_url@@@|$SYNONYM_BASE_URL|g" inc/config.ini
sed -i "s|@@@dev_mode@@@|$DEV_MODE|g" inc/config.ini
cp inc/task_manager_config.ini.sample inc/task_manager_config.ini

sed -i "s/FILTERS_ADDRESS.*/FILTERS_ADDRESS = http:\/\/localhost:8732/g" inc/config.ini
sed -i "s/FILTERS_MASHAPE_KEY.*/FILTERS_MASHAPE_KEY = /g" inc/config.ini
chown -R www-data:www-data $STORAGE_DIR


########### BOOT ANALYSIS
#pushd ./lib/Utils/Analysis
#/bin/bash restartAnalysis.sh
#popd


echo "Starting Apache..."
/etc/init.d/apache2 restart

# Make sure everything in local_storage can be accessed by Apache
chown -R ${USER_OWNER} $STORAGE_DIR
chmod -R 777 $STORAGE_DIR

apache2ctl restart
echo "Apache Started"

while true; do
#    echo date " => Waiting for an infinite. More or less..."
    sleep 5
done
