#!/bin/bash

chmod 666 /var/www/html/includes/config.php;
chmod 666 /var/www/html/mailer/mailerconfig.php;
chmod -R 777 /var/www/html/templates_c;
chmod -R 777 /var/www/html/avatars;
chmod -R 777 /var/www/html/images/tribes;
chmod -R 777 /var/www/html/templates_c/layout1;
chmod -R 777 /var/www/html/cache;

exec "$@"


# Zanim zaczniesz instalację upewnij się, że skrypt posiada uprawnienia do modyfikacji odpowiednich plików
# Uprawnienia do includes/config.php : 0664	Problem
# Próba nadania pełnych uprawnień do includes/config.php : 	Problem
# Uprawnienia do templates_c/ : 0775	Problem
# Próba nadania pełnych uprawnień do templates_c/ : 	Problem
# Uprawnienia do avatars/ : 0775	Problem
# Próba nadania pełnych uprawnień do avatars/ : 	Problem
# Uprawnienia do images/tribes/ : 0775	Problem
# Próba nadania pełnych uprawnień do images/tribes/ : 	Problem
# Uprawnienia do templates_c/layout1/ : 0775	Problem
# Próba nadania pełnych uprawnień do templates_c/layout1/ : 	Problem
# Uprawnienia do cache/ : 0775	Problem
# Próba nadania pełnych uprawnień do cache/ : 	Problem
# Uprawnienia do mailer/mailerconfig.php : 0664	Problem
# Próba nadania pełnych uprawnień do mailer/mailerconfig.php : 	Problem