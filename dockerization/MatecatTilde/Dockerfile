FROM ubuntu:bionic

RUN export DEBIAN_FRONTEND=noninteractive
RUN ln -fs /usr/share/zoneinfo/Europe/Brussels /etc/localtime

ARG BRANCH
ARG RELATIVE_HOST_NAME="http://local.matecat.com/"
ARG JWT_KEY=""
ARG JWT_KEY_KEYCLOAK=""
ARG JWT_ISSUER_KEYCLOAK=""
ARG AUTH_REDIRECT="https://hugo.lv/lv/Account/Login?ReturnUrl="
ARG STORAGE_DIR="/var/www/matecat/storage/"
ARG BRANCH="dockerization"
ARG MT_BASE_URL=""
ARG MT_CLIENT_ID=""
ARG MT_APP_ID=""
ARG TM_BASE_URL=""
ARG TOKEN_REFRESH_URL=""
ARG TERM_BASE_URL=""
ARG SYNONYM_BASE_URL=""
ARG FILE_CONVERTER_BASE_URL="http://hugodevcode.tilde.lv:5000/"
ARG DEV_MODE=true
ARG DOCUMENT_WHITELIST=''
ARG ALLOW_CORS=true
ARG CORS_WHITELIST=''
ARG TM_BLACKLIST=''

RUN apt-get update
RUN apt-get -y full-upgrade
RUN apt-get -y install locales software-properties-common
RUN locale-gen en_US.UTF-8
ENV LANG='en_US.UTF-8' LANGUAGE='en_US:en' LC_ALL='en_US.UTF-8'
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/apache2
RUN apt-get update
RUN apt-get -y install apache2 php7.0 php7.0-mysql libapache2-mod-php7.0 php7.0-curl php7.0-json php7.0-xml php7.0-mcrypt php7.0-mbstring php7.0-zip php-xdebug wget mysql-server screen git
RUN rm -rf /var/www/matecat
RUN a2enmod rewrite filter deflate headers expires proxy_http.load

COPY ./MatecatTilde/run.sh /tmp/run.sh
COPY ./MatecatTilde/install_node.sh /tmp/install_node.sh
RUN chmod +x /tmp/run.sh
RUN chmod +x /tmp/install_node.sh
RUN /tmp/install_node.sh

# Prepare the environment
ENV PHP_POST_MAX_SIZE 1024M
ENV PHP_UPLOAD_MAX_FILESIZE 1024M
ENV PHP_MAX_MEMORY 4096M

ENV SERVICES_DIR "/etc/init.d"
ENV USER_OWNER "www-data"

RUN sed -i 's/short_open_tag = .*/short_open_tag = On/g' /etc/php/7.0/cli/php.ini
RUN sed -i 's/memory_limit = .*/memory_limit = 1024M/g' /etc/php/7.0/cli/php.ini

WORKDIR "/var/www"
RUN git clone https://github.com/tilde-nlp/MateCat.git matecat
WORKDIR "matecat"
RUN git fetch --all
RUN git checkout $BRANCH

RUN cp INSTALL/matecat-vhost.conf.sample /etc/apache2/sites-available/matecat-vhost.conf
RUN sed -i 's|@@@path@@@|/var/www/matecat/public|g' /etc/apache2/sites-available/matecat-vhost.conf
RUN sed -i 's|#ServerName www.example.com|ServerName DONT-USE-ME-DOT-COM|g' /etc/apache2/sites-enabled/000-default.conf
RUN a2ensite matecat-vhost.conf
RUN a2dissite 000-default
RUN apache2ctl restart

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN php composer.phar install
RUN cp inc/config.ini.sample inc/config.ini
RUN sed -i "s|@@@relative_host_name@@@|$RELATIVE_HOST_NAME|g" inc/config.ini
RUN sed -i "s|@@@jwt_key@@@|$JWT_KEY|g" inc/config.ini
RUN sed -i "s|@@@jwt_key_keycloak@@@|$JWT_KEY_KEYCLOAK|g" inc/config.ini
RUN sed -i "s|@@@jwt_issuer_keycloak@@@|$JWT_ISSUER_KEYCLOAK|g" inc/config.ini
RUN sed -i "s|@@@auth_redirect@@@|$AUTH_REDIRECT|g" inc/config.ini
RUN sed -i "s|@@@storage_dir@@@|$STORAGE_DIR|g" inc/config.ini
RUN sed -i "s|@@@mt_base_url@@@|$MT_BASE_URL|g" inc/config.ini
RUN sed -i "s|@@@mt_client_id@@@|$MT_CLIENT_ID|g" inc/config.ini
RUN sed -i "s|@@@mt_app_id@@@|$MT_APP_ID|g" inc/config.ini
RUN sed -i "s|@@@tm_base_url@@@|$TM_BASE_URL|g" inc/config.ini
RUN sed -i "s|@@@token_refresh_url@@@|$TOKEN_REFRESH_URL|g" inc/config.ini
RUN sed -i "s|@@@term_base_url@@@|$TERM_BASE_URL|g" inc/config.ini
RUN sed -i "s|@@@file_converter_base_url@@@|$FILE_CONVERTER_BASE_URL|g" inc/config.ini
RUN sed -i "s|@@@synonym_base_url@@@|$SYNONYM_BASE_URL|g" inc/config.ini
RUN sed -i "s|@@@dev_mode@@@|$DEV_MODE|g" inc/config.ini
RUN sed -i "s|@@@document_whitelist@@@|$DOCUMENT_WHITELIST|g" inc/config.ini
RUN sed -i "s|@@@allow_cors@@@|$ALLOW_CORS|g" inc/config.ini
RUN sed -i "s|@@@cors_whitelist@@@|$CORS_WHITELIST|g" inc/config.ini
RUN sed -i "s|@@@tm_blacklist@@@|$TM_BLACKLIST|g" inc/config.ini
RUN cp inc/task_manager_config.ini.sample inc/task_manager_config.ini

RUN sed -i "s/FILTERS_ADDRESS.*/FILTERS_ADDRESS = http:\/\/localhost:8732/g" inc/config.ini
RUN sed -i "s/FILTERS_MASHAPE_KEY.*/FILTERS_MASHAPE_KEY = /g" inc/config.ini
RUN chown -R www-data:www-data $STORAGE_DIR
RUN chmod -R 777 $STORAGE_DIR

WORKDIR "/var/www"
CMD ["/tmp/run.sh"]
