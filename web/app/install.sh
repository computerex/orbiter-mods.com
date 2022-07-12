
#!/bin/sh

apt update
apt install python3-pip

chown www-data:www-data /var/www/html/public
chown www-data:www-data /tmp