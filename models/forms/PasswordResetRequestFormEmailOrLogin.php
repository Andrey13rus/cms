<?php
/**
 * PasswordResetRequestFormEmailOrLogin
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 26.02.2015
 */
namespace skeeks\cms\models\forms;
use yii\base\Model;
use yii\web\User;

/**
 * Class PasswordResetRequestFormEmailOrLogin
 * @package skeeks\cms\models\forms
 */
class PasswordResetRequestFormEmailOrLogin extends Model
{
    public $identifier;

    /**
     * На какой контроллер формировать ссылку на сброс пароля, админский или сайтовый.
     * @var bool
     */
    public $isAdmin = true;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $identityClassName = \Yii::$app->user->identityClass;
        return [
            ['identifier', 'filter', 'filter' => 'trim'],
            ['identifier', 'required'],
            ['identifier', 'validateEdentifier'],
            /*['email', 'exist',
                'targetClass' => $identityClassName,
                'filter' => ['status' => $identityClassName::STATUS_ACTIVE],
                'message' => 'There is no user with such email.'
            ],*/
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'identifier'    => 'Логин или Email',
        ];
    }

    public function validateEdentifier($attr)
    {
        $identityClassName = \Yii::$app->user->identityClass;
        $user = $identityClassName::findByUsernameOrEmail($this->identifier);

        if (!$user)
        {
            $this->addError($attr, 'Пользователь не найден');
        }
    }
    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $identityClassName = \Yii::$app->user->identityClass;

        $user = $identityClassName::findByUsernameOrEmail($this->identifier);
        //$user = $identityClassName::

        if ($user)
        {
            if (!$identityClassName::isPasswordResetTokenValid($user->password_reset_token))
            {
                $user->generatePasswordResetToken();
            }

            if ($user->save())
            {
                if ($this->isAdmin)
                {
                    $resetLink = \skeeks\cms\helpers\UrlHelper::construct('admin/auth/reset-password', ['token' => $user->password_reset_token])->enableAbsolute()->enableAdmin();
                } else
                {
                    $resetLink = \skeeks\cms\helpers\UrlHelper::construct('cms/auth/reset-password', ['token' => $user->password_reset_token])->enableAbsolute();
                }


                $message = \Yii::$app->mailer->compose('@skeeks/cms/mail/passwordResetToken', [
                        'user'      => $user,
                        'resetLink' => $resetLink
                    ])
                    ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
                    ->setTo($user->email)
                    ->setSubject('Запрос на смену пароля для ' . \Yii::$app->cms->appName);

                return $message->send();
            }
        }

        return false;
    }
}
