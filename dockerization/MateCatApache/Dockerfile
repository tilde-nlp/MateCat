FROM ubuntu:bionic

RUN export DEBIAN_FRONTEND=noninteractive
RUN ln -fs /usr/share/zoneinfo/Europe/Brussels /etc/localtime

RUN apt-get update
RUN apt-get -y full-upgrade

RUN apt-get -y install locales software-properties-common
RUN locale-gen en_US.UTF-8
ENV LANG='en_US.UTF-8' LANGUAGE='en_US:en' LC_ALL='en_US.UTF-8'
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/apache2

RUN apt-get -y --fix-missing install ssh-client vim locate iputils-ping monit git curl wget net-tools tree \
    apache2 apache2-dev libapache2-mod-php5.6 php5.6 php5.6-json php5.6-curl php5.6-xdebug php5.6-mysql \
    php5.6-xml php5.6-mbstring php5.6-dev php5.6-mcrypt php5.6-redis \
    php5.6-zip mysql-client libzip-dev openssh-server psmisc screen dstat \
    traceroute whois libaio1 perl perl-base perl-modules \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# SSH Server
RUN mkdir /var/run/sshd
RUN echo 'root:root' | chpasswd
RUN sed -i 's/PermitRootLogin without-password/PermitRootLogin yes/' /etc/ssh/sshd_config
# SSH login fix. Otherwise user is kicked off after login
RUN sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd
ENV NOTVISIBLE "in users profile"
RUN echo "export VISIBLE=now" >> /etc/profile

# Prepare the environment
ENV PHP_POST_MAX_SIZE 1024M
ENV PHP_UPLOAD_MAX_FILESIZE 1024M
ENV PHP_MAX_MEMORY 4096M

ENV SERVICES_DIR "/etc/init.d"
ENV USER_OWNER "www-data"
ENV MATECAT_HOME "/var/www/matecat"

# Copy matecat source code
COPY matecat /var/www/matecat

# Make sure there's a matecat/local_storage folder
RUN mkdir -p /var/www/matecat/local_storage

COPY ./app_configs/config.ini /tmp/config.ini
COPY ./app_configs/node_config.ini /tmp/node_config.ini
COPY ./app_configs/Error_Mail_List.ini /tmp/Error_Mail_List.ini

# Google OAuth
COPY ./app_configs/oauth_config.ini /tmp/oauth_config.ini

COPY ./app_configs/task_manager_config.ini /tmp/task_manager_config.ini

# Node.js
COPY ./node-install.sh /tmp/node-install.sh
RUN chmod +x /tmp/node-install.sh
RUN /tmp/node-install.sh
RUN rm /tmp/node-install.sh

# Monit
COPY monitrc /etc/monit/monitrc

# Moreutils: for sponge
RUN apt-get install -y moreutils

# Apache
RUN mkdir /var/log/apache2/matecat/
RUN rm -rf /etc/apache2/sites-available/default
RUN rm -rf /etc/apache2/sites-enabled/*
RUN userdel www-data
RUN groupadd www-data
RUN useradd -ms /bin/bash -g www-data www-data

# Redis
RUN sed -i 's/session.save_handler\s*=\s*files/session.save_handler = redis/' /etc/php/5.6/apache2/php.ini
RUN echo 'session.save_path = "tcp://redis:6379?database=15"' >> /etc/php/5.6/apache2/php.ini

## ENABLE Deployment utils ( apache follows symbolic links to DocumentRoot )
RUN git clone https://github.com/etsy/mod_realdoc.git
WORKDIR "/mod_realdoc"
RUN apxs2 -i -a -c mod_realdoc.c
WORKDIR "/"
RUN rm -rf ./mod_realdoc

## Enable MateCat site
COPY 000-matecat.conf /etc/apache2/sites-enabled/000-matecat.conf
RUN a2enmod rewrite filter deflate headers expires proxy_http.load php5.6
RUN phpenmod mcrypt

# Start app_configslication
COPY run.sh /tmp/run.sh
RUN chmod +x /tmp/run.sh

WORKDIR "/var/www/matecat"
CMD ["/tmp/run.sh"]