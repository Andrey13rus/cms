<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 27.03.2015
 */

namespace skeeks\cms;

use skeeks\cms\models\CmsSite;
use skeeks\cms\models\CmsSiteDomain;
use yii\base\Component;
use yii\caching\TagDependency;

/**
 * @property CmsSite $site
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class Skeeks extends Component
{
    /**
     * @var CmsSite
     */
    protected $_site = null;

    /**
     * @var string
     */
    public $siteClass = CmsSite::class;

    /**
     * @var null
     */
    private $_serverName = null;

    /**
     * @return CmsSite
     */
    public function getSite()
    {
        $cmsSiteClass = $this->siteClass;

        if ($this->_site === null) {
            if (\Yii::$app instanceof \yii\console\Application) {

                if ($cms_site_id = getenv("CMS_SITE")) {
                    $this->_site = $cmsSiteClass::find()->active()->andWhere(['id' => $cms_site_id])->one();
                } else {
                    $this->_site = $cmsSiteClass::find()->active()->andWhere(['is_default' => 1])->one();
                }


            } else {
                $this->_serverName = \Yii::$app->getRequest()->getServerName();
                $dependencySiteDomain = new TagDependency([
                    'tags' =>
                        [
                            (new CmsSiteDomain())->getTableCacheTag(),
                        ],
                ]);


                $cmsDomain = CmsSiteDomain::getDb()->cache(function ($db) {
                    return CmsSiteDomain::find()->where(['domain' => $this->_serverName])->one();
                }, null, $dependencySiteDomain);

                /**
                 * @var CmsSiteDomain $cmsDomain
                 */
                if ($cmsDomain) {
                    $this->_site = $cmsDomain->cmsSite;
                } else {

                    $this->_site = CmsSiteDomain::getDb()->cache(function ($db) use ($cmsSiteClass) {
                        return $cmsSiteClass::find()->active()->andWhere(['is_default' => 1])->one();
                    },
                        null,
                        new TagDependency([
                            'tags' => [
                                (new $cmsSiteClass())->getTableCacheTag(),
                            ],
                        ])
                    );
                }
            }
        }

        return $this->_site;
    }
}