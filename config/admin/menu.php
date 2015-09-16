<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */

/**
 * Меню контента
 * @return array
 */
function contentMenu()
{
    $result = [];

    if ($contentTypes = \skeeks\cms\models\CmsContentType::find()->orderBy("priority DESC")->all())
    {
        /**
         * @var $contentType \skeeks\cms\models\CmsContentType
         */
        foreach ($contentTypes as $contentType)
        {
            $itemData = [
                'code'      => "content-block-" . $contentType->id,
                'label'     => $contentType->name,
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.article.png'],
            ];

            if ($contents = $contentType->cmsContents)
            {
                foreach ($contents as $content)
                {
                    $itemData['items'][] =
                    [
                        'label' => $content->name,
                        'url'   => ["cms/admin-cms-content-element/index", "content_id" => $content->id, "content_type" => $contentType->code],
                    ];
                }
            }

            $result[] = new \skeeks\cms\modules\admin\helpers\AdminMenuItemCmsConent($itemData);
        }
    }

    return $result;
};

function componentsMenu()
{
    $result = [];

    foreach (\Yii::$app->getComponents(true) as $id => $data)
    {
        $loadedComponent = \Yii::$app->get($id);
        if ($loadedComponent instanceof \skeeks\cms\base\Component)
        {
            $result[] = new \skeeks\cms\modules\admin\helpers\AdminMenuItemCmsConent([
                'label'     => $loadedComponent->descriptor->name,
                'url'   => ["cms/admin-settings", "component" => $loadedComponent->className()],
                /*"activeCallback"       => function(\skeeks\cms\modules\admin\helpers\AdminMenuItem $adminMenuItem)
                {
                    return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                },*/
            ]);
        }
    }

    return $result;
}

return
[
    'content' =>
    [
        'priority'  => 0,
        'label'     => 'Контент',
        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.tree.gif'],

        'items' => array_merge([

            [
                "label"     => "Разделы",
                "url"       => ["cms/admin-tree"],
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.tree.gif']
            ],

            [
                "label"     => "Файловый менеджер",
                "url"       => ["cms/admin-file-manager"],
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/folder.png'],
            ],

            [
                "label"     => "Файлы в хранилище",
                "url"       => ["cms/admin-storage-files/index"],
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/storage_file.png'],
            ],
        ], contentMenu())
    ],

    'settings' =>
    [
        'priority'  => 10,
        'label'     => 'Настройки',
        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],

        'items' =>
        [

            [
                "label"     => "Настройки продукта",
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],

                'items' =>
                [
                    [
                        "label"     => "Сайты",
                        "url"       => ["cms/admin-cms-site"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/www.png']
                    ],

                    [
                        "label"     => "Языки",
                        "url"       => ["cms/admin-cms-lang"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/lang.png']
                    ],

                    [
                        "label"     => "Метки разделов",
                        "url"       => ["cms/admin-tree-menu"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.tree.menu.png']
                    ],

                    [
                        "label"     => "Сервера файлового хранилища",
                        "url"       => ["cms/admin-storage/index"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/servers.png'],
                    ],


                    [
                        "label"     => "Настройки разделов",
                        "url"       => ["cms/admin-cms-tree-type"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.tree.gif'],
                    ],

                    [
                        "label"     => "Настройки контента",
                        "url"       => ["cms/admin-cms-content-type"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/content.png'],
                    ],

                    [
                        "label"     => "Настройки модулей",
                        "url"       => ["cms/admin-settings"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],
                        'items'     => componentsMenu()
                    ],

                    [
                        "label"     => "Агенты",
                        "url"       => ["cms/admin-cms-agent"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/clock.png'],
                    ],
                ],
            ],


            [
                'label'     => 'Пользователи и доступ',
                'priority'  => 0,
                'enabled'   => true,

                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/user.png'],

                'items' =>
                [
                    [
                        "label"     => "Управление пользователями",
                        "url"       => ["cms/admin-user"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/user.png']
                    ],

                    [
                        "label"     => "Свойства пользователей",
                        "url"       => ["cms/admin-cms-user-universal-property"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png']
                    ],

                    [
                        "label"     => "База email адресов",
                        "url"       => ["cms/admin-user-email"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/email-2.png']
                    ],

                    [
                        "label"     => "База телефонов",
                        "url"       => ["cms/admin-user-phone"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/phone.png']
                    ],

                    [
                        "label"     => "Социальные профили",
                        "url"       => ["cms/admin-user-auth-client"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/facebook.png']
                    ],


                    [
                        "label"     => "Роли",
                        "url"       => ["admin/admin-role"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.users_role.png'],
                        'enabled'   => true,
                        'priority'  => 0,
                    ],

                    [
                        "label"     => "Привилегии",
                        "url"       => ["admin/admin-permission"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/access.png'],
                        'enabled'   => true,
                        'priority'  => 0,
                    ],
                ],
            ],


            [

                "label"     => "Поиск",
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/search.png'],

                'items' =>
                [
                    [
                        "label" => "Настройки",
                        "url"   => ["cms/admin-settings", "component" => 'skeeks\cms\components\CmsSearchComponent'],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings.png'],
                        "activeCallback"       => function(\skeeks\cms\modules\admin\helpers\AdminMenuItem $adminMenuItem)
                        {
                            return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                        },
                    ],

                    [
                        "label"     => "Статистика",
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/statistics.png'],

                        'items' =>
                        [
                            [
                                "label" => "Список переходов",
                                "url"   => ["cms/admin-search-phrase"],
                            ],

                            [
                                "label" => "Список фраз",
                                "url"   => ["cms/admin-search-phrase-group"],
                            ],
                        ],
                    ],
                ],
            ],



            [
                'label'     => 'Инструменты',
                'priority'  => 0,
                'enabled'   => true,

                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/tools.png'],

                'items' =>
                [
                    [
                        "label"     => "Проверка системы",
                        "url"       => ["admin/checker"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/tools.png'],
                    ],

                    [
                        "label"     => "Информация",
                        "url"       => ["admin/info"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.infoblock.png'],
                    ],

                    [
                        "label"     => "Отправка email",
                        "url"       => ["admin/email"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/email.png'],
                    ],

                    [
                        "label"     => "Чистка временных данных",
                        "url"       => ["admin/clear"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/clear.png'],
                    ],

                    [
                        "label"     => "Работа с базой данных",
                        "url"       => ["admin/db"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/icon.bd_arch.png'],
                    ],

                    [
                        "label"     => "Обновления",
                        "url"       => ["admin/update"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/update.png'],
                    ],

                    [
                        "label"     => "Ssh console",
                        "url"       => ["admin/ssh"],
                        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/ssh.png'],
                    ],
                    [
                        "label"         => "Генератор кода gii",
                        "url"           => ["admin/gii"],
                        "img"           => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/ssh.png'],
                        "accessCallback"=> function()
                        {
                            if ((bool) \Yii::$app->hasModule('gii'))
                            {
                                /**
                                 * @var $gii yii\gii\Module
                                 */
                                $gii = \Yii::$app->getModule('gii');

                                $ip = Yii::$app->getRequest()->getUserIP();
                                foreach ($gii->allowedIPs as $filter) {
                                    if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                                        return true;
                                    }
                                }
                            }

                            return false;
                        },
                    ],
                ]
            ],
        ]
    ],


    'marketplace' =>
    [
        'priority'  => 20,
        'label'     => 'Маркетплейс',
        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/marketplace.png'],

        'items' =>
        [

            [
                "label"     => "Каталог",
                "url"       => ["cms/admin-marketplace/catalog"],
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/marketplace.png']
            ],

            [
                "label"     => "Установленные",
                "url"       => ["cms/admin-marketplace/index"],
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/installed.png']
            ],

            [
                "label"     => "Установить/Удалить",
                "url"       => ["cms/admin-marketplace/install"],
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/installer.png']
            ],

            [
                "label"     => "Обновление платформы",
                "url"       => ["cms/admin-marketplace/update"],
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/update-2.png']
            ],
        ]
    ],


    'other' =>
    [
        'priority'  => 100,
        'label'     => 'Дополнительно',
        "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/other.png'],

        'items' =>
        [
            [
                "label"     => "Чистка временных данных",
                "url"       => ["admin/clear"],
                "img"       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/clear.png'],
            ],
        ]
    ]
];