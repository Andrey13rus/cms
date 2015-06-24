<?php
/**
 * Module
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 28.10.2014
 * @since 1.0.0
 */
namespace skeeks\cms\base;
use skeeks\cms\base\components\Descriptor;
use skeeks\cms\traits\HasComponentDescriptorTrait;
use skeeks\sx\Dir;

/**
 * Class Module
 * @package skeeks\cms
 */
abstract class Module extends \yii\base\Module
{
    use HasComponentDescriptorTrait;

    const CHECKS_DIR_NAME = "checks";
    /**
     * namespace проверок
     * skeeks\cms\checks - например, если не будет задан, то будет сформирован опираясь на значение controllerNamespace
     * @var null
     */
    public $checkNamespace = null;


    public function init()
    {
        parent::init();

        if ($this->controllerNamespace && !$this->checkNamespace)
        {
            $data = explode('\\', $this->controllerNamespace);
            if (count($data) > 1)
            {
                unset($data[count($data)-1]);
                $data[] = static::CHECKS_DIR_NAME;
                $this->checkNamespace = implode("\\", $data);
            }
        }
    }

    /**
     * @return CheckComponent[]
     */
    public function loadChecksComponents()
    {
        $result = [];

        $dir = new Dir($this->basePath . "/" . static::CHECKS_DIR_NAME);
        if ($dir->isExist())
        {
            if ($files = $dir->findFiles())
            {
                foreach ($files as $file)
                {
                    $className = $this->checkNamespace . "\\" . $file->getFileName();

                    if (class_exists($className))
                    {
                        $component = new $className();
                        if (is_subclass_of($component, CheckComponent::className()))
                        {
                            $result[$component->className()] = $component;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Название модуля
     *
     * @return string
     */
    public function getName()
    {
        return $this->getDescriptor()->name;
    }

    /**
     * Версия
     *
     * @return string
     */
    public function getVersion()
    {
        return (string) $this->getDescriptor()->version;
    }


    /**
     *
     * TODO: is depricated (начиная с версии 1.1.5)
     * Использовать: \Yii::$app->view->render("@skeeks/cms/views/test")
     *
     * Берет файл относительно views модуля и рендерит его
     *
     * @param string $filePath helpers/test.php
     * @param array $data
     * @param null $context
     * @return string
     */
    public function renderFile($filePath, $data = [], $context = null)
    {
        return \Yii::$app->view->renderFile($this->getViewPath() . "/" . $filePath, $data, $context);
    }


}