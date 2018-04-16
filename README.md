Привет! Это тестовое задание для ЦВТ - приложение для учета расходов. Оформлено в виде бандла Symfony.

Готовая рабочая версия есть здесь:
[symfo.fruityloop.tk](http://symfo.fruityloop.tk)

Требования:
php 7.1
symfony installer
composer

Установка

1. Создаем новый проект Symfony

```bash
$ symfony new project 3.4
```

2. Переходим в него и устанавливаем мой пакет через composer. Зависимости подтягиваются самостоятельно.

```bash
$ cd project
$ composer require egorzz/testbundle dev-master
```

Пакет установлен в /vendor/egorzz/testbundle. Баг разработки: пространство имен определено как Egor/TestBundle, но пакет устанавливается в egorzz/testbundle, поскольку уже существует много разработчиков по имени Egor. Потом переименую.

3. Регистрируем мой бандл и зависимости в AppKernel.php

```php
#project/app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            ...
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle(),
            new Egor\TestBundle\EgorTestBundle(),
            ...
        ];
```

4. Проверяем секцию "autoload" в composer.json, должна содержать правильный путь, важно не упустить момент с "egorzz":

```json
#project/composer.json

    "autoload": {
        "psr-4": {
            "AppBundle\\": "src/AppBundle",
            "Egor\\TestBundle\\": "vendor/egorzz/testbundle"
        },
```

5. Обновляем
```bash
$ composer dumpautoload
```

6. Заменяем файлы конфигурации config.yml, parameters.yml, parameters.yml.dist, routing.yml в папке project/app/config на те, что приложены в архиве. Там определены пути к БД, маршруты и некоторые функции для Доктрине, без которых ничего не работает :(

7. Проверяем наличие и доступность файлов limit.conf, test, money.db в корне моего бандла project/vendor/egorzz/testbundle/
Если их нет, добавляем приложенные.

8. чтобы не настраивать Виртуалхосты запускаем встроенный сервер Symfony

```bash
$ bin/console server:start
```

Переходим по адресу из ответа этой команды (http://localhost:8***/) и поражаемся великолепию приложения. 
