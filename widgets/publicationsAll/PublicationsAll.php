<?php
/**
 * Publications
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 08.12.2014
 * @since 1.0.0
 */
namespace skeeks\cms\widgets\publicationsAll;

use skeeks\cms\base\Widget;
use skeeks\cms\models\Publication;
use skeeks\cms\models\Tree;
use skeeks\cms\widgets\WidgetHasTemplate;
use Yii;
use yii\data\Pagination;

/**
 * Class Publications
 * @package skeeks\cms\widgets\PublicationsAll
 */
class PublicationsAll extends WidgetHasTemplate
{
    /**
     * @var null|string
     */
    public $title                   = '';
    public $types                   = [];
    public $statuses                = [];
    public $statusesAdults          = [];
    public $limit                   = 0;
    public $orderBy                 = null;

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function bind()
    {
        $find = Publication::find();

        $tree = \Yii::$app->cms->getCurrentTree();

        if ($tree)
        {
            $ids[] = $tree->id;
            if ($tree->hasChildrens())
            {
                if ($childrens = $tree->fetchChildrens())
                {
                    foreach ($childrens as $chidren)
                    {
                        $ids[] = $chidren->id;
                    }
                }
            }

            foreach ($ids as $id)
            {
                $find->orWhere("(FIND_IN_SET ('{$id}', tree_ids) or tree_id = '{$id}')");
            }

        }

        /*if ($this->limit)
        {
            $find->limit($this->limit);
        }*/

        if ($this->orderBy)
        {
            $find->orderBy($this->limit);
        }

        if ($this->statuses)
        {
            $find->andWhere(['status' => $this->statuses]);
        }

        if ($this->statusesAdults)
        {
            $find->andWhere(['status_adult' => $this->statuses]);
        }

        if ($this->types)
        {
            $find->andWhere(['type' => $this->types]);
        }

        $find->andWhere(['<=', 'published_at', time()]);
        $find->orderBy('published_at DESC');


        $countQuery = clone $find;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);

        if ($this->limit)
        {
            $pages->defaultPageSize = $this->limit;
        }

        $models = $find->offset($pages->offset)
                          ->limit($pages->limit)
                          ->all();

        $this->_data->set('models', $models);
        $this->_data->set('pages', $pages);

        return $this;
    }

    /**
     * @return array|null|Tree
     */
    public function fetchFirstTree()
    {
        if ($id = $this->getFirstTreeId())
        {
            return Tree::find()->where(['id' => $id])->one();
        } else
        {
            return null;
        }
    }

    /**
     * @return int
     */
    public function getFirstTreeId()
    {
        if ($this->tree_ids)
        {
            return (int) array_shift($this->tree_ids);
        } else
        {
            return 0;
        }
    }
}
