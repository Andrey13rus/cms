<?php
/**
 * Наличие свойств в связанных таблицах
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 18.05.2015
 */

namespace skeeks\cms\models\behaviors;
use skeeks\cms\relatedProperties\models\RelatedPropertiesModel;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use yii\base\Behavior;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\ErrorHandler;

/**
 * Class HasRelatedProperties
 * @package skeeks\cms\models\behaviors
 */
class HasRelatedProperties extends Behavior
{
    /**
     * @var string связующая модель ( например CmsContentElementProperty::className() )
     */
    public $relatedElementPropertyClassName;

    /**
     * @var string модель свойства ( например CmsContentProperty::className() )
     */
    public $relatedPropertyClassName;

    /**
     * Значения связанных свойств.
     * Вернуться только заданные значения свойств.
     *
     * @return ActiveQuery
     */
    public function getRelatedElementProperties()
    {
        return $this->owner->hasMany($this->relatedElementPropertyClassName, ['element_id' => 'id']);
    }

    /**
     *
     * Все возможные свойства, для модели.
     * Это может зависеть от группы элемента, или от его типа, например.
     * Для разных групп пользователей можно задать свои свойства, а у пользователя можно заполнять только те поля котоыре заданы для группы к которой он относиться.
     *
     * @return ActiveQuery
     */
    public function getRelatedProperties()
    {
        $className  = $this->relatedPropertyClassName;
        $find       = $className::find()->orderBy(['priority' => SORT_ASC]);;
        $find->multiple = true;

        return $find;
    }

    /**
     * @return RelatedPropertiesModel
     */
    public function createRelatedPropertiesModel()
    {
        return new RelatedPropertiesModel([], [
            'relatedElementModel' => $this->owner
        ]);
    }

    /**
     * @var RelatedPropertiesModel
     */
    public $_relatedPropertiesModel = null;

    /**
     * @return RelatedPropertiesModel
     */
    public function getRelatedPropertiesModel()
    {
        if ($this->_relatedPropertiesModel === null)
        {
            $this->_relatedPropertiesModel = $this->createRelatedPropertiesModel();
        }

        return $this->_relatedPropertiesModel;
    }

    /**
     * @param RelatedPropertyModel $property
     * @param $value
     * @return $this
     * @throws \Exception
     */
    public function saveRelatedPropertyValue($property, $value)
    {
        if ($this->owner->isNewRecord)
        {
            throw new Exception("Additional property \"" . $property->code . "\" can not be saved until the stored parent model");
        }

        if ($property->multiple == "Y")
        {
            /*$propertyValues = $this->findRelatedElementProperties($property->id)->all();*/
            $propertyValues = $this->getRelatedElementProperties()->where(['property_id' => $property->id])->all();
            if ($propertyValues)
            {
                foreach ($propertyValues as $pv)
                {
                    $pv->delete();
                }
            }

            $values = (array) $value;

            if ($values)
            {
                foreach ($values as $key => $value)
                {
                    $className = $this->relatedElementPropertyClassName;
                    $productPropertyValue = new $className([
                        'element_id'    => $this->owner->id,
                        'property_id'   => $property->id,
                        'value'         => (string) $value,
                        'value_enum'    => (int) $value,
                        'value_num'     => (float) $value,
                    ]);

                    if (!$productPropertyValue->save())
                    {
                        throw new Exception("{$property->code} not save");
                    }
                }
            }

        } else
        {
            /*if ($productPropertyValue = $this->findRelatedElementProperties($property->id)->one())*/
            if ($productPropertyValue = $this->getRelatedElementProperties()->where(['property_id' => $property->id])->one())
            {
                $productPropertyValue->value        = (string) $value;
                $productPropertyValue->value_enum   = (int) $value;
                $productPropertyValue->value_num    = (float) $value;
            } else
            {
                $className = $this->relatedElementPropertyClassName;

                $productPropertyValue = new $className([
                    'element_id'    => $this->owner->id,
                    'property_id'   => $property->id,
                    'value'         => (string) $value,
                    'value_enum'    => (int) $value,
                    'value_num'     => (float) $value,
                ]);
            }

            if (!$productPropertyValue->save())
            {
                throw new Exception("{$property->code} not save");
            }
        }

        return $this;
    }
}