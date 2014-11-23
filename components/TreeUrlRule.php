<?php
/**
 * TreeUrlRule
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 09.11.2014
 * @since 1.0.0
 */
namespace skeeks\cms\components;
use skeeks\cms\App;
use skeeks\cms\filters\NormalizeDir;
use skeeks\cms\models\Tree;
use \yii\base\InvalidConfigException;
use yii\helpers\Url;

/**
 * Class Storage
 * @package skeeks\cms\components
 */
class TreeUrlRule
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

    /**
     * @param \yii\web\UrlManager $manager
     * @param string $route
     * @param array $params
     * @return bool|string
     */
    public function createUrl($manager, $route, $params)
    {

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

        if (!$normalizeDir) //главная страница
        {
            $treeNode = Tree::findCurrentRoot();
            if (!$treeNode)
            {
                return false;
            }

        } else //второстепенная страница
        {
            $treeRoot = Tree::findCurrentRoot();
            if (!$treeRoot)
            {
                return false;
            }

            $treeNode           = Tree::find()->where([
                $treeRoot->dirAttrName      => $normalizeDir,
                $treeRoot->pidMainAttrName  => $treeRoot->id,
            ])->one();

        }

        if ($treeNode)
        {
            $params['model']        = $treeNode;
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
