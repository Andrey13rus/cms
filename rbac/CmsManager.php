<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 20.03.2015
 */
namespace skeeks\cms\rbac;
/**
 * Class CmsManager
 * @package skeeks\cms\rbac
 */
class CmsManager extends \yii\rbac\DbManager
{
    /**
     * Доступ к админке
     */
    const PERMISSION_ADMIN_ACCESS       = 'cms.admin-access';
    /**
     * Понель управления сайтом из сайтовой части
     */
    const PERMISSION_CONTROLL_PANEL     = 'cms.controll-panel-access';


    const PERMISSION_ALLOW_EDIT         = 'cms.allow-edit';

    /**
     * Редактирование служебных данных (наприме id и url и т.д.)
     */
    const PERMISSION_ALLOW_EDIT_SYSTEM       = 'cms.allow-edit-system';


    const ROLE_ROOT     = 'root';
    const ROLE_ADMIN    = 'admin';
    const ROLE_MANGER   = 'manager';
    const ROLE_EDITOR   = 'editor';
    const ROLE_USER     = 'user';

    static public function protectedRoles()
    {
        return [
            static::ROLE_ROOT,
            static::ROLE_ADMIN,
            static::ROLE_MANGER,
            static::ROLE_EDITOR,
            static::ROLE_USER,
        ];
    }

    static public function protectedPermissions()
    {
        return [
            static::PERMISSION_ADMIN_ACCESS,
            static::PERMISSION_CONTROLL_PANEL,
            static::PERMISSION_ALLOW_EDIT_SYSTEM,
        ];
    }
}