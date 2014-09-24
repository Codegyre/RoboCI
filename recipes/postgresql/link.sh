socat TCP4-LISTEN:5432,fork,reuseaddr TCP4:postgresql:5432 &
sudo mkdir /var/run/postgresql
sudo chown travis /var/run/postgresql
socat UNIX-LISTEN:/var/run/postgresql/.s.PGSQL.5432,fork,reuseaddr TCP4:postgresql:3306 &
psql -c "CREATE USER postgres WITH PASSWORD ''  ;"
