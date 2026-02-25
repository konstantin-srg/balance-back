#!/bin/bash
set -e

cd /var/www/balance-back
git pull
cd /var/www/balance-back/src
php composer.phar install --ignore-platform-reqs

read -p "Press enter to continue"


#sh /var/www/deploy.sh