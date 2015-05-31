<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 29.05.2015
 */
namespace skeeks\cms\modules\admin\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\components\UrlRule;
use skeeks\cms\modules\admin\controllers\helpers\ActionManager;
use skeeks\cms\modules\admin\filters\AccessControl;
use skeeks\cms\modules\admin\filters\AccessRule;
use skeeks\cms\modules\admin\widgets\ControllerActions;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\validators\HasBehavior;
use skeeks\sx\validate\Validate;
use yii\base\ActionEvent;
use yii\base\InlineAction;
use yii\base\Model;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;

/**
 * @property AdminAction[]    $actions
 *
 * Class AdminController
 * @package skeeks\cms\modules\admin\controllers
 */
abstract class AdminController extends Controller
{
    /**
     * @var null
     * @see parrent::$beforeRender
     */
    public $beforeRender    = null;

    /**
     * @var string Понятное название контроллера, будет добавлено в хлебные крошки и title страницы
     */
    public $name           = 'Название контроллера';

    /**
     * @var null|AdminAction[]
     */
    protected $_actions    = null;

    /**
     * Проверка доступа к админке
     * @return array
     */
    public function behaviors()
    {
        return
        [
            'access' =>
            [
                'class'         => AccessControl::className(),
                'ruleConfig'    => [
                    'class' => AccessRule::className()
                ],

                'rules' =>
                [
                    [
                        'allow' => true,
                        'roles' => [CmsManager::PERMISSION_ADMIN_ACCESS],
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();

        if (!$this->name)
        {
            $this->name = Inflector::humanize($this->id);
        }

        $this->layout = \Yii::$app->cms->moduleAdmin()->layout;
    }

    /**
     * Массив объектов действий доступных для текущего контроллера
     * Используется при построении меню.
     * @see ControllerActions
     * @return AdminAction[]
     */
    public function getActions()
    {
        if ($this->_actions !== null)
        {
            return $this->_actions;
        }

        $actions = $this->actions();

        if ($actions)
        {
            foreach ($actions as $id => $data)
            {
                $action                 = $this->createAction($id);
                $this->_actions[$id]    = $action;
            }
        } else
        {
            $this->_actions = [];
        }

        return $this->_actions;
    }


    /**
     * @return \yii\web\Response
     */
    public function redirectRefresh()
    {
        return $this->redirect(UrlHelper::constructCurrent()->setRoute($this->action->id)->normalizeCurrentRoute()->enableAdmin()->toString());
    }
}