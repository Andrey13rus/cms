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
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\behaviors\HasFiles;
use skeeks\cms\models\behaviors\HasMultiLangAndSiteFields;
use skeeks\cms\models\behaviors\HasRef;
use skeeks\cms\models\behaviors\HasStatus;
use skeeks\cms\models\behaviors\TimestampPublishedBehavior;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\modules\cms\user\models\User;
use Yii;
use yii\db\BaseActiveRecord;
use yii\widgets\ActiveForm;

/**
 * This is the model class for table "{{%cms_content_property}}".
 *
 * @property integer $tree_type_id
 *
 * @property CmsTreeType $treeType
 * @property CmsTreeTypePropertyEnum[] $enums
 * @property CmsTreeProperty[] $elementProperties
 */
class CmsTreeTypeProperty extends RelatedPropertyModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_tree_type_property}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElementProperties()
    {
        return $this->hasMany(CmsTreeProperty::className(), ['property_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnums()
    {
        return $this->hasMany(CmsTreeTypePropertyEnum::className(), ['property_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTreeType()
    {
        return $this->hasOne(CmsTreeType::className(), ['id' => 'tree_type_id']);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'tree_type_id' => Yii::t('app', 'Связь с типом раздела'),
        ]);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['tree_type_id'], 'integer'],
            //[['code'], 'unique'],
            [['code', 'tree_type_id'], 'unique', 'targetAttribute' => ['tree_type_id', 'code'], 'message' => 'Для данного типа раздела этот код уже занят.'],
        ]);
    }
}