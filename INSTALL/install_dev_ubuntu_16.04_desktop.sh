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

MYSQL_ROOT_PWD="matecatRuuc"
MATECAT_USER="dark"
# SERVERNAME must match with google-s Authorized origins and Authorized redirect URIs
SERVERNAME="local.matecat.com"
RELATIVE_HOST_NAME="http://local.matecat.com/"
JWT_KEY=""
AUTH_REDIRECT="https://hugo.lv/lv/Account/Login?ReturnUrl="
STORAGE_DIR="/home/dark/cattool/storage/"
BRANCH="code-merge"
MT_BASE_URL=""
MT_CLIENT_ID=""
MT_APP_ID=""
TM_BASE_URL=""

sudo apt-get update
# ----- Apache2
sudo apt-get install -y apache2
# remove mod_version https://stackoverflow.com/a/38818529
sudo a2enmod rewrite filter deflate headers expires proxy_http.load
sudo apache2ctl restart

# ----- Mysql 5.7
echo "mysql-server mysql-server/root_password password $MYSQL_ROOT_PWD" | sudo debconf-set-selections
echo "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PWD" | sudo debconf-set-selections
sudo apt-get install -y mysql-server mysql-client
# set sql-mode to default for mysql<= 5.7.4 otherwise msql dump import fails with
# ERROR 1364 (HY000) at line 1189: Field 'id_dqf_project' doesn't have a default value
sudo sed -i '/sql-mode/d' /etc/mysql/mysql.conf.d/mysqld.cnf
sudo sed -i '/\[mysqld\]/a sql-mode = NO_ENGINE_SUBSTITUTION' /etc/mysql/mysql.conf.d/mysqld.cnf
sudo systemctl restart mysql.service

# ----- PHP7.0
# sudo apt-add-repository ppa:ondrej/php -y
# Use php7.0 in place of php5.0
# Devs are ignoring answars about compatibility with php7.0 https://github.com/matecat/MateCat/issues/1118
# added php7.0-xml and php7.0-mcrypt [composer error: the requested PHP extension xml & mcrypt is missing from your system]
# added php7.0-mbstring [Call to undefined function mb_substr() in /home/matecat/cattool/lib/Model/Users/UserStruct.php:67]
# added php7.0-zip [Class 'ZipArchive' not found]
sudo apt-get install -y php7.0 php7.0-mysql libapache2-mod-php7.0 php7.0-curl php7.0-json php7.0-xml php7.0-mcrypt php7.0-mbstring php7.0-zip php-xdebug
sudo apt-get install -y screen

# ----- ActiveMQ 5.11.3
echo "Installing ActiveMq\n"
sudo apt-get install -y openjdk-8-jdk
#sudo apt-get install -y openjdk-8-jre
wget https://archive.apache.org/dist/activemq/5.11.3/apache-activemq-5.11.3-bin.tar.gz
sudo tar xzf apache-activemq-5.11.3-bin.tar.gz -C /opt/ && rm apache-activemq-5.11.3-bin.tar.gz
#sudo mv apache-activemq-5.11.3 /opt
sudo ln -sf /opt/apache-activemq-5.11.3/ /opt/activemq
sudo adduser -system activemq
sudo sed -i "s#activemq:/bin/false#activemq:/bin/bash#g" /etc/passwd
sudo chown -R activemq: /opt/apache-activemq-5.11.3/
sudo ln -sf /opt/activemq/bin/activemq /etc/init.d/

# ActiveMQ as systemd service
sudo bash <<EOF
echo "[Unit]" > /etc/systemd/system/activemq.service
echo "Description=Apache ActiveMQ" >> /etc/systemd/system/activemq.service
echo "After=network-online.target" >> /etc/systemd/system/activemq.service
echo "[Service]" >> /etc/systemd/system/activemq.service
echo "Type=forking" >> /etc/systemd/system/activemq.service
echo "PIDFile=/opt/activemq/data/activemq.pid" >> /etc/systemd/system/activemq.service
echo "WorkingDirectory=/opt/activemq/bin" >> /etc/systemd/system/activemq.service
echo "ExecStart=/etc/init.d/activemq start" >> /etc/systemd/system/activemq.service
echo "ExecStop=/etc/init.d/activemq stop" >> /etc/systemd/system/activemq.service
echo "Restart=on-abort" >> /etc/systemd/system/activemq.service
echo "User=activemq" >> /etc/systemd/system/activemq.service
echo "[Install]" >> /etc/systemd/system/activemq.service
echo "WantedBy=multi-user.target" >> /etc/systemd/system/activemq.service
EOF
sudo systemctl daemon-reload
sudo systemctl enable activemq.service || echo "Continue no matter what. ExitCode [$?]"


yes | sudo /etc/init.d/activemq create /etc/default/activemq || echo "Continue no matter what. ExitCode [$?]"
sudo chown root:nogroup /etc/default/activemq
sudo chmod 600 /etc/default/activemq
sudo sed -i 's/managementContext createConnector="false"/managementContext createConnector="true"/g' /opt/activemq/conf/activemq.xml
sudo ln -sf /etc/init.d/activemq /usr/bin/activemq
sudo systemctl start activemq.service

# ----- Redis
sudo apt-get install -y redis-server
sudo systemctl restart redis-server.service

# ----- Node.js
sudo apt-get install -y curl
curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -
sudo apt-get install -y nodejs

# TODO: check this, node should start server.js which is not downloaded yet!
# https://nodesource.com/blog/running-your-node-js-app-with-systemd-part-1/
# Node.js app as systemd service
sudo bash <<EOF
echo "[Unit]" > /etc/systemd/system/nodejs-server.service
echo "Description=NodeJS server.js" >> /etc/systemd/system/nodejs-server.service
echo "After=network-online.target" >> /etc/systemd/system/nodejs-server.service
echo "[Service]" >> /etc/systemd/system/nodejs-server.service
echo "Type=simple" >> /etc/systemd/system/nodejs-server.service
echo "PIDFile=/opt/activemq/data/activemq.pid" >> /etc/systemd/system/nodejs-server.service
echo "ExecStart=/usr/bin/node /home/$MATECAT_USER/cattool/nodejs/server.js" >> /etc/systemd/system/nodejs-server.service
echo "Restart=on-failure" >> /etc/systemd/system/nodejs-server.service
echo "[Install]" >> /etc/systemd/system/nodejs-server.service
echo "WantedBy=multi-user.target" >> /etc/systemd/system/nodejs-server.service
EOF
sudo systemctl daemon-reload
sudo systemctl enable nodejs-server.service || echo "Continue no matter what. ExitCode [$?]"
	# start Node.js at boot
	#sudo crontab -l > mycron || echo "^ Safe to ignore \"no crontab for root\""
	# remove activemq cron task(s) on repeated installs
	#sed -i '/screen/d' mycron
	# add entry
	#echo "@reboot screen -d -m -S 'node' node /home/$MATECAT_USER/cattool/nodejs/server.js" >> mycron
	#sudo crontab mycron
	#rm mycron

sudo sed -i 's/short_open_tag = .*/short_open_tag = On/g' /etc/php/7.0/cli/php.ini
sudo sed -i 's/memory_limit = .*/memory_limit = 1024M/g' /etc/php/7.0/cli/php.ini
sudo apache2ctl restart


# ----- MateCat
id -u "$MATECAT_USER" || sudo adduser --disabled-password --gecos "" $MATECAT_USER
sudo apt-get -y install git

# delete contents. Git cant clone in to non-empty directory on repeated installs
sudo rm -rf /home/$MATECAT_USER/cattool
sudo -i -u $MATECAT_USER git clone git@github.com:tilde-nlp/MateCat.git cattool
sudo -u $MATECAT_USER -H sh -c "cd /home/$MATECAT_USER/cattool; git fetch --all"
sudo -u $MATECAT_USER -H sh -c "cd /home/$MATECAT_USER/cattool; git checkout $BRANCH"
mysql -u root -p$MYSQL_ROOT_PWD < /home/$MATECAT_USER/cattool/lib/Model/matecat.sql
mysql -u root -p$MYSQL_ROOT_PWD < /home/$MATECAT_USER/cattool/INSTALL/17-06-2018_user_email_alter.sql
mysql -u root -p$MYSQL_ROOT_PWD < /home/$MATECAT_USER/cattool/INSTALL/20-06-2018_user_job_segment_create.sql
mysql -u root -p$MYSQL_ROOT_PWD < /home/$MATECAT_USER/cattool/INSTALL/11-07-2018_add_save_type.sql
mysql -u root -p$MYSQL_ROOT_PWD < /home/$MATECAT_USER/cattool/INSTALL/29-07-2018_alter_projects.sql

# Apache matecat vhost
sudo cp /home/$MATECAT_USER/cattool/INSTALL/matecat-vhost.conf.sample /etc/apache2/sites-available/matecat-vhost.conf
sudo sed -i "s/@@@path@@@/\/home\/$MATECAT_USER\/cattool/g" /etc/apache2/sites-available/matecat-vhost.conf
sudo sed -i -- "s/localhost/$SERVERNAME/g" /etc/apache2/sites-available/matecat-vhost.conf
sudo sed -i -- "s/#ServerName www.example.com/ServerName DONT-USE-ME-DOT-COM/g" /etc/apache2/sites-enabled/000-default.conf
sudo a2ensite matecat-vhost.conf
sudo apache2ctl restart

# download composer installer
sudo -u $MATECAT_USER -H sh -c "cd /home/$MATECAT_USER/cattool; php -r \"copy('https://getcomposer.org/installer', 'composer-setup.php');\""
# install composer
sudo -u $MATECAT_USER -H sh -c "cd /home/$MATECAT_USER/cattool;php /home/$MATECAT_USER/cattool/composer-setup.php"
# remove composer installer
sudo -u $MATECAT_USER -H sh -c "cd /home/$MATECAT_USER/cattool;php -r \"unlink('composer-setup.php');\""
# install matecat prereqs
sudo -u $MATECAT_USER -H sh -c "cd /home/$MATECAT_USER/cattool;php composer.phar install"
sudo -u $MATECAT_USER -H sh -c "cp /home/$MATECAT_USER/cattool/inc/config.ini.sample /home/$MATECAT_USER/cattool/inc/config.ini"
sudo sed -i "s|@@@relative_host_name@@@|$RELATIVE_HOST_NAME|g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo sed -i "s|@@@jwt_key@@@|$JWT_KEY|g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo sed -i "s|@@@auth_redirect@@@|$AUTH_REDIRECT|g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo sed -i "s|@@@storage_dir@@@|$STORAGE_DIR|g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo sed -i "s|@@@mt_base_url@@@|$MT_BASE_URL|g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo sed -i "s|@@@mt_client_id@@@|$MT_CLIENT_ID|g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo sed -i "s|@@@mt_app_id@@@|$MT_APP_ID|g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo sed -i "s|@@@tm_base_url@@@|$TM_BASE_URL|g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo -u $MATECAT_USER -H sh -c "cp /home/$MATECAT_USER/cattool/inc/task_manager_config.ini.sample /home/$MATECAT_USER/cattool/inc/task_manager_config.ini"

# DEV_ONLY (this is only necessary for development build)
sudo -u $MATECAT_USER -H sh -c "cd /home/$MATECAT_USER/cattool/vue_src;npm install"

# Configure Node.js server app
sudo -u $MATECAT_USER -H sh -c "cp /home/$MATECAT_USER/cattool/nodejs/config.ini.sample /home/$MATECAT_USER/cattool/nodejs/config.ini"
sudo -u $MATECAT_USER -H sh -c "cd /home/$MATECAT_USER/cattool/nodejs;npm install"
sudo systemctl start nodejs-server.service

# ----- Configure database address (optional)
# ----- Turning on the analysis daemon
sudo -u $MATECAT_USER -H sh -c "echo '25' > /home/$MATECAT_USER/cattool/daemons/.num_processes"
sudo /bin/bash /home/$MATECAT_USER/cattool/daemons/restartAnalysis.sh
sudo crontab -l > mycron || echo "^ Safe to ignore \"no crontab for root\""
# remove restartAnalysis cron task(s) on repeated installs
sed -i '/restartAnalysis/d' mycron
# echo new cron into cron file
echo "@reboot /bin/bash /home/$MATECAT_USER/cattool/daemons/restartAnalysis.sh" >> mycron
# install new cron file
sudo crontab mycron
sudo rm mycron
sudo chown -R www-data $STORAGE_DIR
# ----- Matecat done

# ----- Google auth
sudo -u $MATECAT_USER -H sh -c "cp /home/$MATECAT_USER/cattool/inc/oauth_config.ini.sample /home/$MATECAT_USER/cattool/inc/oauth_config.ini"
# create empty file, because matecat tries to open in to save encryption key
sudo -u $MATECAT_USER -H sh -c "touch /home/$MATECAT_USER/cattool/inc/oauth-token-key.txt"
# repeat chown on [storage] directory - matecat can't write logs (log.txt)
sudo chown www-data:www-data /home/$MATECAT_USER/cattool/inc/oauth-token-key.txt


# ----- FILTERS
sudo apt-get -y install maven openjdk-8-jdk
# ----- JAVA8 on 14.04
#sudo add-apt-repository ppa:openjdk-r/ppa
#sudo apt-get update
#sudo apt-get install openjdk-8-jdk
#sudo update-java-alternatives -s java-1.8.0-openjdk-amd64

# delete contents. Git cant clone in to non-empty directory on repeated installs
sudo rm -rf ~/okapi
git clone https://bitbucket.org/okapiframework/okapi.git
cd okapi/
mvn clean install -DskipTests

git clone https://github.com/matecat/MateCat-Filters.git
cd MateCat-Filters/filters
mvn clean package -DskipTests

# copy files to destination folder for filter service
sudo mkdir -p /opt/filters
sudo cp target/filters-1.2.3.jar /opt/filters/
sudo cp src/main/resources/config.sample.properties /opt/filters/config.properties

#cd target
#cp ../src/main/resources/config.sample.properties config.properties
#java -cp ".:filters-1.2.3.jar" com.matecat.converter.Main

# MateCat filter as systemd service
sudo bash <<EOF
echo "[Unit]" > /etc/systemd/system/matecat-filter.service
echo "Description=Matecat filter service" >> /etc/systemd/system/matecat-filter.service
echo "[Service]" >> /etc/systemd/system/matecat-filter.service
echo "WorkingDirectory=/opt/filters/" >> /etc/systemd/system/matecat-filter.service
echo "ExecStart=/usr/bin/java -cp \".:filters-1.2.3.jar\" com.matecat.converter.Main" >> /etc/systemd/system/matecat-filter.service
echo "Restart=always" >> /etc/systemd/system/matecat-filter.service
echo "RestartSec=10" >> /etc/systemd/system/matecat-filter.service
echo "SyslogIdentifier=Matecat-Filter" >> /etc/systemd/system/matecat-filter.service
echo "[Install]" >> /etc/systemd/system/matecat-filter.service
echo "WantedBy=multi-user.target" >> /etc/systemd/system/matecat-filter.service
EOF

sudo systemctl daemon-reload
sudo systemctl enable matecat-filter.service
sudo systemctl start matecat-filter.service

sudo sed -i "s/FILTERS_ADDRESS.*/FILTERS_ADDRESS = http:\/\/localhost:8732/g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo sed -i "s/FILTERS_MASHAPE_KEY.*/FILTERS_MASHAPE_KEY = /g" /home/$MATECAT_USER/cattool/inc/config.ini
sudo chown -R www-data:$MATECAT_USER $STORAGE_DIR



