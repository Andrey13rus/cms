<?php
/**
 * ActiveForm
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 11.11.2014
 * @since 1.0.0
 */
namespace skeeks\cms\modules\admin\widgets;
use skeeks\cms\base\db\ActiveRecord;
use skeeks\cms\validators\db\IsNewRecord;
use skeeks\sx\validate\Validate;
use yii\base\Model;
use yii\helpers\Html;

/**
 * Class ActiveForm
 * @package skeeks\cms\modules\admin\widgets
 */
class ActiveForm extends \skeeks\cms\base\widgets\ActiveForm
{
    /**
     * @param Model $model
     * @return string
     */
    public function buttonsCreateOrUpdate(Model $model)
    {
        if (Validate::validate(new IsNewRecord(), $model)->isValid())
        {
            $submit = Html::submitButton(\Yii::t('app', 'Create'), ['class' => 'btn btn-success']);
        } else
        {
            $submit = Html::submitButton(\Yii::t('app', 'Update'), ['class' => 'btn btn-primary']);
        }
        return Html::tag('div',
            $submit,
            ['class' => 'form-group']
        );
    }
}