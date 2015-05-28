<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.03.2015
 */
namespace skeeks\cms\components;
use skeeks\cms\exceptions\NotConnectedToDbException;
use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsSiteDomain;
use yii\base\Component;
use yii\db\Exception;

/**
 * @property CmsSite                            $site
 * @package skeeks\cms\components
 */
class CurrentSite extends Component
{
    /**
     * @var CmsSite
     */
    protected $_site = null;

    /**
     * @return CmsSite
     */
    public function getSite()
    {
        if ($this->_site === null)
        {
            if (\Yii::$app instanceof \yii\console\Application)
            {
                $this->_site = CmsSite::find()->active()->andWhere(['def' => Cms::BOOL_Y])->one();
            } else
            {
                $serverName = \Yii::$app->getRequest()->getServerName();
                try
                {
                    /**
                     * @var CmsSiteDomain $cmsDomain
                     */
                    if ($cmsDomain = CmsSiteDomain::find()->where(['domain' => $serverName])->one())
                    {
                        $this->_site = $cmsDomain->cmsSite;
                    } else
                    {
                        $this->_site = CmsSite::find()->active()->andWhere(['def' => Cms::BOOL_Y])->one();
                    }
                } catch (Exception $e)
                {
                    if ($e->getCode() == 1045)
                    {
                        throw new NotConnectedToDbException;
                    }
                }

            }
        }

        return $this->_site;
    }

    /**
     * @param CmsSite $site
     * @return $this
     */
    public function set(CmsSite $site)
    {
        $this->_site = $site;
        return $this;
    }
}