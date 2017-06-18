<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.05.2015
 */
namespace skeeks\cms\controllers;

use skeeks\cms\backend\actions\BackendModelCreateAction;
use skeeks\cms\backend\actions\BackendModelUpdateAction;
use skeeks\cms\backend\IBackendAction;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\IHasUrl;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsContentType;
use skeeks\cms\models\searchs\CmsContentElementSearch;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorCreateAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiDialogModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\modules\admin\widgets\GridViewStandart;
use Yii;
use skeeks\cms\models\User;
use skeeks\cms\models\searchs\User as UserSearch;
use yii\base\ActionEvent;
use yii\bootstrap\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Application;

/**
 * @property CmsContent|static $content
 *
 * Class AdminCmsContentTypeController
 * @package skeeks\cms\controllers
 */
class AdminCmsContentElementController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name                     = \Yii::t('skeeks/cms', 'Elements');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = CmsContentElement::class;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = ArrayHelper::merge(parent::actions(),
            [

                "index" =>
                [
                    'modelSearchClassName' => CmsContentElementSearch::class
                ],

                "create" =>
                [
                    'class'         => BackendModelCreateAction::class,
                    "callback"      => [$this, 'create'],
                ],

                "update" =>
                [
                    'class'         => BackendModelUpdateAction::class,
                    "callback"      => [$this, 'update'],
                ],

                "activate-multi" =>
                [
                    'class' => AdminMultiModelEditAction::class,
                    "name" => \Yii::t('skeeks/cms', 'Activate'),
                    //"icon"              => "glyphicon glyphicon-trash",
                    "eachCallback" => [$this, 'eachMultiActivate'],
                ],

                "inActivate-multi" =>
                [
                    'class' => AdminMultiModelEditAction::class,
                    "name" => \Yii::t('skeeks/cms', 'Deactivate'),
                    //"icon"              => "glyphicon glyphicon-trash",
                    "eachCallback" => [$this, 'eachMultiInActivate'],
                ],

                "change-tree-multi" =>
                [
                    'class'             => AdminMultiDialogModelEditAction::class,
                    "name"              => \Yii::t('skeeks/cms', 'The main section'),
                    "viewDialog"        => "change-tree-form",
                    "eachCallback"      => [$this, 'eachMultiChangeTree'],
                ],

                "change-trees-multi" =>
                [
                    'class'             => AdminMultiDialogModelEditAction::class,
                    "name"              => \Yii::t('skeeks/cms', 'Related topics'),
                    "viewDialog"        => "change-trees-form",
                    "eachCallback"      => [$this, 'eachMultiChangeTrees'],
                ],

                "rp" =>
                [
                    'class'             => AdminMultiDialogModelEditAction::class,
                    "name"              => \Yii::t('skeeks/cms', 'Properties'),
                    "viewDialog"        => "multi-rp",
                    "eachCallback"      => [$this, 'eachRelatedProperties'],
                ],
            ]
        );

        return $actions;
    }



    public function create($adminAction)
    {
        $modelClassName = $this->modelClassName;
        $model          = new $modelClassName;

        $model->loadDefaultValues();

        if ($content_id = \Yii::$app->request->get("content_id"))
        {
            $contentModel       = \skeeks\cms\models\CmsContent::findOne($content_id);
            $model->content_id  = $content_id;
        }

        $relatedModel = $model->relatedPropertiesModel;
        $relatedModel->loadDefaultValues();

        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());

            return \yii\widgets\ActiveForm::validateMultiple([
                $model, $relatedModel
            ]);
        }

        if ($post = \Yii::$app->request->post())
        {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());

                if ($model->save() && $relatedModel->save())
                {
                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/cms','Saved'));

                    if (\Yii::$app->request->post('submit-btn') == 'apply')
                    {
                        $url = '';
                        $this->model = $model;

                        if ($this->modelActions)
                        {
                            if ($action = ArrayHelper::getValue($this->modelActions, $this->modelDefaultAction))
                            {
                                $url = $action->url;
                            }
                        }

                        if (!$url)
                        {
                            $url = $this->url;
                        }

                        return $this->redirect($url);
                    } else
                    {
                        return $this->redirect(
                            $this->url
                        );
                    }
                }
            }

        }

        return $this->render('_form', [
            'model'           => $model,
            'relatedModel'    => $relatedModel
        ]);
    }

    public function update($adminAction)
    {
        /**
         * @var $model CmsContentElement
         */
        $model = $this->model;
        $relatedModel = $model->relatedPropertiesModel;

        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
            return \yii\widgets\ActiveForm::validateMultiple([
                $model, $relatedModel
            ]);
        }

        if ($post = \Yii::$app->request->post())
        {
            $model->load(\Yii::$app->request->post());
            $relatedModel->load(\Yii::$app->request->post());
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                $model->load(\Yii::$app->request->post());
                $relatedModel->load(\Yii::$app->request->post());

                if ($model->save() && $relatedModel->save())
                {
                    \Yii::$app->getSession()->setFlash('success', \Yii::t('skeeks/cms','Saved'));

                    if (\Yii::$app->request->post('submit-btn') == 'apply')
                    {} else
                    {
                        return $this->redirect(
                            $this->url
                        );
                    }

                    $model->refresh();

                }
            }

        }

        return $this->render('_form', [
            'model'           => $model,
            'relatedModel'    => $relatedModel
        ]);
    }

    /**
     * @param CmsContentElement $model
     * @param $action
     * @return bool
     */
    public function eachMultiChangeTree($model, $action)
    {
        try
        {
            $formData = [];
            parse_str(\Yii::$app->request->post('formData'), $formData);
            $tmpModel = new CmsContentElement();
            $tmpModel->load($formData);
            if ($tmpModel->tree_id && $tmpModel->tree_id != $model->tree_id)
            {
                $model->tree_id = $tmpModel->tree_id;
                return $model->save(false);
            }

            return false;
        } catch (\Exception $e)
        {
            return false;
        }
    }

    public function eachRelatedProperties($model, $action)
    {
        try
        {
            $formData = [];
            parse_str(\Yii::$app->request->post('formData'), $formData);

            if (!$formData)
            {
                return false;
            }

            if (!$content_id = ArrayHelper::getValue($formData, 'content_id'))
            {
                return false;
            }

            if (!$fields = ArrayHelper::getValue($formData, 'fields'))
            {
                return false;
            }


            /**
             * @var CmsContent $content
             */
            $content = CmsContent::findOne($content_id);
            if (!$content)
            {
                return false;
            }


            $element            = $content->createElement();
            $relatedProperties  = $element->relatedPropertiesModel;
            $relatedProperties->load($formData);
            /**
             * @var $model CmsContentElement
             */
            $rpForSave = $model->relatedPropertiesModel;

            foreach ((array) ArrayHelper::getValue($formData, 'fields') as $code)
            {
                if ($rpForSave->hasAttribute($code))
                {
                    $rpForSave->setAttribute($code, ArrayHelper::getValue($formData, 'RelatedPropertiesModel.' . $code));
                }
            }

            return $rpForSave->save(false);
        } catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * @param CmsContentElement $model
     * @param $action
     * @return bool
     */
    public function eachMultiChangeTrees($model, $action)
    {
        try
        {
            $formData = [];
            parse_str(\Yii::$app->request->post('formData'), $formData);
            $tmpModel = new CmsContentElement();
            $tmpModel->load($formData);

            if (ArrayHelper::getValue($formData, 'removeCurrent'))
            {
                $model->treeIds = [];
            }

            if ($tmpModel->treeIds)
            {
                $model->treeIds = array_merge($model->treeIds, $tmpModel->treeIds);
                $model->treeIds = array_unique($model->treeIds);
            }

            return $model->save(false);
        } catch (\Exception $e)
        {
            return false;
        }
    }


    /**
     * @return string
     */
    public function getPermissionName()
    {
        $unique = parent::getPermissionName();

        if ($this->content)
        {
            $unique = $unique . "__" . $this->content->id;
        }

        return $unique;
    }


    /**
     * @var CmsContent
     */
    protected $_content = null;

    /**
     * @return CmsContent|static
     */
    public function getContent()
    {
        if ($this->_content !== null)
        {
            return $this->_content;
        }

        if ($this->model)
        {
            /**
             * @var $model CmsContentElement
             */
            $model = $this->model;
            $this->_content = $model->cmsContent;
        }

        if (\Yii::$app instanceof Application && \Yii::$app->request->get('content_id'))
        {
            $content_id = \Yii::$app->request->get('content_id');
            $this->_content = CmsContent::findOne($content_id);
        }

        return $this->_content;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    public function getActions()
    {
        /**
         * @var AdminAction $action
         */
        $actions = parent::getActions();
        if ($actions)
        {
            foreach ($actions as $action)
            {
                if ($this->content)
                {
                    $action->url = ArrayHelper::merge($action->urlData, ['content_id' => $this->content->id]);
                }
            }
        }

        return $actions;
    }

    public function getModelActions()
    {
        /**
         * @var AdminAction $action
         */
        $actions = parent::getModelActions();
        if ($actions)
        {
            foreach ($actions as $action)
            {
                $action->url = ArrayHelper::merge($action->urlData, ['content_id' => $this->content ? $this->content->id : ""]);
            }
        }

        return $actions;
    }


    public function beforeAction($action)
    {
        if ($this->content)
        {
            if ($this->content->name_meny)
            {
                $this->name = $this->content->name_meny;
            } else
            {
                $this->name = $this->content->name;
            }
        }

        return parent::beforeAction($action);
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        $actions = $this->getActions();
        $index = ArrayHelper::getValue($actions, 'index');
        if ($index && $index instanceof IHasUrl)
        {
            return $index->url;
        }

        return '';
    }






    /**
     * @param CmsContent $cmsContent
     * @return array
     */
    static public function getColumnsByContent($cmsContent = null, $dataProvider = null)
    {
        $autoColumns = [];

        if (!$cmsContent)
        {
            return [];
        }

        $model = CmsContentElement::find()->where(['content_id' => $cmsContent->id])->one();

        if (!$model)
        {
            $model = new CmsContentElement([
                'content_id' => $cmsContent->id
            ]);
        }

        if (is_array($model) || is_object($model))
        {
            foreach ($model as $name => $value) {
                $autoColumns[] = [
                    'attribute' => $name,
                    'visible' => false,
                    'format' => 'raw',
                    'class' => \yii\grid\DataColumn::class,
                    'value' => function($model, $key, $index) use ($name)
                    {
                        if (is_array($model->{$name}))
                        {
                            return implode(",", $model->{$name});
                        } else
                        {
                            return $model->{$name};
                        }
                    },
                ];
            }

            $searchRelatedPropertiesModel = new \skeeks\cms\models\searchs\SearchRelatedPropertiesModel();
            $searchRelatedPropertiesModel->initProperties($cmsContent->cmsContentProperties);
            $searchRelatedPropertiesModel->load(\Yii::$app->request->get());
            if ($dataProvider)
            {
                $searchRelatedPropertiesModel->search($dataProvider);
            }

            /**
             * @var $model \skeeks\cms\models\CmsContentElement
             */
            if ($model->relatedPropertiesModel)
            {
                $autoColumns = ArrayHelper::merge($autoColumns, GridViewStandart::getColumnsByRelatedPropertiesModel($model->relatedPropertiesModel, $searchRelatedPropertiesModel));
            }
        }

        return $autoColumns;
    }


    /**
     * @param CmsContent $cmsContent
     * @return array
     */
    static public function getDefaultColumns($cmsContent = null)
    {
        $columns = [
            [
                'class' => \skeeks\cms\grid\ImageColumn2::class,
            ],

            'name',
            ['class' => \skeeks\cms\grid\CreatedAtColumn::class],
            [
                'class' => \skeeks\cms\grid\UpdatedAtColumn::class,
                'visible' => false
            ],
            [
                'class' => \skeeks\cms\grid\PublishedAtColumn::class,
                'visible' => false
            ],
            [
                'class' => \skeeks\cms\grid\DateTimeColumnData::class,
                'attribute' => "published_to",
                'visible' => false
            ],

            ['class' => \skeeks\cms\grid\CreatedByColumn::class],
            //['class' => \skeeks\cms\grid\UpdatedByColumn::class],

            [
                'class'     => \yii\grid\DataColumn::class,
                'value'     => function(\skeeks\cms\models\CmsContentElement $model)
                {
                    if (!$model->cmsTree)
                    {
                        return null;
                    }

                    $path = [];

                    if ($model->cmsTree->parents)
                    {
                        foreach ($model->cmsTree->parents as $parent)
                        {
                            if ($parent->isRoot())
                            {
                                $path[] =  "[" . $parent->site->name . "] " . $parent->name;
                            } else
                            {
                                $path[] =  $parent->name;
                            }
                        }
                    }
                    $path = implode(" / ", $path);
                    return "<small><a href='{$model->cmsTree->url}' target='_blank' data-pjax='0'>{$path} / {$model->cmsTree->name}</a></small>";
                },
                'format'    => 'raw',
                'filter' => \skeeks\cms\helpers\TreeOptions::getAllMultiOptions(),
                'attribute' => 'tree_id'
            ],

            'additionalSections' => [
                'class'     => \yii\grid\DataColumn::class,
                'value'     => function(\skeeks\cms\models\CmsContentElement $model)
                {
                    $result = [];

                    if ($model->cmsContentElementTrees)
                    {
                        foreach ($model->cmsContentElementTrees as $contentElementTree)
                        {

                            $site = $contentElementTree->tree->root->site;
                            $result[] = "<small><a href='{$contentElementTree->tree->url}' target='_blank' data-pjax='0'>[{$site->name}]/.../{$contentElementTree->tree->name}</a></small>";

                        }
                    }

                    return implode('<br />', $result);

                },
                'format' => 'raw',
                'label' => \Yii::t('skeeks/cms','Additional sections'),
                'visible' => false
            ],

            [
                'attribute' => 'active',
                'class' => \skeeks\cms\grid\BooleanColumn::class
            ],

            [
                'class'     => \yii\grid\DataColumn::class,
                'label'     => "Смотреть",
                'value'     => function(\skeeks\cms\models\CmsContentElement $model)
                {

                    return \yii\helpers\Html::a('<i class="glyphicon glyphicon-arrow-right"></i>', $model->absoluteUrl, [
                        'target' => '_blank',
                        'title' => \Yii::t('skeeks/cms','Watch to site (opens new window)'),
                        'data-pjax' => '0',
                        'class' => 'btn btn-default btn-sm'
                    ]);

                },
                'format' => 'raw'
            ]
        ];


        return $columns;
    }



    /**
     * @param CmsContent $model
     * @return array
     */
    static public function getColumns($cmsContent = null, $dataProvider = null)
    {
        return \yii\helpers\ArrayHelper::merge(
            static::getDefaultColumns($cmsContent),
            static::getColumnsByContent($cmsContent, $dataProvider)
        );
    }
}
