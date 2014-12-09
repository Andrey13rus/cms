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

use skeeks\cms\models\behaviors\HasPageOptions;
use skeeks\cms\models\behaviors\SeoPageName;
use skeeks\cms\models\behaviors\traits\TreeBehaviorTrait;
use skeeks\cms\models\behaviors\TreeBehavior;
use Yii;
use yii\db\ActiveQuery;

/**
 *
 * @property string $type
 *
 * Class Tree
 * @package skeeks\cms\models
 */
class Tree extends PageAdvanced
{
    use TreeBehaviorTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_tree}}';
    }

    /**
     * @return static
     */
    static public function findCurrentRoot()
    {
        if ($site = \Yii::$app->currentSite->get())
        {
            return self::find()->where(['id' => $site->cms_tree_id])->one();
        } else {
            return self::findDefaultRoot();
        }
    }

    /**
     * Нода по умолчанию, задается для всех сайтов проекта.
     * @return static
     */
    static public function findDefaultRoot()
    {
        return self::find()->where(['main_root' => 1])->one();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $result = [];
        foreach ($behaviors as $key => $behavior) {
            if ($behavior != SeoPageName::className()) {
                $result[$key] = $behavior;
            }
        }

        $result[] = TreeBehavior::className();
        $result[HasPageOptions::className()] = HasPageOptions::className();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'type' => Yii::t('app', 'Tree type'),
            'pid_main' => Yii::t('app', 'Pid main'),
            'page_options' => Yii::t('app', 'Page Options'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['type'], 'string'],
            [['pid_main', 'priority'], 'integer'],
            [['page_options', 'multiPageOptions'], 'safe'],
        ]);
    }

    public function createAbsoluteUrl()
    {
        return $this->createUrl();
    }

    /**
     * @return string
     */
    public function createUrl()
    {
        $sites = Site::getAllKeyTreeId();
        if ($this->isRoot())
        {
            $site = $sites[$this->id];
        } else
        {
            $site = $sites[$this->getPidMain()];
        }

        if ($site) {
            if ($this->getDir()) {

                return $site->getBaseUrl() . DIRECTORY_SEPARATOR . $this->getDir() . (\Yii::$app->urlManager->suffix ? \Yii::$app->urlManager->suffix : '');
            } else {
                return $site->getBaseUrl();
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
     * @return null|ModelType
     */
    public function getType()
    {
        if ($this->type)
        {
            return \Yii::$app->registeredModels->getDescriptor($this)->getTypes()->getComponent($this->type);
        }

        return null;
    }



}
