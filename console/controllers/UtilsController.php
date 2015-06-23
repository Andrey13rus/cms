<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 18.06.2015
 */
namespace skeeks\cms\console\controllers;

use skeeks\cms\base\console\Controller;
use Yii;
use yii\console\controllers\HelpController;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Полезные утилиты SkeekS CMS
 *
 * @package skeeks\cms\console\controllers
 */
class UtilsController extends Controller
{
    /**
     * Проверка какие библиотечные файлы были заменены вручную (папка vendor)
     * Не желательно менять библиотечные файлы, поскольку при следующем обновлении все изменения могут быть стерты.
     */
    public function actionVendorStatus()
    {
        $this->systemCmdRoot("php composer.phar status -v");
    }

    /**
     * Проверка какие библиотечные файлы были заменены вручную (папка vendor)
     * Не желательно менять библиотечные файлы, поскольку при следующем обновлении все изменения могут быть стерты.
     */
    public function actionAllCmd()
    {
        /**
         * @var $controllerHelp HelpController
         */
        $controllerHelp = \Yii::$app->createController('help')[0];
        $commands = $controllerHelp->getCommands();

        foreach ($controllerHelp->getCommands() as $controller)
        {
            $subController = \Yii::$app->createController($controller)[0];
            $actions = $controllerHelp->getActions($subController);

            if ($actions)
            {
                foreach ($actions as $actionId)
                {
                    $commands[] = $controller . "/" . $actionId;
                }
            }
        };

        $this->stdout(implode("\n", $commands));
    }
}