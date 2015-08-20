<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.02.2015
 */
namespace skeeks\cms\models\user;

use skeeks\cms\components\Cms;
use skeeks\cms\models\User;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use \yii\db\ActiveRecord;

/**
 * @property User $user
 *
 * Class UserEmail
 * @package skeeks\cms\models\user
 */
class UserEmail extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_user_email}}';
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            TimestampBehavior::className()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['value', 'approved_key'], 'string'],
            [['value'], 'email'],
            [['value'], 'unique'],
            [['approved'], 'string'],
            [['approved'], 'default', 'value' => Cms::BOOL_N],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['create'] = ['value', 'user_id'];
        $scenarios['update'] = ['value', 'user_id'];
        $scenarios['nouser'] = ['value'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'Пользователь'),
            'value' => "Email",
            'approved' => "Подтвержден",
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        $userClass = \Yii::$app->user->identityClass;
        return $this->hasOne($userClass::className(), ['id' => 'user_id']);
    }


    /**
     * Этот email является главным?
     *
     * @return bool
     */
    public function isMain()
    {
        if ($this->user)
        {
            if ($this->user->email == $this->value)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Сделать этот email основным для пользователя.
     *
     * @return $this
     * @throws Exception
     */
    public function setMainForUser()
    {
        if (!$this->user)
        {
            throw new Exception("Email не привязан к пользователю");
        }

        if ($this->isMain())
        {
            return $this;
        }

        $this->user->email    = $this->value;
        $this->user->scenario = "update";
        if (!$this->user->save())
        {
            throw new Exception("Не удалось сохранить данные пользователя");
        }

        return $this;
    }

    /**
     * @return bool|int
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->isMain())
        {
            throw new Exception("Этот email является основным и не может быть удален. Для начала его необходимо отвязать.");
        }

        return parent::delete();
    }

}
