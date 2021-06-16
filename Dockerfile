FROM centos/systemd
COPY ./INSTALL/install_dev_for_docker.sh /entrypoint/
RUN yum -y update; yum clean all

WORKDIR /app

EXPOSE 80
EXPOSE 3306

CMD ["/usr/sbin/init"]