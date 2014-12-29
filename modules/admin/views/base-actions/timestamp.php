
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\Page */
/* @var $form yii\widgets\ActiveForm */
?>


<?php $form = ActiveForm::begin(); ?>
<hr />

<? if (\skeeks\sx\validate\Validate::validate(
    new \skeeks\cms\validators\HasBehavior(\yii\behaviors\TimestampBehavior::className()), $model
)->isValid()) : ?>
    <?= $form->field($model, 'created_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        //'displayFormat' => 'php:d-M-Y H:i:s',
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
    ]); ?>
<? endif;?>



<? if (\skeeks\sx\validate\Validate::validate(
    new \skeeks\cms\validators\HasBehavior(\skeeks\cms\models\behaviors\TimestampPublishedBehavior::className()), $model
)->isValid()) : ?>

    <?= $form->field($model, 'published_at')->widget(\kartik\datecontrol\DateControl::classname(), [
        //'displayFormat' => 'php:d-M-Y H:i:s',
        'type' => \kartik\datecontrol\DateControl::FORMAT_DATETIME,
    ]); ?>
<? endif;?>
<div class="form-group">
    <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

