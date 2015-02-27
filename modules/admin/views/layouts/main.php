<?php
use skeeks\cms\modules\admin\assets\AdminAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use skeeks\cms\App;
use skeeks\cms\helpers\UrlHelper;

/* @var $this \yii\web\View */
/* @var $content string */

AdminAsset::register($this);

$sidebarHidden = \Yii::$app->user->getIsGuest();

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="icon" href="http://skeeks.com/favicon.ico"  type="image/x-icon" />
    <?php $this->head() ?>
</head>
<body class="<?= $sidebarHidden ? "sidebar-hidden" : ""?>">


<?php $this->beginBody() ?>
<div class="navbar sx-navbar" role="navigation">
    <!--<div class="navbar-header">
        <?/*= Html::a('<i class="fa fa-lightbulb-o"></i> <span>Logo</span>', \Yii::$app->cms->moduleAdmin()->createUrl(["admin/index/index"]), ["class" => "navbar-brand"]); */?>
    </div>-->

    <? if (!$sidebarHidden): ?>

    <ul class="nav navbar-nav navbar-actions navbar-left">
        <li class="visible-md visible-lg"><a href="#" id="main-menu-toggle" data-sx-widget="tooltip" data-original-title="Открыть закрыть левое меню"><i class="fa fa-bars"></i></a></li>
        <li class="visible-xs visible-sm"><a href="#" id="sidebar-menu"><i class="fa fa-bars"></i></a></li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <? if (Yii::$app->cms->getAuthUser()->hasMainImage()) : ?>
                    <img src="<?= Yii::$app->cms->getAuthUser()->getAvatarSrc(); ?>" width="49" height="49"/>
                <? else : ?>
                    <i class="icon-settings"></i>
                <? endif; ?>
            </a>
            <ul class="dropdown-menu sx-dropdown-menu-left">
                <li class="dropdown-menu-header text-center">
                    <strong><?= Yii::$app->cms->getAuthUser()->username ?><?= Yii::$app->cms->getAuthUser()->getMainImageSrc() ?></strong>
                </li>
                <li><a href="<?= UrlHelper::construct("cms/admin-profile")->enableAdmin() ?>"><i class="glyphicon glyphicon-user"></i> Профиль</a></li>
                <!--<li><a href="#"><i class="fa fa-envelope-o"></i> Сообщения <span class="label label-info">42</span></a></li>-->
                <li class="divider"></li>
                <li>
                    <?= Html::a('<i class="fa fa-lock"></i> Выход', UrlHelper::construct("admin/auth/logout")->enableAdmin()->setCurrentRef(), ["data-method" => "post"])?>
                </li>
            </ul>
        </li>

    </ul>

    <? endif; ?>

    <!--<ul class="nav navbar-nav visible-md visible-lg">
        <li>&nbsp;</li>
        <li>
            <?/*= Html::button('Перейти на сайт', [
                'class' => 'btn btn-default',
                'onclick' => 'sx.helpers.Url.redirect("' . \yii\helpers\Url::home() . '"); return false;'
            ])*/?>
        </li>
    </ul>-->

    <ul class="nav navbar-nav navbar-right visible-md visible-lg">
        <!--<li><span class="timer"><i class="icon-clock"></i> <span id="clock"></span></span></li>-->
        <li class="dropdown visible-md visible-lg"></li>
        <? if (!Yii::$app->user->isGuest): ?>

            <? if ($sites = \skeeks\cms\models\Site::getAll()) : ?>
                <li>
                    <div class="btn-group">
                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <?= \Yii::$app->cms->moduleAdmin()->getCurrentSite() ? \Yii::$app->cms->moduleAdmin()->getCurrentSite()->host_name : 'Сайт'?> <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" role="menu">
                          <? if (\Yii::$app->cms->moduleAdmin()->getCurrentSite()) : ?>
                              <li>
                                <a href="<?= UrlHelper::construct('admin/admin-system/session')->enableAdmin()->set('site', ''); ?>" data-method="post">
                                    Сбросить сайт
                                </a>
                              </li>
                          <? endif; ?>

                        <? foreach ($sites as $site) : ?>
                            <li>
                                <a href="<?= UrlHelper::construct('admin/admin-system/session')->enableAdmin()->set('site', $site->primaryKey); ?>" data-method="post">
                                    <?= $site->host_name; ?> (<?= $site->name; ?>)
                                </a>
                            </li>
                        <? endforeach; ?>

                      </ul>
                    </div>
                </li>
            <? endif; ?>

            <? if ($langs = \Yii::$app->langs->getComponents()) : ?>
                <li>
                    <div class="btn-group">
                      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <?= \Yii::$app->cms->moduleAdmin()->getCurrentLang() ? \Yii::$app->cms->moduleAdmin()->getCurrentLang()->name . ' (' . \Yii::$app->cms->moduleAdmin()->getCurrentLang()->id . ')' : 'Язык'?> <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" role="menu">

                          <? if (\Yii::$app->cms->moduleAdmin()->getCurrentLang()) : ?>
                              <li>
                                <a href="<?= UrlHelper::construct('admin/admin-system/session')->enableAdmin()->set('lang', ''); ?>" data-method="post">
                                    Сбросить язык
                                </a>
                              </li>
                          <? endif; ?>

                        <? foreach ($langs as $lang) : ?>
                            <li><a href="<?= UrlHelper::construct('admin/admin-system/session')->enableAdmin()->set('lang', $lang->id); ?>" data-method="post"><?= $lang->name; ?> (<?= $lang->id; ?>)</a></li>
                        <? endforeach; ?>
                      </ul>
                    </div>
                </li>
            <? endif; ?>


        <li class="dropdown visible-md visible-lg">
            <a href="/" style="width: auto;" target="_blank">Перейти на сайт →</a>
        </li>
        <? endif; ?>
    </ul>

    <?php
/*        NavBar::begin([
            'brandLabel' => 'My Company',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        $menuItems = [
            ['label' => 'Home', 'url' => ['/site/index']],
        ];
        if (Yii::$app->user->isGuest) {
            $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
        } else {
            $menuItems[] = [
                'label' => 'Logout (' . Yii::$app->user->identity->username . ')',
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post']
            ];
        }
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => $menuItems,
        ]);
        NavBar::end();
    */?>
</div>

<? if (!$sidebarHidden): ?>
<!-- start: Main Menu -->
<div class="sidebar sx-sidebar">
    <div class="inner-wrapper scrollbar-macosx">
        <div class="sidebar-collapse sx-sidebar-collapse">

            <? if ($items = \Yii::$app->adminMenu->getAllowData()) : ?>
                <? foreach ($items as $keyGroup => $groupData) : ?>

                    <? if (\yii\helpers\ArrayHelper::getValue($groupData, 'enabled' , true) === true) : ?>
                        <div class="sidebar-menu" id="sx-admin-menu-<?= $keyGroup; ?>">
                            <div class="sx-head">
                                <i class="icon icon-arrow-up" style=""></i>
                                <?= \yii\helpers\ArrayHelper::getValue($groupData, 'label', 'Название не задано'); ?>
                            </div>

                            <? if ($itemsGroup = \yii\helpers\ArrayHelper::getValue($groupData, 'items', [])) : ?>
                                <ul class="nav nav-sidebar">
                                    <? foreach ($itemsGroup as $itemData) : ?>
                                        <? if (\yii\helpers\ArrayHelper::getValue($itemData, 'enabled' , true) === true) : ?>
                                            <li <?= strpos('-' . \Yii::$app->controller->route, $itemData["url"][0]) ? 'class="active"' : '' ?>>
                                                <a href="<?= \Yii::$app->cms->moduleAdmin()->createUrl((array) $itemData["url"]) ?>" title="#" class="sx-test">
                                                    <? if ($imgData = \yii\helpers\ArrayHelper::getValue($itemData, 'img', [])) : ?>
                                                        <? list($assetClassName, $localPath) = $imgData; ?>
                                                        <span class="sx-icon">
                                                            <img src="<?= \Yii::$app->getAssetManager()->getAssetUrl($assetClassName::register($this), $localPath); ?>" />
                                                        </span>
                                                    <? else: ?>
                                                        <span class="sx-icon">
                                                            <img src="<?= \Yii::$app->getAssetManager()->getAssetUrl(AdminAsset::register($this), 'images/icons/ico_block.gif'); ?>" />
                                                        </span>
                                                    <? endif; ?>
                                                    <span class="txt"><?= $itemData["label"]; ?></span>
                                                </a>
                                            </li>
                                        <? endif; ?>
                                    <? endforeach; ?>
                                </ul>
                            <? endif; ?>

                        </div>
                    <? endif; ?>
                <? endforeach; ?>
            <? endif; ?>
        </div>
    </div>
</div>
<!-- end: Main Menu -->
<? endif; ?>



<div class="main">

<?php /*\yii\widgets\Pjax::begin([
    'id' => 'sx-pjax-global',
    'linkSelector' => 'body a',
]); */?>

    <div class="col-lg-12">
        <div class="panel panel-primary sx-panel sx-panel-content">
            <div class="panel-heading sx-no-icon">
                <h2>
                    <?= Breadcrumbs::widget([
                        'homeLink' => ['label' => \Yii::t("yii", "Home"), 'url' => [
                            'admin/index',
                            'namespace' => 'admin'
                        ]],
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    ]) ?>
                </h2>
                <div class="panel-actions">
                </div>
            </div><!-- End .panel-heading -->
            <div class="panel-body">
                    <div class="panel-content-before">
                        <?= $this->params['actions'] ?>
                        <?/*= Alert::widget() */?>
                    </div>
                    <div class="panel-content sx-unblock-onWindowReady">
                        <!--<div class="sx-show-onWindowReady">-->
                            <?= \skeeks\cms\modules\admin\widgets\Alert::widget(); ?>
                            <?= $content ?>
                        <!--</div>-->
                    </div><!-- End .panel-body -->
            </div><!-- End .panel-body -->
        </div><!-- End .widget -->

    </div><!-- End .col-lg-12  -->

    <?php /*\yii\widgets\Pjax::end(); */?>

</div>

<footer class="sx-admin-footer">
    <div class="row">
        <div class="col-sm-5">
            <?= \Yii::$app->cms->moduleCms()->getDescriptor()->getCopyright(); ?>
             | <a href="http://skeeks.com" target="_blank" data-sx-widget="tooltip" title="Перейти на сайт разработчика системы">SkeekS.com</a>
        </div><!--/.col-->
        <div class="col-sm-7 text-right">

        </div><!--/.col-->
    </div><!--/.row-->
</footer>

    <?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>
