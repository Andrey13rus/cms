Skeeks CMS 2.* (Yii2 cms)
================

[![skeeks!](http://cms.skeeks.com/uploads/all/02/bb/d1/02bbd1ed904fc44bdee66e33b661cf2c/sx-filter__skeeks-cms-components-imaging-filters-Thumbnail/15f3c42a5e338e459b5bfe72f1874494/sx-file.png?w=409&h=258)](http://en.cms.skeeks.com)  



##Links
* [Web site](http://en.cms.skeeks.com)
* [Author](http://skeeks.com)
* [ChangeLog](https://github.com/skeeks-cms/cms/blob/master/CHANGELOG.md)
* [Plans](https://github.com/skeeks-cms/cms/blob/master/PLANS.md)

## Info

SkeekS CMS - modern generic content management system based on yii2 php framework.

It provides capabilities to manage site structure and content elements (news, publications, products, etc.).

Opportunities for management and dynamic creation of additional properties of the content sections and elements through the administration system.

Opportunities to manage users, privileges, roles and their purpose.

Supports single query entry point (one index.php), for backend and frontend parts of the project. By then, it is more convenient to configure nginx and apache.

Almost every page of the site - content item or section. Each of these models has some povdeniem availability of additional properties. Therefore, any product publkatsii, news, etc. It has a set of common properties, which are described in the model, and a set of dynamically created properties, through the administration system.

This versatility allows you to easily create any site, without writing and design of additional models and migration. What idelalno for quickly writing conventional nevysokonagruzhennyh projects (this does not mean that you can not write highly loaded projects).

Just have http://marketplace.cms.skeeks.com/ marketpleys the CMS for this, which is constantly updated with useful solutions.

##Last video
[![Video on youtube](http://img.youtube.com/vi/u9JRc27WVYY/0.jpg)](http://www.youtube.com/watch?v=u9JRc27WVYY)


##Screenshot
[![SkeekS CMS admin panel](http://cms.skeeks.com/uploads/all/7a/72/a6/7a72a6bad8c89b27c09231a90b41f75e.png)](http://cms.skeeks.com/uploads/all/7a/72/a6/7a72a6bad8c89b27c09231a90b41f75e.png)
___
[![SkeekS CMS admin panel](http://cms.skeeks.com/uploads/all/4d/d7/38/4dd7380094d34a062a66d81c65c90be2.png)](http://cms.skeeks.com/uploads/all/4d/d7/38/4dd7380094d34a062a66d81c65c90be2.png)
___
[![SkeekS CMS admin panel](http://cms.skeeks.com/uploads/all/93/1b/7d/931b7d207ca2d0ea41ddf45193fea218.png)](http://cms.skeeks.com/uploads/all/93/1b/7d/931b7d207ca2d0ea41ddf45193fea218.png)

___
[![SkeekS CMS admin panel](http://cms.skeeks.com/uploads/all/35/b4/b6/35b4b6e7c1edf46b320002d61ffad411.png)](http://cms.skeeks.com/uploads/all/35/b4/b6/35b4b6e7c1edf46b320002d61ffad411.png)



##Install

* Install files
```php
//Скачивание свежей версии composer
php -r "readfile('https://getcomposer.org/installer');" | php
//Установка базового проекта SkeekS CMS
COMPOSER_HOME=.composer php composer.phar create-project --no-install --prefer-dist skeeks/app-basic app-basic
//Спускаемся в папку
cd app-basic
//Качаем композер в проект
php -r "readfile('https://getcomposer.org/installer');" | php
//Используем самую последнюю стабильную версию
COMPOSER_HOME=.composer php composer.phar self-update 1.0.0-beta1
//Установка дополнительных плагинов
COMPOSER_HOME=.composer php composer.phar global require "fxp/composer-asset-plugin:1.1.2" --profile
//Ну и собственно установка проекта
//В процессе вероятнее всего у вас будет запрошен доступ к github, поскольку большинство пакетов лежат именно на его серверах
COMPOSER_HOME=.composer php composer.phar install
//После установки, запуск команды, для инициализации проекта
php yii cms/init
```

* Db connect
Update file: common/config/db.php

* Install migrations
```bash
#Установка из готового дампа
php yii cms/db/first-dump-restore
#Установка недостающих миграций
php yii cms/db/apply-migrations
#Авто настройка прав доступа
php yii cms/rbac/init
```

*  Backend (username and password by default)

http://your-site.ru/~sx

root

skeeks

___

> [![skeeks!](https://gravatar.com/userimage/74431132/13d04d83218593564422770b616e5622.jpg)](http://skeeks.com)  
<i>SkeekS CMS (Yii2) — быстро, просто, эффективно!</i>  
[skeeks.com](http://skeeks.com) | [cms.skeeks.com](http://cms.skeeks.com) | [marketplace.cms.skeeks.com](http://marketplace.cms.skeeks.com)

