<?php
/**
 * ActionColumn
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 30.10.2014
 * @since 1.0.0
 */
namespace skeeks\cms\modules\admin\grid;

use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\widgets\ControllerActions;
use skeeks\cms\modules\admin\widgets\ControllerModelActions;
use skeeks\cms\modules\admin\widgets\DropdownControllerActions;
use yii\base\InvalidConfigException;
use yii\grid\DataColumn;

/**
 * Class ActionColumn
 * @package skeeks\cms\modules\admin\grid
 */
class ActionColumn extends DataColumn
{
    public $filter          = false;

    /**
     * @var AdminModelEditorController
     */
    public $controller              = null;
    public $isOpenNewWindow         = null;
    public $clientOptions           = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->controller)
        {
            throw new InvalidConfigException("controller - не определен.");
        }

        if (!$this->controller instanceof AdminModelEditorController)
        {
            throw new InvalidConfigException("controller должен быть наследован от: " . AdminModelEditorController::className());
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        //print_r($this->grid->pjax);die;
        //Строемся в гриде который использует pjax
        if ($this->grid->pjax)
        {
            $this->clientOptions['pjax-id'] = $this->grid->pjax->options['id'];
        }

        $controller = clone $this->controller;
        $controller->model = $model;

        return DropdownControllerActions::widget([
            "controller"            => $controller,
            "isOpenNewWindow"       => $this->isOpenNewWindow,
            "clientOptions"         => $this->clientOptions,
        ]);
    }
}