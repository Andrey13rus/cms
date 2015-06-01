<?php

use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;
use skeeks\cms\models\Tree;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\CmsLang */
?>


<?php $form = ActiveForm::begin(); ?>


<?= $form->field($model, 'code')->textInput(); ?>
<?= $form->fieldRadioListBoolean($model, 'active')->hint('На сайте должен быть включен хотя бы один язык'); ?>
<?= $form->field($model, 'name')->textarea(); ?>
<?= $form->field($model, 'description')->textarea(); ?>
<?= $form->fieldInputInt($model, 'priority'); ?>

<?= $form->buttonsStandart($model) ?>

<?php ActiveForm::end(); ?>