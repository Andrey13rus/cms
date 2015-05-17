<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \yii\db\ActiveRecord */
/* @var $console \skeeks\cms\controllers\AdminUserController */
?>


<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Основное'); ?>

    <? if ($content_type = \Yii::$app->request->get('content_type')) : ?>
        <?= $form->field($model, 'content_type')->hiddenInput(['value' => $content_type])->label(false); ?>
    <? else: ?>
        <div style="display: none;">
            <?= $form->fieldSelect($model, 'content_type', \yii\helpers\ArrayHelper::map(\skeeks\cms\models\CmsContentType::find()->all(), 'code', 'name')); ?>
        </div>
    <? endif; ?>

    <?= $form->field($model, 'image')->widget(
        \skeeks\cms\modules\admin\widgets\formInputs\StorageImages::className(),
        [
            'fileGroup' => 'image',
        ]
    )->label('Изображение'); ?>

    <?= $form->field($model, 'name')->textInput(); ?>
    <?= $form->field($model, 'code')->textInput(); ?>
    <?= $form->fieldRadioListBoolean($model, 'active'); ?>
    <?= $form->fieldInputInt($model, 'priority'); ?>

    <?= $form->fieldRadioListBoolean($model, 'index_element'); ?>
    <?= $form->fieldRadioListBoolean($model, 'index_tree'); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Подписи'); ?>
    <?= $form->field($model, 'trees_name')->textInput(); ?>
    <?= $form->field($model, 'tree_name')->textInput(); ?>

    <?= $form->field($model, 'elements_name')->textInput(); ?>
    <?= $form->field($model, 'element_name')->textInput(); ?>
<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>
