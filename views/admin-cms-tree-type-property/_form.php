<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\models\Tree;
use skeeks\cms\modules\admin\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model Tree */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/cms','Basic settings')) ?>

    <?= $form->fieldRadioListBoolean($model, 'active') ?>
    <?= $form->fieldRadioListBoolean($model, 'is_required') ?>


<? if ($content_id = \Yii::$app->request->get('tree_type_id')) : ?>

    <?= $form->field($model, 'tree_type_id')->hiddenInput(['value' => $content_id])->label(false); ?>

<? else: ?>

    <?= $form->field($model, 'tree_type_id')->label(\Yii::t('skeeks/cms','Section type'))->widget(
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
        \Yii::t('skeeks/cms','Base types')          => \Yii::$app->cms->basePropertyTypes(),
        \Yii::t('skeeks/cms','Custom types') => \Yii::$app->cms->userPropertyTypes(),
    ])
        ->label(\Yii::t('skeeks/cms',"Property type"))
        ;
    ?>
    <?= $form->field($model, 'component_settings')->label(false)->widget(
        \skeeks\cms\widgets\formInputs\componentSettings\ComponentSettingsWidget::className(),
        [
            'componentSelectId' => Html::getInputId($model, "component")
        ]
    ); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?= $form->field($model, 'code')->textInput() ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet(\Yii::t('skeeks/cms','Additionally')) ?>
    <?= $form->field($model, 'hint')->textInput() ?>
    <?= $form->fieldInputInt($model, 'priority') ?>


    <?= $form->fieldRadioListBoolean($model, 'searchable') ?>
    <?/*= $form->fieldRadioListBoolean($model, 'filtrable') */?><!--
    --><?/*= $form->fieldRadioListBoolean($model, 'smart_filtrable') */?>
    <?= $form->fieldRadioListBoolean($model, 'with_description') ?>
<?= $form->fieldSetEnd(); ?>

<? if (!$model->isNewRecord) : ?>
<?= $form->fieldSet(\Yii::t('skeeks/cms','Values for list')) ?>

    <?= \skeeks\cms\modules\admin\widgets\RelatedModelsGrid::widget([
        'label'             => \Yii::t('skeeks/cms',"Values for list"),
        'hint'              => \Yii::t('skeeks/cms',"You can snap to the element number of properties, and set the value to them"),
        'parentModel'       => $model,
        'relation'          => [
            'property_id' => 'id'
        ],
        'controllerRoute'   => 'cms/admin-cms-tree-type-property-enum',
        'gridViewOptions'   => [
            'sortable' => true,
            'columns' => [
                /*'id',
                'code',
                'value',
                'priority',
                'def',*/
                [
                    'attribute'     => 'id',
                    'enableSorting' => false
                ],

                [
                    'attribute'     => 'code',
                    'enableSorting' => false
                ],

                [
                    'attribute'     => 'value',
                    'enableSorting' => false
                ],

                [
                    'attribute'     => 'priority',
                    'enableSorting' => false
                ],

                [
                    'class'         => \skeeks\cms\grid\BooleanColumn::className(),
                    'attribute'     => 'def',
                    'enableSorting' => false
                ],
            ],
        ],
    ]); ?>

<?= $form->fieldSetEnd(); ?>
<? endif; ?>

<?= $form->buttonsCreateOrUpdate($model); ?>

<?php ActiveForm::end(); ?>




