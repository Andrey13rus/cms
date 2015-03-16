<?php
/**
 * Селект в который можно добавлять записи
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 16.03.2015
 */
namespace skeeks\cms\widgets\formInputs;

use skeeks\cms\Exception;
use skeeks\cms\models\behaviors\HasFiles;
use skeeks\cms\models\Publication;
use skeeks\cms\modules\admin\widgets\Pjax;
use skeeks\cms\validators\HasBehavior;
use skeeks\sx\validate\Validate;
use skeeks\widget\chosen\Chosen;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use Yii;

/**
 * Class EditedSelect
 * @package skeeks\cms\widgets\formInputs
 */
class EditedSelect extends Chosen
{
    protected $_pjaxId = '';

    /**
     * @var string
     */
    public $createAction = 'create';
    public $updateAction = 'update';

    public $controllerRoute = '';

    public function init()
    {
        $this->_pjaxId = 'pjax-'  . $this->getId();

        Pjax::begin([
            'id' => $this->_pjaxId
        ]);

        parent::init();
    }
    /**
     * @inheritdoc
     */
    public function run()
    {

    echo "<div class='row'>";
        echo "<div class='col-md-6'>";
        if ($this->hasModel())
        {
            echo Html::activeListBox($this->model, $this->attribute, $this->items, $this->options);
        } else
        {
            echo Html::listBox($this->name, $this->value, $this->items, $this->options);
        }
        echo "</div>";

        echo "<div class='col-md-6'>";


        $createUrl = (string) \skeeks\cms\helpers\UrlHelper::construct($this->controllerRoute . '/' . $this->createAction)
                ->setSystemParam(\skeeks\cms\modules\admin\Module::SYSTEM_QUERY_EMPTY_LAYOUT, 'true')
                ->setSystemParam(\skeeks\cms\modules\admin\Module::SYSTEM_QUERY_NO_ACTIONS_MODEL, 'true')
                ->enableAdmin()->toString();

        $updateUrl =  (string) \skeeks\cms\helpers\UrlHelper::construct($this->controllerRoute . '/' . $this->updateAction)
                ->setSystemParam(\skeeks\cms\modules\admin\Module::SYSTEM_QUERY_EMPTY_LAYOUT, 'true')
                ->setSystemParam(\skeeks\cms\modules\admin\Module::SYSTEM_QUERY_NO_ACTIONS_MODEL, 'true')
                ->enableAdmin()->toString();


            echo <<<HTML
            <a href="{$createUrl}" class="btn btn-default sx-btn-create sx-btn-controll" ><span class="glyphicon glyphicon-plus"></span> Создать</a>
            <a href="{$updateUrl}" class="btn btn-default sx-btn-update sx-btn-controll" ><span class="glyphicon glyphicon-pencil"></span> Редактировать</a>
HTML;



        echo "</div>";




    echo "</div>";
        Pjax::end();

        $options = [
            'multiple'              => (int) $this->multiple,
        ];

        $optionsString = Json::encode($options);

        $this->view->registerJs(<<<JS
        (function(sx, $, _)
        {
            sx.classes.FormElementEditedSelect = sx.classes.Widget.extend({

                _init: function()
                {},

                _onDomReady: function()
                {
                    var self = this;

                    $(this.getWrapper()).on('change', 'select', function()
                    {
                        self.updateButtons();
                    });

                    $(this.getWrapper()).on('click', '.sx-btn-create', function()
                    {
                        var windowWidget = new sx.classes.Window($(this).attr('href'));

                        windowWidget.bind('close', function(e, data)
                        {
                            self.reload();
                        });

                        windowWidget.open();

                        return false;
                    });

                    $(this.getWrapper()).on('click', '.sx-btn-update', function()
                    {
                        var windowWidget = new sx.classes.Window($(this).attr('href') + '&id=' + $('select', self.getWrapper()).val());

                        windowWidget.bind('close', function(e, data)
                        {
                            self.reload();
                        });

                        windowWidget.open();

                        return false;
                    });

                    self.updateButtons();
                },

                _onWindowReady: function()
                {},


                updateButtons: function()
                {
                    var self = this;

                    if (!self.get('multiple'))
                    {
                        if ($('select', this.getWrapper()).val())
                        {
                            self.showUpdateControll();
                        } else
                        {
                            self.hideUpdateControll();
                        }
                    }

                    return this;

                },

                /**
                *
                * @returns {sx.classes.FormElementEditedSelect}
                */
                hideUpdateControll: function()
                {
                    $('.sx-btn-update', this.getWrapper()).hide();
                    return this;
                },

                /**
                *
                * @returns {sx.classes.FormElementEditedSelect}
                */
                showUpdateControll: function()
                {
                    $('.sx-btn-update', this.getWrapper()).show();
                    return this;
                },

                reload: function()
                {
                    $.pjax.reload(this.getWrapper(), {});
                }
            });

            new sx.classes.FormElementEditedSelect('#{$this->_pjaxId}', {$optionsString});
        })(sx, sx.$, sx._);
JS
);

    }

}
