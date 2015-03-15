<?php
/**
 * User
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 20.10.2014
 * @since 1.0.0
 */

namespace skeeks\cms\models;

use Imagine\Image\ManipulatorInterface;
use skeeks\cms\base\db\ActiveRecord;

use skeeks\cms\models\behaviors\HasFiles;
use skeeks\cms\models\behaviors\HasRef;
use skeeks\cms\models\user\UserEmail;
use skeeks\cms\validators\string\LoginValidator;
use skeeks\cms\validators\user\UserEmailValidator;
use skeeks\sx\validate\Validate;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;
use yii\web\IdentityInterface;

use skeeks\cms\models\behaviors\HasSubscribes;

/**
 * This is the model class for table "{{%cms_user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $city
 * @property string $address
 * @property string $info
 * @property string $image
 * @property string $image_cover
 * @property string $gender
 *
 * @property Comment[] $comments
 * @property Publication[] $publications
 * @property StorageFile[] $storageFiles
 * @property Subscribe[] $subscribes
 * @property StorageFile $imageCover
 * @property StorageFile $image0
 * @property UserAuthclient[] $userAuthclients
 * @property Vote[] $votes
 */
class User
    extends ActiveRecord
    implements IdentityInterface
{
    use behaviors\traits\HasSubscribes;
    use behaviors\traits\HasFiles;

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const ROLE_USER = 10;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_user}}';
    }

    /**
     * Логины которые нельзя удалять, и нельзя менять
     * @return array
     */
    static public function getProtectedUsernames()
    {
        return ['root', 'admin'];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(BaseActiveRecord::EVENT_BEFORE_INSERT,    [$this, "checkDataBeforeInsert"]);
        $this->on(BaseActiveRecord::EVENT_BEFORE_UPDATE,    [$this, "checkDataBeforeUpdate"]);

        $this->on(BaseActiveRecord::EVENT_AFTER_INSERT,    [$this, "checkDataAfterSave"]);
        $this->on(BaseActiveRecord::EVENT_AFTER_UPDATE,    [$this, "checkDataAfterSave"]);

        $this->on(BaseActiveRecord::EVENT_BEFORE_DELETE,    [$this, "checkDataBeforeDelete"]);
    }

    /**
     * @throws Exception
     */
    public function checkDataBeforeDelete()
    {
        if (in_array($this->username, static::getProtectedUsernames()))
        {
            throw new Exception('Этого пользователя нельзя удалить');
        }

        if ($this->id == \Yii::$app->cms->getAuthUser()->id)
        {
            throw new Exception('Нельзя удалять самого себя');
        }

        /*$userEmail = UserEmail::find()->where(['value' => $this->email])->one();
        $userEmail->user_id = null;
        $userEmail->save();*/
    }


    public function checkDataAfterSave()
    {
        if ($this->email)
        {
            $email              = UserEmail::find()->where(['value' => $this->email])->one();

            if ($email)
            {
                $email->user_id     = $this->id;
                $email->save();
            }

        }
    }

    public function checkDataBeforeInsert()
    {
        //Если установлен email новому пользователю
        if ($this->email)
        {
            //Проверим
            Validate::ensure(new UserEmailValidator(), $this);

            //Создаем mail
            $email = new UserEmail([
                'value' => $this->email
            ]);

            $email->scenario = "nouser";

            if (!$email->save())
            {
                throw new ErrorException("Email не удалось добавить");
            };
        }
    }

    public function checkDataBeforeUpdate()
    {
        if ($this->email)
        {
            //Если email изменен
            if ($this->oldAttributes['email'] != $this->email)
            {
                Validate::ensure(new UserEmailValidator(), $this);

                if (!$myEmail = $this->findEmail()->where(["value" => $this->email])->one())
                {
                    $email = new UserEmail([
                        'value' => $this->email,
                        'user_id' => $this->id
                    ]);

                    if (!$email->save())
                    {
                        throw new ErrorException("Email не удалось добавить");
                    };
                }

            }
        }
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

            TimestampBehavior::className(),
            HasSubscribes::className(),

            behaviors\HasFiles::className() =>
            [
                "class"  => behaviors\HasFiles::className(),
                "groups" =>
                [
                    "image" =>
                    [
                        'name'      => 'Аватар',
                    ],
                ]
            ],
        ]);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenario = $scenarios[self::SCENARIO_DEFAULT];

        foreach ($scenario as $key => $code)
        {
            if (in_array($code, ['auth_key', 'password_hash']))
            {
                unset($scenario[$key]);
            }
        }

        $scenarios['create'] = $scenario;
        $scenarios['update'] = $scenario;

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            ['role', 'default', 'value' => self::ROLE_USER],
            ['role', 'in', 'range' => [self::ROLE_USER]],

            [['username', 'auth_key', 'password_hash'], 'required'],
            [['role', 'status', 'created_at', 'updated_at', 'group_id'], 'integer'],
            [['info', 'gender', 'status_of_life'], 'string'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'name', 'city', 'address'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],

            [['email'], 'required'],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['email'], 'validateEmails'],

            [['username'], 'required'],
            ['username', 'string', 'min' => 3, 'max' => 12],
            [['username'], 'unique'],
            [['username'], 'validateLogin'],
        ];
    }

    /**
     * @param $attribute
     */
    public function validateLogin($attribute)
    {
        $validate = Validate::validate(new LoginValidator(), $this->$attribute);

        if ($validate->isInvalid())
        {
            $this->addError($attribute, $validate->getErrorMessage());
        }
    }

    /**
     * Проверка базы email адресов
     * @param $attribute
     */
    public function validateEmails($attribute)
    {
        $validate = Validate::validate(new UserEmailValidator(), $this);

        if ($validate->isInvalid())
        {
            $this->addError($attribute, $validate->getErrorMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Логин'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'email' => Yii::t('app', 'Email'),
            'phone' => Yii::t('app', 'Телефон'),
            'role' => Yii::t('app', 'Role'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'name' => Yii::t('app', 'Имя'),
            'city' => Yii::t('app', 'Город'),
            'address' => Yii::t('app', 'Адрес'),
            'info' => Yii::t('app', 'Информация'),
            'gender' => Yii::t('app', 'Пол'),
            'status_of_life' => Yii::t('app', 'Статус'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['created_by' => 'id']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublications()
    {
        return $this->hasMany(Publication::className(), ['created_by' => 'id']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStorageFiles()
    {
        return $this->hasMany(StorageFile::className(), ['created_by' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscribes()
    {
        return $this->hasMany(Subscribe::className(), ['created_by' => 'id']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAuthclients()
    {
        return $this->hasMany(UserAuthclient::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVotes()
    {
        return $this->hasMany(Vote::className(), ['created_by' => 'id']);
    }




    /**
     * @param $name
     * @return \yii\db\ActiveQuery|null
     */
    public function getSubscribesModels($name)
    {
        $subscribes = $this->getSubscribes()->where(["linked_to_model" => $name::className()])->all();
        if ($subscribes)
        {
            $ids = [];
            foreach ($subscribes as $subscribe)
            {
                $ids[] = $subscribe->getAttribute("linked_to_value");
            }

            if ($ids)
            {
                return $name::find()->where(["id" => $ids]);
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->name ? $this->name : $this->username;
    }

    public function getPageUrl()
    {
        return \Yii::$app->urlManager->createUrl(["cms/user/view", "username" => $this->username]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email
     *
     * @param $email
     * @return static
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * Поиск пользователя по email или логину
     * @param $value
     * @return User
     */
    static public function findByUsernameOrEmail($value)
    {
        if ($user = static::findByUsername($value))
        {
            return $user;
        }

        if ($user = static::findByEmail($value))
        {
            return $user;
        }

        return null;
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }







    /**
     * @return \yii\db\ActiveQuery
     */
    public function findEmail()
    {
        return $this->hasOne(UserEmail::className(), ['user_id' => 'id']);
    }


    /**
     * @param int $width
     * @param int $height
     * @param $mode
     * @return mixed|null|string
     */
    public function getAvatarSrc($width = 50, $height = 50, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND)
    {
        if ($this->hasMainImage())
        {
            return \Yii::$app->imaging->getImagingUrl($this->getMainImageSrc(), new \skeeks\cms\components\imaging\filters\Thumbnail([
                'w'    => $width,
                'h'    => $height,
                'm'    => $mode,
            ]));
        }

        return null;
    }

}
