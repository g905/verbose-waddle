Привет! Это тестовое задание для ЦВТ - приложение для учета расходов. Оформлено в виде бандла Symfony.

Установка

1. Создаем новый проект Symfony

```bash
symfony new project 3.4
```

2. Переходим в него и устанавливаем мой пакет через composer. Зависимости подтягиваются самостоятельно.

```bash
cd project
composer require egorzz/testbundle dev-master
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

```php
//php code 
$foo = new BarClass();
```
