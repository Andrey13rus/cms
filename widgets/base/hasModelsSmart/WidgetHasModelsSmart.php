<?php
/**
 * WidgetHasModelsSmart
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 02.02.2015
 * @since 1.0.0
 */
namespace skeeks\cms\widgets\base\hasModelsSmart;

use skeeks\cms\base\Widget;
use skeeks\cms\models\Publication;
use skeeks\cms\models\Search;
use skeeks\cms\models\Tree;
use skeeks\cms\widgets\base\hasModels\WidgetHasModels;
use skeeks\cms\widgets\base\hasTemplate\WidgetHasTemplate;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * Class WidgetHasModelsSmart
 * @package skeeks\cms\widgets\base\hasModelsSmart
 */
class WidgetHasModelsSmart extends WidgetHasModels
{
    public $defaultPageSize         = 10;
    public $enablePjaxPagination    = 0;

    public $createdBy               = [];
    public $updatedBy               = [];

    /**
     * @var bool
     */
    public $applySearchParams       = 1;


    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['createdBy', 'updatedBy', 'applySearchParams', 'enablePjaxPagination'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'createdBy'                 => 'Авторы',
            'updatedBy'                 => 'Полседние обновившие',
            'enablePjaxPagination'      => 'Включить ajax навигацию'
        ]);
    }

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function bind()
    {
        $this->buildSearch();
        if ($this->applySearchParams)
        {
            $this->_data->search->search(\Yii::$app->request->queryParams);
        }
        return $this;
    }

}
