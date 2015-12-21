<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (�����)
 * @date 21.12.2015
 */
namespace skeeks\cms\models;

use skeeks\cms\models\query\SourceMessageQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

class SourceMessage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->get(Yii::$app->i18n->db);
    }
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public static function tableName()
    {
        $i18n = Yii::$app->i18n;
        if (!isset($i18n->sourceMessageTable)) {
            throw new InvalidConfigException('You should configure i18n component');
        }
        return $i18n->sourceMessageTable;
    }

    public static function find()
    {
        return new SourceMessageQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['message', 'string']
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ID'),
            'category' => Yii::t('Category'),
            'message' => Yii::t('Message'),
            'status' => Yii::t('Translation status')
        ];
    }
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['id' => 'id'])->indexBy('language');
    }
    /**
     * @return array|SourceMessage[]
     */
    public static function getCategories()
    {
        return SourceMessage::find()->select('category')->distinct('category')->asArray()->all();
    }

    public function initMessages()
    {
        $messages = [];
        foreach (Yii::$app->i18n->languages as $language) {
            if (!isset($this->messages[$language])) {
                $message = new Message;
                $message->language = $language;
                $messages[$language] = $message;
            } else {
                $messages[$language] = $this->messages[$language];
            }
        }
        $this->populateRelation('messages', $messages);
    }

    public function saveMessages()
    {
        /** @var Message $message */
        foreach ($this->messages as $message) {
            $this->link('messages', $message);
            $message->save();
        }
    }

    public function isTranslated()
    {
        foreach ($this->messages as $message) {
            if (!$message->translation) {
                return false;
            }
        }
        return true;
    }
}
