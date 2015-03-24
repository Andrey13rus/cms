<?php
/**
 * Виджет который может использовать для рендеринга шаблона
 * Обычно шаблон описан в декскрипторе
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 24.11.2014
 * @since 1.0.0
 */
namespace skeeks\cms\widgets\base\hasTemplate;

use skeeks\cms\base\Widget;
use skeeks\sx\Entity;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

/**
 * Class WidgetHasTemplate
 * @package skeeks\cms\widgets\base\hasTemplate
 */
abstract class WidgetHasTemplate extends Widget
{
    /**
     * @var null|string
     */
    public $template                 = 'default';

    /**
     * @var null
     */
    public $viewFile                 = null;

    static public function getDescriptorConfig()
    {
        return ArrayHelper::merge(parent::getDescriptorConfig(), [
            'templates' =>
            [
                'default' =>
                [
                    'name' => 'Базовый шаблон',
                    'baseDir' => ['@app/views/widgets']
                ]
            ]
        ]);
    }

    /**
     * @var Entity
     */
    protected $_data = null;
    protected $_binded = null;

    public function init()
    {
        parent::init();
        $this->_data = new Entity();
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            ['template', 'string'],
            ['viewFile', 'string']
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'template' => 'Шаблон',
            'viewFile' => 'Путь к файлу отображения'
        ]);
    }


    /**
     * Формирование данных для шаблона
     * @return $this
     */
    public function bind()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function run()
    {
        if ($this->_binded === null)
        {
            $this->bind();
            $this->_binded = true;
        }

        $this->_data->set('widget', $this);

        $result = '';
        try
        {
            /**
             * Если задан файл шаблона для которого передаются данные, то просто рендерим его.
             */
            if ($this->viewFile)
            {
                try
                {
                    return $this->render($this->viewFile, $this->_data->toArray());
                } catch (InvalidParamException $e)
                {
                    return 'Шаблон не найден: ' . $this->viewFile;
                }

            }

            /**
             * Если прямой файл не задан, читаем декскрптор виджета, смотрим доступные шаблоны.
             * Ищем и ренерим его.
             */
            $template = $this->getDescriptor()->getTemplatesObject()->getComponent($this->template);
            if ($template)
            {
                try
                {
                    $result = '';
                    $isRendered = false;

                    if ($template->baseDir)
                    {
                        $possibleBaseDirs = [];

                        if (is_string($template->baseDir))
                        {
                            $possibleBaseDirs = [$template->baseDir];
                        } else if (is_array($template->baseDir))
                        {
                            $possibleBaseDirs = $template->baseDir;
                        }

                        if ($possibleBaseDirs)
                        {
                            foreach ($possibleBaseDirs as $baseDir)
                            {
                                $fileTemplate = $baseDir . DIRECTORY_SEPARATOR . $this->template . '.php';
                                if (file_exists(\Yii::getAlias($fileTemplate)))
                                {
                                    $result = $this->renderFile($fileTemplate, $this->_data->toArray());
                                    $isRendered = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($isRendered === false)
                    {
                        $result = $this->render($this->template, $this->_data->toArray());
                    }


                } catch (\Exception $e)
                {
                    ob_end_clean();
                    return 'Ошибка виджета: ' . $e->getMessage();
                }

            } else
            {
                $result = $this->render($this->template, $this->_data->toArray());
            }
        } catch (\Exception $e)
        {
            ob_end_clean();
            return 'Ошибка виджета: ' . $e->getMessage();
        }

        return $result;
    }


}
