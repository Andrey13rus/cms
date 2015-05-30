<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 17.05.2015
 */
namespace skeeks\cms\controllers;

use skeeks\cms\models\CmsContentProperty;
use skeeks\cms\models\CmsContentType;
use skeeks\cms\models\CmsTreeTypeProperty;
use skeeks\cms\models\CmsTreeTypePropertyEnum;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\modules\admin\controllers\AdminModelEditorSmartController;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use Yii;
use skeeks\cms\models\User;
use skeeks\cms\models\searchs\User as UserSearch;

/**
 * Class AdminCmsContentPropertyController
 * @package skeeks\cms\controllers
 */
class AdminCmsTreeTypePropertyEnumController extends AdminModelEditorSmartController
{
    public function init()
    {
        $this->name                   = "Управление значениями свойств раздела";
        $this->modelShowAttribute      = "value";
        $this->modelClassName          = CmsTreeTypePropertyEnum::className();

        parent::init();

    }

}
