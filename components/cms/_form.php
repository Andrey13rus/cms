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

$templates = [];

foreach (\Yii::$app->cms->templates as $code => $data)
{
    $templates[$code] = \yii\helpers\ArrayHelper::getValue($data, 'name');
}

$emailTemplates = [];

foreach (\Yii::$app->cms->emailTemplates as $code => $data)
{
    $emailTemplates[$code] = \yii\helpers\ArrayHelper::getValue($data, 'name');
}
?>

<?= $form->fieldSet(\Yii::t('app', 'Main')); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => \Yii::t('app', 'Main')
    ])?>
    <?= $form->field($model, 'appName')->textInput()->hint(''); ?>

    <?= $form->field($model, 'noImageUrl')->widget(
        \skeeks\cms\modules\admin\widgets\formInputs\OneImage::className()
    )->hint('Это изображение показывается в тех случаях, когда не найдено основное.'); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Шаблоны/отображение'
    ])?>

    <?= $form->fieldSelect($model, 'template', $templates); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Языковые настройки'
    ])?>

    <?= $form->fieldSelect($model, 'languageCode', \yii\helpers\ArrayHelper::map(
        \skeeks\cms\models\CmsLang::find()->active()->all(),
        'code',
        'name'
    )); ?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => 'Email'
    ])?>

    <?= $form->field($model, 'adminEmail')->textInput()->hint('E-Mail администратора сайта. Этот email будет отображаться как отправитель, в отправленных письмах с сайта.'); ?>
    <?= $form->field($model, 'notifyAdminEmailsHidden')->textarea()->hint('E-Mail адрес или список адресов через запятую на который будут дублироваться все исходящие сообщения. Скрытая копия!'); ?>
    <?= $form->field($model, 'notifyAdminEmails')->textarea()->hint('E-Mail адрес или список адресов через запятую на который будут дублироваться все исходящие сообщения. Эти email адреса будут отображаться в открытой копии.'); ?>

    <?= $form->fieldSelect($model, 'emailTemplate', $emailTemplates); ?>

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet('Безопасность'); ?>
    <?= $form->fieldInputInt($model, 'passwordResetTokenExpire')->hint('Другими словами, ссылки на восстановление пароля перестанут работать через указанное время'); ?>

    <hr />
    <? \yii\bootstrap\Alert::begin([
        'options' => [
          'class' => 'alert-warning',
      ],
    ]); ?>
    <b>Внимание!</b> аккуратно используйте эту настройку.
    <? \yii\bootstrap\Alert::end()?>

    <?= $form->fieldRadioListBoolean($model, 'enabledHttpAuth')->hint('Очень осторожно включайте эту настройку! Вы не сможете попасть ни на одну страницу сайта, без логина и пароля указанного ниже.'); ?>
    <?= $form->fieldRadioListBoolean($model, 'enabledHttpAuthAdmin'); ?>
    <?= $form->field($model, 'httpAuthLogin')->textInput(); ?>
    <?= $form->field($model, 'httpAuthPassword')->textInput(); ?>

<?= $form->fieldSetEnd(); ?>


<?= $form->fieldSet('Авторизация'); ?>
    <?= $form->fieldSelectMulti($model, 'registerRoles',
        \yii\helpers\ArrayHelper::map(\Yii::$app->authManager->getRoles(), 'name', 'description')
    )->hint('Так же после созданию пользователя, ему будут назначены, выбранные группы.'); ?>

<?= $form->fieldSetEnd(); ?>

<?= $form->fieldSet('Доступ'); ?>

     <? \yii\bootstrap\Alert::begin([
        'options' => [
          'class' => 'alert-warning',
      ],
    ]); ?>
    <b>Внимание!</b> Права доступа сохраняются в режиме реального времени. Так же эти настройки не зависят от сайта или пользователя.
    <? \yii\bootstrap\Alert::end()?>

    <?= \skeeks\cms\modules\admin\widgets\BlockTitleWidget::widget([
        'content' => "Файлы"
    ])?>

    <?= \skeeks\cms\widgets\rbac\PermissionForRoles::widget([
        'permissionName'        => \skeeks\cms\rbac\CmsManager::PERMISSION_ELFINDER_USER_FILES,
        'label'                 => 'Доступ к личным файлам',
    ]); ?>

    <?= \skeeks\cms\widgets\rbac\PermissionForRoles::widget([
        'permissionName'        => \skeeks\cms\rbac\CmsManager::PERMISSION_ELFINDER_COMMON_PUBLIC_FILES,
        'label'                 => 'Доступ к общим файлам',
    ]); ?>


    <?= \skeeks\cms\widgets\rbac\PermissionForRoles::widget([
        'permissionName'        => \skeeks\cms\rbac\CmsManager::PERMISSION_ELFINDER_ADDITIONAL_FILES,
        'label'                 => 'Доступ ко всем файлам',
    ]); ?>


<?= $form->fieldSetEnd(); ?>



