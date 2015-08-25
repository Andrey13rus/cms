<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.03.2015
 */
use yii\helpers\Html;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab as ActiveForm;

/* @var $this yii\web\View */
/* @var $model \skeeks\cms\models\WidgetConfig */
?>
<?php $form = ActiveForm::begin(); ?>


<?= $form->fieldSet('Основное'); ?>

    <?= $form->field($model, 'enabled')->widget(
        \skeeks\widget\chosen\Chosen::className(),
        [
            'items' => \Yii::$app->formatter->booleanFormat
        ]
    )->hint('Этот параметр отключает/включает панель для всех пользователей сайта, независимо от их прав и возможностей'); ?>


    <?= $form->field($model, 'enableFancyboxWindow')->widget(
        \skeeks\widget\chosen\Chosen::className(),
        [
            'items' => \Yii::$app->formatter->booleanFormat
        ]
    )->hint('Диалоговые окна в сайтовой части будут более красивые, однако это может портить верстку (но это происходит крайне редко)'); ?>

    <?= $form->field($model, 'mode')->widget(
        \skeeks\widget\chosen\Chosen::className(),
        [
            'items' => [
                \skeeks\cms\components\CmsToolbar::EDIT_MODE => 'Включен',
                \skeeks\cms\components\CmsToolbar::NO_EDIT_MODE => 'Выключен'
            ]
        ]
    )->hint('Режим редактирования сайта по умолчаню, изначально.'); ?>

    <?= $form->field($model, 'infoblockEditBorderColor')->widget(
        \skeeks\cms\widgets\ColorInput::className()
    )->hint('Цвет рамки вокруг инфоблоков в режиме редактирования'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Доступ'); ?>

    <?= \skeeks\cms\widgets\rbac\PermissionForRoles::widget([
        'permissionName'        => \skeeks\cms\rbac\CmsManager::PERMISSION_CONTROLL_PANEL,
        'label'                 => 'Доступ к панеле разрешен',
    ]); ?>


<?= $form->fieldSetEnd(); ?>

<?= $form->buttonsCreateOrUpdate($model); ?>
<?php ActiveForm::end(); ?>


