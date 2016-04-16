<?php
/**
 * Стандартный загрузчик
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 20.02.2015
 * @since 1.0.0
 */

require(VENDOR_DIR . '/autoload.php');
require(VENDOR_DIR . '/yiisoft/yii2/Yii.php');
require(COMMON_CONFIG_DIR . '/bootstrap.php');
require(APP_CONFIG_DIR . '/bootstrap.php');

$config             = new \skeeks\cms\Config(); //добавлены пути к конфигам cms

//Автоматически созданный файл, хранит пути к конфигам всех модулей
if (ENABLE_MODULES_CONF)
{
    if (APP_TYPE == 'web')
    {
        $config->appendFiles([SKEEKS_CONFIG_DIR . '/main.php']); //добавлены пути к конфигам cms
    } else if (APP_TYPE == 'console')
    {
        $config->appendFiles([SKEEKS_CONFIG_DIR . '/main-console.php']); //добавлены пути к конфигам cms
    }

    $modulesConfigFiles = [];
    if (file_exists(AUTO_GENERATED_MODULES_FILE)) {
        $modulesConfigFiles = include AUTO_GENERATED_MODULES_FILE;
        if (isset($modulesConfigFiles[APP_TYPE]))
        {
            $modulesConfigFiles = $modulesConfigFiles[APP_TYPE];
        }
    }

    $config->appendFiles($modulesConfigFiles); //добавлены пути к конфигам всех файлов
}

$config->cacheDir   = APP_RUNTIME_DIR;
$config->cache      = CONFIG_CACHE;
$config->name       = 'config_' . APP_TYPE;

$config->appendDependency(Yii::getVersion());
$config->appendDependency(YII_ENV);
$config->appendDependency(PHP_VERSION); //кэш будет сбрасываться при редактировании файла с общим конфигом
$config->appendDependency(filemtime(COMMON_CONFIG_DIR . '/main.php')); //кэш будет сбрасываться при редактировании файла с общим конфигом
$config->appendDependency(filemtime(APP_CONFIG_DIR . '/main.php')); //кэш будет сбрасываться при редактировании файла с общим конфигом
$config->appendDependency(filemtime(COMMON_CONFIG_DIR . '/db.php')); //кэш будет сбрасываться при включении и отключении модульных конфигов


$configData = $config->getResult();

$configCommon = [];
if (file_exists(COMMON_CONFIG_DIR . '/main.php'))
{
    $configCommon = (array) include COMMON_CONFIG_DIR . '/main.php';
}

$configCommonEnv = [];
if (file_exists(COMMON_ENV_CONFIG_DIR . '/main.php'))
{
    $configCommonEnv = (array) include COMMON_ENV_CONFIG_DIR . '/main.php';
}

$configApp = [];
if (file_exists(APP_CONFIG_DIR . '/main.php'))
{
    $configApp = (array) include APP_CONFIG_DIR . '/main.php';
}

$configAppEnv = [];
if (file_exists(APP_ENV_CONFIG_DIR . '/main.php'))
{
    $configAppEnv = (array) include APP_ENV_CONFIG_DIR . '/main.php';
}

$configData = \yii\helpers\ArrayHelper::merge(
    $configData,
    $configCommon,
    $configCommonEnv,
    $configApp,
    $configAppEnv
);

return $configData;