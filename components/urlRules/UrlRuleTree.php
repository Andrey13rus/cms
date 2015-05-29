<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 24.05.2015
 */
namespace skeeks\cms\components\urlRules;
use skeeks\cms\App;
use skeeks\cms\exceptions\NotConnectedToDbException;
use skeeks\cms\filters\NormalizeDir;
use skeeks\cms\models\Tree;
use \yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class UrlRuleTree
 * @package skeeks\cms\components\urlRules
 */
class UrlRuleTree
    extends \yii\web\UrlRule
{
    /**
     *
     * Добавлять слэш на конце или нет
     *
     * @var bool
     */
    public $useLastDelimetr = true;

    public function init()
    {
        if ($this->name === null)
        {
            $this->name = __CLASS__;
        }
    }

    static public $models = [];

    /**
     * @param \yii\web\UrlManager $manager
     * @param string $route
     * @param array $params
     * @return bool|string
     */
    public function createUrl($manager, $route, $params)
    {
        if ($route == 'cms/tree/view')
        {
            $id = (int) ArrayHelper::getValue($params, 'id');
            if (!$id)
            {
                return false;
            }

            if (!$tree = ArrayHelper::getValue(self::$models, $id))
            {
                $tree = Tree::findOne(['id' => $id]);
                self::$models[$id] = $tree;
            }

            if (!$tree)
            {
                return false;
            }

            $url = $tree->url;

            unset($params['id']);
            /*if ($url !== '') {
                $url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
            }*/

            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $url .= '?' . $query;
            }

            return $url;

        }
        return false;
    }

    /**
     * @param \yii\web\UrlManager $manager
     * @param \yii\web\Request $request
     * @return array|bool
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo           = $request->getPathInfo();
        $params             = $request->getQueryParams();
        $treeNode           = null;

        if (!$pathInfo)
        {
            return $this->_go();
        }

        //Если урл преобразован, редирректим по новой
        $pathInfoNormal = $this->_normalizeDir($pathInfo);
        if ($pathInfo != $pathInfoNormal)
        {
            \Yii::$app->response->redirect(DIRECTORY_SEPARATOR . $pathInfoNormal . ($params ? '?' . http_build_query($params) : '') );
        }

        return $this->_go($pathInfoNormal);
    }

    protected function _go($normalizeDir = null)
    {
        if ($this->useLastDelimetr)
        {
            $normalizeDir = substr($normalizeDir, 0, (strlen($normalizeDir) - 1));
        }


        try
        {
            $dependency = new TagDependency([
                'tags'      =>
                [
                    (new Tree())->getTableCacheTag(),
                ],
            ]);


            if (!$normalizeDir) //главная страница
            {
                $treeNode = Tree::getDb()->cache(function ($db) {
                    return Tree::find()->where([
                        "site_code"         => \Yii::$app->cms->site->code,
                        "level"             => 0,
                    ])->one();
                }, null, $dependency);

                /*$treeNode = Tree::find()->where([
                    "site_code"         => \Yii::$app->cms->site->code,
                    "level"             => 0,
                ])->one();*/


            } else //второстепенная страница
            {
                /*$treeNode = Tree::getDb()->cache(function ($db) {
                    return Tree::find()->where([
                        (new Tree())->dirAttrName       => $normalizeDir,
                        "site_code"                     => \Yii::$app->cms->site->code,
                    ])->one();
                }, null, $dependency);*/

                $treeNode = Tree::find()->where([
                    (new Tree())->dirAttrName       => $normalizeDir,
                    "site_code"                     => \Yii::$app->cms->site->code,
                ])->one();
            }
        } catch (Exception $e)
        {
            if ($e->getCode() == 1045)
            {
                throw new NotConnectedToDbException;
            }
        }

        if ($treeNode)
        {
            \Yii::$app->cms->setCurrentTree($treeNode);

            $params['id']        = $treeNode->id;
            return ['cms/tree/view', $params];
        } else
        {
            return false;
        }
    }


    /**
     * Преобразование path, убираем лишние слэши, если надо добавляем последний слэш
     * @param $pathInfo
     * @return string
     */
    protected function _normalizeDir($pathInfo)
    {
        $filter             = new NormalizeDir();
        $pathInfoNormal     = $filter->filter($pathInfo);

        if ($this->useLastDelimetr)
        {
            return $pathInfoNormal . DIRECTORY_SEPARATOR;
        } else
        {
            return $pathInfoNormal;
        }
    }
}
