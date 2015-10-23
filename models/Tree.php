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

use skeeks\cms\validators\db\IsNewRecord;
use skeeks\cms\validators\db\NotNewRecord;
use skeeks\cms\validators\db\NotSame;
use skeeks\sx\filters\string\SeoPageName as FilterSeoPageName;
use skeeks\cms\validators\model\TreeSeoPageName;
use Imagine\Image\ManipulatorInterface;
use skeeks\cms\components\Cms;
use skeeks\cms\models\behaviors\CanBeLinkedToTree;
use skeeks\cms\models\behaviors\HasRelatedProperties;
use skeeks\cms\models\behaviors\HasStorageFile;
use skeeks\cms\models\behaviors\HasStorageFileMulti;
use skeeks\cms\models\behaviors\HasTableCache;
use skeeks\cms\models\behaviors\Implode;
use skeeks\cms\models\behaviors\SeoPageName;
use skeeks\cms\models\behaviors\traits\HasRelatedPropertiesTrait;
use skeeks\cms\models\behaviors\traits\HasUrlTrait;
use skeeks\cms\models\behaviors\traits\TreeBehaviorTrait;
use skeeks\cms\models\behaviors\TreeBehavior;
use skeeks\sx\validate\Validate;
use skeeks\sx\validators\ChainAnd;
use Yii;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

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
 * @property string $description_short_type
 * @property string $description_full_type
 * @property integer $image_full_id
 * @property integer $image_id
 *
 * @property string $absoluteUrl
 * @property string $url
 *
 * @property CmsStorageFile $image
 * @property CmsStorageFile $imageFull
 *
 * @property CmsTreeFile[]  $cmsTreeFiles
 * @property CmsTreeImage[] $cmsTreeImages
 *
 * @property CmsStorageFile[] $files
 * @property CmsStorageFile[] $images
 *
 * @property CmsContentElement[]        $cmsContentElements
 * @property CmsContentElementTree[]    $cmsContentElementTrees
 * @property CmsSite                    $site
 * @property CmsSite                    $cmsSiteRelation
 * @property CmsTreeType                $treeType
 * @property CmsTreeProperty[]          $cmsTreeProperties
 *
 * @property Tree                       $parent
 * @property Tree[]                     $parents
 * @property Tree[]                     $children
 * @property Tree                       $root
 * @property Tree                       $prev
 * @property Tree                       $next
 */
class Tree extends Core
{
    use HasRelatedPropertiesTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_tree}}';
    }

    const PRIORITY_STEP = 100; //Шаг приоритета
    const PIDS_DELIMETR = "/"; //Шаг приоритета


    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return ArrayHelper::merge(parent::behaviors(), [

            HasStorageFile::className() =>
            [
                'class'     => HasStorageFile::className(),
                'fields'    => ['image_id', 'image_full_id']
            ],

            HasStorageFileMulti::className() =>
            [
                'class'         => HasStorageFileMulti::className(),
                'relations'     => ['images', 'files']
            ],

            Implode::className() =>
            [
                'class' => Implode::className(),
                "fields" =>  [
                    "tree_menu_ids"
                ]
            ],

            "implode_tree" =>
            [
                'class' => Implode::className(),
                "fields" =>  ["pids"],
                "delimetr" => self::PIDS_DELIMETR,
            ],

            HasRelatedProperties::className() =>
            [
                'class' => HasRelatedProperties::className(),
                'relatedElementPropertyClassName'   => CmsTreeProperty::className(),
                'relatedPropertyClassName'          => CmsTreeTypeProperty::className(),
            ],
        ]);
    }

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'beforeSaveTree']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'beforeSaveTree']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, 'afterUpdateTree']);
        $this->on(self::EVENT_BEFORE_DELETE, [$this, 'beforeDeleteTree']);
        $this->on(self::EVENT_AFTER_DELETE, [$this, 'afterDeleteTree']);
    }


    /**
     * Если есть дети для начала нужно удалить их всех
     * @param Event $event
     * @throws \Exception
     */
    public function beforeDeleteTree(Event $event)
    {
        if ($this->children)
        {
            foreach ($this->children as $childNode)
            {
                $childNode->delete();
            }
        }
    }

    /**
     * После удаления нужно родителя пересчитать
     * @param Event $event
     */
    public function afterDeleteTree(Event $event)
    {
        if ($this->parent)
        {
            $this->parent->processNormalize();
        }
    }

    /**
     * Изменился код
     * @param AfterSaveEvent $event
     */
    public function afterUpdateTree(AfterSaveEvent $event)
    {
        if ($event->changedAttributes)
        {
            //Если изменилось название seo_page_name
            if (isset($event->changedAttributes['code']))
            {
                $event->sender->processNormalize();
            }
        }
    }

    /**
     * Проверки и дополнения перед сохранением раздела
     * @param $event
     */
    public function beforeSaveTree($event)
    {
        if (!$this->site_code)
        {
            if ($this->parent)
            {
                $this->site_code = $this->parent->site_code;
            }
        }

        if (!$this->tree_type_id)
        {
            if ($this->parent)
            {
                $this->tree_type_id = $this->parent->tree_type_id;
            }
        }


        //Если не заполнено название, нужно сгенерить
        if (!$this->name)
        {
            $this->generateName();
        }

        if (!$this->code)
        {
            $this->generateCode();
        }
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id' => Yii::t('app', 'ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'published_at' => Yii::t('app', 'Published At'),
            'published_to' => Yii::t('app', 'Published To'),
            'priority' => Yii::t('app', 'Priority'),
            'active' => Yii::t('app', 'Active'),
            'name' => Yii::t('app', 'Name'),
            'tree_type_id'              => Yii::t('app', 'Type'),
            'redirect'          => Yii::t('app', 'Redirect'),
            'tree_menu_ids'     => Yii::t('app', 'Menu Positions'),
            'priority'          => Yii::t('app', 'Priority'),
            'code'              => Yii::t('app', 'Code'),
            'active'              => Yii::t('app', 'Active'),
            'meta_title'        => Yii::t('app', 'Meta Title'),
            'meta_keywords'         => Yii::t('app', 'Meta Keywords'),
            'meta_description'  => Yii::t('app', 'Meta Description'),
            'description_short' => Yii::t('app', 'Description Short'),
            'description_full' => Yii::t('app', 'Description Full'),
            'description_short_type' => Yii::t('app', 'Description Short Type'),
            'description_full_type' => Yii::t('app', 'Description Full Type'),
            'image_id' => Yii::t('app', 'Main Image (announcement)'),
            'image_full_id' => Yii::t('app', 'Main Image'),
            'images' => Yii::t('app', 'Images'),
            'files' => Yii::t('app', 'Files'),
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
            [['priority', 'tree_type_id', 'image_id', 'image_full_id'], 'integer'],
            [['tree_menu_ids'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['meta_title', 'meta_description', 'meta_keywords'], 'string'],
            [['meta_title'], 'string', 'max' => 500],
            [['site_code'], 'string', 'max' => 15],
            [['pid', 'code'], 'unique', 'targetAttribute' => ['pid', 'code'], 'message' => \Yii::t('app','For this subsection of the code is already in use.')],
            [['pid', 'code'], 'unique', 'targetAttribute' => ['pid', 'code'], 'message' => \Yii::t('app','The combination of Code and Pid has already been taken.')],

            ['description_short_type', 'string'],
            ['description_full_type', 'string'],
            ['description_short_type', 'default', 'value' => "text"],
            ['description_full_type', 'default', 'value' => "text"],
        ]);
    }


    /**
     *
     * Корневые разделы дерева.
     *
     * @return ActiveQuery
     */
	static public function findRoots()
	{
		return static::find()->where(['level' => 0])->orderBy(["priority" => SORT_ASC]);
	}


    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->redirect)
        {
            return $this->redirect;
        }

        return ($this->site ? $this->site->url : "") . Url::to(['/cms/tree/view', 'model' => $this]);
    }


    /**
     * @return string
     */
    public function getAbsoluteUrl()
    {
        return $this->url;
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
     * @return ActiveQuery
     */
    public function getCmsSiteRelation()
    {
        return $this->hasOne(CmsSite::className(), ['code' => 'site_code']);
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


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFullImage()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_full_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(StorageFile::className(), ['id' => 'storage_file_id'])
            ->via('cmsTreeImages');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(StorageFile::className(), ['id' => 'storage_file_id'])
            ->via('cmsTreeFiles');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsTreeFiles()
    {
        return $this->hasMany(CmsTreeFile::className(), ['tree_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsTreeImages()
    {
        return $this->hasMany(CmsTreeImage::className(), ['tree_id' => 'id']);
    }














        //Работа с деревом

    /**
     * @param null $depth
     * @return array
     */
    public function getParentsIds($depth = null)
    {
        return (array) $this->pids;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(static::className(), ['id' => 'pid']);
    }

    /**
     *
     * To get root of a node:
     *
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoot()
    {
        $tableName = $this->tableName();
        $id = $this->getParentsIds();
        $id = $id[0];
        $query = $this->find()
            ->andWhere(["{$tableName}.[[" . $this->primaryKey()[0] . "]]" => $id]);
        $query->multiple = false;
        return $query;
    }

    /**
     * @param int|null $depth
     * @return \yii\db\ActiveQuery
     * @throws Exception
     */
    public function getParents($depth = null)
    {
        $tableName = $this->tableName();
        $ids = $this->getParentsIds($depth);
        $query = $this->find()
            ->andWhere(["{$tableName}.[[" . $this->primaryKey()[0] . "]]" => $ids]);
        $query->multiple = true;
        return $query;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        $result = $this->hasMany($this->className(), ["pid" => "id"]);
        $result->orderBy(["priority" => SORT_ASC]);

        return $result;
    }


    /**
     * @return \yii\db\ActiveQuery
     * @throws NotSupportedException
     */
    public function getPrev()
    {
        $tableName = $this->tableName();
        $query = $this->find()
            ->andWhere([
                'and',
                ["{$tableName}.[[pid]]" => $this->pid],
                ['<', "{$tableName}.[[priority]]", $this->priority],
            ])
            ->orderBy(["{$tableName}.[[priority]]" => SORT_ASC])
            ->limit(1);
        $query->multiple = false;
        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws NotSupportedException
     */
    public function getNext()
    {
        $tableName = $this->tableName();
        $query = $this->find()
            ->andWhere([
                'and',
                ["{$tableName}.[[pid]]" => $this->pid],
                ['>', "{$tableName}.[[priority]]", $this->priority],
            ])
            ->orderBy(["{$tableName}.[[priority]]" => SORT_ASC])
            ->limit(1);
        $query->multiple = false;
        return $query;
    }







        //Манипуляции с деревом


    /**
     * Автоматическая генерация названия раздела
     * @return $this
     */
    public function generateName()
    {
        $lastTree = $this->find()->orderBy(["id" => SORT_DESC])->one();
        $this->setAttribute("name", "pk-" . $lastTree->primaryKey);

        return $this;
    }

    /**
     * Автоматическая генерация code по названию
     * @return $this
     */
    public function generateCode()
    {
        if ($this->isRoot())
        {
            $this->setAttribute("code", null);
        } else
        {
            $filter     = new FilterSeoPageName();
            $newName    = $filter->filter($this->name);

            if (Validate::validate(new TreeSeoPageName($this), $newName)->isInvalid())
            {
                $newName    = $filter->filter($newName . "-" . substr(md5(uniqid() . time()), 0, 4));

                if (!Validate::validate(new TreeSeoPageName($this), $newName)->isValid())
                {
                    $this->generateName();
                }
            }

            $this->setAttribute("code", $newName);
        }

        return $this;
    }

    /**
     *
     * Обновление всего дерева ниже, и самого элемента.
     * Если найти всех рутов дерева и запустить этот метод, то дерево починиться в случае поломки
     * правильно переустановятся все dir, pids и т.д.
     *
     * @return $this
     */
    public function processNormalize()
    {
        //Если это новая несохраненная сущьность, ничего делать не надо
        if ($this->isNewRecord)
        {
            return $this;
        }

        if (!$this->pid)
        {
            $this->setAttribute("dir", null);
            $this->save(false);
        }
        else
        {
            $this->setAttributesForFutureParent($this->parent);
            $this->save(false);
        }


        //Берем детей на один уровень ниже
        if ($this->children)
        {
            $this->save(false);

            foreach ($this->children as $childModel)
            {
                $childModel->processNormalize();
            }
        }

        return $this;
    }


    /**
     * Установка атрибутов если родителем этой ноды будет новый, читаем родителя, и обновляем необходимые данные у себя
     *
     * @param Tree $parent
     * @return $this
     */
    public function setAttributesForFutureParent(Tree $parent)
    {
        //Родитель должен быть уже сохранен
        Validate::ensure(new ChainAnd([
            new NotNewRecord(),
            new NotSame($this)
        ]), $parent);

        $newPids     = $parent->pids;
        $newPids[]   = $parent->primaryKey;

        $this->setAttribute("level",     ($parent->level + 1));
        $this->setAttribute('pid',       $parent->primaryKey);
        $this->setAttribute("pids",      $newPids);


        if (!$this->name)
        {
            $this->generateName();
        }

        if (!$this->code)
        {
            //Просто генерируем pageName
            $this->generateCode();
        }

        if ($parent->dir)
        {
            $this->setAttribute("dir",       $parent->dir . Tree::PIDS_DELIMETR . $this->code);
        } else
        {
            $this->setAttribute("dir",       $this->code);
        }

        return $this;
    }


    /**
     * Создание дочерней ноды
     *
     * @param Tree $target
     * @return Tree
     * @throws Exception
     * @throws \skeeks\sx\validate\Exception
     */
    public function processCreateNode(Tree $target)
    {
        //Текущая сущьность должна быть уже сохранена
        Validate::ensure(new NotNewRecord(), $this);
        //Новая сущьность должна быть еще не сохранена
        Validate::ensure(new IsNewRecord(), $target);

        //Установка атрибутов будущему ребенку
        $target->setAttributesForFutureParent($this);
        if (!$target->save(false))
        {
            throw new Exception(\Yii::t('app',"Failed to create the child element:  ") . Json::encode($target->attributes));
        }

        $this->save(false);

        return $target;
    }


    /**
     * Процесс вставки ноды одна в другую. Можно вставлять как уже сохраненную модель с дочерними элементами, так и еще не сохраненную.
     *
     * @param Tree $target
     * @return $this
     * @throws Exception
     * @throws \skeeks\sx\validate\Exception
     */
    public function processAddNode(Tree $target)
    {
        //Текущая сущьность должна быть уже сохранена, и не равна $target
        Validate::ensure(new ChainAnd([
            new NotNewRecord(),
            new NotSame($target)
        ]), $this);

        //Если раздел который мы пытаемся добавить новый, то у него нет детей и он
        if ($target->isNewRecord)
        {
            $this->processCreateNode($target);
            return $this;
        }
        else
        {
            $target->setAttributesForFutureParent($this);
            if (!$target->save(false))
            {
                throw new Exception(\Yii::t('app',"Unable to move: ") . Json::encode($target->attributes));
            }

            $this->processNormalize();
        }

        return $this;
    }






    //TODO: is depricated 2.4
    /**
     * @return bool
     */
    public function isRoot()
    {
        return (bool) ($this->level == 0);
    }

    //TODO: is depricated 2.3.3
    /**
     * Найти непосредственных детей ноды
     * @return ActiveQuery
     */
	/*public function findChildrensAll()
	{
        $pidString = implode('/', $this->pids) . "/" . $this->primaryKey;

		return $this->find()
            ->andWhere(['like', 'pids', $pidString . '%', false])
            ->orderBy(["priority" => SORT_ASC]);
	}*/

}



