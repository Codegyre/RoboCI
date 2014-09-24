socat TCP4-LISTEN:3306,fork,reuseaddr TCP4:mysql:3306 &
sudo mkdir /var/run/mysqld
sudo chown travis /var/run/mysqld
socat UNIX-LISTEN:/var/run/mysqld/mysqld.sock,fork,reuseaddr TCP4:mysql:3306 &
mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO travis@$(hostname --ip-address) IDENTIFIED BY ''"