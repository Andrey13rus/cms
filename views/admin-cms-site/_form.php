<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\models\Tree;

/* @var $this yii\web\View */
/* @var $model Tree */
?>


<?php $form = ActiveForm::begin(); ?>


<?= $form->fieldSet("Основное"); ?>

    <?= $form->field($model, 'code')->textInput(); ?>
    <?= $form->fieldRadioListBoolean($model, 'active'); ?>
    <?= $form->fieldRadioListBoolean($model, 'def'); ?>
    <?= $form->field($model, 'name')->textarea(); ?>
    <?= $form->fieldSelect($model, 'lang_code', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsLang::find()->active()->all(),
        'code',
        'name'
    )); ?>

    <?= $form->field($model, 'description')->textarea(); ?>
    <?= $form->field($model, 'server_name')->textInput(['maxlength' => 255]) ?>
    <?= $form->fieldInputInt($model, 'priority'); ?>

<?= $form->fieldSetEnd(); ?>

<? if (!$model->isNewRecord) : ?>
    <?= $form->fieldSet("Домены"); ?>

        <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
            'label'             => "",
            'hint'              => "",
            'parentModel'       => $model,
            'relation'          => [
                'site_code' => 'code'
            ],

            'controllerRoute'   => 'cms/admin-cms-site-domain',
            'gridViewOptions'   => [
                'columns' => [
                    //['class' => 'yii\grid\SerialColumn'],
                    'domain',
                ],
            ],
        ]); ?>

    <?= $form->fieldSetEnd(); ?>
<? endif; ?>
<?= $form->buttonsStandart($model) ?>

<?php ActiveForm::end(); ?>