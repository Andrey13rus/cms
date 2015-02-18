<?php
/**
 * AdminModelEditorSmartController
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 31.10.2014
 * @since 1.0.0
 */
namespace skeeks\cms\modules\admin\controllers;

use skeeks\cms\App;
use skeeks\cms\base\db\ActiveRecord;
use skeeks\cms\controllers\AdminSubscribeController;
use skeeks\cms\Exception;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\behaviors\CanBeLinkedToModel;
use skeeks\cms\models\behaviors\HasAdultStatus;
use skeeks\cms\models\behaviors\HasComments;
use skeeks\cms\models\behaviors\HasDescriptionsBehavior;
use skeeks\cms\models\behaviors\HasFiles;
use skeeks\cms\models\behaviors\HasMetaData;
use skeeks\cms\models\behaviors\HasPageOptions;
use skeeks\cms\models\behaviors\HasSeoPageUrl;
use skeeks\cms\models\behaviors\HasStatus;
use skeeks\cms\models\behaviors\HasSubscribes;
use skeeks\cms\models\behaviors\HasVotes;
use skeeks\cms\models\behaviors\HasPublications;

use skeeks\cms\models\behaviors\TimestampPublishedBehavior;
use skeeks\cms\models\Comment;
use skeeks\cms\models\Publication;
use skeeks\cms\models\Search;
use skeeks\cms\models\StorageFile;
use skeeks\cms\models\Subscribe;
use skeeks\cms\models\Vote;
use skeeks\cms\modules\admin\controllers\helpers\rules\HasModelBehaviors;
use skeeks\cms\modules\admin\controllers\helpers\rules\ModelHasBehaviors;
use skeeks\cms\validators\HasBehavior;
use skeeks\sx\validate\Validate;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\Model;
use yii\base\View;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\filters\VerbFilter;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * Class AdminModelEditorAdvancedController
 * @package skeeks\cms\modules\admin\controllers
 */
abstract class AdminModelEditorSmartController extends AdminModelEditorController
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            self::BEHAVIOR_ACTION_MANAGER =>
            [
                "actions" =>
                [
                    'descriptions' =>
                    [
                        "label"     => "Описание",
                        "icon"     => "glyphicon glyphicon-paperclip",
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasDescriptionsBehavior::className()
                            ]
                        ]
                    ],

                    'files' =>
                    [
                        "label"     => "Файлы",
                        "icon"     => "glyphicon glyphicon-folder-open",
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasFiles::className()
                            ]
                        ]
                    ],



                    /*'comments' =>
                    [
                        "label"     => "Комментарии",
                        'icon'      => 'glyphicon glyphicon-comment',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasComments::className()
                            ]
                        ]
                    ],

                    'votes' =>
                    [
                        "label"     => "Голоса",
                        'icon'      => 'glyphicon glyphicon-thumbs-up',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasVotes::className()
                            ]
                        ]
                    ],


                    'subscribes' =>
                    [
                        "label"     => "Подписаны",
                        "icon"     => "glyphicon glyphicon-heart-empty",
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasSubscribes::className()
                            ]
                        ]
                    ],*/

                    'publications' =>
                    [
                        "label"     => "Публикации",
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasPublications::className()
                            ]
                        ]
                    ],

                    /*'seo-page-url' =>
                    [
                        "label"     => "Адрес на сайте",
                        "icon"     => "glyphicon glyphicon-magnet",
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasSeoPageUrl::className()
                            ]
                        ]
                    ],*/


                    /*'universal-link' =>
                    [
                        "label"     => "Универсальная связь",
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => CanBeLinkedToModel::className()
                            ]
                        ]
                    ],*/

                    'page-options' =>
                    [
                        "label"     => "Дополнительные свойства",
                        'icon'      => 'glyphicon glyphicon-plus-sign',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasPageOptions::className()
                            ]
                        ]
                    ],


                    /*'status' =>
                    [
                        "label"     => "Статус",
                        'icon'      => 'glyphicon glyphicon-plus-sign',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasStatus::className()
                            ]
                        ]
                    ],*/

                    /*'status-adult' =>
                    [
                        "label"     => "Возрастной статус",
                        'icon'      => 'glyphicon glyphicon-plus-sign',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => HasAdultStatus::className()
                            ]
                        ]
                    ],*/

                    /*'author' =>
                    [
                        "label"     => "Автор",
                        'icon'      => 'glyphicon glyphicon-user',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => BlameableBehavior::className()
                            ]
                        ]
                    ],*/

                    /*'timestamp' =>
                    [
                        "label"     => "Время создания",
                        'icon'      => 'glyphicon glyphicon-time',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => [TimestampBehavior::className(), TimestampPublishedBehavior::className()],
                                "useOr" => true
                            ]
                        ]
                    ],*/

                    'system' =>
                    [
                        "label"     => "Служебные данные",
                        'icon'      => 'glyphicon glyphicon-user',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => [
                                    TimestampBehavior::className(),
                                    TimestampPublishedBehavior::className(),
                                    BlameableBehavior::className(),
                                    HasAdultStatus::className(),
                                    HasStatus::className(),
                                    HasSeoPageUrl::className(),
                                ],
                                "useOr" => true
                            ]
                        ]
                    ],
                    'social' =>
                    [
                        "label"     => "Социальные данные",
                        'icon'      => 'glyphicon glyphicon-thumbs-up',
                        "rules"     =>
                        [
                            [
                                "class"     => HasModelBehaviors::className(),
                                "behaviors" => [
                                    HasVotes::className(),
                                    HasComments::className(),
                                    HasSubscribes::className()
                                ],
                                "useOr" => true
                            ]
                        ]
                    ],

                ]
            ]
        ]);
    }



    /**
     * @return string|\yii\web\Response
     */
    public function actionFiles()
    {
        if (\Yii::$app->request->isPost)
        {
            $group = \Yii::$app->request->post('group');
            \Yii::$app->getSession()->set('cms-admin-files-group', $group);
        } else
        {
            $group = \Yii::$app->getSession()->get('cms-admin-files-group');
        }

        $search         = new Search(StorageFile::className());
        $dataProvider   = $search->search(\Yii::$app->request->queryParams);
        $searchModel    = $search->getLoadedModel();

        $dataProvider->query->andWhere($this->getCurrentModel()->getRef()->toArray());

        if ($group)
        {
            if ($groupObject = $this->getModel()->getFilesGroups()->getComponent($group))
            {
                $dataProvider->query->andWhere(['src' => (array) $groupObject->items]);
            }
        }

        $controller = \Yii::$app->cms->moduleCms()->createControllerByID("admin-storage-files");

        $clientOptions['simpleUpload'] = $this->_getSourceSimpleUploadOptions($group);


        return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/files.php", [
            "model"             => $this->getModel(),
            'searchModel'       => $searchModel,
            'dataProvider'      => $dataProvider,
            'controller'        => $controller,
            'group'              => $group,
            'clientOptions'     => (array) $clientOptions,
        ]));

    }


    private function _getSourceSimpleUploadOptions($group = '')
    {
        $backendSimpleUpload = \Yii::$app->urlManager->createUrl(["cms/storage-files/upload",
            "linked_to_model"   => $this->getModel()->getRef()->getCode(),
            "linked_to_value"   => $this->getModel()->getRef()->getValue(),
            "group"              => $group
        ]);


        //Опции которые перетирать нельзя
        $mainOptions =
        [
            "url"               => $backendSimpleUpload,
            "name"              => "imgfile", //TODO: хардкод
            "hoverClass"        => 'btn-hover',
            "focusClass"        => 'active',
            "disabledClass"     => 'disabled',
            "responseType"      => 'json',
            "multiplie"          => true,

        ];

        //Опции которые вычисляются из поведения моедли
        $fromBehaviorOptions = [];
        /*$config = $this->_modelAttributeConfig;
        if (isset($config[HasFiles::MAX_SIZE]))
        {
            $fromBehaviorOptions["maxSize"] = $config[HasFiles::MAX_SIZE];
        }

        if (isset($config[HasFiles::ALLOWED_EXTENSIONS]))
        {
            $fromBehaviorOptions["allowedExtensions"] = $config[HasFiles::ALLOWED_EXTENSIONS];
        }

        if (isset($config[HasFiles::ACCEPT_MIME_TYPE]))
        {
            $fromBehaviorOptions["accept"] = $config[HasFiles::ACCEPT_MIME_TYPE];
        }*/


        return array_merge($fromBehaviorOptions, $mainOptions);
    }


    protected function _actionComments()
    {
        $result = "";

        if ( Validate::isValid(new HasBehavior(HasComments::className()), $this->getCurrentModel()) )
        {
            $search = new Search(Comment::className());
            $dataProvider   = $search->search(\Yii::$app->request->queryParams);
            $searchModel    = $search->getLoadedModel();

            $dataProvider->query->andWhere($this->getCurrentModel()->getRef()->toArray());

            $controller = \Yii::$app->cms->moduleCms()->createControllerByID("admin-comment");

            $result = \Yii::$app->cms->moduleCms()->renderFile("admin-comment/index.php", [
                'searchModel'   => $searchModel,
                'dataProvider'  => $dataProvider,
                'controller'    => $controller,
            ]);
        }

        return $result;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function _actionVotes()
    {
        $result = "";

        if ( Validate::isValid(new HasBehavior(HasVotes::className()), $this->getCurrentModel()) )
        {
            $search = new Search(Vote::className());
            $dataProvider   = $search->search(\Yii::$app->request->queryParams);
            $searchModel    = $search->getLoadedModel();

            $dataProvider->query->andWhere($this->getCurrentModel()->getRef()->toArray());

            $controller = \Yii::$app->cms->moduleCms()->createControllerByID("admin-vote");

            $result = \Yii::$app->cms->moduleCms()->renderFile("admin-vote/index.php", [
                'searchModel'   => $searchModel,
                'dataProvider'  => $dataProvider,
                'controller'    => $controller,
            ]);
        };

        return $result;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function _actionSubscribes()
    {
        $result = "";

        if ( Validate::isValid(new HasBehavior(HasSubscribes::className()), $this->getCurrentModel()) )
        {
            $search = new Search(Subscribe::className());
            $dataProvider   = $search->search(\Yii::$app->request->queryParams);
            $searchModel    = $search->getLoadedModel();

            $dataProvider->query->andWhere($this->getCurrentModel()->getRef()->toArray());

            $controller = \Yii::$app->cms->moduleCms()->createControllerByID("admin-subscribe");

            $result = \Yii::$app->cms->moduleCms()->renderFile("admin-subscribe/index.php", [
                'searchModel'   => $searchModel,
                'dataProvider'  => $dataProvider,
                'controller'    => $controller,
            ]);
        }

        return $result;

    }


    public function actionSocial()
    {
        $subscribes = $this->_actionSubscribes();
        $comments   = $this->_actionComments();
        $votes      = $this->_actionVotes();

        return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/social.php", [
            'subscribes'   => $subscribes,
            'comments'   => $comments,
            'votes'   => $votes,
        ]));

    }

    public function actionPublications()
    {
        $search = new Search(Publication::className());
        $dataProvider   = $search->search(\Yii::$app->request->queryParams);
        $searchModel    = $search->getLoadedModel();

        $dataProvider->query->andWhere($this->getCurrentModel()->getRef()->toArray());

        $controller = \Yii::$app->cms->moduleCms()->createControllerByID("admin-publication");

        return $this->output(\Yii::$app->cms->moduleCms()->renderFile("admin-publication/index.php", [
            'searchModel'   => $searchModel,
            'dataProvider'  => $dataProvider,
            'controller'    => $controller,
        ]));
    }



    /*public function actionSeoPageUrl()
    {
        $model = $this->getModel();

        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            \Yii::$app->getSession()->setFlash('success', 'Успешно сохранено');
            return $this->redirectRefresh();
        } else
        {
            return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/seo-page-url.php", [
                "model" => $this->getModel()
            ]));
        }
    }*/

    public function actionSystem()
    {
        /*$model = $this->getModel();

        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            \Yii::$app->getSession()->setFlash('success', 'Успешно сохранено');
            return $this->redirectRefresh();
        } else
        {
            return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/system.php", [
                "model" => $this->getModel()
            ]));
        }*/



        $model = $this->getModel();

        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            \Yii::$app->getSession()->setFlash('success', 'Успешно сохранено');
            if (!\Yii::$app->request->isAjax)
            {
                return $this->redirectRefresh();
            }

        } else
        {
            if (\Yii::$app->request->isPost)
            {
                \Yii::$app->getSession()->setFlash('error', 'Не удалось сохранить');
            }
        }

        return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/system.php", [
            "model" => $this->getModel()
        ]));
    }

    /*public function actionAuthor()
    {
        $model = $this->getModel();

        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            \Yii::$app->getSession()->setFlash('success', 'Успешно сохранено');
            return $this->redirectRefresh();
        } else
        {
            return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/author.php", [
                "model" => $this->getModel()
            ]));
        }
    }

    public function actionTimestamp()
    {
        $model = $this->getModel();

        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            \Yii::$app->getSession()->setFlash('success', 'Успешно сохранено');
            return $this->redirectRefresh();
        } else
        {
            return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/timestamp.php", [
                "model" => $this->getModel()
            ]));
        }
    }

    public function actionUniversalLink()
    {
        $model = $this->getModel();
        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            return $this->redirectRefresh();
        } else
        {
            return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/seo-page-url.php", [
                "model" => $this->getModel()
            ]));
        }
    }*/

    public function actionPageOptions()
    {
        $model = $this->getModel();

        $optionsCurrent = $model->getMultiPageOptionsData();

        $pageOption         = null;
        if ($pageOptionId = \Yii::$app->request->getQueryParam('page-option'))
        {
            $pageOption = \Yii::$app->pageOptions->getComponent($pageOptionId);

            if ($model->hasPageOptionValueData($pageOptionId))
            {
                $pageOption->getValue()->setAttributes($model->getPageOptionValueData($pageOptionId));

            }
        }

        if ($pageOption && \Yii::$app->request->isPost)
        {
            if ($pageOption->getValue()->load(\Yii::$app->request->post()))
            {
                $optionsCurrent[$pageOptionId] = $pageOption->getValue()->attributes;
                $model->setMultiPageOptionsData($optionsCurrent);
                $model->save(false);

                return $this->redirectRefresh();
            } else
            {
                $optionsCurrent[$pageOptionId] = $pageOption->getValue()->attributes;
                $model->setMultiPageOptionsData('');
                $model->save(false);

                return $this->redirectRefresh();
            }
        }


        return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/page-options.php", [
            "model"         => $this->getModel(),
            "pageOption"    => $pageOption
        ]));
    }



    /**
     * @return string|\yii\web\Response
     */
    public function actionDescriptions()
    {
        $model = $this->getModel();

        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            \Yii::$app->getSession()->setFlash('success', 'Успешно сохранено');
            if (!\Yii::$app->request->isAjax)
            {
                return $this->redirectRefresh();
            }

        } else
        {
            if (\Yii::$app->request->isPost)
            {
                \Yii::$app->getSession()->setFlash('error', 'Не удалось сохранить');
            }
        }

        return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/descriptions.php", [
            "model" => $this->getModel()
        ]));
    }


    /**
     * @return string|\yii\web\Response
     */
    public function actionStatus()
    {
        $model = $this->getModel();

        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            return $this->redirectRefresh();
        } else
        {
            return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/status.php", [
                "model" => $this->getModel()
            ]));
        }
    }



    /**
     * @return string|\yii\web\Response
     */
    public function actionStatusAdult()
    {
        $model = $this->getModel();

        if ($model->load(\Yii::$app->request->post()) && $model->save(false))
        {
            return $this->redirectRefresh();
        } else
        {
            return $this->output(\Yii::$app->cms->moduleAdmin()->renderFile("base-actions/status-adult.php", [
                "model" => $this->getModel()
            ]));
        }
    }

}