<?php
/**
 * AdminUserController
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 31.10.2014
 * @since 1.0.0
 */
namespace skeeks\cms\controllers;

use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\forms\PasswordChangeForm;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\controllers\helpers\rules\HasModel;
use skeeks\cms\widgets\ActiveForm;
use Yii;
use skeeks\cms\models\User;
use skeeks\cms\models\searchs\User as UserSearch;
use yii\base\ActionEvent;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\rbac\Item;
use yii\web\Response;

/**
 * Class AdminUserController
 * @package skeeks\cms\controllers
 */
class AdminUserController extends AdminModelEditorController
{
    public function init()
    {
        $this->name                     = "Управление пользователями";
        $this->modelShowAttribute       = "username";
        $this->modelClassName           = User::className();

        parent::init();
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'create' =>
            [
                'modelScenario' => 'create'
            ],
            'update' =>
            [
                'modelScenario' => 'update'
            ],
            'change-password' =>
            [
                "class"     => AdminOneModelEditAction::className(),
                "name"      => "Изменение пароля",
                "icon"      => "glyphicon glyphicon-cog",
                "callback"  => [$this, "actionChangePassword"],
            ],

            'permission' =>
            [
                "class"     => AdminOneModelEditAction::className(),
                "name"      => "Привилегии",
                "icon"      => "glyphicon glyphicon-exclamation-sign",
                "callback"  => [$this, "actionPermission"],
            ],
        ]);
    }


    /**
     * Updates an existing Game model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionChangePassword()
    {
        $model = $this->model;

        $modelForm = new PasswordChangeForm([
            'user' => $model
        ]);

        $rr = new RequestResponse();

        if (\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax)
        {
            return $rr->ajaxValidateForm($modelForm);
        }


        if ($modelForm->load(\Yii::$app->request->post()) && $modelForm->changePassword())
        {
            \Yii::$app->getSession()->setFlash('success', 'Успешно сохранено');
        } else
        {
            if (\Yii::$app->request->isPost)
            {
                \Yii::$app->getSession()->setFlash('error', 'Не удалось изменить пароль');
            }
        }

        return $this->render($this->action->id, [
            'model' => $modelForm,
        ]);
    }

    /**
     * @return string
     */
    public function actionPermission()
    {
        $model = $this->model;
        $authManager = Yii::$app->authManager;
        $avaliable = [];
        $assigned = [];
        foreach ($authManager->getRolesByUser($model->primaryKey) as $role) {
            $type = $role->type;
            $assigned[$type == Item::TYPE_ROLE ? 'Roles' : 'Permissions'][$role->name] = $role->name;
        }
        foreach ($authManager->getRoles() as $role) {
            if (!isset($assigned['Roles'][$role->name])) {
                $avaliable['Roles'][$role->name] = $role->name;
            }
        }
        foreach ($authManager->getPermissions() as $role) {
            if ($role->name[0] !== '/' && !isset($assigned['Permissions'][$role->name])) {
                $avaliable['Permissions'][$role->name] = $role->name;
            }
        }

        return $this->render('permission', [
                'model' => $model,
                'avaliable' => $avaliable,
                'assigned' => $assigned,
                'idField' => 'id',
                'usernameField' => 'username',
        ]);
    }



    /**
     * Assign or revoke assignment to user
     * @param  integer $id
     * @param  string  $action
     * @return mixed
     */
    public function actionAssign($id, $action)
    {
        $post = Yii::$app->request->post();
        $roles = $post['roles'];
        $manager = Yii::$app->authManager;
        $error = [];
        if ($action == 'assign') {
            foreach ($roles as $name) {
                try {
                    $item = $manager->getRole($name);
                    $item = $item ? : $manager->getPermission($name);
                    $manager->assign($item, $id);
                } catch (\Exception $exc) {
                    $error[] = $exc->getMessage();
                }
            }
        } else {
            foreach ($roles as $name) {
                try {
                    $item = $manager->getRole($name);
                    $item = $item ? : $manager->getPermission($name);
                    $manager->revoke($item, $id);
                } catch (\Exception $exc) {
                    $error[] = $exc->getMessage();
                }
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [$this->actionRoleSearch($id, 'avaliable', $post['search_av']),
            $this->actionRoleSearch($id, 'assigned', $post['search_asgn']),
            $error];
    }

    /**
     * Search roles of user
     * @param  integer $id
     * @param  string  $target
     * @param  string  $term
     * @return string
     */
    public function actionRoleSearch($id, $target, $term = '')
    {
        $authManager = Yii::$app->authManager;
        $avaliable = [];
        $assigned = [];
        foreach ($authManager->getRolesByUser($id) as $role) {
            $type = $role->type;
            $assigned[$type == Item::TYPE_ROLE ? 'Roles' : 'Permissions'][$role->name] = $role->name;
        }
        foreach ($authManager->getRoles() as $role) {
            if (!isset($assigned['Roles'][$role->name])) {
                $avaliable['Roles'][$role->name] = $role->name;
            }
        }
        foreach ($authManager->getPermissions() as $role) {
            if ($role->name[0] !== '/' && !isset($assigned['Permissions'][$role->name])) {
                $avaliable['Permissions'][$role->name] = $role->name;
            }
        }

        $result = [];
        $var = ${$target};
        if (!empty($term)) {
            foreach (['Roles', 'Permissions'] as $type) {
                if (isset($var[$type])) {
                    foreach ($var[$type] as $role) {
                        if (strpos($role, $term) !== false) {
                            $result[$type][$role] = $role;
                        }
                    }
                }
            }
        } else {
            $result = $var;
        }

        return Html::renderSelectOptions('', $result);
    }
}
