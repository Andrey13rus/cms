===========
Quick start
===========

Работа с URL
============

Везде в своих проектах правильно формируйте url, на любое действие на любой раздел, на любой элемент и т.д. Это позволит избежать кучи проблем с ростом проекта. Особенно с добавлением мультиязычности на сайт.

И так, в yii2 на эту тему есть множество примеров, с ними можно ознакомиться, например тут: https://github.com/yiisoft/yii2/blob/master/docs/guide/helper-url.md

Здесь же, мы рассмотрим конкретные примеры всего что связано с базовым модулем cms

Ссылки на разделы
-----------------

Ссылки на разделы сайта, по их id параметру

.. code-block:: php

    \yii\helpers\Url::to(['/cms/tree/view', 'id' => 10])

Ссылки на разделы сайта, по их объекту модели model

.. code-block:: php

    $model = \skeeks\cms\models\CmsTree::findOne(10);
    \yii\helpers\Url::to(['/cms/tree/view', 'model' => $model])

Ссылки на разделы сайта, по их dir параметру

.. code-block:: php

    //Ссылка в раздел about
    \yii\helpers\Url::to(['/cms/tree/view', 'dir' => 'about'])

Прочие примеры с параметрами

.. code-block:: php

    //Ссылка в раздел about с параметрами
    \yii\helpers\Url::to(['/cms/tree/view', 'dir' => 'about', 'param1' => 'test1', '#' => 'test1'])

    //Абсолютная ссылка на раздел about
    \yii\helpers\Url::to(['/cms/tree/view', 'dir' => 'about'], true)

    //Абсолютная https ссылка на раздел about
    \yii\helpers\Url::to(['/cms/tree/view', 'dir' => 'about'], 'https')

    //Ссылка на вложенный раздел
    \yii\helpers\Url::to(['/cms/tree/view', 'dir' => 'about/level-2/level-3'])

Но cms поддерживает концепцию многосайтовости. Поэтому можно в параметрах указать код желаемого сайта:

.. code-block:: php

    \yii\helpers\Url::to(['/cms/tree/view', 'dir' => 'about/level-2/level-3', 'site_code' => 's2'])


Ссылки в консольном приложении
------------------------------

Об этом стоит сказать особенно. Частый случай, что в yii2 сыпятся ошибки при запуске каких либо консольных утилит. Для корректной работы ссылок, необходимо сконфигурировать компонент UrlManager в конскольном приложении.

.. code-block:: php

    'urlManager' => [
        'baseUrl'   => '',
        'hostInfo' => 'http://your-site.com'
    ]

А так же в bootstrap определить пару алиасов:

.. code-block:: php

    \Yii::setAlias('webroot', dirname(dirname(__DIR__)) . '/frontend/web');
    \Yii::setAlias('web', '');

Авторизация / Регистрация
=========================

Стандартная авторизация/регистрация
-----------------------------------

В **SkeekS CMS** уже реализован процесс авторизации, регистрации и восстановления пароля (через email).
Реализация находится в ``cms/auth`` контроллере.

Методы реализающие эти процессы:

* ``login`` — процесс авторизации
* ``register`` — процесс регистрации
* ``register-by-email`` — регистрация через email (только ajax)
* ``forget`` — запроса начала процедуры восстановления пароля
* ``reset-password`` — действие подтверждения смены пароля


Проверка текущего пользователя
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Для проверки авторизации текущего пользователя на сайте, используется стандартная конструкция yii2.

.. code-block:: php

    if (\Yii::$app->user->isGuest)
    {
        //Пользователь неавторизован
    } else
    {
        //Пользователь авторизован можно запросить его данные
        print_r(\Yii::$app->user->identity->toArray());
    }

Ссылки на авторизацию
~~~~~~~~~~~~~~~~~~~~~

Как получить ссылку на действия связанные с авторизацией

.. code-block:: php

    echo \yii\helpers\Url::to(['cms/auth/login']);
    echo \yii\helpers\Url::to(['cms/auth/register']);
    echo \yii\helpers\Url::to(['cms/auth/forget']);

Еще один вариант через хелпер SkeekS CMS

.. code-block:: php

    echo \skeeks\cms\helpers\UrlHelper::construct('cms/auth/login')->setCurrentRef()


Форма авторизации
~~~~~~~~~~~~~~~~~

Эту форму можно вставить в любое место на сайте, работает через ajax.

.. code-block:: php

    $model = new \skeeks\cms\models\forms\LoginFormUsernameOrEmail();

    <?php $form = skeeks\cms\base\widgets\ActiveFormAjaxSubmit::begin([
        'action' => \skeeks\cms\helpers\UrlHelper::construct('cms/auth/login')->setCurrentRef()->toString(),
        'validationUrl' => \skeeks\cms\helpers\UrlHelper::construct('cms/auth/login')->setSystemParam(\skeeks\cms\helpers\RequestResponse::VALIDATION_AJAX_FORM_SYSTEM_NAME)->toString()
    ]); ?>
        <?= $form->field($model, 'identifier') ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
        <?= $form->field($model, 'rememberMe')->checkbox() ?>

        <div class="form-group">
            <?= \yii\helpers\Html::submitButton("<i class=\"glyphicon glyphicon-off\"></i> Войти", ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>

    <?php skeeks\cms\base\widgets\ActiveFormAjaxSubmit::end(); ?>


Форма регистрации
~~~~~~~~~~~~~~~~~

.. code-block:: php

    <?php $form = ActiveForm::begin([
                    'action' => UrlHelper::construct('cms/auth/register-by-email')->toString(),
                    'validationUrl' => UrlHelper::construct('cms/auth/register-by-email')->setSystemParam(\skeeks\cms\helpers\RequestResponse::VALIDATION_AJAX_FORM_SYSTEM_NAME)->toString(),
                    'afterValidateCallback' => <<<JS
        function(jForm, ajaxQuery)
        {
            var handler = new sx.classes.AjaxHandlerStandartRespose(ajaxQuery, {
                'blockerSelector' : '#' + jForm.attr('id'),
                'enableBlocker' : true,
            });

            handler.bind('success', function()
            {
                _.delay(function()
                {
                    $('#sx-login').click();
                }, 2000);
            });
        }
    JS

                ]); ?>
        <?= $form->field($model, 'email') ?>

        <div class="form-group">
            <?= Html::submitButton("<i class=\"glyphicon glyphicon-off\"></i> Зарегистрироваться", ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>



Форма восстановления пароля
~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    <?php $form = ActiveForm::begin([
        'action' => UrlHelper::construct('cms/auth/forget')->toString(),
        'validationUrl' => UrlHelper::construct('cms/auth/forget')->setSystemParam(\skeeks\cms\helpers\RequestResponse::VALIDATION_AJAX_FORM_SYSTEM_NAME)->toString()
    ]); ?>
        <?= $form->field($model, 'identifier') ?>

        <div class="form-group">
            <?= Html::submitButton("Отправить", ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>




