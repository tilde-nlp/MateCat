#!/bin/bash

yum install wget -y
# ----- Apache2
yum install -y httpd
cat >> /etc/httpd/conf.modules.d/00-cattools.conf << EOF
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule filter_module modules/mod_filter.so
LoadModule deflate_module modules/mod_deflate.so
LoadModule headers_module modules/mod_headers.so
LoadModule expires_module modules/mod_expires.so
EOF
apachectl restart

# ----- Mysql 5.7
wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm
rpm -ivh mysql-community-release-el7-5.noarch.rpm
yum update
yum install mysql-server -y
systemctl start mysqld



