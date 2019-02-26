#!/bin/bash
SQL_host=localhost #mysql host
SQL_User=root      #mysql UserName
#SQL_Passwd=1&$]|n".j+[Gf@S	#User password
SQL_Passwd=123456	#User password
SQL_db=$(date +%Y%m%d)   #database name

backup_path=/data/mysqlbak    #set slave backup path
file=$(date +%Y%m%d).tar.gz #backup name and time
#mysql_path=/usr/local/mysql/bin #set the mysql database bin path
cd $backup_path
if [ -d $backup_path/$(date +%Y%m%d) ]
then
cd $backup_path/$(date +%Y%m%d)
else
mkdir $backup_path/$(date +%Y%m%d)
cd $backup_path/$(date +%Y%m%d)
fi
mysqldump -h $SQL_host -u $SQL_User -p$SQL_Passwd --add-drop-database --add-drop-table -F test > $SQL_db.sql --single-transaction
sleep 5
tar -czf $file $SQL_db.sql
sleep 10
#path=/backup/
data=`date -d ' 8 day ago' +%Y%m%d`
rm -rf $backup_path/"$data"
