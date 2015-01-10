#!/bin/bash

if [ ! -f ./composer.phar ];
then
    curl -sS https://getcomposer.org/installer | php
fi

php composer.phar install

cp config.php.dist config.php
