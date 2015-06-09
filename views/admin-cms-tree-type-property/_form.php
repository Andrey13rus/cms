<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\models\Tree;
use skeeks\cms\modules\admin\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model Tree */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet('Основные настройки') ?>

    <?= $form->fieldRadioListBoolean($model, 'active') ?>

<? if ($content_id = \Yii::$app->request->get('tree_type_id')) : ?>

    <?= $form->field($model, 'tree_type_id')->hiddenInput(['value' => $content_id])->label(false); ?>

<? else: ?>

    <?= $form->field($model, 'tree_type_id')->label('Тип раздела')->widget(
        \skeeks\cms\widgets\formInputs\EditedSelect::className(), [
            'items' => \yii\helpers\ArrayHelper::map(
                 \skeeks\cms\models\CmsTreeType::find()->active()->all(),
                 "id",
                 "name"
             ),
            'controllerRoute' => 'cms/admin-cms-tree-type',
        ]);
    ?>

<? endif; ?>

    <?= $form->fieldSelect($model, 'component', [
        'Базовые типы'          => \Yii::$app->cms->basePropertyTypes(),
        'Пользовательские типы' => \Yii::$app->cms->userPropertyTypes(),
    ])->label("Тип свойства");; ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'hint')->textInput() ?>
    <?= $form->field($model, 'code')->textInput() ?>

    <?/*= $form->fieldRadioListBoolean($model, 'multiple') */?>
    <?= $form->fieldRadioListBoolean($model, 'is_required') ?>

    <?= $form->fieldRadioListBoolean($model, 'searchable') ?>
    <?= $form->fieldRadioListBoolean($model, 'filtrable') ?>
    <?= $form->fieldRadioListBoolean($model, 'smart_filtrable') ?>
    <?= $form->fieldRadioListBoolean($model, 'with_description') ?>

    <?= $form->fieldInputInt($model, 'priority') ?>

    <?/*= $form->field($model, 'default_value')->textInput() */?>
<!--

    --><?/*= $form->fieldInputInt($model, 'multiple_cnt') */?>


<?= $form->fieldSetEnd(); ?>

<? if (!$model->isNewRecord) : ?>
<?= $form->fieldSet('Значения для списка') ?>

    <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
        'label'             => "Значения для списка",
        'hint'              => "Вы можете привязать к элементу несколько свойст, и задать им значение",
        'parentModel'       => $model,
        'relation'          => [
            'property_id' => 'id'
        ],

        'sort'              => [
            'defaultOrder' =>
            [
                'priority' => SORT_DESC
            ]
        ],

        'controllerRoute'   => 'cms/admin-cms-tree-type-property-enum',
        'gridViewOptions'   => [
            'sortable' => true,
            'columns' => [
                'id',
                'code',
                'value',
                'priority',
                'def',
            ],
        ],
    ]); ?>

<?= $form->fieldSetEnd(); ?>
<? endif; ?>

<?= $form->buttonsCreateOrUpdate($model); ?>

<?php ActiveForm::end(); ?>




