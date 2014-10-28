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
namespace skeeks\cms\modules\storage;

use skeeks\cms\Module as CmsModule;

/**
 * Class Module
 * @package skeeks\cms\modules\storage
 */
class Module extends CmsModule
{
    public $controllerNamespace = 'skeeks\cms\modules\storage\controllers';

    public function init()
    {
        parent::init();
    }
}