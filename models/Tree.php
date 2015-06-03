<?php
/**
 * Publication
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 31.10.2014
 * @since 1.0.0
 */

namespace skeeks\cms\models;

use skeeks\cms\components\Cms;
use skeeks\cms\models\behaviors\CanBeLinkedToTree;
use skeeks\cms\models\behaviors\HasFiles;
use skeeks\cms\models\behaviors\HasRelatedProperties;
use skeeks\cms\models\behaviors\HasTableCache;
use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\behaviors\SeoPageName;
use skeeks\cms\models\behaviors\traits\HasRelatedPropertiesTrait;
use skeeks\cms\models\behaviors\traits\HasUrlTrait;
use skeeks\cms\models\behaviors\traits\TreeBehaviorTrait;
use skeeks\cms\models\behaviors\TreeBehavior;
use Yii;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;

/**
 * This is the model class for table "{{%cms_tree}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description_short
 * @property string $description_full
 * @property string $files
 * @property string $code
 * @property integer $pid
 * @property string $pids
 * @property integer $level
 * @property string $dir
 * @property integer $has_children
 * @property integer $priority
 * @property string $tree_type_id
 * @property integer $published_at
 * @property string $redirect
 * @property string $tree_menu_ids
 * @property string $active
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keywords
 * @property string $site_code
 *
 * @property string $absoluteUrl
 * @property string $url
 *
 * @property CmsContentElement[]        $cmsContentElements
 * @property CmsContentElementTree[]    $cmsContentElementTrees
 * @property CmsTree                    $parentTree
 * @property CmsTree[]                  $parentTrees
 * @property CmsSite                    $site
 * @property CmsTreeType                $treeType
 * @property CmsTreeProperty[]          $cmsTreeProperties
 */
class Tree extends Core
{
    use TreeBehaviorTrait;
    use HasUrlTrait;
    use HasRelatedPropertiesTrait;
    use \skeeks\cms\models\behaviors\traits\HasFiles;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_tree}}';
    }


    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $result = [];

        $result[] = SeoPageName::className();
        $result[] = HasFiles::className();
        $result[] = TreeBehavior::className();
        $result[] = [
            'class' => Implode::className(),
            "fields" =>  [
                "tree_menu_ids"
            ]
        ];

        $result[HasRelatedProperties::className()] =
        [
            'class'                             => HasRelatedProperties::className(),
            'relatedElementPropertyClassName'   => CmsTreeProperty::className(),
            'relatedPropertyClassName'          => CmsTreeTypeProperty::className(),
        ];

        $result[HasTableCache::className()] =
        [
            'class' => HasTableCache::className(),
            'cache' => \Yii::$app->cache
        ];

        return $result;
    }

    public function init()
    {
        parent::init();

        $this->on(BaseActiveRecord::EVENT_BEFORE_INSERT, [$this, 'checksBeforeSave']);
        $this->on(BaseActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'checksBeforeSave']);
    }

    public function checksBeforeSave($event)
    {
        if (!$this->site_code)
        {
            if ($this->parentTree)
            {
                $this->site_code = $this->parentTree->site_code;
            }
        }

        if (!$this->tree_type_id)
        {
            if ($this->parentTree)
            {
                $this->tree_type_id = $this->parentTree->tree_type_id;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'tree_type_id'              => Yii::t('app', 'Тип'),
            'redirect'          => Yii::t('app', 'Redirect'),
            'tree_menu_ids'     => Yii::t('app', 'Позиции меню'),
            'priority'          => Yii::t('app', 'Приоритет'),
            'code'              => Yii::t('app', 'Код'),
            'active'              => Yii::t('app', 'Active'),
            'meta_title'        => Yii::t('app', 'Meta Title'),
            'meta_keywords'         => Yii::t('app', 'Meta Keywords'),
            'meta_description'  => Yii::t('app', 'Meta Description'),
            'description_short' => Yii::t('app', 'Description Short'),
            'description_full' => Yii::t('app', 'Description Full'),
        ]);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['description_short', 'description_full'], 'string'],
            ['active', 'default', 'value' => Cms::BOOL_Y],
            [['redirect'], 'string'],
            [['priority', 'tree_type_id'], 'integer'],
            [['tree_menu_ids'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['meta_title', 'meta_description', 'meta_keywords'], 'string'],
            [['meta_title'], 'string', 'max' => 500],
            [['site_code'], 'string', 'max' => 5],
            [['pid', 'code'], 'unique', 'targetAttribute' => ['pid', 'code'], 'message' => 'The combination of Code and Pid has already been taken.']
        ]);
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->isLink())
        {
            if ($this->redirect)
            {
                return $this->redirect;
            }
        }

        if ($this->site)
        {
            if ($this->getDir())
            {
                return $this->site->getUrl() . DIRECTORY_SEPARATOR . $this->getDir() . (\Yii::$app->urlManager->suffix ? \Yii::$app->urlManager->suffix : '');
            } else {
                return $this->site->getUrl();
            }
        } else {
            if ($this->getDir()) {
                return \Yii::$app->request->getHostInfo() . DIRECTORY_SEPARATOR . $this->getDir() . (\Yii::$app->urlManager->suffix ? \Yii::$app->urlManager->suffix : '');
            } else {
                return \Yii::$app->request->getHostInfo();
            }
        }
    }

    /**
     * @return array
     */
    public function getParentTrees()
    {
        if ($parents = $this->findParents())
        {
            return $parents->all();
        }

        return [];
    }



    /**
     * Эта страница является ссылкой?
     *
     * @return bool
     */
    public function isLink()
    {
        return (bool) ($this->redirect);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentTree()
    {
        return $this->hasOne(static::className(), ['id' => 'pid']);
    }

    /**
     * @return CmsSite
     */
    public function getSite()
    {
        //return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
        return CmsSite::getByCode($this->site_code);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElements()
    {
        return $this->hasMany(CmsContentElement::className(), ['tree_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElementTrees()
    {
        return $this->hasMany(CmsContentElementTree::className(), ['tree_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsTreeProperties()
    {
        return $this->hasMany(CmsTreeProperty::className(), ['element_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTreeType()
    {
        return $this->hasOne(CmsTreeType::className(), ['id' => 'tree_type_id']);
    }


    /**
     *
     * Все возможные свойства связанные с моделью
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRelatedProperties()
    {
        return $this->treeType->cmsTreeTypeProperties;
    }

}
