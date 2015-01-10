xakep-crawler
=============

Экспериментальный граббер для стягивания журналов [отсюда](https://xakep.ru/issues)

#### Установка

    composer install
    cp config.php.dist config.php

Устанавливаем нужный путь в конфиге _config.php_

#### Запуск

    ./bin/grabber.php
    
Скачает доступные журналы только с первой страницы, т.е. самые свежие.

    ./bin/grabber.php all

Скачает все журналы из архива.
