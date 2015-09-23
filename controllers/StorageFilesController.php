<?php
/**
 * StorageFilesController
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 03.11.2014
 * @since 1.0.0
 */


namespace skeeks\cms\controllers;

use skeeks\cms\Exception;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\models\CmsStorageFile;
use skeeks\sx\models\Ref;
use Yii;
use skeeks\cms\models\StorageFile;
use skeeks\cms\models\searchs\StorageFile as StorageFileSearch;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * StorageFileController implements the CRUD actions for StorageFile model.
 */
class StorageFilesController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'upload' => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    /**
     * Lists all StorageFile models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StorageFileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /*public function actionDetachFile()
    {
        $response =
        [
            'success' => false
        ];

        $request = Yii::$app->getRequest();
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($request->get("linked_to_model") && $request->get("linked_to_value") && $request->get("field") && $request->get("src"))
        {
            $ref = ModelRef::createFromData(Yii::$app->getRequest()->getQueryParams());

            /**
             * @var Game $model
            if (!$model = $ref->findModel())
            {
                throw new Exception("Не найдена сущьность к которой обавляется файл");
            }

            $model->detachFile($request->get("field"), $request->get("src"));
        }


        return $response;
    }*/
    public function actionUpload()
    {
        $response =
        [
            'success' => false
        ];

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $request = Yii::$app->getRequest();


        $dir = \skeeks\sx\Dir::runtimeTmp();

        $uploader = new \skeeks\widget\simpleajaxuploader\backend\FileUpload("imgfile");
        $file = $dir->newFile()->setExtension($uploader->getExtension());

        $originalName = $uploader->getFileName();

        $uploader->newFileName = $file->getBaseName();
        $result = $uploader->handleUpload($dir->getPath() . DIRECTORY_SEPARATOR);

        if (!$result)
        {
            $response["msg"] = $uploader->getErrorMsg();
            return $result;

        } else {

            $storageFile = Yii::$app->storage->upload($file, array_merge(
                [
                    "name" => "",
                    "original_name" => $originalName
                ]
            ));



            if ($request->get('modelData') && is_array($request->get('modelData')))
            {
                $storageFile->setAttributes($request->get('modelData'));
            }

            $storageFile->save(false);

            if ($group = $request->get("group")) {

                /**
                 *
                 * @var \skeeks\cms\models\helpers\ModelFilesGroup $group
                 */
                $group = $model->getFilesGroups()->getComponent($group);
                if ($group) {
                    try {
                        $group->attachFile($storageFile)->save();
                    } catch (\yii\base\Exception $e) {
                        $response["msgError"] = $e->getMessage();
                    }
                }
            }


            $response["success"] = true;
            $response["file"] = $storageFile;
            return $response;
        }

        return $response;
    }

    public function actionRemoteUpload()
    {
        $response =
            [
                'success' => false
            ];

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        $get = Yii::$app->getRequest();

        $request = Yii::$app->getRequest();

        if (\Yii::$app->request->post('link'))
        {
            $storageFile = Yii::$app->storage->upload(\Yii::$app->request->post('link'), array_merge(
                [
                    "name"          => isset($model->name) ? $model->name : "",
                    "original_name" => basename($post['link'])
                ]
            ));



            if ($request->post('modelData') && is_array($request->post('modelData')))
            {
                $storageFile->setAttributes($request->post('modelData'));
            }

            $storageFile->save(false);


            if ($group = \Yii::$app->request->post("group"))
            {
                /**
                 *
                 * @var \skeeks\cms\models\helpers\ModelFilesGroup $group
                 */
                $group = $model->getFilesGroups()->getComponent($group);
                if ($group)
                {
                    try
                    {
                        $group->attachFile($storageFile)->save();
                    } catch (\yii\base\Exception $e)
                    {
                        $response["msgError"]  = $e->getMessage();
                    }
                }
            }

            $response["success"]  = true;
            $response["file"]     = $storageFile;
            return $response;
        }

        return $response;
    }


    /**
     * Прикрепить к моделе другой файл
     * @see skeeks\cms\widgets\formInputs\StorageImage
     * @return RequestResponse
     */
    public function actionLinkToModel()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            try
            {
                if (!\Yii::$app->request->post('file_id') || !\Yii::$app->request->post('modelId') || !\Yii::$app->request->post('modelClassName') || !\Yii::$app->request->post('modelAttribute'))
                {
                    throw new \yii\base\Exception("Не достаточно входных данных");
                }

                $file = CmsStorageFile::findOne(\Yii::$app->request->post('file_id'));
                if (!$file)
                {
                    throw new \yii\base\Exception("Возможно файл уже удален или не загрузился");
                }

                if (!is_subclass_of(\Yii::$app->request->post('modelClassName'), ActiveRecord::className()))
                {
                    throw new \yii\base\Exception("Невозможно привязать файл к этой моделе");
                }

                $className = \Yii::$app->request->post('modelClassName');
                /**
                 * @var $model ActiveRecord
                 */
                $model = $className::findOne(\Yii::$app->request->post('modelId'));
                if (!$model)
                {
                    throw new \yii\base\Exception("Модель к которой необходимо привязать файл не найдена");
                }

                if (!$model->hasAttribute(\Yii::$app->request->post('modelAttribute')))
                {
                    throw new \yii\base\Exception("У модели не найден атрибут привязки файла: " . \Yii::$app->request->post('modelAttribute'));
                }

                //Удаление старого файла
                if ($oldFileId = $model->{\Yii::$app->request->post('modelAttribute')})
                {
                    /**
                     * @var $oldFile CmsStorageFile
                     * @var $file CmsStorageFile
                     */
                    $oldFile = CmsStorageFile::findOne($oldFileId);
                    $oldFile->delete();
                }

                $model->{\Yii::$app->request->post('modelAttribute')} = $file->id;
                if (!$model->save(false))
                {
                    throw new \yii\base\Exception("Не удалось сохранить модель");
                }

                $file->name = $model->name;
                $file->save(false);

                $rr->success = true;
                $rr->message = "";

            } catch(\Exception $e)
            {
                $rr->success = false;
                $rr->message = $e->getMessage();
            }

        }

        return $rr;
    }

    /**
     * Прикрепить к моделе другой файл
     * @see skeeks\cms\widgets\formInputs\StorageImage
     * @return RequestResponse
     */
    public function actionLinkToModels()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            try
            {
                if (!\Yii::$app->request->post('file_id') || !\Yii::$app->request->post('modelId') || !\Yii::$app->request->post('modelClassName') || !\Yii::$app->request->post('modelRelation'))
                {
                    throw new \yii\base\Exception("Не достаточно входных данных");
                }

                $file = CmsStorageFile::findOne(\Yii::$app->request->post('file_id'));
                if (!$file)
                {
                    throw new \yii\base\Exception("Возможно файл уже удален или не загрузился");
                }

                if (!is_subclass_of(\Yii::$app->request->post('modelClassName'), ActiveRecord::className()))
                {
                    throw new \yii\base\Exception("Невозможно привязать файл к этой моделе");
                }

                $className = \Yii::$app->request->post('modelClassName');
                /**
                 * @var $model ActiveRecord
                 */
                $model = $className::findOne(\Yii::$app->request->post('modelId'));
                if (!$model)
                {
                    throw new \yii\base\Exception("Модель к которой необходимо привязать файл не найдена");
                }

                if (!$model->hasProperty(\Yii::$app->request->post('modelRelation')))
                {
                    throw new \yii\base\Exception("У модели не найден атрибут привязки к файлам modelRelation: " . \Yii::$app->request->post('modelRelation'));
                }

                try
                {
                    $model->link(\Yii::$app->request->post('modelRelation'), $file);

                    if (!$file->name)
                    {
                        $file->name = $model->name;
                        $file->save(false);
                    }

                    $rr->success = true;
                    $rr->message = "";
                } catch(\Exception $e)
                {
                    $rr->success = false;
                    $rr->message = $e->getMessage();
                }





            } catch(\Exception $e)
            {
                $rr->success = false;
                $rr->message = $e->getMessage();
            }

        }

        return $rr;
    }
}
