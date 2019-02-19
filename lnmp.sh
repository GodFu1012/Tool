#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH



#先手动 安装包库
#apt-get install build-essential libxml2 libxml2-dev libevent-1.4-2 libevent-dev libcurl4-openssl-dev libgd2-xpm libgd2-xpm-dev libpng12-0 libpng12-dev  libgif4 libgiftiio-dev libfreetype6 libfreetype6-dev  zlib1g zlib1g-dev libbz2-1.0 libbz2-dev libtool libmcrypt4 libmcrypt-dev libmysqlclient-dev libpcre3 libpcre3-dev autoconf

<<EOF

注释1
注释2
EOF

cur_time=`date +%Y%m%d%H%M%S`




read -p "(please select opt [nginx or php or mysql or all or cfg_lnmp]):" opt




#nginx 安装目录
nginx_path="nginx-1.8.0"

#php 安装目录
php_path="php-5.6.12"


function install_php()
{
    if [ ${opt} != "all"]; then
        read -p "(please input php tar file name,example php-5.6.12):" php_path
    fi
    
    echo ${php_path}
    
    if [ -f "${php_path}.tar.gz" ];then
        echo ${php_path}.tar.gz
        tar zxvf ${php_path}.tar.gz
    else
        echo "${php_path}.tar.gz not exists"
        return
    fi
    
    cd ${php_path}
    
    ./configure --prefix=/usr --with-config-file-path=/etc/php --with-mysql --with-mysqli --with-pdo-mysql --with-gd --with-freetype-dir=/usr --with-jpeg-dir=/usr --with-png-dir=/usr --with-libxml-dir=/usr --with-zlib --enable-exif --enable-xml --enable-bcmath --enable-shmop --enable-sysvsem --enable-inline-optimization --with-bz2 --with-curl --with-curlwrappers --with-mcrypt --with-openssl --with-gettext --enable-filter --enable-simplexml --enable-mbstring --enable-soap --enable-dba --enable-ftp --enable-fpm --enable-sockets --enable-sysvmsg --enable-pcntl
    make
    make install
    #开多进程
    #--enable-pcntl
    #开多线程
    #--enable-maintainer-zts
    cd ../
}



function install_nginx()
{
    if [ ${opt} != "all"]; then
        read -p "(please input nginx tar file name,example nginx-1.8.0):" nginx_path
    fi 
    
    if [ -f "${nginx_path}.tar.gz" ];then
        echo ${nginx_path}.tar.gz
        tar zxvf ${nginx_path}.tar.gz
    else
        echo "${nginx_path}.tar.gz not exists"
        return
    fi
    
    cd ${nginx_path}
    
    mkdir /var/tmp/nginx
    
    ./configure --prefix=/usr --sbin-path=/usr/sbin --conf-path=/etc/nginx/nginx.conf --pid-path=/var/run/nginx.pid --lock-path=/var/lock/nginx.lock --error-log-path=/var/log/nginx/main_error.log --http-log-path=/var/log/nginx/ --http-client-body-temp-path=/var/tmp/nginx/client_body/ --http-proxy-temp-path=/var/tmp/nginx/proxy/ --http-fastcgi-temp-path=/var/tmp/nginx/fcgi/ --with-http_stub_status_module
    make
    make install
    cd ../
    
    #nginx服务自启动(必须init.d有这文件)
    update-rc.d nginx defaults
    
    #移除
    #update-rc.d -f nginx remove
}


function install_mysql()
{
    #//mysql
    #直接用命令安装即可
    apt-get install mysql-server
}


function config_lnmp()
{
    #拷贝配置好的nginx启动程序
    cp config/init.d/nginx-with-fpm /etc/init.d/nginx
    
    #删除默认的nginx配置文件
    if [ -d "/etc/nginx" ]; then
        mv  /etc/nginx /etc/nginx.old_${cur_time}
    else
        echo "not exists /etc/nginx or already move"
    fi
    
    #拷贝配置好的nginx配置文件
    cp -R config/nginx /etc/

    
    #拷贝配置好的php配置文件
    cp -R config/php /etc/
}








if [  ${opt} = "nginx" ]; then
    install_nginx
elif [ ${opt} = "php" ]; then
    install_php
elif [ ${opt} = "mysql" ]; then
    install_mysql
elif [ ${opt} = "all" ]; then
   install_nginx
   install_php
#   install_mysql
elif [ ${opt} = "cfg_lnmp" ]; then
   config_lnmp
fi







