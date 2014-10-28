<?php
/**
 * Game
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 18.10.2014
 * @since 1.0.0
 */
namespace skeeks\cms\models;

use Yii;

use skeeks\cms\db\ActiveRecord;

use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

use skeeks\sx\models\Ref;
use yii\base\Event;
/**
 * This is the model class for table "{{%vote}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $value
 * @property string $linked_to
 *
 * @property User $updatedBy
 * @property User $createdBy
 */
class Vote extends ActiveRecord
{

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_INSERT, [$this, "_reCalculateModel"]);
        $this->on(self::EVENT_AFTER_DELETE, [$this, "_reCalculateModel"]);
    }

    /**
     * После добавления подписки нун
     * @param Event $e
     * @return $this
     */
    protected function _reCalculateModel(Event $e)
    {
        $ref = new Ref($e->sender->linked_to_model, $e->sender->linked_to_value);
        $model = $ref->findModel();
        $model->calculateVotes();
        return $this;
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%vote}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            BlameableBehavior::className(),
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'value'], 'integer'],
            [['linked_to_model', 'linked_to_value'], 'required'],
            [['linked_to_model', 'linked_to_value'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'value' => Yii::t('app', 'Value'),
            'linked_to_model' => Yii::t('app', 'Linked To Model'),
            'linked_to_value' => Yii::t('app', 'Linked To Value'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
