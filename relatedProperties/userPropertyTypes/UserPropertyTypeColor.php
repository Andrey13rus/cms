<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 30.04.2015
 */
namespace skeeks\cms\relatedProperties\userPropertyTypes;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\relatedProperties\PropertyType;
use skeeks\cms\widgets\ColorInput;
use yii\helpers\ArrayHelper;

/**
 * Class UserPropertyTypeColor
 * @package skeeks\cms\relatedProperties\userPropertyTypes
 */
class UserPropertyTypeColor extends PropertyType
{
    public $code = self::CODE_STRING;
    public $name = "Выбор цвета";


    public $showDefaultPalette  = Cms::BOOL_Y;
    public $saveValueAs         = Cms::BOOL_Y;
    public $useNative           = Cms::BOOL_N;

    public $showAlpha           = Cms::BOOL_Y;
    public $showInput           = Cms::BOOL_Y;
    public $showPalette         = Cms::BOOL_Y;

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
        [
            'showDefaultPalette'    => 'Показывать расширенную политру цветов',
            'saveValueAs'           => 'Формат сохранения значения',
            'useNative'             => 'Использовать нативный выбор цвета',
            'showAlpha'             => 'Управление прозрачностью',
            'showInput'             => 'Показывать поле для ввода значения',
            'showPalette'           => 'Вообще показывать политру',
        ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
        [
            ['showDefaultPalette', 'string'],
            ['useNative', 'string'],
            ['showAlpha', 'string'],
            ['showInput', 'string'],
            ['showPalette', 'string'],
            [['showDefaultPalette', 'useNative', 'showAlpha', 'showInput', 'showPalette'], 'in', 'range' => array_keys(\Yii::$app->cms->booleanFormat())],

            ['saveValueAs', 'string'],
            ['saveValueAs', 'in', 'range' => array_keys(ColorInput::$possibleSaveAs)],
        ]);
    }

    /**
     * @return \yii\widgets\ActiveField
     */
    public function renderForActiveForm()
    {
        $field = parent::renderForActiveForm();

        $pluginOptions = [
            'showAlpha' => (bool) ($this->showAlpha === Cms::BOOL_Y),
            'showInput' => (bool) ($this->showInput === Cms::BOOL_Y),
            'showPalette' => (bool) ($this->showPalette === Cms::BOOL_Y),
        ];

        $field->widget(ColorInput::className(), [
            'showDefaultPalette'    => (bool) ($this->showDefaultPalette === Cms::BOOL_Y),
            'useNative'             => (bool) ($this->useNative === Cms::BOOL_Y),
            'saveValueAs'           => (string) $this->saveValueAs,
            'pluginOptions'         => $pluginOptions,
        ]);

        return $field;
    }


    /**
     * Файл с формой настроек, по умолчанию лежит в той же папке где и компонент.
     *
     * @return string
     */
    public function getConfigFormFile()
    {
        $class = new \ReflectionClass($this->className());
        return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views/_formUserPropertyColor.php';
    }
}