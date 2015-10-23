<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 29.05.2015
 */
namespace skeeks\cms\modules\admin\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\exceptions\NotConnectedToDbException;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\components\UrlRule;
use skeeks\cms\modules\admin\controllers\events\AdminInitEvent;
use skeeks\cms\modules\admin\controllers\helpers\ActionManager;
use skeeks\cms\modules\admin\filters\AccessControl;
use skeeks\cms\modules\admin\filters\AccessRule;
use skeeks\cms\modules\admin\filters\AdminAccessControl;
use skeeks\cms\modules\admin\filters\AdminLastActivityAccessControl;
use skeeks\cms\modules\admin\widgets\ControllerActions;
use skeeks\cms\rbac\CmsManager;
use skeeks\cms\validators\HasBehavior;
use skeeks\sx\validate\Validate;
use yii\base\ActionEvent;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InlineAction;
use yii\base\Model;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Inflector;
use yii\web\ForbiddenHttpException;

/**
 * @property AdminAction[]      $actions
 * @property string             $permissionName
 *
 * Class AdminController
 * @package skeeks\cms\modules\admin\controllers
 */
abstract class AdminController extends Controller
{
    const EVENT_INIT                   = 'event.adminController.init';

    /**
     * @var null
     * @see parrent::$beforeRender
     */
    public $beforeRender    = null;

    /**
     * @var string Понятное название контроллера, будет добавлено в хлебные крошки и title страницы
     */
    public $name           = '';

    /**
     * @var null|AdminAction[]
     */
    protected $_actions    = null;

    /**
     * После инициализации, контроллера, любой компонент, может добавить свои дейсвия, они будут добавлены к текущим дейсвоиям контроллера.
     * @see init()
     * @see actions()
     * @var array
     */
    public $eventActions = [];


    /**
     * @return string
     */
    public function getPermissionName()
    {
        return $this->getUniqueId();
    }

    /**
     * Проверка доступа к админке
     * @return array
     */
    public function behaviors()
    {
        return
        [
            //Проверка доступа к админ панели
            'adminAccess' =>
            [
                'class'         => AdminAccessControl::className(),
                'rules' =>
                [
                    [
                        'allow'         => true,
                        'roles'         =>
                        [
                            CmsManager::PERMISSION_ADMIN_ACCESS
                        ],
                    ],
                ]
            ],

            //Стандартная проверка доступности действия. Если действие заведено, в привилегиях, то проверяется наличие у пользователя
            'adminActionsAccess' =>
            [
                'class'         => AdminAccessControl::className(),
                'rules' =>
                [
                    [
                        'allow'         => true,
                        'matchCallback' => function($rule, $action)
                        {
                            if ($permission = \Yii::$app->authManager->getPermission($this->permissionName))
                            {
                                if (!\Yii::$app->user->can($permission->name))
                                {
                                    return false;
                                }
                            }

                            return true;
                        }
                    ]
                ],
            ],

            'adminLastActivityAccess' =>
            [
                'class'         => AdminLastActivityAccessControl::className(),
                'rules' =>
                [
                    [
                        'allow'         => true,
                        'matchCallback' => function($rule, $action)
                        {
                            if (\Yii::$app->user->identity->lastAdminActivityAgo > \Yii::$app->admin->blockedTime)
                            {
                                return false;
                            }

                            if (\Yii::$app->user->identity)
                            {
                                \Yii::$app->user->identity->updateLastAdminActivity();
                            }

                            return true;
                        }
                    ]
                ],
            ],
        ];
    }


    public function init()
    {
        parent::init();

        //TODO: временное решение, проверка наличия соединенеия с базой, если этого не будет, то при проверки прав будут ошибки.
        try
        {
            \Yii::$app->db->open();
            if (!\Yii::$app->db->schema->getTableNames())
            {
                throw new NotConnectedToDbException;
            }

        } catch(\yii\db\Exception $e)
        {
            if (in_array($e->getCode(), NotConnectedToDbException::$invalidConnectionCodes))
            {
                throw new NotConnectedToDbException;
            }
        }


        if (!$this->name)
        {
            $this->name = \Yii::t('app','The name of the controller'); //Inflector::humanize($this->id);
        }

        $this->layout = \Yii::$app->cms->moduleAdmin()->layout;


        \Yii::$app->trigger(self::EVENT_INIT, new AdminInitEvent([
            'name'          => self::EVENT_INIT,
            'controller'    => $this
        ]));
    }

    /**
     * @return array
     */
    public function actions()
    {
        return ArrayHelper::merge($this->eventActions, []);
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

                if ($action->isVisible())
                {
                    $this->_actions[$id]    = $action;
                }
            }
        } else
        {
            $this->_actions = [];
        }

        //Сортировка по приоритетам
        if ($this->_actions)
        {
            ArrayHelper::multisort($this->_actions, 'priority');

        }

        return $this->_actions;
    }


}