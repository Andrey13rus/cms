<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link https://skeeks.com/
 * @copyright (c) 2010 SkeekS
 * @date 11.03.2018
 */

namespace skeeks\cms\widgets;

use skeeks\yii2\config\ConfigBehavior;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 * Class GridView
 * @package skeeks\cms
 */
class TestWidget extends Widget
{
    public $test = '';

    public $config = [];

    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            ConfigBehavior::class => ArrayHelper::merge([
                'class' => ConfigBehavior::class,
                'configModel' => [
                    'fields' => [
                        'test'
                    ],
                    'defineAttributes' => [
                        'test' => '222'
                    ],
                    'rules' => [
                        ['test', 'string']
                    ]
                ]
            ], (array) $this->config),

        ]);
    }

    public function run()
    {
        return $this->test;
    }
}