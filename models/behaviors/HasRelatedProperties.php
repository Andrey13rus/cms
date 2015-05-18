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
use skeeks\cms\base\behaviors\ActiveRecord;
use skeeks\cms\relatedProperties\models\RelatedPropertiesModel;
use yii\db\ActiveQuery;
use yii\web\ErrorHandler;

/**
 * Class HasRelatedProperties
 * @package skeeks\cms\models\behaviors
 */
class HasRelatedProperties extends ActiveRecord
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
     *
     * TODO: подумать, может быть если свойства еще не заданы, надо возвращать значения по умолчанию.
     *
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
        $className = $this->relatedPropertyClassName;
        return $className::find()->all();
    }

    /**
     * @param $property_ids
     * @return ActiveQuery
     */
    public function findRelatedElementProperties($property_ids)
    {
        return $this->getRelatedElementProperties()->where(['property_id' => $property_ids]);
    }

    /**
     * @return string
     */
    public function renderRelatedPropertiesForm()
    {
        try
        {
            return \Yii::$app->view->render('@skeeks/cms/views/blank-form', [
                'modelHasRelatedProperties'     => $this->owner,
            ]);

        } catch (\Exception $e)
        {
            ob_end_clean();
            ErrorHandler::convertExceptionToError($e);
            return 'Ошибка рендеринга формы: ' . $e->getMessage();
        }
    }

    /**
     * @return RelatedPropertiesModel
     */
    public function createRelatedPropertiesModel()
    {
        return new RelatedPropertiesModel([
            'relatedElementModel' => $this
        ]);
    }

    /**
     * @var RelatedPropertiesModel
     */
    public $relatedPropertiesModel = null;

    /**
     * @return RelatedPropertiesModel
     */
    public function getRelatedPropertiesModel()
    {
        if ($this->relatedPropertiesModel === null)
        {
            $this->relatedPropertiesModel = $this->createRelatedPropertiesModel();
        }

        return $this->relatedPropertiesModel;
    }
}