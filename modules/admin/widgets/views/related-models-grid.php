<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 12.03.2015
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\modules\admin\widgets\RelatedModelsGrid */
/* @var $controller \skeeks\cms\modules\admin\controllers\AdminModelEditorController */
use \skeeks\cms\modules\admin\widgets\Pjax;
$controller = \Yii::$app->createController($widget->controllerRoute)[0];

?>

<?/* Pjax::begin([
    'id' => $pjaxId,
]); */?>

<? if ($widget->label) : ?>
    <label><?= $widget->label; ?></label>
<? endif;?>

<? if ($widget->hint) : ?>
    <p><small><?= $widget->hint; ?></small></p>
<? endif;?>

<div>
    <a class="btn btn-default btn-xs" onclick="<?= new \yii\web\JsExpression(<<<JS
        new sx.classes.RelationModelsGrid({
        'createUrl': '{$createUrl}',
        'pjaxId': '{$pjaxId}',
        }); return false;
JS
    ); ?>"><i class="glyphicon glyphicon-plus"></i>Добавить</a>

            <?= \skeeks\cms\modules\admin\widgets\GridViewHasSettings::widget($gridOptions); ?>

    <?

        $this->registerJs(<<<JS
        (function(sx, $, _)
        {
            sx.classes.RelationModelsGrid = sx.classes.Component.extend({

                _init: function()
                {
                    var self = this;
                    var window = new sx.classes.Window(this.get('createUrl'));
                    window.bind("close", function()
                    {
                        $.pjax.reload('#' + self.get('pjaxId'), {});
                    });

                    window.open();
                },

                _onDomReady: function()
                {},

                _onWindowReady: function()
                {},
            });
        })(sx, sx.$, sx._);
JS
);
    ?>

</div>

<?/* Pjax::end(); */?>