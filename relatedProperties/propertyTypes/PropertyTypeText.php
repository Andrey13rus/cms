<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 30.04.2015
 */
namespace skeeks\cms\relatedProperties\propertyTypes;
use skeeks\cms\relatedProperties\PropertyType;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class PropertyTypeTextarea
 * @package skeeks\cms\relatedProperties\propertyTypes
 */
class PropertyTypeText extends PropertyType
{
    public $code             = self::CODE_STRING;
    public $name             = "";

    /*static public $fieldElements    =
    [
        'textarea'  => 'Текстовое поле (textarea)',
        'textInput' => 'Текстовая строка (input)',
    ];*/

    public $fieldElement            = 'textInput';
    public $rows                    = 5;

    static public function fieldElements()
    {
        return [
            'textarea'      => \Yii::t('skeeks/cms','Text field').' (textarea)',
            'textInput'     => \Yii::t('skeeks/cms','Text string').' (input)',
            'hiddenInput'   => \Yii::t('skeeks/cms','Скрытое поле').' (hiddenInput)',
        ];
    }

    public function init()
    {
        parent::init();

        if(!$this->name)
        {
            $this->name = \Yii::t('skeeks/cms','Text');
        }
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'fieldElement'  => \Yii::t('skeeks/cms','Element form'),
            'rows'          => \Yii::t('skeeks/cms','The number of lines of the text field')
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
        [
            ['fieldElement', 'string'],
            ['rows', 'integer', 'min' => 1, 'max' => 50],
        ]);
    }

    /**
     * @return string
     */
    public function renderConfigForm(ActiveForm $activeForm)
    {
        echo $activeForm->fieldSelect($this, 'fieldElement', \skeeks\cms\relatedProperties\propertyTypes\PropertyTypeText::fieldElements());
        echo $activeForm->fieldInputInt($this, 'rows');
    }

    /**
     * @return \yii\widgets\ActiveField
     */
    public function renderForActiveForm()
    {
        $field = parent::renderForActiveForm();

        if (in_array($this->fieldElement, array_keys(self::fieldElements())))
        {
            $fieldElement = $this->fieldElement;
            $field->$fieldElement([
                'rows' => $this->rows
            ]);

            if ($this->fieldElement == 'hiddenInput')
            {
                $field->label(false);
            }
        } else
        {
            $field->textInput([]);
        }

        return $field;
    }

}