#!/bin/bash

chmod 666 /var/www/html/includes/config.php;
chmod 666 /var/www/html/mailer/mailerconfig.php;
chmod -R 777 /var/www/html/templates_c;
chmod -R 777 /var/www/html/avatars;
chmod -R 777 /var/www/html/images/tribes;
chmod -R 777 /var/www/html/templates_c/layout1;
chmod -R 777 /var/www/html/cache;

exec "$@"
