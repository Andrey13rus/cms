<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.06.2015
 */
namespace skeeks\cms\widgets\formInputs\comboText;

use skeeks\cms\Exception;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\behaviors\HasFiles;
use skeeks\cms\validators\HasBehavior;
use skeeks\sx\validate\Validate;
use skeeks\widget\codemirror\CodemirrorWidget;
use skeeks\yii2\ckeditor\CKEditorPresets;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use Yii;

/**
 * Class ComboTextInputWidget
 * @package skeeks\cms\widgets\formInputs\comboText
 */
class ComboTextInputWidget extends InputWidget
{
    const CONTROLL_TEXT     = "text";
    const CONTROLL_EDITOR   = "editor";
    const CONTROLL_HTML     = "html";

    /**
     * @var array Возможные редакторы.
     */
    static public $editors = [
        self::CONTROLL_TEXT          => 'Текст',
        self::CONTROLL_EDITOR        => 'Визуальный редактор',
        self::CONTROLL_HTML          => 'HTML',
    ];

    /**
     * @var array Опции текстового поля по умолчанию.
     */
    public $defaultOptions = [
        'class' => 'form-control',
        'rows'  => '20',
    ];

    /**
     * @var array Общие js опции текущего виджета
     */
    public $clientOptions = [];

    /**
     * @var string название поля, в котором будет храниться выбранный тип редактора.
     * Если не будет указан, то редактор по умолчанию будет выбран из настроек.
     */
    public $modelAttributeSaveType = "";


    /**
     * @var array Опции для CKEditor
     */
    public $ckeditorOptions = [];

    /**
     * @var array Опции для CodeMirror
     */
    public $codemirrorOptions = [];





    //TODO: сделать etter и зактрытый setter
    /**
     * @var \skeeks\cms\widgets\formInputs\ckeditor\Ckeditor
     */
    public $ckeditor = null;

    /**
     * @var CodemirrorWidget
     */
    public $codemirror = null;



    public function init()
    {
        parent::init();

        if (!array_key_exists('id', $this->clientOptions))
        {
            $this->clientOptions['id'] = $this->id;
        }
    }

    /**
	 * @inheritdoc
	 */
	public function run()
	{
        $this->options = ArrayHelper::merge($this->defaultOptions, $this->options);

        if ($this->hasModel())
        {
            if (!array_key_exists('id', $this->options))
            {
                $this->clientOptions['inputId'] = Html::getInputId($model, $attribute);
            } else
            {
                $this->clientOptions['inputId'] = $this->options['id'];
            }

			$textarea = Html::activeTextarea($this->model, $this->attribute, $this->options);
		} else
        {
            //TODO: реализовать для работы без модели
            echo Html::textarea($this->name, $this->value, $this->options);
            return;
		}

        $this->registerPlugin();

        echo $this->render('combo-text', [
            'widget'    => $this,
            'textarea'  => $textarea
        ]);
	}



    /**
	 * Registers CKEditor plugin
	 */
	protected function registerPlugin()
	{
		$view = $this->getView();

        $this->ckeditor = new \skeeks\cms\widgets\formInputs\ckeditor\Ckeditor(ArrayHelper::merge([
            'model'         => $this->model,
            'attribute'     => $this->attribute,
            'relatedModel'  => $this->model
        ], $this->ckeditorOptions));

        $this->codemirror = new CodemirrorWidget(ArrayHelper::merge([
            'model'         => $this->model,
            'attribute'     => $this->attribute,

            'preset'    => 'htmlmixed',
            'assets'    =>
            [
                \skeeks\widget\codemirror\CodemirrorAsset::THEME_NIGHT
            ],
            'clientOptions'   =>
            [
                'theme' => 'night'
            ],

        ], $this->codemirrorOptions));

        $this->ckeditor->registerAssets();
        $this->codemirror->registerAssets();

        $this->clientOptions['ckeditor']    = $this->ckeditor->clientOptions;
        $this->clientOptions['codemirror']  = $this->codemirror->clientOptions;

        ComboTextInputWidgetAsset::register($this->view);

	}
}

