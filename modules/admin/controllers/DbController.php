<?php
/**
 * DbController
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 08.11.2014
 * @since 1.0.0
 */
namespace skeeks\cms\modules\admin\controllers;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\Search;
use skeeks\cms\modules\admin\actions\AdminAction;
use skeeks\cms\modules\admin\controllers\helpers\rules\NoModel;
use skeeks\sx\Dir;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class IndexController
 * @package skeeks\cms\modules\admin\controllers
 */
class DbController extends AdminController
{
    public function init()
    {
        $this->name = "Удаление временных файлов";

        parent::init();
    }


    public function actions()
    {
        return
        [
            "index" =>
            [
                "class"        => AdminAction::className(),
                "name"         => "Работа с базой данных",
                "callback"     => [$this, 'actionIndex'],
            ],
        ];
    }


    public function actionIndex()
    {
        $message = '';

        if (\Yii::$app->request->isPost)
        {
            if (\Yii::$app->request->getQueryParam('act') == 'refresh-tables')
            {
                \Yii::$app->db->getSchema()->refresh();
                \Yii::$app->getSession()->setFlash('success', 'Кэш таблиц успешно обновлен');
            }
        }


        $dataProvider = new ArrayDataProvider([
            'allModels' => \Yii::$app->db->getSchema()->getTableSchemas(),
            'sort' => [
                'attributes' => ['name', 'fullName'],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        return $this->render('index', [
            'dataProvider'  => $dataProvider,
        ]);
    }


}