<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 20.06.2015
 */
namespace skeeks\cms\controllers;

use skeeks\cms\base\Controller;
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\CmsSearchPhrase;
use skeeks\cms\models\Tree;
use Yii;
use yii\web\Response;

/**
 * Class SearchController
 * @package skeeks\cms\controllers
 */
class SearchController extends Controller
{
    /**
     * Динамическая отдача robots.txt
     */
    public function actionResult()
    {
        $searchQuery = \Yii::$app->cmsSearch->searchQuery;
        $this->view->title = StringHelper::ucfirst($searchQuery) . " — результаты поиска";

        return $this->render($this->action->id);
    }
}
