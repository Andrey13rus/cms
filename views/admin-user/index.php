<?php
/**
 * index
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 30.10.2014
 * @since 1.0.0
 */
use skeeks\cms\modules\admin\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\searchs\Game */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>

<?= GridView::widget([
    'dataProvider'  => $dataProvider,
    'filterModel'   => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        [
            'class'         => \skeeks\cms\modules\admin\grid\ActionColumn::className(),
            'controller'    => $controller
        ],

        ['class' => \skeeks\cms\grid\ImageColumn::className()],

        'username',
        'name',


        ['class' => \skeeks\cms\grid\CreatedAtColumn::className()],

        [
            'class'     => \yii\grid\DataColumn::className(),
            'value'     => function(\skeeks\cms\models\User $model)
            {
                $result = [];

                if ($roles = \Yii::$app->authManager->getRolesByUser($model->id))
                {
                    foreach ($roles as $role)
                    {
                        $result[] = $role->name;
                    }
                }

                return implode(', ', $result);
            },
            'format' => 'html',
        ],

    ],
]); ?>
