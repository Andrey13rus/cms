<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.03.2015
 */
namespace skeeks\cms\console\controllers;

use skeeks\cms\base\console\Controller;
use skeeks\cms\models\User;
use skeeks\cms\modules\admin\components\UrlRule;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\rbac\AuthorRule;
use skeeks\sx\Dir;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Работа с базоый данных mysql
 *
 * @package skeeks\cms\controllers
 */
class DbController extends Controller
{
    /**
     * Просмотр созданных бекапов баз данных
     */
    public function actionDumpList()
    {
        if (!\Yii::$app->dbDump->backupDir->isExist() || !$files = \Yii::$app->dbDump->backupDir->findFiles())
        {
            $this->stdoutN('Бэкапов не найдено');
            return;
        }

        $this->stdoutN('Найдено бэкапов баз данных: ' . count($files));

        foreach ($files as $file)
        {
            $this->stdoutN($file->getBaseName() . " (" . $file->size()->toString() . ")");
        }
    }

    /**
     * Установить базу данных из бэкап файла
     */
    public function actionFirstDumpRestore()
    {
        \Yii::$app->dbDump->firstDumpRestore();
    }

    /**
     * Установить базу данных из бэкап файла
     */
    public function actionDumpRestore($fileName)
    {
        \Yii::$app->dbDump->dumpRestore($fileName);
    }

    /**
     * Инвалидация кэша стуктуры базы данных
     */
    public function actionDbRefresh()
    {
        \Yii::$app->db->getSchema()->refresh();
    }

    /**
     * Проведение всех миграций всех подключенных расширений
     */
    public function actionApplyMigrations()
    {
        $tmpMigrateDir = \Yii::getAlias('@runtime/db-migrate');

        FileHelper::removeDirectory($tmpMigrateDir);
        FileHelper::createDirectory($tmpMigrateDir);

        if (!is_dir($tmpMigrateDir))
        {
            $this->stdoutN('could not create a temporary directory migration');
        }

        $this->stdoutN('Tmp migrate dir is ready');
        $this->stdoutN('Copy migrate files');

        if ($dirs = $this->_findMigrationDirs())
        {
            foreach ($dirs as $path)
            {
                FileHelper::copyDirectory($path, $tmpMigrateDir);
            }
        }

        $appMigrateDir = \Yii::getAlias("@console/migrations");
        if (is_dir($appMigrateDir))
        {
            FileHelper::copyDirectory($appMigrateDir, $tmpMigrateDir);
        }


        $cmd = "php yii migrate --migrationPath=" . $tmpMigrateDir . '  --interactive=0';
        $this->systemCmdRoot($cmd);

        /*$cmd = "php yii migrate --interactive=0";
        $this->systemCmdRoot($cmd);*/

        \Yii::$app->db->getSchema()->refresh();
    }

    /**
     * Найти все дирриктории проекта, и всех расширений, где есть файлы миграций
     */
    public function actionFindMigrationDirs()
    {
        print_r($this->_findMigrationDirs());
    }

    /**
     * Найти все дирриктории проекта, и всех расширений, где могут быть файлы
     */
    public function actionFindMigrationPossibleDirs()
    {
        print_r($this->_findMigrationPossibleDirs());
    }

    /**
     * @return array
     */
    private function _findMigrationDirs()
    {
        $result = [];

        foreach ($this->_findMigrationPossibleDirs() as $migrationPath)
        {
            if (is_dir($migrationPath))
            {
                $result[] = $migrationPath;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function _findMigrationPossibleDirs()
    {
        $result = [];

        foreach (\Yii::$app->extensions as $code => $data)
        {
            if ($data['alias'])
            {
                foreach ($data['alias'] as $code => $path)
                {
                    $migrationsPath = $path . '/migrations';
                    $result[] = $migrationsPath;


                }
            }
        }

        return $result;
    }
}