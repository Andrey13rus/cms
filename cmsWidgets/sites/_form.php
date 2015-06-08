<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.05.2015
 */
/* @var $this yii\web\View */
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
?>
<?php $form = ActiveForm::begin(); ?>
    <?= $form->fieldSet('Отображение'); ?>
        <?= $form->field($model, 'viewFile')->textInput(); ?>
    <?= $form->fieldSetEnd(); ?>

    <?= $form->fieldSet('Фильтрация'); ?>
        <?= $form->fieldSelect($model, 'active', \Yii::$app->cms->booleanFormat()); ?>
    <?= $form->fieldSetEnd(); ?>

    <?= $form->fieldSet('Сортировка'); ?>
        <?= $form->fieldSelect($model, 'orderBy', (new \skeeks\cms\models\CmsSite())->attributeLabels()); ?>
        <?= $form->fieldSelect($model, 'order', [
            SORT_ASC    => "ASC (от меньшего к большему)",
            SORT_DESC   => "DESC (от большего к меньшему)",
        ]); ?>
    <?= $form->fieldSetEnd(); ?>

    <?= $form->fieldSet('Дополнительно'); ?>
        <?= $form->field($model, 'label')->textInput(); ?>
    <?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Настройки кэширования'); ?>
        <?= $form->fieldRadioListBoolean($model, 'enabledRunCache', \Yii::$app->cms->booleanFormat()); ?>
        <?= $form->fieldInputInt($model, 'runCacheDuration'); ?>
    <?= $form->fieldSetEnd(); ?>



<?= $form->buttonsStandart($model) ?>
<?php ActiveForm::end(); ?>