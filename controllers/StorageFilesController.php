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
use skeeks\cms\models\helpers\ModelRef;
use skeeks\sx\models\Ref;
use Yii;
use skeeks\cms\models\StorageFile;
use skeeks\cms\models\searchs\StorageFile as StorageFileSearch;
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

    public function actionDetachFile()
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
             */
            if (!$model = $ref->findModel())
            {
                throw new Exception("Не найдена сущьность к которой обавляется файл");
            }

            $model->detachFile($request->get("field"), $request->get("src"));
        }


        return $response;
    }
    public function actionUpload()
    {
        $response =
        [
            'success' => false
        ];

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $request = Yii::$app->getRequest();
        if ($request->get("linked_to_model") && $request->get("linked_to_value") && $request->get("field"))
        {

            $ref = ModelRef::createFromData(Yii::$app->getRequest()->getQueryParams());

            /**
             * @var \common\models\Game $model
             */
            if (!$model = $ref->findModel())
            {
                throw new Exception("Не найдена сущьность к которой обавляется файл");
            }

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

            } else
            {

                $storageFile = Yii::$app->storage->upload($file, array_merge(
                    [
                        "name"          => isset($model->name) ? $model->name : "",
                        "original_name" => $originalName
                    ]
                ));

                $model->appendFile($storageFile, $request->get("field"));

                $response["success"]  = true;
                $response["file"]     = $storageFile;
                return $response;
            }
        }

        return $response;
    }
}
