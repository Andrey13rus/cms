<?php
/**
 * GridView
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 18.02.2015
 * @since 1.0.0
 */

namespace skeeks\cms\modules\admin\widgets;
use yii\helpers\ArrayHelper;

/**
 * Class Pjax
 * @package skeeks\cms\modules\admin\widgets
 */
class GridView extends \yii\grid\GridView
{
    /**
     * @var bool
     */
    public $usePjax = true;
    /**
     * @var array
     */
    public $PjaxOptions = [];

    /**
     * Runs the widget.
     */
    public function run()
    {
        if ($this->usePjax) {
            Pjax::begin(ArrayHelper::merge([
                'id' => 'sx-pjax-grid-' . $this->id,
            ], $this->PjaxOptions));

        }

        parent::run();

        if ($this->usePjax)
        {
            Pjax::end();
        }
    }
}