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
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel \skeeks\cms\models\Search */
/* @var $dataProvider yii\data\ActiveDataProvider */

$dataProvider->query->orderBy('published_at DESC');
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

        'name',

        //['class' => \skeeks\cms\grid\LinkedToType::className()],
        //['class' => \skeeks\cms\grid\LinkedToModel::className()],

        //['class' => \skeeks\cms\grid\DescriptionShortColumn::className()],
        //['class' => \skeeks\cms\grid\DescriptionFullColumn::className()],

        ['class' => \skeeks\cms\grid\CreatedAtColumn::className()],
        //['class' => \skeeks\cms\grid\UpdatedAtColumn::className()],
        ['class' => \skeeks\cms\grid\PublishedAtColumn::className()],

        ['class' => \skeeks\cms\grid\CreatedByColumn::className()],
        //['class' => \skeeks\cms\grid\UpdatedByColumn::className()],
    ],
]); ?>
