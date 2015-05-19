<?php
/**
 * StorageFile
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 26.02.2015
 */

namespace skeeks\cms\models;
use skeeks\cms\base\db\ActiveRecord;

use skeeks\cms\components\storage\ClusterLocal;
use skeeks\cms\models\behaviors\CanBeLinkedToModel;
use skeeks\cms\models\behaviors\HasDescriptionsBehavior;
use skeeks\cms\models\behaviors\HasFiles;
use skeeks\cms\models\behaviors\TimestampPublishedBehavior;
use skeeks\cms\models\helpers\ModelFilesGroup;
use skeeks\cms\validators\HasBehavior;
use skeeks\sx\validate\Validate;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

use skeeks\sx\models\Ref;
use yii\base\Event;

/**
 * This is the model class for table "{{%cms_storage_file}}".
 *
 * @property integer $id
 * @property string $src
 * @property string $cluster_id
 * @property string $cluster_file
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $size
 * @property string $type
 * @property string $mime_type
 * @property string $extension
 * @property string $original_name
 * @property string $name_to_save
 * @property string $name
 * @property integer $status
 * @property string $description_short
 * @property string $description_full
 * @property integer $image_height
 * @property integer $image_width
 * @property integer $count_comment
 * @property integer $count_subscribe
 * @property string $users_subscribers
 * @property integer $count_vote
 * @property integer $result_vote
 * @property string $users_votes_up
 * @property string $users_votes_down
 * @property string $linked_to_model
 * @property string $linked_to_value
 *
 * @property User $updatedBy
 * @property User $createdBy
 */
class StorageFile extends Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_storage_file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            [['src'], 'required'],
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'size', 'status', 'image_height', 'image_width', 'count_comment', 'count_subscribe', 'count_vote', 'result_vote', 'published_at'], 'integer'],
            [['description_short', 'description_full', 'users_subscribers', 'users_votes_up', 'users_votes_down'], 'string'],
            [['src', 'cluster_file', 'original_name', 'name', 'linked_to_model', 'linked_to_value'], 'string', 'max' => 255],
            [['cluster_id', 'type', 'mime_type', 'extension'], 'string', 'max' => 16],
            [['name_to_save'], 'string', 'max' => 32],
            [['src'], 'unique'],
            [['cluster_id', 'cluster_file'], 'unique', 'targetAttribute' => ['cluster_id', 'cluster_file'], 'message' => 'The combination of Cluster ID and Cluster Src has already been taken.'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id' => Yii::t('app', 'ID'),
            'src' => Yii::t('app', 'Src'),
            'cluster_id' => Yii::t('app', 'Хранилище'),
            'cluster_file' => Yii::t('app', 'Cluster File'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'size' => Yii::t('app', 'Размер файла'),
            'type' => Yii::t('app', 'Type'),
            'mime_type' => Yii::t('app', 'Тип файла'),
            'extension' => Yii::t('app', 'Расширение'),
            'original_name' => Yii::t('app', 'Оригинальное название файла'),
            'name_to_save' => Yii::t('app', 'Название при скачивании'),
            'name' => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
            'description_short' => Yii::t('app', 'Description Short'),
            'description_full' => Yii::t('app', 'Description Full'),
            'image_height' => Yii::t('app', 'Высота изображения'),
            'image_width' => Yii::t('app', 'Ширина изображения'),
            'count_comment' => Yii::t('app', 'Count Comment'),
            'count_subscribe' => Yii::t('app', 'Count Subscribe'),
            'users_subscribers' => Yii::t('app', 'Users Subscribers'),
            'count_vote' => Yii::t('app', 'Count Vote'),
            'result_vote' => Yii::t('app', 'Result Vote'),
            'users_votes_up' => Yii::t('app', 'Users Votes Up'),
            'users_votes_down' => Yii::t('app', 'Users Votes Down'),
            'linked_to_model' => Yii::t('app', 'Привязка'),
            'linked_to_value' => Yii::t('app', 'Linked To Value'),
            'published_at'  => Yii::t('app', 'Дата публикации'),
        ]);
    }


    //['status', 'default', 'value' => self::STATUS_ACTIVE],

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const TYPE_FILE     = "file";
    const TYPE_IMAGE    = "image";


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            behaviors\HasStatus::className() => behaviors\HasStatus::className(),
            CanBeLinkedToModel::className() => CanBeLinkedToModel::className(),
            TimestampPublishedBehavior::className() => TimestampPublishedBehavior::className()
        ]);
    }


    /**
     * @inheritdoc
     */
    public static function findIdentity($src)
    {
        return static::findOne(['src' => $src]);
    }

    /**
     * Найти сущьность к которой привязан файл.
     *
     * @return null|ActiveRecord
     */
    public function getLinkedToModel()
    {
        return $this->findLinkedToModel();
    }

    /**
     * Ресурсозатратно, но пока так...
     * Получение всех групп к которым привязан файл.
     *
     * @return ModelFilesGroup[]
     */
    public function getFilesGroups()
    {
        $result = [];
        //Если этот файл вообще имеет привязку
        if ($this->isLinked())
        {
            //Найдена модель к которой привязан файл
            if ($toModel = $this->getLinkedToModel())
            {
                if (Validate::validate(new HasBehavior(HasFiles::className()), $toModel)->isValid())
                {
                    if ($groups = $toModel->getFilesGroups()->all())
                    {
                        /**
                         * @var $filesGroup ModelFilesGroup
                         */
                        foreach ($groups as $key => $filesGroup)
                        {
                            if ($filesGroup->hasFile($this))
                            {
                                $result[$key] = $filesGroup;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
    /**
     * @return bool|int
     * @throws \Exception
     */
    public function delete()
    {
        //Сначала удалить файл
        try
        {
            $cluster = $this->cluster();

            $cluster->deleteTmpDir($this->cluster_file);
            $cluster->delete($this->cluster_file);

        } catch (\common\components\storage\Exception $e)
        {
            return false;
        }

        return parent::delete();
    }

    /**
     * @return $this
     */
    public function deleteTmpDir()
    {
        $cluster = $this->cluster();
        $cluster->deleteTmpDir($this->cluster_file);

        return $this;
    }

    /**
     * Тип файла - первая часть mime_type
     * @return string
     */
    public function getFileType()
    {
        $dataMimeType = explode('/', $this->mime_type);
        return (string) $dataMimeType[0];
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        if ($this->getFileType() == 'image')
        {
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * @return \skeeks\cms\components\storage\Cluster
     */
    public function cluster()
    {
        return \Yii::$app->storage->getCluster($this->cluster_id);
    }

    /**
     * TODO: Переписать нормально
     * Обновление информации о файле
     *
     * @return $this
     */
    public function updateFileInfo()
    {
        $src = $this->src;

        if ($this->cluster() instanceof ClusterLocal)
        {
            if (!\Yii::$app->request->hostInfo)
            {
                return $this;
            }

            $src = \Yii::$app->request->hostInfo . $this->src;
        }
        //Елси это изображение
        if ($this->isImage())
        {
            if (extension_loaded('gd'))
            {
                list($width, $height, $type, $attr) = getimagesize($src);
                $this->image_height       = $height;
                $this->image_width        = $width;
            }
        }

        $this->save();
        return $this;
    }

}
