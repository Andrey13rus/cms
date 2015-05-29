<?php
/**
 * Infoblock
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 09.11.2014
 * @since 1.0.0
 */

namespace skeeks\cms\models;

use skeeks\cms\base\Widget;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\behaviors\HasFiles;
use skeeks\cms\models\behaviors\HasMultiLangAndSiteFields;
use skeeks\cms\models\behaviors\HasRef;
use skeeks\cms\models\behaviors\HasStatus;
use skeeks\cms\models\behaviors\TimestampPublishedBehavior;
use skeeks\cms\traits\ValidateRulesTrait;
use skeeks\modules\cms\user\models\User;
use Yii;

/**
 * This is the model class for table "{{%cms_content}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $code
 * @property string $active
 * @property integer $priority
 * @property string $description
 * @property string $files
 * @property string $content_type
 * @property string $index_for_search
 * @property string $tree_chooser
 * @property string $list_mode
 * @property string $name_meny
 * @property string $name_one
 *
 * @property CmsContentType $contentType
 * @property CmsContentElement[] $cmsContentElements
 * @property CmsContentProperty[] $cmsContentProperties
 */
class CmsContent extends Core
{
    use ValidateRulesTrait;
    use \skeeks\cms\models\behaviors\traits\HasFiles;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_content}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            HasFiles::className() => HasFiles::className(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'name' => Yii::t('app', 'Name'),
            'code' => Yii::t('app', 'Code'),
            'active' => Yii::t('app', 'Active'),
            'priority' => Yii::t('app', 'Priority'),
            'description' => Yii::t('app', 'Description'),
            'files' => Yii::t('app', 'Files'),
            'content_type' => Yii::t('app', 'Content Type'),
            'index_for_search' => Yii::t('app', 'Индексировать для модуля поиска'),
            'tree_chooser' => Yii::t('app', 'Интерфейс привязки элемента к разделам'),
            'list_mode' => Yii::t('app', 'Режим просмотра разделов и элементов'),
            'name_meny' => Yii::t('app', 'Название элементов (множественное число)'),
            'name_one' => Yii::t('app', 'Название одного элемента'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority'], 'integer'],
            [['name', 'content_type', 'code'], 'required'],
            [['description', 'files'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'unique'],
            [['code'], 'validateCode'],
            [['active', 'index_for_search', 'tree_chooser', 'list_mode'], 'string', 'max' => 1],
            [['content_type'], 'string', 'max' => 32],
            [['name_meny', 'name_one'], 'string', 'max' => 100],
            ['priority', 'default', 'value'         => 500],
            ['active', 'default', 'value'           => "Y"],
            ['name_meny', 'default', 'value'    => "Элементы"],
            ['name_one', 'default', 'value'     => "Элемент"],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentType()
    {
        return $this->hasOne(CmsContentType::className(), ['code' => 'content_type']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElements()
    {
        return $this->hasMany(CmsContentElement::className(), ['content_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentProperties()
    {
        return $this->hasMany(CmsContentProperty::className(), ['content_id' => 'id']);
    }
}